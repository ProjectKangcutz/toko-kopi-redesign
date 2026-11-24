@props([
    'product',
    'eager' => false,
])

@php
    $primaryImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
    $pricing = $product->displayPricing();
    $effectivePrice = $pricing['effective_price'];
    $regularPrice = $pricing['regular_price'];
    $isPromo = $pricing['is_promo'];
    $startsFrom = $pricing['starts_from'];
    $discountPercent = ($isPromo && $regularPrice > 0)
        ? max(1, (int) round((($regularPrice - $effectivePrice) / $regularPrice) * 100))
        : 0;
    $availableStock = $product->availableStock();
    $validSoldCount = (int) ($product->getAttribute('valid_sold_count') ?? 0);
    $productUrl = Route::has('storefront.product')
        ? route('storefront.product', $product->slug)
        : url('/produk/'.$product->slug);
    $primaryCategory = $product->relationLoaded('categories')
        ? ($product->categories->first(fn ($cat) => (bool) ($cat->pivot?->is_primary ?? false)) ?? $product->categories->first())
        : null;
@endphp

<article class="kn-product-card h-100 d-flex flex-column overflow-hidden position-relative">
    <a href="{{ $productUrl }}" class="kn-product-media text-decoration-none">
        @if($primaryImage)
            <picture>
                @if(!empty($primaryImage->webp_path))
                    <source srcset="{{ \App\Services\Storefront\HomepageService::mediaUrl($primaryImage->webp_path) }}" type="image/webp">
                @endif
                <img
                    src="{{ \App\Services\Storefront\HomepageService::mediaUrl($primaryImage->image_path) }}"
                    alt="{{ $primaryImage->alt_text ?: $product->name }}"
                    class="mlm-product-thumb"
                    loading="{{ $eager ? 'eager' : 'lazy' }}"
                    decoding="async"
                >
            </picture>
        @else
            <div class="mlm-product-thumb d-flex align-items-center justify-content-center" style="color: var(--kn-caramel);">
                <i class="bi bi-cup-hot fs-1 opacity-50" aria-hidden="true"></i>
            </div>
        @endif

        @if($isPromo)
            <span class="badge kn-promo-badge position-absolute top-0 start-0 m-2">
                Promo @if($discountPercent > 0)-{{ $discountPercent }}%@endif
            </span>
        @endif

        @if($availableStock <= 0)
            <span class="badge kn-stock-out-badge position-absolute top-0 end-0 m-2">Stok Habis</span>
        @endif
    </a>

    <div class="p-3 d-flex flex-column flex-grow-1">
        @if($primaryCategory)
            <div class="kn-product-cat-label text-truncate mb-1">{{ $primaryCategory->name }}</div>
        @endif

        <h3 class="kn-product-title mb-2 mlm-line-clamp-2">
            <a href="{{ $productUrl }}" class="text-decoration-none text-reset stretched-link">
                {{ $product->name }}
            </a>
        </h3>

        <div class="d-flex flex-wrap align-items-center gap-2 small text-mlm-muted mb-3 kn-tabular" style="font-size: 0.79rem;">
            <span class="d-inline-flex align-items-center gap-1">
                <i class="bi bi-box-seam" aria-hidden="true"></i>
                <span>Stok: {{ number_format($availableStock, 0, ',', '.') }}</span>
            </span>
            @if($validSoldCount > 0)
                <span>&bull; Terjual {{ number_format($validSoldCount, 0, ',', '.') }}</span>
            @endif
        </div>

        <div class="mt-auto pt-2 border-top d-flex align-items-end justify-content-between gap-2" style="border-color: var(--kn-border-soft) !important;">
            <div>
                @if($isPromo)
                    <div class="kn-product-price-old">
                        Rp {{ number_format($regularPrice, 0, ',', '.') }}
                    </div>
                @endif
                <div class="kn-product-price">
                    @if($startsFrom)
                        <span class="small fw-normal text-mlm-muted" style="font-size: 0.78rem;">Mulai</span>
                    @endif
                    Rp {{ number_format($effectivePrice, 0, ',', '.') }}
                </div>
            </div>

            <span
                class="d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                style="width: 34px; height: 34px; background-color: var(--kn-cream); color: var(--kn-espresso);"
                aria-hidden="true"
            >
                <i class="bi bi-arrow-right-short fs-5"></i>
            </span>
        </div>
    </div>
</article>
