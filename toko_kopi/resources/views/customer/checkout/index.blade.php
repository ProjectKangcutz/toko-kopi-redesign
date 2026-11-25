@extends('layouts.storefront')

@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('canonical', $seo['canonical'])
@section('robots', $seo['robots'])
@section('og_title', $seo['title'])
@section('og_description', $seo['description'])

@section('content')
<div class="container py-3 py-md-4 pb-5">
    <x-breadcrumb :items="$breadcrumbs" />

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <span class="kn-eyebrow mb-1">Penyelesaian Pesanan</span>
            <h1 class="display-6 fw-bold mb-1" style="color: var(--kn-espresso); letter-spacing: -0.02em;">Checkout</h1>
            <p class="small text-mlm-muted mb-0">
                Kelola alamat tujuan dan pilih layanan pengiriman resmi sebelum menyelesaikan pesanan Anda.
            </p>
        </div>

        <a href="{{ route('storefront.cart') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            <span>Kembali ke Keranjang</span>
        </a>
    </div>

    @if(! $canCheckout)
        <x-empty-state
            icon="bi-bag-x"
            title="Keranjang Belanja Kosong atau Belum Siap"
            message="Anda belum memiliki produk aktif di keranjang belanja. Tambahkan produk terlebih dahulu sebelum melanjutkan proses checkout."
            action-label="Jelajahi Katalog Produk"
            :action-url="route('storefront.catalog')"
        />

        @if(! empty($cart['has_unavailable_items']))
            <div class="text-center mt-2">
                <a href="{{ route('storefront.cart') }}" class="btn btn-outline-primary">
                    <i class="bi bi-cart" aria-hidden="true"></i> Periksa Item di Keranjang
                </a>
            </div>
        @endif
    @else
        @if(! empty($cart['has_unavailable_items']) && ! empty($cart['warnings']))
            <x-alert type="warning" class="mb-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 small">
                    <div>
                        <div class="fw-semibold mb-1">
                            <i class="bi bi-exclamation-triangle-fill me-1" aria-hidden="true"></i>
                            Sebagian item di keranjang tidak tersedia dan tidak diikutsertakan dalam checkout:
                        </div>
                        <ul class="mb-0 ps-3">
                            @foreach($cart['warnings'] as $warning)
                                <li>{{ $warning }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <a href="{{ route('storefront.cart') }}" class="btn btn-sm btn-outline-primary flex-shrink-0">
                        Kelola Keranjang
                    </a>
                </div>
            </x-alert>
        @endif

        <div
            x-data="checkoutPage(@js($checkoutConfig))"
            @keydown.escape.window="handleEscapeKey()"
            class="row g-4 align-items-start"
        >
            {{-- Kolom Kiri: Alamat, Layanan Pengiriman, Daftar Item, Catatan --}}
            <div class="col-12 col-lg-7 col-xl-8 d-flex flex-column gap-4">
                {{-- Banner Notifikasi Aksi Alamat Inline --}}
                <div
                    x-show="addressFeedback"
                    x-cloak
                    class="alert d-flex align-items-center justify-content-between gap-2 mb-0 small"
                    :class="addressFeedbackType === 'error' ? 'alert-danger' : 'alert-success'"
                    role="status"
                    aria-live="polite"
                >
                    <div class="d-flex align-items-center gap-2">
                        <i
                            class="bi"
                            :class="addressFeedbackType === 'error' ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill'"
                            aria-hidden="true"
                        ></i>
                        <span x-text="addressFeedback"></span>
                    </div>
                    <button type="button" class="btn-close btn-sm" aria-label="Tutup pemberitahuan" @click="addressFeedback = ''"></button>
                </div>

                {{-- 1. Pemilihan & Pengelolaan Alamat Pengiriman --}}
                <section class="mlm-card p-3 p-md-4" aria-labelledby="checkout-address-heading">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pb-3 mb-3 border-bottom" style="border-color: var(--kn-border-soft) !important;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="kn-step-badge">1</span>
                            <h2 id="checkout-address-heading" class="h5 fw-bold mb-0" style="color: var(--kn-espresso);">Alamat Pengiriman</h2>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="btn btn-primary btn-sm"
                                :disabled="submitting"
                                @click="openCreateAddressModal()"
                            >
                                <i class="bi bi-plus-lg" aria-hidden="true"></i>
                                <span>Tambah Alamat</span>
                            </button>
                            <a href="{{ route('customer.addresses.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-geo-alt" aria-hidden="true"></i> Kelola di Akun
                            </a>
                        </div>
                    </div>

                    {{-- Empty State Ketika Pelanggan Belum Memiliki Alamat --}}
                    <div
                        x-show="addresses.length === 0"
                        @if(count($addresses) > 0) x-cloak @endif
                        class="text-center py-4 px-3 border rounded-3"
                        style="background-color: var(--kn-canvas);"
                    >
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-mlm-secondary text-mlm-primary mb-3" style="width: 56px; height: 56px;">
                            <i class="bi bi-geo-alt fs-4" aria-hidden="true"></i>
                        </div>
                        <h3 class="h6 fw-bold mb-1" style="color: var(--kn-espresso);">Belum Ada Alamat Pengiriman</h3>
                        <p class="small text-mlm-muted mx-auto mb-3" style="max-width: 460px;">
                            Tambahkan alamat pengiriman langsung di sini agar sistem dapat menghitung ongkos kirim ke lokasi Anda tanpa keluar dari halaman checkout.
                        </p>
                        <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                            <button
                                type="button"
                                class="btn btn-primary"
                                @click="openCreateAddressModal()"
                            >
                                <i class="bi bi-plus-circle me-1" aria-hidden="true"></i>
                                <span>Tambah Alamat Pengiriman</span>
                            </button>
                            <a href="{{ route('customer.addresses.create') }}" class="btn btn-outline-primary btn-sm">
                                Form Halaman Alamat
                            </a>
                        </div>
                    </div>

                    {{-- Alamat Terpilih & Daftar Alamat --}}
                    <div x-show="addresses.length > 0" @if(count($addresses) === 0) x-cloak @endif>
                        {{-- Alamat Terpilih Saat Ini --}}
                        <div class="mlm-region-summary mb-3" aria-live="polite">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                <div class="min-w-0 flex-grow-1">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <span class="kn-eyebrow">Dikirim ke</span>
                                        <span class="fw-bold text-body" x-text="selectedAddress ? selectedAddress.label : @js($selectedAddress?->label ?? '')">
                                            {{ $selectedAddress?->label }}
                                        </span>
                                        <span
                                            x-show="selectedAddress && selectedAddress.is_primary"
                                            @if(! $selectedAddress?->is_primary) x-cloak @endif
                                            class="badge bg-white text-mlm-primary border border-success-subtle"
                                        >
                                            Alamat Utama
                                        </span>
                                        <span
                                            x-show="selectedAddress && !selectedAddress.has_valid_region"
                                            x-cloak
                                            class="badge text-bg-warning"
                                        >
                                            Perlu Pilih Wilayah
                                        </span>
                                    </div>
                                    <div class="fw-semibold small text-body">
                                        <span x-text="selectedAddress ? selectedAddress.recipient_name : @js($selectedAddress?->recipient_name ?? '')">{{ $selectedAddress?->recipient_name }}</span>
                                        <span class="text-mlm-muted fw-normal">&bull;</span>
                                        <span class="text-mlm-muted fw-normal kn-tabular" x-text="selectedAddress ? selectedAddress.recipient_phone : @js($selectedAddress?->recipient_phone ?? '')">{{ $selectedAddress?->recipient_phone }}</span>
                                    </div>
                                    <p class="small text-body mb-1 mt-1" x-text="selectedAddress ? selectedAddress.address_line : @js($selectedAddress?->address_line ?? '')">
                                        {{ $selectedAddress?->address_line }}
                                    </p>
                                    <div class="small text-mlm-muted">
                                        <i class="bi bi-geo-alt-fill text-mlm-primary me-1" aria-hidden="true"></i>
                                        <span x-text="selectedAddress ? selectedAddress.formatted_region : @js($selectedAddress?->formattedRegion() ?? '')">{{ $selectedAddress?->formattedRegion() }}</span>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2 flex-shrink-0">
                                    <button
                                        type="button"
                                        x-show="selectedAddress"
                                        class="btn btn-outline-primary btn-sm"
                                        :disabled="submitting"
                                        @click="openEditAddressModal(selectedAddress)"
                                    >
                                        <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                        <span>Ubah</span>
                                    </button>
                                    <button
                                        type="button"
                                        x-show="addresses.length > 1"
                                        @if(count($addresses) <= 1) x-cloak @endif
                                        class="btn btn-outline-primary btn-sm"
                                        @click="addressListExpanded = !addressListExpanded"
                                        :aria-expanded="addressListExpanded ? 'true' : 'false'"
                                    >
                                        <i class="bi bi-arrow-left-right" aria-hidden="true"></i>
                                        <span x-text="addressListExpanded ? 'Tutup Pilihan Alamat' : 'Ganti Alamat (' + addresses.length + ')'">Ganti Alamat</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- SSR Initial Fallback sebelum Alpine aktif --}}
                        @if(count($addresses) > 0)
                            <div x-show="!hydrated" class="d-flex flex-column gap-2">
                                @foreach($addresses as $address)
                                    <div class="kn-address-option-card {{ $selectedAddress && (int) $selectedAddress->id === (int) $address['id'] ? 'is-selected' : '' }}">
                                        <div class="mlm-checkout-option">
                                            <div class="d-flex align-items-start gap-3">
                                                <span class="mlm-checkout-radio-dot mt-1" aria-hidden="true"></span>
                                                <div class="flex-grow-1 min-w-0">
                                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
                                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                                            <span class="fw-bold">{{ $address['label'] }}</span>
                                                            @if($address['is_primary'])
                                                                <span class="badge bg-mlm-secondary text-mlm-primary border border-success-subtle">
                                                                    Alamat Utama
                                                                </span>
                                                            @endif
                                                            @if(! $address['has_valid_region'])
                                                                <span class="badge text-bg-warning">Perlu Pilih Wilayah</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="small fw-medium text-body">
                                                        {{ $address['recipient_name'] }} ({{ $address['recipient_phone'] }})
                                                    </div>
                                                    <div class="small text-mlm-muted mt-1">{{ $address['address_line'] }}</div>
                                                    <div class="small text-mlm-muted">{{ $address['formatted_region'] }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Daftar Alamat Aktif Reaktif --}}
                        <div x-show="hydrated && (addressListExpanded || addresses.length <= 1)" x-cloak>
                            <div
                                role="radiogroup"
                                aria-label="Pilih alamat pengiriman"
                                class="d-flex flex-column gap-2"
                            >
                                <template x-for="addr in addresses" :key="addr.id">
                                    <div
                                        class="kn-address-option-card"
                                        :class="{ 'is-selected': Number(selectedAddressId) === Number(addr.id) }"
                                    >
                                        <button
                                            type="button"
                                            role="radio"
                                            class="mlm-checkout-option"
                                            :class="{ 'is-selected': Number(selectedAddressId) === Number(addr.id) }"
                                            :aria-checked="Number(selectedAddressId) === Number(addr.id) ? 'true' : 'false'"
                                            @click="selectAddress(addr.id)"
                                        >
                                            <div class="d-flex align-items-start gap-3">
                                                <span class="mlm-checkout-radio-dot mt-1" aria-hidden="true"></span>
                                                <div class="flex-grow-1 min-w-0">
                                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
                                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                                            <span class="fw-bold" x-text="addr.label"></span>
                                                            <span
                                                                x-show="addr.is_primary"
                                                                class="badge bg-mlm-secondary text-mlm-primary border border-success-subtle"
                                                            >
                                                                Alamat Utama
                                                            </span>
                                                            <span
                                                                x-show="!addr.has_valid_region"
                                                                class="badge text-bg-warning"
                                                            >
                                                                Perlu Pilih Wilayah
                                                            </span>
                                                        </div>
                                                        <span class="small fw-semibold text-mlm-primary" x-show="Number(selectedAddressId) === Number(addr.id)">
                                                            <i class="bi bi-check-circle-fill me-1" aria-hidden="true"></i>Dipilih
                                                        </span>
                                                    </div>
                                                    <div class="small fw-medium text-body">
                                                        <span x-text="addr.recipient_name"></span>
                                                        (<span class="kn-tabular" x-text="addr.recipient_phone"></span>)
                                                    </div>
                                                    <div class="small text-mlm-muted mt-1" x-text="addr.address_line"></div>
                                                    <div class="small text-mlm-muted" x-text="addr.formatted_region"></div>
                                                </div>
                                            </div>
                                        </button>

                                        <div class="kn-address-card-actions">
                                            <button
                                                type="button"
                                                class="btn btn-outline-primary btn-sm py-1 px-2"
                                                style="min-height: 32px; font-size: 0.78rem;"
                                                :disabled="submitting || addressMutatingId === addr.id"
                                                @click.stop="openEditAddressModal(addr)"
                                            >
                                                <i class="bi bi-pencil" aria-hidden="true"></i>
                                                <span>Ubah</span>
                                            </button>

                                            <button
                                                type="button"
                                                x-show="!addr.is_primary"
                                                class="btn btn-outline-primary btn-sm py-1 px-2"
                                                style="min-height: 32px; font-size: 0.78rem;"
                                                :disabled="submitting || addressMutatingId === addr.id"
                                                @click.stop="setPrimaryAddress(addr)"
                                            >
                                                <span x-show="addressMutatingId === addr.id" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                                <i x-show="addressMutatingId !== addr.id" class="bi bi-star" aria-hidden="true"></i>
                                                <span>Jadikan Utama</span>
                                            </button>

                                            <button
                                                type="button"
                                                class="btn btn-outline-danger btn-sm py-1 px-2 ms-auto"
                                                style="min-height: 32px; font-size: 0.78rem;"
                                                :disabled="submitting || addressMutatingId === addr.id"
                                                @click.stop="confirmDeleteAddress(addr)"
                                            >
                                                <i class="bi bi-trash" aria-hidden="true"></i>
                                                <span>Hapus</span>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 2. Pemilihan Kurir & Layanan Pengiriman --}}
                <section
                    x-show="addresses.length > 0"
                    @if(count($addresses) === 0) x-cloak @endif
                    class="mlm-card p-3 p-md-4"
                    aria-labelledby="checkout-shipping-heading"
                >
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pb-3 mb-3 border-bottom" style="border-color: var(--kn-border-soft) !important;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="kn-step-badge">2</span>
                            <div>
                                <h2 id="checkout-shipping-heading" class="h5 fw-bold mb-0" style="color: var(--kn-espresso);">Layanan Pengiriman</h2>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn btn-outline-primary btn-sm"
                            :disabled="quoteLoading || !selectedAddressId"
                            @click="retryQuote()"
                        >
                            <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
                            <span>Perbarui Tarif</span>
                        </button>
                    </div>

                    {{-- Peringatan Alamat Manual / Belum Ada Wilayah Tujuan --}}
                    <div
                        x-show="selectedAddress && !selectedAddress.has_valid_region && !quoteLoading && !quoteError"
                        x-cloak
                        class="alert alert-warning mb-3"
                        role="alert"
                    >
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 small">
                            <div>
                                <div class="fw-semibold mb-1">Alamat ini belum memiliki ID wilayah tujuan pengiriman</div>
                                <p class="mb-0">
                                    Lengkapi wilayah pengiriman pada alamat ini agar sistem dapat mengambil ongkos kirim resmi secara otomatis.
                                </p>
                            </div>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary flex-shrink-0"
                                @click="openEditAddressModal(selectedAddress)"
                            >
                                Lengkapi Alamat
                            </button>
                        </div>
                    </div>

                    {{-- State Loading --}}
                    <div x-show="quoteLoading" x-cloak class="text-center py-4" aria-live="polite">
                        <div class="spinner-border text-mlm-primary mb-2" role="status">
                            <span class="visually-hidden">Memuat ongkos kirim...</span>
                        </div>
                        <div class="fw-semibold text-mlm-primary">Menghitung tarif pengiriman...</div>
                        <p class="small text-mlm-muted mb-0">Mengambil pilihan kurir resmi berdasarkan berat paket dan alamat tujuan.</p>
                    </div>

                    {{-- State Error --}}
                    <div x-show="!quoteLoading && quoteError" x-cloak class="alert alert-danger mb-0" role="alert" aria-live="polite">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                            <div class="small">
                                <div class="fw-bold mb-1">
                                    <i class="bi bi-exclamation-octagon-fill me-1" aria-hidden="true"></i>
                                    Gagal Memuat Tarif Pengiriman
                                </div>
                                <div x-text="quoteError"></div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 flex-shrink-0">
                                <button
                                    type="button"
                                    x-show="selectedAddress && !selectedAddress.has_valid_region"
                                    class="btn btn-sm btn-outline-primary"
                                    @click="openEditAddressModal(selectedAddress)"
                                >
                                    Edit Alamat
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-primary"
                                    :disabled="quoteLoading"
                                    @click="retryQuote()"
                                >
                                    <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
                                    <span>Coba Lagi</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- State Empty (Tidak ada layanan tersedia) --}}
                    <div
                        x-show="!quoteLoading && !quoteError && quoteFetched && rates.length === 0"
                        x-cloak
                        class="text-center py-4 px-3 border rounded-3"
                        style="background-color: var(--kn-canvas);"
                        aria-live="polite"
                    >
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-mlm-secondary text-mlm-primary mb-2" style="width: 52px; height: 52px;">
                            <i class="bi bi-truck fs-4" aria-hidden="true"></i>
                        </div>
                        <h3 class="h6 fw-bold mb-1">Belum Ada Layanan Pengiriman Tersedia</h3>
                        <p class="small text-mlm-muted mx-auto mb-3" style="max-width: 440px;">
                            Tidak ditemukan layanan ekspedisi aktif yang mendukung rute atau berat paket untuk alamat ini. Anda dapat memilih alamat lain atau mencoba cek ulang.
                        </p>
                        <button
                            type="button"
                            class="btn btn-outline-primary btn-sm"
                            :disabled="quoteLoading"
                            @click="retryQuote()"
                        >
                            <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
                            <span>Coba Cek Ulang</span>
                        </button>
                    </div>

                    {{-- Daftar Layanan Terkelompok per Kurir & Terurut Termurah --}}
                    <div x-show="!quoteLoading && !quoteError && rates.length > 0" x-cloak>
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 small text-mlm-muted kn-tabular">
                            <span>
                                Berat aktual <strong class="text-body" x-text="formatWeight(quoteData?.actual_weight_grams ?? serverSummary.total_weight_grams)"></strong>
                                &bull; Berat tagih ekspedisi <strong class="text-mlm-primary" x-text="formatWeight(quoteData?.billable_weight_grams ?? serverSummary.total_weight_grams)"></strong>
                            </span>
                            <span class="badge rounded-pill px-3 py-1" style="background-color: var(--kn-sage); color: var(--kn-forest);">
                                <span x-text="rates.length"></span> layanan &bull; Termurah dulu
                            </span>
                        </div>

                        <div class="d-flex flex-column gap-3" role="radiogroup" aria-label="Pilih layanan pengiriman">
                            <template x-for="group in groupedRates" :key="group.courier_code">
                                <div class="kn-courier-group">
                                    <div class="kn-courier-group-header">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-truck text-mlm-primary" aria-hidden="true"></i>
                                            <span class="fw-bold small" style="color: var(--kn-espresso);" x-text="group.courier_name"></span>
                                        </div>
                                        <span class="small text-mlm-muted kn-tabular">
                                            Mulai <strong class="text-mlm-primary" x-text="formatRupiah(group.minCost)"></strong>
                                        </span>
                                    </div>

                                    <div class="kn-courier-group-body">
                                        <template x-for="rate in group.services" :key="rateKey(rate)">
                                            <button
                                                type="button"
                                                role="radio"
                                                class="mlm-checkout-option"
                                                :class="{ 'is-selected': selectedRateKey === rateKey(rate) }"
                                                :aria-checked="selectedRateKey === rateKey(rate) ? 'true' : 'false'"
                                                @click="selectRate(rate)"
                                            >
                                                <div class="d-flex align-items-center justify-content-between gap-3">
                                                    <div class="d-flex align-items-start gap-3 min-w-0">
                                                        <span class="mlm-checkout-radio-dot mt-1" aria-hidden="true"></span>
                                                        <div class="min-w-0">
                                                            <div class="d-flex flex-wrap align-items-center gap-2">
                                                                <span class="badge bg-mlm-secondary text-mlm-primary fw-bold" x-text="rate.service_code"></span>
                                                                <span
                                                                    class="fw-semibold small text-body"
                                                                    x-show="rate.service_name && rate.service_name.toLowerCase() !== rate.service_code.toLowerCase()"
                                                                    x-text="rate.service_name"
                                                                ></span>
                                                            </div>
                                                            <div class="small text-mlm-muted mt-1 d-flex flex-wrap align-items-center gap-2">
                                                                <span x-show="rate.etd">
                                                                    <i class="bi bi-clock me-1 text-mlm-primary" aria-hidden="true"></i>
                                                                    Estimasi <span x-text="formatEtd(rate.etd)"></span>
                                                                </span>
                                                                <span x-show="!rate.etd">
                                                                    <i class="bi bi-clock me-1" aria-hidden="true"></i>
                                                                    Estimasi mengikuti kurir
                                                                </span>
                                                                <span
                                                                    x-show="rate.description && rate.description.toLowerCase() !== String(rate.service_name || '').toLowerCase()"
                                                                    class="text-mlm-muted"
                                                                    x-text="'• ' + rate.description"
                                                                ></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="text-end flex-shrink-0 kn-tabular">
                                                        <div class="fw-bold text-mlm-primary fs-6" x-text="formatRupiah(rate.cost)"></div>
                                                    </div>
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- State Awal sebelum JS aktif (SSR Fallback) --}}
                    <div x-show="!hydrated" class="small text-mlm-muted py-2">
                        Menyiapkan permintaan tarif pengiriman ke server...
                    </div>
                </section>

                {{-- 3. Daftar Produk dalam Keranjang --}}
                <section class="mlm-card p-3 p-md-4" aria-labelledby="checkout-items-heading">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pb-3 mb-3 border-bottom" style="border-color: var(--kn-border-soft) !important;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="kn-step-badge" x-text="addresses.length > 0 ? '3' : '2'">{{ count($addresses) > 0 ? '3' : '2' }}</span>
                            <h2 id="checkout-items-heading" class="h5 fw-bold mb-0" style="color: var(--kn-espresso);">Ringkasan Produk</h2>
                        </div>

                        <a href="{{ route('storefront.cart') }}" class="btn btn-outline-primary btn-sm">
                            Ubah Keranjang
                        </a>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @foreach($items as $item)
                            <article class="d-flex gap-3 pb-3 {{ ! $loop->last ? 'border-bottom' : '' }}" style="border-color: var(--kn-border-soft) !important;">
                                <div class="mlm-checkout-item-thumb">
                                    @if(! empty($item['image_url']))
                                        <img
                                            src="{{ $item['image_url'] }}"
                                            alt="{{ $item['name'] }}"
                                            class="w-100 h-100 object-fit-cover"
                                            loading="lazy"
                                        >
                                    @else
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="color: var(--kn-caramel);">
                                            <i class="bi bi-cup-hot fs-5 opacity-50" aria-hidden="true"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-2">
                                        <div class="min-w-0">
                                            <h3 class="h6 fw-bold mb-1">
                                                @if(! empty($item['product_url']))
                                                    <a href="{{ $item['product_url'] }}" class="text-decoration-none text-reset">
                                                        {{ $item['name'] }}
                                                    </a>
                                                @else
                                                    {{ $item['name'] }}
                                                @endif
                                            </h3>

                                            @if(! empty($item['variant_name']))
                                                <div class="small text-mlm-primary fw-medium">
                                                    Varian: {{ $item['variant_name'] }}
                                                </div>
                                            @endif

                                            @if(! empty($item['variant_attributes']))
                                                <div class="d-flex flex-wrap gap-1 mt-1">
                                                    @foreach($item['variant_attributes'] as $attr)
                                                        <span class="badge bg-mlm-secondary text-mlm-primary fw-normal">
                                                            {{ $attr['name'] }}: {{ $attr['value'] }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif

                                            <div class="small text-mlm-muted mt-1 kn-tabular">
                                                {{ number_format($item['quantity'], 0, ',', '.') }} x Rp {{ number_format($item['unit_price'], 0, ',', '.') }}
                                                @if($item['is_promo'])
                                                    <span class="text-decoration-line-through ms-1">
                                                        Rp {{ number_format($item['regular_price'], 0, ',', '.') }}
                                                    </span>
                                                @endif
                                                <span class="ms-2">&bull; {{ number_format($item['line_weight_grams'], 0, ',', '.') }} g</span>
                                            </div>
                                        </div>

                                        <div class="fw-bold text-mlm-primary text-sm-end flex-shrink-0 kn-tabular">
                                            Rp {{ number_format($item['line_subtotal'], 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                {{-- 4. Catatan Pesanan Opsional --}}
                <section
                    x-show="addresses.length > 0"
                    @if(count($addresses) === 0) x-cloak @endif
                    class="mlm-card p-3 p-md-4"
                    aria-labelledby="checkout-notes-heading"
                >
                    <div class="d-flex align-items-center gap-2 pb-2 mb-3 border-bottom" style="border-color: var(--kn-border-soft) !important;">
                        <span class="kn-step-badge">4</span>
                        <h2 id="checkout-notes-heading" class="h5 fw-bold mb-0" style="color: var(--kn-espresso);">Catatan Pesanan (Opsional)</h2>
                    </div>

                    <label for="checkout-customer-notes" class="form-label small fw-semibold">
                        Catatan untuk Penjual atau Pengemasan
                    </label>
                    <textarea
                        id="checkout-customer-notes"
                        rows="2"
                        maxlength="500"
                        x-model="customerNotes"
                        @input="refreshIdempotencyKey()"
                        :disabled="submitting"
                        class="form-control"
                        placeholder="Contoh: Mohon dikemas rapi, kirim pada jam kerja..."
                    ></textarea>
                    <div class="d-flex justify-content-between small text-mlm-muted mt-1 kn-tabular">
                        <span>Opsional, maksimal 500 karakter.</span>
                        <span x-text="(customerNotes || '').length + '/500'">0/500</span>
                    </div>
                </section>
            </div>

            {{-- Kolom Kanan: Ringkasan Checkout (Preview Total Sementara) --}}
            <div class="col-12 col-lg-5 col-xl-4">
                <aside class="mlm-card p-3 p-md-4 mlm-cart-summary-sticky" aria-label="Ringkasan Checkout">
                    <div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom" style="border-color: var(--kn-border-soft) !important;">
                        <h2 class="h5 fw-bold mb-0" style="color: var(--kn-espresso);">Ringkasan Checkout</h2>
                        <span class="badge rounded-pill fw-semibold kn-tabular" style="background-color: var(--kn-sage); color: var(--kn-forest);">
                            {{ number_format((int) $serverSummary['valid_quantity'], 0, ',', '.') }} item
                        </span>
                    </div>

                    <dl class="mb-3 small kn-tabular">
                        <div class="d-flex justify-content-between align-items-start gap-2 py-2 border-bottom" style="border-color: var(--kn-border-soft) !important;">
                            <dt class="fw-normal text-mlm-muted">Alamat Tujuan</dt>
                            <dd class="mb-0 fw-semibold text-end">
                                <span x-show="selectedAddress" @if(! $selectedAddress) x-cloak @endif x-text="selectedAddress ? selectedAddress.label : ''">
                                    {{ $selectedAddress?->label }}
                                </span>
                                <span x-show="!selectedAddress" @if($selectedAddress) x-cloak @endif class="text-mlm-muted">
                                    Belum ada alamat
                                </span>
                            </dd>
                        </div>

                        <div class="d-flex justify-content-between align-items-start gap-2 py-2 border-bottom" style="border-color: var(--kn-border-soft) !important;">
                            <dt class="fw-normal text-mlm-muted">Layanan Pengiriman</dt>
                            <dd class="mb-0 fw-semibold text-end">
                                <span x-show="selectedRate" x-cloak class="text-mlm-primary" x-text="selectedRate ? (selectedRate.courier_name + ' - ' + selectedRate.service_code) : ''"></span>
                                <span x-show="!selectedRate" class="text-mlm-muted">Belum dipilih</span>
                            </dd>
                        </div>

                        <div class="d-flex justify-content-between align-items-center py-2">
                            <dt class="fw-normal text-mlm-muted">Berat Total Produk</dt>
                            <dd class="mb-0 fw-semibold">
                                @php $grams = (int) $serverSummary['total_weight_grams']; @endphp
                                {{ $grams >= 1000 ? number_format($grams / 1000, 2, ',', '.').' kg' : number_format($grams, 0, ',', '.').' gram' }}
                            </dd>
                        </div>

                        <div class="d-flex justify-content-between align-items-center py-2">
                            <dt class="fw-normal text-mlm-muted">Subtotal Produk</dt>
                            <dd class="mb-0 fw-semibold">
                                Rp {{ number_format((float) $serverSummary['subtotal'], 0, ',', '.') }}
                            </dd>
                        </div>

                        @if((float) $serverSummary['discount_amount'] > 0)
                            <div class="d-flex justify-content-between align-items-center py-2">
                                <dt class="fw-normal text-mlm-muted">Diskon</dt>
                                <dd class="mb-0 fw-semibold text-success">
                                    - Rp {{ number_format((float) $serverSummary['discount_amount'], 0, ',', '.') }}
                                </dd>
                            </div>
                        @endif

                        @if((float) $serverSummary['tax_amount'] > 0)
                            <div class="d-flex justify-content-between align-items-center py-2">
                                <dt class="fw-normal text-mlm-muted">
                                    Pajak ({{ rtrim(rtrim(number_format((float) $serverSummary['tax_percentage'], 2, ',', '.'), '0'), ',') }}%)
                                </dt>
                                <dd class="mb-0 fw-semibold">
                                    Rp {{ number_format((float) $serverSummary['tax_amount'], 0, ',', '.') }}
                                </dd>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between align-items-center py-2">
                            <dt class="fw-normal text-mlm-muted">Ongkos Kirim</dt>
                            <dd class="mb-0 fw-semibold">
                                <span x-show="selectedRate" x-cloak class="text-mlm-primary" x-text="selectedRate ? formatRupiah(selectedRate.cost) : ''"></span>
                                <span x-show="!selectedRate" class="text-mlm-muted">Pilih layanan</span>
                            </dd>
                        </div>
                    </dl>

                    <div class="kn-price-box p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <span class="fw-bold" style="color: var(--kn-espresso);">Total Sementara</span>
                            <span class="fs-4 fw-bold kn-tabular" style="color: var(--kn-forest);" aria-live="polite">
                                <span x-show="!hydrated">
                                    Rp {{ number_format(max(0, (float) $serverSummary['subtotal'] - (float) $serverSummary['discount_amount']) + (float) $serverSummary['tax_amount'], 0, ',', '.') }}
                                </span>
                                <span x-show="hydrated" x-cloak x-text="formatRupiah(estimatedGrandTotal)"></span>
                            </span>
                        </div>
                    </div>

                    <div
                        class="rounded-3 p-3 small mb-3"
                        :class="selectedRate ? 'bg-mlm-secondary text-mlm-primary border border-success-subtle' : 'bg-light text-mlm-muted border'"
                    >
                        <div x-show="selectedRate" x-cloak class="fw-semibold">
                            <i class="bi bi-check-circle-fill me-1" aria-hidden="true"></i>
                            Layanan pengiriman siap: <span x-text="selectedRate ? (selectedRate.courier_name + ' ' + selectedRate.service_code) : ''"></span>
                        </div>
                        <div x-show="!selectedRate">
                            <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
                            <span x-text="addresses.length === 0 ? 'Tambahkan alamat pengiriman terlebih dahulu untuk menghitung ongkos kirim.' : 'Silakan pilih alamat dan satu layanan pengiriman untuk melihat total sementara termasuk ongkos kirim.'"></span>
                        </div>
                    </div>

                    <div
                        x-show="submitError"
                        x-cloak
                        class="alert alert-danger small mb-3"
                        role="alert"
                        aria-live="polite"
                    >
                        <div class="fw-bold mb-1">
                            <i class="bi bi-exclamation-octagon-fill me-1" aria-hidden="true"></i>
                            Pesanan Belum Dapat Dibuat
                        </div>
                        <div x-text="submitError"></div>
                    </div>

                    <p class="small text-mlm-muted mb-3">
                        Harga produk, ketersediaan stok, berat paket, dan ongkos kirim diverifikasi ulang langsung di server saat pesanan dibuat.
                    </p>

                    <button
                        type="button"
                        x-show="addresses.length > 0"
                        @if(count($addresses) === 0) x-cloak @endif
                        class="btn btn-primary w-100 fw-semibold mb-2"
                        style="min-height: 46px;"
                        :disabled="!canSubmitOrder"
                        @click="submitOrder()"
                    >
                        <span x-show="!submitting">
                            <i class="bi bi-bag-check me-1" aria-hidden="true"></i>
                            <span>Buat Pesanan</span>
                        </span>
                        <span x-show="submitting" x-cloak>
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            <span>Memproses Pesanan...</span>
                        </span>
                    </button>

                    <a href="{{ route('storefront.cart') }}" class="btn btn-outline-primary w-100">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i>
                        <span>Kembali ke Keranjang</span>
                    </a>
                </aside>
            </div>

            {{-- MODAL: Tambah / Edit Alamat Pengiriman Inline --}}
            <div
                x-show="addressModalOpen"
                x-cloak
                class="kn-modal-backdrop"
                role="dialog"
                aria-modal="true"
                aria-labelledby="checkout-address-modal-title"
                @mousedown.self="closeAddressModal()"
            >
                <div class="kn-modal-panel">
                    <div class="kn-modal-header">
                        <div>
                            <span class="kn-eyebrow d-block mb-1" x-text="addressModalMode === 'edit' ? 'Perbarui Alamat' : 'Alamat Baru'"></span>
                            <h3 id="checkout-address-modal-title" class="h5 fw-bold mb-0" style="color: var(--kn-espresso);" x-text="addressModalMode === 'edit' ? 'Ubah Alamat Pengiriman' : 'Tambah Alamat Pengiriman'"></h3>
                        </div>
                        <button
                            type="button"
                            class="btn-close"
                            aria-label="Tutup modal alamat"
                            :disabled="savingAddress"
                            @click="closeAddressModal()"
                        ></button>
                    </div>

                    <form @submit.prevent="submitAddressModal()" class="d-flex flex-column min-h-0 flex-grow-1">
                        <div class="kn-modal-body d-flex flex-column gap-3">
                            <div x-show="addressModalError" x-cloak class="alert alert-danger small mb-0" role="alert">
                                <i class="bi bi-exclamation-octagon-fill me-1" aria-hidden="true"></i>
                                <span x-text="addressModalError"></span>
                            </div>

                            {{-- Label Alamat --}}
                            <div>
                                <label for="modal-address-label" class="form-label small fw-semibold mb-1">
                                    Label Alamat <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <template x-for="preset in ['Rumah', 'Kantor', 'Apartemen', 'Kos']" :key="preset">
                                        <button
                                            type="button"
                                            class="kn-label-chip"
                                            :class="{ 'is-active': addressForm.label === preset }"
                                            @click="addressForm.label = preset"
                                            x-text="preset"
                                        ></button>
                                    </template>
                                    <button
                                        type="button"
                                        class="kn-label-chip"
                                        :class="{ 'is-active': addressForm.label && !['Rumah', 'Kantor', 'Apartemen', 'Kos'].includes(addressForm.label) }"
                                        @click="focusCustomLabel()"
                                    >
                                        Lainnya
                                    </button>
                                </div>
                                <input
                                    id="modal-address-label"
                                    x-ref="modalLabelInput"
                                    type="text"
                                    maxlength="60"
                                    x-model="addressForm.label"
                                    class="form-control"
                                    :class="{ 'is-invalid': addressFieldError('label') }"
                                    placeholder="Contoh: Rumah, Kantor, Gudang"
                                    required
                                >
                                <div class="invalid-feedback" x-text="addressFieldError('label')"></div>
                            </div>

                            {{-- Nama Penerima & Telepon --}}
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <label for="modal-recipient-name" class="form-label small fw-semibold mb-1">
                                        Nama Penerima <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        id="modal-recipient-name"
                                        type="text"
                                        maxlength="255"
                                        x-model="addressForm.recipient_name"
                                        class="form-control"
                                        :class="{ 'is-invalid': addressFieldError('recipient_name') }"
                                        placeholder="Nama lengkap penerima paket"
                                        required
                                    >
                                    <div class="invalid-feedback" x-text="addressFieldError('recipient_name')"></div>
                                </div>

                                <div class="col-12 col-sm-6">
                                    <label for="modal-recipient-phone" class="form-label small fw-semibold mb-1">
                                        Nomor Telepon Penerima <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        id="modal-recipient-phone"
                                        type="tel"
                                        x-model="addressForm.recipient_phone"
                                        class="form-control kn-tabular"
                                        :class="{ 'is-invalid': addressFieldError('recipient_phone') }"
                                        placeholder="Contoh: 081234567890"
                                        required
                                    >
                                    <div class="invalid-feedback" x-text="addressFieldError('recipient_phone')"></div>
                                </div>
                            </div>

                            {{-- Wilayah Pengiriman --}}
                            <div class="kn-region-box">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                    <div>
                                        <div class="fw-bold small" style="color: var(--kn-espresso);">
                                            Wilayah Pengiriman <span class="text-danger">*</span>
                                        </div>
                                        <div class="text-mlm-muted" style="font-size: 0.78rem;">
                                            Pilih Provinsi hingga Kelurahan/Desa atau cari cepat agar ongkos kirim RajaOngkir terhitung akurat.
                                        </div>
                                    </div>
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Metode pilih wilayah">
                                        <button
                                            type="button"
                                            class="btn"
                                            :class="regionInputMode === 'cascade' ? 'btn-primary' : 'btn-outline-primary'"
                                            @click="switchRegionMode('cascade')"
                                        >
                                            Pilih Bertahap
                                        </button>
                                        <button
                                            type="button"
                                            class="btn"
                                            :class="regionInputMode === 'search' ? 'btn-primary' : 'btn-outline-primary'"
                                            @click="switchRegionMode('search')"
                                        >
                                            Cari Cepat
                                        </button>
                                    </div>
                                </div>

                                <div
                                    x-show="addressFieldError('province_id') || addressFieldError('city_id') || addressFieldError('subdistrict_id')"
                                    x-cloak
                                    class="alert alert-danger py-2 px-3 small mb-2"
                                >
                                    <span x-text="addressFieldError('subdistrict_id') || addressFieldError('city_id') || addressFieldError('province_id')"></span>
                                </div>

                                {{-- Mode Dropdown Bertahap --}}
                                <div x-show="regionInputMode === 'cascade'" class="row g-2 mb-2">
                                    <div class="col-12" x-show="cascadeError" x-cloak>
                                        <div class="alert alert-warning py-2 px-3 small mb-1 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                            <span x-text="cascadeError"></span>
                                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" @click="switchRegionMode('search')">
                                                Gunakan Cari Cepat
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-6">
                                        <label for="modal-province-select" class="form-label small fw-medium mb-1">Provinsi</label>
                                        <select
                                            id="modal-province-select"
                                            class="form-select form-select-sm"
                                            :disabled="cascadeLoading.provinces || provincesList.length === 0"
                                            :value="addressForm.province_id"
                                            @change="onProvinceSelectChange($event.target.value)"
                                        >
                                            <option value="" x-text="cascadeLoading.provinces ? 'Memuat provinsi...' : '-- Pilih Provinsi --'"></option>
                                            <template x-for="prov in provincesList" :key="prov.id">
                                                <option :value="prov.id" x-text="prov.name"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <div class="col-12 col-sm-6">
                                        <label for="modal-city-select" class="form-label small fw-medium mb-1">Kota / Kabupaten</label>
                                        <select
                                            id="modal-city-select"
                                            class="form-select form-select-sm"
                                            :disabled="cascadeLoading.cities || !addressForm.province_id || citiesList.length === 0"
                                            :value="addressForm.city_id"
                                            @change="onCitySelectChange($event.target.value)"
                                        >
                                            <option value="" x-text="cascadeLoading.cities ? 'Memuat kota/kabupaten...' : '-- Pilih Kota/Kabupaten --'"></option>
                                            <template x-for="city in citiesList" :key="city.id">
                                                <option :value="city.id" x-text="city.name"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <div class="col-12 col-sm-6">
                                        <label for="modal-district-select" class="form-label small fw-medium mb-1">Kecamatan</label>
                                        <select
                                            id="modal-district-select"
                                            class="form-select form-select-sm"
                                            :disabled="cascadeLoading.districts || !addressForm.city_id || districtsList.length === 0"
                                            :value="addressForm.district_id || ''"
                                            @change="onDistrictSelectChange($event.target.value)"
                                        >
                                            <option value="" x-text="cascadeLoading.districts ? 'Memuat kecamatan...' : '-- Pilih Kecamatan --'"></option>
                                            <template x-for="dist in districtsList" :key="dist.id">
                                                <option :value="dist.id" x-text="dist.name"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <div class="col-12 col-sm-6">
                                        <label for="modal-subdistrict-select" class="form-label small fw-medium mb-1">Kelurahan / Desa</label>
                                        <select
                                            id="modal-subdistrict-select"
                                            class="form-select form-select-sm"
                                            :disabled="cascadeLoading.subdistricts || !addressForm.district_id || subdistrictsList.length === 0"
                                            :value="addressForm.subdistrict_id || ''"
                                            @change="onSubdistrictSelectChange($event.target.value)"
                                        >
                                            <option value="" x-text="cascadeLoading.subdistricts ? 'Memuat kelurahan...' : '-- Pilih Kelurahan/Desa --'"></option>
                                            <template x-for="sub in subdistrictsList" :key="sub.id">
                                                <option :value="sub.id" x-text="sub.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>

                                {{-- Mode Cari Cepat Wilayah --}}
                                <div x-show="regionInputMode === 'search'" x-cloak class="mb-2">
                                    <label for="modal-region-keyword" class="form-label small fw-medium mb-1">
                                        Cari Kelurahan, Kecamatan, Kota, atau Kode Pos
                                    </label>
                                    <div class="d-flex gap-2">
                                        <input
                                            id="modal-region-keyword"
                                            type="search"
                                            class="form-control form-control-sm"
                                            x-model="regionKeyword"
                                            @keydown.enter.prevent="searchRegionsInModal()"
                                            placeholder="Contoh: Sumur Bandung atau 40111"
                                            autocomplete="off"
                                        >
                                        <button
                                            type="button"
                                            class="btn btn-primary btn-sm flex-shrink-0"
                                            :disabled="regionSearching"
                                            @click="searchRegionsInModal()"
                                        >
                                            <span x-show="!regionSearching">Cari</span>
                                            <span x-show="regionSearching" x-cloak class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        </button>
                                    </div>

                                    <div x-show="regionSearchError" x-cloak class="alert alert-warning py-2 px-3 small mt-2 mb-0" x-text="regionSearchError"></div>

                                    <div x-show="regionSearchDone && !regionSearching && !regionSearchError && regionSearchResults.length === 0" x-cloak class="small text-mlm-muted mt-2">
                                        Wilayah tidak ditemukan. Coba kata kunci kecamatan atau kode pos lain.
                                    </div>

                                    <div x-show="regionSearchResults.length > 0" x-cloak class="mlm-region-results mt-2" role="listbox" aria-label="Hasil pencarian wilayah">
                                        <template x-for="item in regionSearchResults" :key="item.id + '-' + item.label">
                                            <button
                                                type="button"
                                                class="mlm-region-option"
                                                @click="applySearchedRegion(item)"
                                            >
                                                <div class="fw-semibold small" x-text="item.label"></div>
                                                <div class="text-mlm-muted" style="font-size: 0.76rem;">
                                                    <span x-text="[item.subdistrict_name, item.district_name, item.city_name, item.province_name].filter(Boolean).join(' • ')"></span>
                                                    <span x-show="item.zip_code" class="ms-1 badge text-bg-light border" x-text="item.zip_code"></span>
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                {{-- Ringkasan Wilayah Terpilih --}}
                                <div x-show="hasModalSelectedRegion" x-cloak class="mlm-region-summary mt-2 py-2 px-3">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                        <div class="small">
                                            <div class="fw-semibold text-mlm-primary">
                                                <i class="bi bi-check-circle-fill me-1" aria-hidden="true"></i>
                                                Wilayah Terpilih
                                            </div>
                                            <div class="fw-medium text-body" x-text="modalFormattedRegion"></div>
                                        </div>
                                        <span
                                            class="badge bg-white border"
                                            :class="addressForm.subdistrict_id ? 'text-mlm-primary' : 'text-warning'"
                                            x-text="addressForm.subdistrict_id ? 'Siap Hitung Ongkir' : 'Pilih s/d Kelurahan'"
                                        ></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Kode Pos & Alamat Lengkap --}}
                            <div class="row g-3">
                                <div class="col-12 col-sm-8">
                                    <label for="modal-address-line" class="form-label small fw-semibold mb-1">
                                        Alamat Lengkap <span class="text-danger">*</span>
                                    </label>
                                    <textarea
                                        id="modal-address-line"
                                        rows="2"
                                        maxlength="1750"
                                        x-model="addressForm.address_line"
                                        class="form-control"
                                        :class="{ 'is-invalid': addressFieldError('address_line') }"
                                        placeholder="Nama jalan, nomor rumah, blok, RT/RW"
                                        required
                                    ></textarea>
                                    <div class="invalid-feedback" x-text="addressFieldError('address_line')"></div>
                                </div>

                                <div class="col-12 col-sm-4">
                                    <label for="modal-postal-code" class="form-label small fw-semibold mb-1">
                                        Kode Pos <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        id="modal-postal-code"
                                        type="text"
                                        inputmode="numeric"
                                        maxlength="5"
                                        x-model="addressForm.postal_code"
                                        class="form-control kn-tabular"
                                        :class="{ 'is-invalid': addressFieldError('postal_code') }"
                                        placeholder="40111"
                                        required
                                    >
                                    <div class="invalid-feedback" x-text="addressFieldError('postal_code')"></div>
                                </div>
                            </div>

                            {{-- Catatan Kurir (Opsional) --}}
                            <div>
                                <label for="modal-courier-note" class="form-label small fw-semibold mb-1">
                                    Catatan Kurir / Patokan Lokasi (Opsional)
                                </label>
                                <input
                                    id="modal-courier-note"
                                    type="text"
                                    maxlength="200"
                                    x-model="addressForm.courier_note"
                                    class="form-control form-control-sm"
                                    placeholder="Contoh: Rumah pagar kayu cokelat di samping kedai kopi"
                                >
                            </div>

                            {{-- Jadikan Alamat Utama --}}
                            <div class="form-check">
                                <input
                                    id="modal-is-primary"
                                    type="checkbox"
                                    class="form-check-input"
                                    x-model="addressForm.is_primary"
                                >
                                <label for="modal-is-primary" class="form-check-label small fw-medium">
                                    Jadikan sebagai alamat pengiriman utama
                                </label>
                            </div>
                        </div>

                        <div class="kn-modal-footer">
                            <button
                                type="button"
                                class="btn btn-outline-primary"
                                :disabled="savingAddress"
                                @click="closeAddressModal()"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="savingAddress"
                            >
                                <span x-show="!savingAddress">
                                    <i class="bi bi-check2-circle me-1" aria-hidden="true"></i>
                                    <span x-text="addressModalMode === 'edit' ? 'Simpan Perubahan' : 'Simpan & Gunakan Alamat'"></span>
                                </span>
                                <span x-show="savingAddress" x-cloak>
                                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                    <span>Menyimpan...</span>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- MODAL: Konfirmasi Hapus Alamat --}}
            <div
                x-show="deleteConfirmAddress !== null"
                x-cloak
                class="kn-modal-backdrop"
                role="dialog"
                aria-modal="true"
                aria-labelledby="checkout-delete-address-title"
                @mousedown.self="cancelDeleteAddress()"
            >
                <div class="kn-modal-panel kn-modal-panel--sm">
                    <div class="kn-modal-header">
                        <h3 id="checkout-delete-address-title" class="h6 fw-bold mb-0 text-danger">
                            <i class="bi bi-exclamation-triangle-fill me-1" aria-hidden="true"></i>
                            Hapus Alamat Pengiriman?
                        </h3>
                        <button
                            type="button"
                            class="btn-close"
                            aria-label="Tutup dialog konfirmasi"
                            :disabled="deletingAddress"
                            @click="cancelDeleteAddress()"
                        ></button>
                    </div>

                    <div class="kn-modal-body small">
                        <p class="mb-2">
                            Anda yakin ingin menghapus alamat <strong x-text="deleteConfirmAddress ? deleteConfirmAddress.label : ''"></strong>
                            (<span x-text="deleteConfirmAddress ? deleteConfirmAddress.recipient_name : ''"></span>)?
                        </p>

                        <div
                            x-show="deleteConfirmAddress && Number(deleteConfirmAddress.id) === Number(selectedAddressId)"
                            class="alert alert-warning py-2 px-3 mb-2"
                        >
                            Alamat ini sedang dipilih untuk pengiriman pesanan saat ini. Jika dihapus, sistem akan memilih alamat pengganti atau meminta Anda menambahkan alamat baru.
                        </div>

                        <div
                            x-show="deleteConfirmAddress && deleteConfirmAddress.is_primary && addresses.length > 1"
                            class="text-mlm-muted"
                        >
                            Karena ini alamat utama, sistem akan mempromosikan alamat aktif terbaru Anda menjadi alamat utama.
                        </div>
                    </div>

                    <div class="kn-modal-footer">
                        <button
                            type="button"
                            class="btn btn-outline-primary btn-sm"
                            :disabled="deletingAddress"
                            @click="cancelDeleteAddress()"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            class="btn btn-danger btn-sm"
                            :disabled="deletingAddress"
                            @click="performDeleteAddress()"
                        >
                            <span x-show="!deletingAddress">Ya, Hapus Alamat</span>
                            <span x-show="deletingAddress" x-cloak>
                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                <span>Menghapus...</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@if($canCheckout)
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('checkoutPage', (config) => ({
        hydrated: false,
        canCheckout: Boolean(config.canCheckout),
        quoteUrl: config.quoteUrl || config.quoteEndpoint || null,
        storeUrl: config.storeUrl || config.storeEndpoint || null,
        addressEndpoints: config.addressEndpoints || {},
        customerDefaults: config.customerDefaults || { name: '', phone: '' },
        addresses: Array.isArray(config.addresses) ? config.addresses : [],
        selectedAddressId: config.initialAddressId ? Number(config.initialAddressId) : null,
        addressListExpanded: (Array.isArray(config.addresses) ? config.addresses.length : 0) <= 1,
        serverSummary: config.summary || {
            valid_quantity: 0,
            total_weight_grams: 0,
            subtotal: 0,
            discount_amount: 0,
            tax_percentage: 0,
            tax_amount: 0,
        },
        quoteLoading: false,
        quoteError: '',
        quoteData: null,
        rates: [],
        quoteFetched: false,
        selectedRateKey: null,
        selectedRate: null,
        customerNotes: '',
        idempotencyKey: '',
        submitting: false,
        submitError: '',
        abortController: null,
        activeRequestToken: 0,

        // Inline Address CRUD & Region States
        addressFeedback: '',
        addressFeedbackType: 'success',
        addressMutatingId: null,
        addressModalOpen: false,
        addressModalMode: 'create',
        editingAddress: null,
        savingAddress: false,
        addressModalError: '',
        addressFormErrors: {},
        deleteConfirmAddress: null,
        deletingAddress: false,
        addressForm: {
            label: 'Rumah',
            recipient_name: '',
            recipient_phone: '',
            province_id: '',
            province_name: '',
            city_id: '',
            city_name: '',
            district_id: '',
            district_name: '',
            subdistrict_id: '',
            subdistrict_name: '',
            postal_code: '',
            address_line: '',
            courier_note: '',
            is_primary: false,
        },
        regionInputMode: 'cascade',
        provincesList: [],
        citiesList: [],
        districtsList: [],
        subdistrictsList: [],
        cascadeLoading: {
            provinces: false,
            cities: false,
            districts: false,
            subdistricts: false,
        },
        cascadeError: '',
        regionKeyword: '',
        regionSearching: false,
        regionSearchDone: false,
        regionSearchError: '',
        regionSearchResults: [],

        init() {
            this.hydrated = true;
            this.idempotencyKey = this.generateIdempotencyKey();

            if (this.canCheckout && this.addresses.length > 0 && this.selectedAddressId && this.quoteUrl) {
                const current = this.selectedAddress;
                if (current && current.has_valid_region) {
                    this.fetchShippingQuote(true);
                }
            }
        },

        get selectedAddress() {
            if (!this.selectedAddressId) {
                return null;
            }
            return this.addresses.find((item) => Number(item.id) === Number(this.selectedAddressId)) || null;
        },

        get groupedRates() {
            const groups = [];
            const groupMap = new Map();

            for (const rate of this.rates) {
                const code = String(rate.courier_code || '').toLowerCase();
                if (!groupMap.has(code)) {
                    const entry = {
                        courier_code: code,
                        courier_name: rate.courier_name || code.toUpperCase(),
                        minCost: Number(rate.cost) || 0,
                        services: [],
                    };
                    groupMap.set(code, entry);
                    groups.push(entry);
                }
                const group = groupMap.get(code);
                group.services.push(rate);
                const cost = Number(rate.cost) || 0;
                if (cost < group.minCost) {
                    group.minCost = cost;
                }
            }

            return groups;
        },

        get shippingCostPreview() {
            return this.selectedRate ? (Number(this.selectedRate.cost) || 0) : 0;
        },

        get estimatedGrandTotal() {
            const subtotal = Number(this.serverSummary.subtotal) || 0;
            const discount = Number(this.serverSummary.discount_amount) || 0;
            const tax = Number(this.serverSummary.tax_amount) || 0;

            return Math.max(0, subtotal - discount) + tax + this.shippingCostPreview;
        },

        get canSubmitOrder() {
            return this.canCheckout
                && Boolean(this.storeUrl)
                && Boolean(this.selectedAddressId)
                && Boolean(this.selectedRate)
                && !this.quoteLoading
                && !this.submitting;
        },

        get hasModalSelectedRegion() {
            return Boolean(this.addressForm.province_name && this.addressForm.city_name);
        },

        get modalFormattedRegion() {
            const parts = [
                this.addressForm.subdistrict_name,
                this.addressForm.district_name,
                this.addressForm.city_name,
                this.addressForm.province_name,
            ].filter(Boolean);
            const joined = parts.join(', ');
            return this.addressForm.postal_code ? (joined + ' ' + this.addressForm.postal_code).trim() : joined;
        },

        generateIdempotencyKey() {
            if (window.crypto && typeof window.crypto.randomUUID === 'function') {
                return window.crypto.randomUUID();
            }

            if (window.crypto && typeof window.crypto.getRandomValues === 'function') {
                const bytes = new Uint8Array(16);
                window.crypto.getRandomValues(bytes);
                return Array.from(bytes, (b) => b.toString(16).padStart(2, '0')).join('');
            }

            return 'chk-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 14);
        },

        refreshIdempotencyKey() {
            this.idempotencyKey = this.generateIdempotencyKey();
            this.submitError = '';
        },

        csrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        },

        rateKey(rate) {
            if (!rate) {
                return '';
            }
            return `${String(rate.courier_code || '').toLowerCase()}:${String(rate.service_code || '').toUpperCase()}`;
        },

        formatRupiah(value) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(Number(value) || 0));
        },

        formatWeight(grams) {
            const safeGrams = Number(grams) || 0;
            if (safeGrams >= 1000) {
                return (safeGrams / 1000).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2,
                }) + ' kg';
            }
            return new Intl.NumberFormat('id-ID').format(safeGrams) + ' gram';
        },

        formatEtd(etd) {
            if (!etd) {
                return '';
            }
            const trimmed = String(etd).trim();
            if (/^\d+(?:\s*[-–]\s*\d+)?$/.test(trimmed)) {
                return trimmed.replace(/\s*-\s*/, '–') + ' hari';
            }
            return trimmed
                .replace(/\b(days|day)\b/gi, 'hari')
                .replace(/\s*-\s*/, '–');
        },

        resetShippingSelectionAndQuote() {
            this.selectedRateKey = null;
            this.selectedRate = null;
            this.rates = [];
            this.quoteData = null;
            this.quoteError = '';
            this.quoteFetched = false;
            this.refreshIdempotencyKey();
        },

        selectAddress(addressId, forceQuote = false) {
            const normalizedId = Number(addressId);
            if (!normalizedId || this.submitting) {
                return;
            }

            if (!forceQuote && normalizedId === Number(this.selectedAddressId) && this.quoteFetched && !this.quoteError) {
                return;
            }

            this.selectedAddressId = normalizedId;
            this.resetShippingSelectionAndQuote();

            const chosen = this.selectedAddress;
            if (!chosen || !chosen.has_valid_region) {
                if (this.abortController) {
                    this.abortController.abort();
                }
                this.activeRequestToken++;
                this.quoteLoading = false;
                return;
            }

            this.fetchShippingQuote(true);
        },

        retryQuote() {
            if (this.quoteLoading || this.submitting || !this.selectedAddressId) {
                return;
            }

            this.selectedRateKey = null;
            this.selectedRate = null;
            this.refreshIdempotencyKey();
            this.fetchShippingQuote(false);
        },

        selectRate(rate) {
            if (!rate || this.submitting) {
                return;
            }

            const key = this.rateKey(rate);
            const matched = this.rates.find((item) => this.rateKey(item) === key);
            if (!matched) {
                return;
            }

            if (this.selectedRateKey !== key) {
                this.selectedRateKey = key;
                this.selectedRate = matched;
                this.refreshIdempotencyKey();
            }
        },

        async fetchShippingQuote(allowSupersede = false) {
            if (!this.canCheckout || !this.quoteUrl || !this.selectedAddressId) {
                return;
            }

            if (this.quoteLoading && !allowSupersede) {
                return;
            }

            if (this.abortController) {
                this.abortController.abort();
            }

            const requestToken = ++this.activeRequestToken;
            const targetAddressId = Number(this.selectedAddressId);

            // Reset pilihan layanan lama setiap kali quote diminta ulang.
            this.selectedRateKey = null;
            this.selectedRate = null;
            this.quoteLoading = true;
            this.quoteError = '';

            this.abortController = new AbortController();

            try {
                const response = await fetch(this.quoteUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        address_id: targetAddressId,
                    }),
                    signal: this.abortController.signal,
                });

                const payload = await response.json().catch(() => null);

                // Abaikan respons lama jika alamat berubah sebelum request ini selesai.
                if (requestToken !== this.activeRequestToken || Number(this.selectedAddressId) !== targetAddressId) {
                    return;
                }

                if (!response.ok || !payload || payload.success !== true) {
                    const validationMsg = payload?.errors?.address_id?.[0];
                    this.rates = [];
                    this.quoteData = null;
                    this.quoteFetched = false;
                    this.selectedRateKey = null;
                    this.selectedRate = null;
                    this.quoteError = validationMsg || payload?.message || 'Gagal mengambil tarif pengiriman. Silakan coba lagi.';
                    return;
                }

                const data = payload.data || {};
                this.quoteData = data;
                this.rates = Array.isArray(data.rates) ? data.rates : [];
                this.quoteFetched = true;
                this.quoteError = '';
            } catch (error) {
                if (error && error.name === 'AbortError') {
                    return;
                }

                if (requestToken === this.activeRequestToken) {
                    this.rates = [];
                    this.quoteData = null;
                    this.quoteFetched = false;
                    this.selectedRateKey = null;
                    this.selectedRate = null;
                    this.quoteError = 'Terjadi gangguan jaringan saat mengambil ongkos kirim. Silakan coba lagi.';
                }
            } finally {
                if (requestToken === this.activeRequestToken) {
                    this.quoteLoading = false;
                }
            }
        },

        handleEscapeKey() {
            if (this.deleteConfirmAddress && !this.deletingAddress) {
                this.cancelDeleteAddress();
                return;
            }
            if (this.addressModalOpen && !this.savingAddress) {
                this.closeAddressModal();
            }
        },

        showAddressFeedback(message, type = 'success') {
            this.addressFeedback = message;
            this.addressFeedbackType = type;
        },

        addressFieldError(field) {
            const errors = this.addressFormErrors?.[field];
            return Array.isArray(errors) && errors.length > 0 ? errors[0] : '';
        },

        splitAddressAndCourierNote(rawAddressLine) {
            const text = String(rawAddressLine || '');
            const markerMatch = text.match(/(?:\r?\n)?\[Catatan Kurir:\s*([^\]]+)\]\s*$/);
            if (!markerMatch) {
                return { address_line: text, courier_note: '' };
            }
            return {
                address_line: text.slice(0, markerMatch.index).trimEnd(),
                courier_note: markerMatch[1].trim(),
            };
        },

        composeAddressLineWithNote(addressLine, courierNote) {
            const cleanAddress = String(addressLine || '').trim();
            const cleanNote = String(courierNote || '').replace(/[\[\]]/g, '').trim();
            if (!cleanNote) {
                return cleanAddress;
            }
            return cleanAddress + '\n[Catatan Kurir: ' + cleanNote + ']';
        },

        focusCustomLabel() {
            if (['Rumah', 'Kantor', 'Apartemen', 'Kos'].includes(this.addressForm.label)) {
                this.addressForm.label = '';
            }
            this.$nextTick(() => {
                this.$refs.modalLabelInput?.focus();
            });
        },

        resetRegionLists() {
            this.citiesList = [];
            this.districtsList = [];
            this.subdistrictsList = [];
            this.cascadeError = '';
            this.regionKeyword = '';
            this.regionSearchDone = false;
            this.regionSearchError = '';
            this.regionSearchResults = [];
        },

        openCreateAddressModal() {
            if (this.submitting) {
                return;
            }
            this.addressModalMode = 'create';
            this.editingAddress = null;
            this.addressModalError = '';
            this.addressFormErrors = {};
            this.resetRegionLists();
            this.regionInputMode = 'cascade';
            this.addressForm = {
                label: 'Rumah',
                recipient_name: this.customerDefaults?.name || '',
                recipient_phone: this.customerDefaults?.phone || '',
                province_id: '',
                province_name: '',
                city_id: '',
                city_name: '',
                district_id: '',
                district_name: '',
                subdistrict_id: '',
                subdistrict_name: '',
                postal_code: '',
                address_line: '',
                courier_note: '',
                is_primary: this.addresses.length === 0,
            };
            this.addressModalOpen = true;
            this.loadProvinces();
        },

        openEditAddressModal(address) {
            if (!address || this.submitting) {
                return;
            }
            const parsed = this.splitAddressAndCourierNote(address.address_line);
            this.addressModalMode = 'edit';
            this.editingAddress = address;
            this.addressModalError = '';
            this.addressFormErrors = {};
            this.resetRegionLists();
            this.regionInputMode = 'cascade';
            this.addressForm = {
                label: String(address.label || ''),
                recipient_name: String(address.recipient_name || ''),
                recipient_phone: String(address.recipient_phone || ''),
                province_id: String(address.province_id || ''),
                province_name: String(address.province_name || ''),
                city_id: String(address.city_id || ''),
                city_name: String(address.city_name || ''),
                district_id: address.district_id ? String(address.district_id) : '',
                district_name: address.district_name ? String(address.district_name) : '',
                subdistrict_id: address.subdistrict_id ? String(address.subdistrict_id) : '',
                subdistrict_name: address.subdistrict_name ? String(address.subdistrict_name) : '',
                postal_code: String(address.postal_code || ''),
                address_line: parsed.address_line,
                courier_note: parsed.courier_note,
                is_primary: Boolean(address.is_primary),
            };
            this.addressModalOpen = true;
            this.hydrateCascadeListsForEdit();
        },

        closeAddressModal() {
            if (this.savingAddress) {
                return;
            }
            this.addressModalOpen = false;
        },

        switchRegionMode(mode) {
            this.regionInputMode = mode;
            if (mode === 'cascade' && this.provincesList.length === 0 && !this.cascadeLoading.provinces) {
                this.loadProvinces();
            }
        },

        buildRegionUrl(template, id) {
            return String(template || '').replace('__ID__', encodeURIComponent(String(id || '')));
        },

        async fetchRegionJson(url) {
            const response = await fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const payload = await response.json().catch(() => null);
            return { response, payload };
        },

        async loadProvinces() {
            const url = this.addressEndpoints?.regionProvincesUrl;
            if (!url) {
                return false;
            }
            if (this.provincesList.length > 0) {
                return true;
            }
            this.cascadeLoading.provinces = true;
            this.cascadeError = '';
            try {
                const { response, payload } = await this.fetchRegionJson(url);
                if (!response.ok || !payload || payload.success !== true) {
                    this.cascadeError = payload?.message || 'Daftar provinsi tidak tersedia, silakan gunakan tab Cari Cepat.';
                    return false;
                }
                this.provincesList = Array.isArray(payload.data) ? payload.data : [];
                return true;
            } catch (e) {
                this.cascadeError = 'Gagal memuat daftar provinsi. Silakan gunakan tab Cari Cepat.';
                return false;
            } finally {
                this.cascadeLoading.provinces = false;
            }
        },

        async loadCities(provinceId) {
            if (!provinceId || provinceId === 'manual') {
                this.citiesList = [];
                return false;
            }
            const url = this.buildRegionUrl(this.addressEndpoints?.regionCitiesTemplate, provinceId);
            this.cascadeLoading.cities = true;
            this.cascadeError = '';
            try {
                const { response, payload } = await this.fetchRegionJson(url);
                if (!response.ok || !payload || payload.success !== true) {
                    this.cascadeError = payload?.message || 'Daftar kota/kabupaten tidak tersedia.';
                    return false;
                }
                this.citiesList = Array.isArray(payload.data) ? payload.data : [];
                return true;
            } catch (e) {
                this.cascadeError = 'Gagal memuat daftar kota/kabupaten.';
                return false;
            } finally {
                this.cascadeLoading.cities = false;
            }
        },

        async loadDistricts(cityId) {
            if (!cityId || cityId === 'manual') {
                this.districtsList = [];
                return false;
            }
            const url = this.buildRegionUrl(this.addressEndpoints?.regionDistrictsTemplate, cityId);
            this.cascadeLoading.districts = true;
            this.cascadeError = '';
            try {
                const { response, payload } = await this.fetchRegionJson(url);
                if (!response.ok || !payload || payload.success !== true) {
                    this.cascadeError = payload?.message || 'Daftar kecamatan tidak tersedia.';
                    return false;
                }
                this.districtsList = Array.isArray(payload.data) ? payload.data : [];
                return true;
            } catch (e) {
                this.cascadeError = 'Gagal memuat daftar kecamatan.';
                return false;
            } finally {
                this.cascadeLoading.districts = false;
            }
        },

        async loadSubdistricts(districtId) {
            if (!districtId) {
                this.subdistrictsList = [];
                return false;
            }
            const url = this.buildRegionUrl(this.addressEndpoints?.regionSubdistrictsTemplate, districtId);
            this.cascadeLoading.subdistricts = true;
            this.cascadeError = '';
            try {
                const { response, payload } = await this.fetchRegionJson(url);
                if (!response.ok || !payload || payload.success !== true) {
                    this.cascadeError = payload?.message || 'Daftar kelurahan/desa tidak tersedia.';
                    return false;
                }
                this.subdistrictsList = Array.isArray(payload.data) ? payload.data : [];
                return true;
            } catch (e) {
                this.cascadeError = 'Gagal memuat daftar kelurahan/desa.';
                return false;
            } finally {
                this.cascadeLoading.subdistricts = false;
            }
        },

        async hydrateCascadeListsForEdit() {
            const loadedProvinces = await this.loadProvinces();
            if (!loadedProvinces) {
                return;
            }
            const hasProvince = this.provincesList.some((p) => String(p.id) === String(this.addressForm.province_id));
            if (hasProvince) {
                await this.loadCities(this.addressForm.province_id);
                const hasCity = this.citiesList.some((c) => String(c.id) === String(this.addressForm.city_id));
                if (hasCity) {
                    await this.loadDistricts(this.addressForm.city_id);
                    const hasDistrict = this.districtsList.some((d) => String(d.id) === String(this.addressForm.district_id));
                    if (hasDistrict) {
                        await this.loadSubdistricts(this.addressForm.district_id);
                    }
                }
            }
        },

        async onProvinceSelectChange(provinceId) {
            const selected = this.provincesList.find((item) => String(item.id) === String(provinceId));
            this.addressForm.province_id = selected ? String(selected.id) : '';
            this.addressForm.province_name = selected ? String(selected.name) : '';
            this.addressForm.city_id = '';
            this.addressForm.city_name = '';
            this.addressForm.district_id = '';
            this.addressForm.district_name = '';
            this.addressForm.subdistrict_id = '';
            this.addressForm.subdistrict_name = '';
            this.citiesList = [];
            this.districtsList = [];
            this.subdistrictsList = [];

            if (this.addressForm.province_id) {
                await this.loadCities(this.addressForm.province_id);
            }
        },

        async onCitySelectChange(cityId) {
            const selected = this.citiesList.find((item) => String(item.id) === String(cityId));
            this.addressForm.city_id = selected ? String(selected.id) : '';
            this.addressForm.city_name = selected ? String(selected.name) : '';
            this.addressForm.district_id = '';
            this.addressForm.district_name = '';
            this.addressForm.subdistrict_id = '';
            this.addressForm.subdistrict_name = '';
            this.districtsList = [];
            this.subdistrictsList = [];

            if (this.addressForm.city_id) {
                await this.loadDistricts(this.addressForm.city_id);
            }
        },

        async onDistrictSelectChange(districtId) {
            const selected = this.districtsList.find((item) => String(item.id) === String(districtId));
            this.addressForm.district_id = selected ? String(selected.id) : '';
            this.addressForm.district_name = selected ? String(selected.name) : '';
            this.addressForm.subdistrict_id = '';
            this.addressForm.subdistrict_name = '';
            this.subdistrictsList = [];

            if (this.addressForm.district_id) {
                await this.loadSubdistricts(this.addressForm.district_id);
            }
        },

        onSubdistrictSelectChange(subdistrictId) {
            const selected = this.subdistrictsList.find((item) => String(item.id) === String(subdistrictId));
            this.addressForm.subdistrict_id = selected ? String(selected.id) : '';
            this.addressForm.subdistrict_name = selected ? String(selected.name) : '';
            if (selected && selected.zip_code) {
                this.addressForm.postal_code = String(selected.zip_code).replace(/\D+/g, '').slice(0, 5);
            }
        },

        async searchRegionsInModal() {
            const keyword = String(this.regionKeyword || '').trim();
            if (keyword.length < 2) {
                this.regionSearchError = 'Masukkan minimal 2 karakter untuk mencari wilayah.';
                this.regionSearchResults = [];
                this.regionSearchDone = false;
                return;
            }

            const baseSearchUrl = this.addressEndpoints?.regionSearchUrl;
            if (!baseSearchUrl) {
                return;
            }

            this.regionSearching = true;
            this.regionSearchError = '';
            this.regionSearchDone = false;

            try {
                const url = `${baseSearchUrl}?keyword=${encodeURIComponent(keyword)}&limit=15`;
                const { response, payload } = await this.fetchRegionJson(url);
                this.regionSearchDone = true;

                if (!response.ok || !payload || payload.success !== true) {
                    this.regionSearchError = payload?.message || 'Pencarian wilayah gagal dijalankan.';
                    this.regionSearchResults = [];
                    return;
                }

                this.regionSearchResults = Array.isArray(payload.data) ? payload.data : [];
            } catch (e) {
                this.regionSearchError = 'Gangguan jaringan saat mencari wilayah.';
                this.regionSearchResults = [];
            } finally {
                this.regionSearching = false;
            }
        },

        applySearchedRegion(item) {
            if (!item) {
                return;
            }
            const destId = String(item.id || '');
            this.addressForm.province_id = destId;
            this.addressForm.province_name = String(item.province_name || '');
            this.addressForm.city_id = destId;
            this.addressForm.city_name = String(item.city_name || '');
            this.addressForm.district_id = destId;
            this.addressForm.district_name = String(item.district_name || '');
            this.addressForm.subdistrict_id = destId;
            this.addressForm.subdistrict_name = String(item.subdistrict_name || '');
            if (item.zip_code) {
                this.addressForm.postal_code = String(item.zip_code).replace(/\D+/g, '').slice(0, 5);
            }
            this.regionSearchResults = [];
        },

        async submitAddressModal() {
            if (this.savingAddress) {
                return;
            }

            this.addressModalError = '';
            this.addressFormErrors = {};

            if (!this.addressForm.province_id || !this.addressForm.city_id) {
                this.addressModalError = 'Silakan pilih wilayah pengiriman terlebih dahulu sebelum menyimpan alamat.';
                return;
            }

            const isEdit = this.addressModalMode === 'edit' && this.editingAddress;
            const url = isEdit ? this.editingAddress.update_url : this.addressEndpoints?.storeUrl;
            if (!url) {
                return;
            }

            this.savingAddress = true;

            try {
                const response = await fetch(url, {
                    method: isEdit ? 'PUT' : 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        label: String(this.addressForm.label || '').trim(),
                        recipient_name: String(this.addressForm.recipient_name || '').trim(),
                        recipient_phone: String(this.addressForm.recipient_phone || '').trim(),
                        province_id: String(this.addressForm.province_id || '').trim(),
                        province_name: String(this.addressForm.province_name || '').trim(),
                        city_id: String(this.addressForm.city_id || '').trim(),
                        city_name: String(this.addressForm.city_name || '').trim(),
                        district_id: this.addressForm.district_id ? String(this.addressForm.district_id).trim() : null,
                        district_name: this.addressForm.district_name ? String(this.addressForm.district_name).trim() : null,
                        subdistrict_id: this.addressForm.subdistrict_id ? String(this.addressForm.subdistrict_id).trim() : null,
                        subdistrict_name: this.addressForm.subdistrict_name ? String(this.addressForm.subdistrict_name).trim() : null,
                        postal_code: String(this.addressForm.postal_code || '').trim(),
                        address_line: this.composeAddressLineWithNote(this.addressForm.address_line, this.addressForm.courier_note),
                        is_primary: Boolean(this.addressForm.is_primary),
                    }),
                });

                const payload = await response.json().catch(() => null);

                if (!response.ok) {
                    if (response.status === 422 && payload?.errors) {
                        this.addressFormErrors = payload.errors;
                        this.addressModalError = Object.values(payload.errors).flat()[0] || payload?.message || 'Periksa kembali data alamat Anda.';
                    } else {
                        this.addressModalError = payload?.message || 'Gagal menyimpan alamat pengiriman.';
                    }
                    return;
                }

                if (Array.isArray(payload?.addresses)) {
                    this.addresses = payload.addresses;
                }

                const savedId = Number(payload?.address?.id || 0);
                this.addressModalOpen = false;
                this.addressListExpanded = this.addresses.length <= 1;

                this.showAddressFeedback(
                    payload?.message || (isEdit ? 'Alamat berhasil diperbarui.' : 'Alamat baru berhasil ditambahkan.'),
                    'success'
                );

                if (savedId) {
                    this.selectAddress(savedId, true);
                }
            } catch (e) {
                this.addressModalError = 'Terjadi gangguan jaringan saat menyimpan alamat. Silakan coba lagi.';
            } finally {
                this.savingAddress = false;
            }
        },

        async setPrimaryAddress(address) {
            if (!address || !address.set_primary_url || this.addressMutatingId || this.submitting) {
                return;
            }

            this.addressMutatingId = address.id;

            try {
                const response = await fetch(address.set_primary_url, {
                    method: 'PATCH',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({}),
                });

                const payload = await response.json().catch(() => null);
                if (!response.ok) {
                    this.showAddressFeedback(payload?.message || 'Gagal mengubah alamat utama.', 'error');
                    return;
                }

                if (Array.isArray(payload?.addresses)) {
                    this.addresses = payload.addresses;
                }

                this.showAddressFeedback(payload?.message || 'Alamat utama berhasil diperbarui.', 'success');
                this.selectAddress(address.id, true);
            } catch (e) {
                this.showAddressFeedback('Terjadi gangguan jaringan saat memperbarui alamat utama.', 'error');
            } finally {
                this.addressMutatingId = null;
            }
        },

        confirmDeleteAddress(address) {
            if (!address || this.submitting || this.addressMutatingId) {
                return;
            }
            this.deleteConfirmAddress = address;
        },

        cancelDeleteAddress() {
            if (this.deletingAddress) {
                return;
            }
            this.deleteConfirmAddress = null;
        },

        async performDeleteAddress() {
            const target = this.deleteConfirmAddress;
            if (!target || !target.delete_url || this.deletingAddress) {
                return;
            }

            this.deletingAddress = true;
            const deletedId = Number(target.id);
            const wasSelected = deletedId === Number(this.selectedAddressId);

            try {
                const response = await fetch(target.delete_url, {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const payload = await response.json().catch(() => null);
                if (!response.ok) {
                    this.deleteConfirmAddress = null;
                    this.showAddressFeedback(payload?.message || 'Gagal menghapus alamat.', 'error');
                    return;
                }

                this.deleteConfirmAddress = null;

                if (Array.isArray(payload?.addresses)) {
                    this.addresses = payload.addresses;
                } else {
                    this.addresses = this.addresses.filter((item) => Number(item.id) !== deletedId);
                }

                this.showAddressFeedback(payload?.message || 'Alamat berhasil dihapus.', 'success');

                if (this.addresses.length === 0) {
                    this.selectedAddressId = null;
                    this.resetShippingSelectionAndQuote();
                    this.addressListExpanded = true;
                    if (this.abortController) {
                        this.abortController.abort();
                    }
                    this.activeRequestToken++;
                    this.quoteLoading = false;
                    return;
                }

                this.addressListExpanded = this.addresses.length <= 1;

                if (wasSelected || !this.addresses.some((item) => Number(item.id) === Number(this.selectedAddressId))) {
                    const nextAddress = payload?.promoted_primary_address
                        || this.addresses.find((item) => item.is_primary)
                        || this.addresses[0];
                    if (nextAddress && nextAddress.id) {
                        this.selectAddress(nextAddress.id, true);
                    }
                }
            } catch (e) {
                this.deleteConfirmAddress = null;
                this.showAddressFeedback('Terjadi gangguan jaringan saat menghapus alamat.', 'error');
            } finally {
                this.deletingAddress = false;
            }
        },

        async submitOrder() {
            if (!this.canSubmitOrder) {
                return;
            }

            if (!this.idempotencyKey) {
                this.idempotencyKey = this.generateIdempotencyKey();
            }

            this.submitting = true;
            this.submitError = '';

            try {
                const response = await fetch(this.storeUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Idempotency-Key': this.idempotencyKey,
                    },
                    body: JSON.stringify({
                        address_id: Number(this.selectedAddressId),
                        courier_code: String(this.selectedRate.courier_code || '').toLowerCase(),
                        service_code: String(this.selectedRate.service_code || '').toUpperCase(),
                        idempotency_key: this.idempotencyKey,
                        customer_notes: String(this.customerNotes || '').trim() || null,
                    }),
                });

                const payload = await response.json().catch(() => null);

                if (!response.ok || !payload || payload.success !== true) {
                    const firstValidationError = payload?.errors
                        ? Object.values(payload.errors).flat()[0]
                        : null;

                    this.submitError = firstValidationError
                        || payload?.message
                        || 'Gagal membuat pesanan. Silakan periksa kembali data checkout Anda.';

                    if (response.status === 409) {
                        this.idempotencyKey = this.generateIdempotencyKey();
                    }

                    return;
                }

                if (payload.data && payload.data.redirect_url) {
                    window.location.assign(payload.data.redirect_url);
                }
            } catch (error) {
                // Pertahankan idempotencyKey yang sama saat retry akibat gangguan jaringan.
                this.submitError = 'Terjadi gangguan jaringan saat membuat pesanan. Silakan coba kirim ulang.';
            } finally {
                this.submitting = false;
            }
        },
    }));
});
</script>
@endpush
@endif
