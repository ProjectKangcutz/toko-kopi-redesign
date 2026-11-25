<?php

namespace App\Services\Shipping;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\ShippingCourier;
use App\Models\ShippingService;
use App\Services\Setting\StoreSettingService;
use App\Services\Storefront\CartService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Throwable;

class ShippingRateService
{
    public const CACHE_TTL_SECONDS = 900; // 15 menit

    public const CACHE_VERSION_KEY = 'shipping:rates:v2:version';

    public function __construct(
        protected RajaOngkirShippingClient $client,
        protected CartService $cartService,
        protected StoreSettingService $settingService,
    ) {}

    /**
     * Menghitung opsi ongkos kirim untuk keranjang aktif dan alamat milik pelanggan aktif.
     * Berat, nilai barang, origin, destinasi, dan kurir sepenuhnya dihitung di server.
     *
     * @param  array<int, string>|null  $requestedCourierCodes
     * @return array{
     *     origin_id: string,
     *     destination_id: string,
     *     actual_weight_grams: int,
     *     billable_weight_grams: int,
     *     item_value: float,
     *     courier_codes: array<int, string>,
     *     from_cache: bool,
     *     rates: array<int, array{
     *         courier_code: string,
     *         courier_name: string,
     *         service_code: string,
     *         service_name: string,
     *         description: ?string,
     *         cost: int|float,
     *         etd: ?string
     *     }>
     * }
     */
    public function calculateForCustomer(
        Customer $customer,
        CustomerAddress|int $address,
        ?array $requestedCourierCodes = null
    ): array {
        if ($customer->status !== 'active') {
            throw new AuthorizationException('Akun pelanggan Anda sedang dinonaktifkan.');
        }

        $resolvedAddress = $this->resolveOwnedAddress($customer, $address);
        $destinationId = $this->resolveDestinationId($resolvedAddress);

        $config = $this->client->resolveConfig();
        $originId = $config['origin_id'];

        $cart = $this->cartService->getCart($customer);
        if (($cart['valid_quantity'] ?? 0) <= 0 || $cart['items']->isEmpty()) {
            throw ShippingRateException::emptyCart();
        }

        $actualWeightGrams = max(0, (int) ($cart['total_weight_grams'] ?? 0));
        $billableWeightGrams = $this->calculateBillableWeightGrams($actualWeightGrams);
        $itemValue = round((float) ($cart['subtotal'] ?? 0), 2);

        $whitelist = self::configuredWhitelist();
        if ($whitelist === []) {
            throw ShippingRateException::noActiveCouriers();
        }

        $activeCouriers = ShippingCourier::query()
            ->with(['services' => static fn ($query) => $query->orderBy('id')])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->keyBy(static fn (ShippingCourier $courier): string => strtolower(trim($courier->code)))
            ->filter(static fn (ShippingCourier $courier, string $code): bool => isset($whitelist[$code]));

        if ($activeCouriers->isEmpty()) {
            throw ShippingRateException::noActiveCouriers();
        }

        $activeCodes = $activeCouriers->keys()->values()->all();
        if ($requestedCourierCodes !== null) {
            $normalizedRequested = RajaOngkirShippingClient::normalizeCourierCodes($requestedCourierCodes);
            $targetCodes = array_values(array_intersect($activeCodes, $normalizedRequested));
            if ($targetCodes === []) {
                throw ShippingRateException::noActiveCouriers();
            }
        } else {
            $targetCodes = $activeCodes;
        }

        $cacheKey = self::buildCacheKey(
            $config['base_url'],
            $originId,
            $destinationId,
            $billableWeightGrams,
            $itemValue,
            $targetCodes,
            $whitelist
        );

        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return [
                'origin_id' => $originId,
                'destination_id' => $destinationId,
                'actual_weight_grams' => $actualWeightGrams,
                'billable_weight_grams' => $billableWeightGrams,
                'item_value' => $itemValue,
                'courier_codes' => $targetCodes,
                'from_cache' => true,
                'rates' => $cached,
            ];
        }

        $rawRates = $this->client->calculateDomesticCost(
            $destinationId,
            $billableWeightGrams,
            $targetCodes
        );

        $filteredRates = $this->filterAndEnrichRates($rawRates, $activeCouriers, $targetCodes, $whitelist);

        Cache::put($cacheKey, $filteredRates, now()->addSeconds(self::CACHE_TTL_SECONDS));

        return [
            'origin_id' => $originId,
            'destination_id' => $destinationId,
            'actual_weight_grams' => $actualWeightGrams,
            'billable_weight_grams' => $billableWeightGrams,
            'item_value' => $itemValue,
            'courier_codes' => $targetCodes,
            'from_cache' => false,
            'rates' => $filteredRates,
        ];
    }

    /**
     * Memilih dan memverifikasi tarif resmi satu layanan kurir di sisi server
     * sehingga nilai ongkos kirim maupun kurir/layanan non-whitelist dari input browser tidak pernah dipercaya.
     *
     * @return array{
     *     courier_code: string,
     *     courier_name: string,
     *     service_code: string,
     *     service_name: string,
     *     description: ?string,
     *     cost: int|float,
     *     etd: ?string,
     *     origin_id: string,
     *     destination_id: string,
     *     actual_weight_grams: int,
     *     billable_weight_grams: int,
     *     from_cache: bool
     * }
     */
    public function resolveSelectedRate(
        Customer $customer,
        CustomerAddress|int $address,
        string $courierCode,
        string $serviceCode
    ): array {
        $normalizedCourier = strtolower(trim($courierCode));
        $normalizedService = self::normalizeServiceCode($serviceCode);

        if ($normalizedCourier === '' || $normalizedService === '') {
            throw ShippingRateException::rateNotFound();
        }

        $whitelist = self::configuredWhitelist();
        if (! $this->isWhitelistedService($normalizedCourier, $normalizedService, $whitelist)) {
            throw ShippingRateException::rateNotFound();
        }

        $result = $this->calculateForCustomer($customer, $address, [$normalizedCourier]);

        foreach ($result['rates'] as $rate) {
            if (
                strtolower(trim((string) $rate['courier_code'])) === $normalizedCourier
                && self::normalizeServiceCode((string) $rate['service_code']) === $normalizedService
            ) {
                return array_merge($rate, [
                    'origin_id' => $result['origin_id'],
                    'destination_id' => $result['destination_id'],
                    'actual_weight_grams' => $result['actual_weight_grams'],
                    'billable_weight_grams' => $result['billable_weight_grams'],
                    'from_cache' => $result['from_cache'],
                ]);
            }
        }

        throw ShippingRateException::rateNotFound();
    }

    /**
     * Menghitung berat tagih pengiriman berdasarkan `store.min_weight_grams` dan
     * `store.weight_rounding_grams`, dengan jaminan hasil tidak pernah kurang dari berat aktual.
     */
    public function calculateBillableWeightGrams(int $actualWeightGrams): int
    {
        $sanitizedActual = max(0, $actualWeightGrams);
        $minWeightGrams = max(1, (int) $this->settingService->get('store', 'min_weight_grams', 100));
        $roundingGrams = max(0, (int) $this->settingService->get('store', 'weight_rounding_grams', 1000));

        $baseWeight = max($sanitizedActual, $minWeightGrams);

        if ($roundingGrams <= 1) {
            return $baseWeight;
        }

        // Pembulatan ke atas kelipatan roundingGrams menggunakan aritmetika integer agar presisi.
        $multiples = intdiv($baseWeight + $roundingGrams - 1, $roundingGrams);

        return max($baseWeight, $multiples * $roundingGrams);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function configuredWhitelist(): array
    {
        $raw = config('shipping.whitelist', []);
        if (! is_array($raw)) {
            return [];
        }

        $normalized = [];

        foreach ($raw as $courierCode => $services) {
            $courier = strtolower(trim((string) $courierCode));
            if ($courier === '' || ! is_array($services)) {
                continue;
            }

            $allowedServices = [];
            foreach ($services as $service) {
                $normalizedService = self::normalizeServiceCode((string) $service);
                if ($normalizedService !== '') {
                    $allowedServices[] = $normalizedService;
                }
            }

            $allowedServices = array_values(array_unique($allowedServices));
            if ($allowedServices !== []) {
                $normalized[$courier] = $allowedServices;
            }
        }

        return $normalized;
    }

    public static function normalizeServiceCode(string $serviceCode): string
    {
        $collapsed = preg_replace('/\s+/', ' ', trim($serviceCode)) ?? '';

        return strtoupper($collapsed);
    }

    /**
     * @param  array<string, array<int, string>>|null  $whitelist
     */
    public function isWhitelistedService(string $courierCode, string $serviceCode, ?array $whitelist = null): bool
    {
        $resolvedWhitelist = $whitelist ?? self::configuredWhitelist();
        $courier = strtolower(trim($courierCode));
        $service = self::normalizeServiceCode($serviceCode);

        if ($courier === '' || $service === '' || ! isset($resolvedWhitelist[$courier])) {
            return false;
        }

        return in_array($service, $resolvedWhitelist[$courier], true);
    }

    /**
     * @param  array<int, string>  $courierCodes
     * @param  array<string, array<int, string>>|null  $whitelist
     */
    public static function buildCacheKey(
        string $baseUrl,
        string $originId,
        string $destinationId,
        int $weightGrams,
        float $itemValue,
        array $courierCodes,
        ?array $whitelist = null
    ): string {
        $sortedCouriers = RajaOngkirShippingClient::normalizeCourierCodes($courierCodes);
        sort($sortedCouriers);

        $resolvedWhitelist = $whitelist ?? self::configuredWhitelist();
        ksort($resolvedWhitelist);
        $whitelistParts = [];
        foreach ($resolvedWhitelist as $courier => $services) {
            $sortedServices = $services;
            sort($sortedServices);
            $whitelistParts[] = $courier.':'.implode('+', $sortedServices);
        }

        $version = self::cacheVersion();
        $signature = implode('|', [
            rtrim(strtolower(trim($baseUrl)), '/'),
            trim($originId),
            trim($destinationId),
            (string) $weightGrams,
            number_format($itemValue, 2, '.', ''),
            implode(',', $sortedCouriers),
            implode(';', $whitelistParts),
        ]);

        return 'shipping:rates:v2:'.$version.':'.sha1($signature);
    }

    public static function cacheVersion(): int
    {
        try {
            return max(1, (int) Cache::get(self::CACHE_VERSION_KEY, 1));
        } catch (Throwable) {
            return 1;
        }
    }

    public static function invalidateCache(): void
    {
        try {
            $nextVersion = self::cacheVersion() + 1;
            Cache::put(self::CACHE_VERSION_KEY, $nextVersion, now()->addDays(7));
        } catch (Throwable) {
            // Abaikan kegagalan cache saat bootstrap awal atau sebelum tabel cache tersedia.
        }
    }

    protected function resolveOwnedAddress(Customer $customer, CustomerAddress|int $address): CustomerAddress
    {
        if ($address instanceof CustomerAddress) {
            if ($address->trashed()) {
                throw ShippingRateException::invalidDestination('Alamat pengiriman yang dipilih sudah dihapus.');
            }

            Gate::forUser($customer)->authorize('view', $address);

            return $address;
        }

        $found = $customer->addresses()
            ->active()
            ->whereKey($address)
            ->first();

        if (! $found) {
            throw new AuthorizationException('Alamat pengiriman tidak ditemukan atau bukan milik Anda.');
        }

        return $found;
    }

    protected function resolveDestinationId(CustomerAddress $address): string
    {
        if (
            strtolower(trim((string) $address->province_id)) === 'manual'
            || strtolower(trim((string) $address->city_id)) === 'manual'
        ) {
            throw ShippingRateException::invalidDestination(
                'Alamat ini diisi secara manual dan belum memiliki ID wilayah pengiriman. Silakan pilih wilayah melalui pencarian wilayah terlebih dahulu.'
            );
        }

        $subdistrictId = trim((string) ($address->subdistrict_id ?? ''));
        if ($subdistrictId === '') {
            throw ShippingRateException::invalidDestination(
                'Alamat tujuan belum memiliki ID kelurahan/kecamatan pengiriman yang valid.'
            );
        }

        return RajaOngkirShippingClient::validateLocationId($subdistrictId, 'tujuan');
    }

    /**
     * @param  array<int, array{
     *     courier_code: string,
     *     courier_name: string,
     *     service_code: string,
     *     service_name: string,
     *     description: ?string,
     *     cost: int|float,
     *     etd: ?string
     * }>  $rawRates
     * @param  \Illuminate\Support\Collection<string, ShippingCourier>  $activeCouriers
     * @param  array<int, string>  $targetCodes
     * @param  array<string, array<int, string>>  $whitelist
     * @return array<int, array{
     *     courier_code: string,
     *     courier_name: string,
     *     service_code: string,
     *     service_name: string,
     *     description: ?string,
     *     cost: int|float,
     *     etd: ?string
     * }>
     */
    protected function filterAndEnrichRates(array $rawRates, $activeCouriers, array $targetCodes, array $whitelist): array
    {
        $deduped = [];

        foreach ($rawRates as $rate) {
            $courierCode = strtolower(trim((string) ($rate['courier_code'] ?? '')));

            if (
                ! in_array($courierCode, $targetCodes, true)
                || ! $activeCouriers->has($courierCode)
                || ! isset($whitelist[$courierCode])
            ) {
                continue;
            }

            $rawServiceCode = trim((string) ($rate['service_code'] ?? ''));
            $normalizedServiceCode = self::normalizeServiceCode($rawServiceCode);
            if (
                $normalizedServiceCode === ''
                || ! in_array($normalizedServiceCode, $whitelist[$courierCode], true)
            ) {
                continue;
            }

            /** @var ShippingCourier $courier */
            $courier = $activeCouriers->get($courierCode);

            $canonicalServiceCode = $this->canonicalDedupeServiceCode($courierCode, $normalizedServiceCode);

            /** @var ?ShippingService $matchedService */
            $matchedService = $courier->services->first(
                fn (ShippingService $service): bool => $this->canonicalDedupeServiceCode(
                    $courierCode,
                    self::normalizeServiceCode((string) $service->service_code)
                ) === $canonicalServiceCode
            );

            // Jika layanan terdaftar di master lokal tetapi dinonaktifkan admin, kecualikan dari hasil.
            if ($matchedService !== null && ! $matchedService->is_active) {
                continue;
            }

            $candidate = [
                'courier_code' => $courierCode,
                'courier_name' => $courier->name ?: $rate['courier_name'],
                'service_code' => $rawServiceCode,
                'service_name' => $matchedService?->service_name ?: $rate['service_name'],
                'description' => $rate['description'],
                'cost' => $rate['cost'],
                'etd' => $rate['etd'],
            ];

            $dedupeKey = $courierCode.':'.$canonicalServiceCode;

            if (
                ! isset($deduped[$dedupeKey])
                || (float) $candidate['cost'] < (float) $deduped[$dedupeKey]['cost']
            ) {
                $deduped[$dedupeKey] = $candidate;
            }
        }

        $filtered = array_values($deduped);

        usort($filtered, static function (array $a, array $b) use ($activeCouriers): int {
            $costComparison = (float) $a['cost'] <=> (float) $b['cost'];
            if ($costComparison !== 0) {
                return $costComparison;
            }

            $orderA = (int) ($activeCouriers->get($a['courier_code'])?->sort_order ?? PHP_INT_MAX);
            $orderB = (int) ($activeCouriers->get($b['courier_code'])?->sort_order ?? PHP_INT_MAX);
            if ($orderA !== $orderB) {
                return $orderA <=> $orderB;
            }

            $courierComparison = strcmp($a['courier_code'], $b['courier_code']);
            if ($courierComparison !== 0) {
                return $courierComparison;
            }

            return strcmp(
                self::normalizeServiceCode((string) $a['service_code']),
                self::normalizeServiceCode((string) $b['service_code'])
            );
        });

        return $filtered;
    }

    protected function canonicalDedupeServiceCode(string $courierCode, string $normalizedServiceCode): string
    {
        if ($courierCode === 'anteraja' && in_array($normalizedServiceCode, ['REG', 'REGULER'], true)) {
            return 'REG';
        }

        return $normalizedServiceCode;
    }
}
