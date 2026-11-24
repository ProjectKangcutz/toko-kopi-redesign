@extends('layouts.storefront')

@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('canonical', $seo['canonical'])
@section('robots', $seo['robots'])
@section('og_title', $seo['title'])
@section('og_description', $seo['description'])

@push('structured_data')
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
<div class="container py-3 py-md-4">
    <x-breadcrumb :items="$breadcrumbs" />

    <section class="kn-catalog-hero p-4 p-md-5 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3">
            <div style="max-width: 680px;">
                <span class="kn-eyebrow mb-2">Koleksi Kopi &amp; Produk Nusantara</span>
                <h1 class="display-6 fw-bold mb-2" style="color: var(--kn-espresso); letter-spacing: -0.025em;">Katalog Produk</h1>
                <p class="text-mlm-muted mb-0">
                    {{ $storeSettings['store_description'] ?? 'Temukan seluruh koleksi produk UMKM lokal pilihan siap kirim.' }}
                </p>
            </div>

            <div class="bg-white rounded-3 px-3 py-2 border d-inline-flex align-items-center gap-2 align-self-start align-self-lg-end kn-tabular shadow-sm" style="border-color: var(--kn-border) !important;">
                <i class="bi bi-box-seam text-mlm-primary" aria-hidden="true"></i>
                <span class="small fw-semibold" style="color: var(--kn-espresso);">
                    @if($products->total() > 0 && $products->isNotEmpty())
                        Menampilkan {{ $products->firstItem() }}–{{ $products->lastItem() }} dari {{ number_format($products->total(), 0, ',', '.') }} produk tersedia
                    @else
                        {{ number_format($products->total(), 0, ',', '.') }} produk tersedia
                    @endif
                </span>
            </div>
        </div>
    </section>

    @if($categories->isNotEmpty())
        <section class="mb-4" aria-label="Kategori katalog">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h2 class="kn-eyebrow kn-eyebrow-forest mb-0">Jelajahi Kategori</h2>
            </div>
            <div class="kn-chip-rail">
                <a href="{{ route('storefront.catalog') }}" class="mlm-category-chip active">
                    Semua Produk
                </a>
                @foreach($categories as $category)
                    <a href="{{ route('storefront.category', $category->slug) }}" class="mlm-category-chip">
                        <span>{{ $category->name }}</span>
                        <span class="badge rounded-pill bg-mlm-secondary text-mlm-primary ms-2 kn-tabular">{{ (int) ($category->active_products_count ?? 0) }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="pb-4">
        <div class="mlm-card p-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-grid-3x3-gap text-mlm-primary" aria-hidden="true"></i>
                <h2 class="h6 fw-bold mb-0" style="color: var(--kn-espresso);">Daftar Produk Tersedia</h2>
            </div>
            <span class="small text-mlm-muted kn-tabular">
                Halaman {{ $products->currentPage() }} dari {{ max(1, $products->lastPage()) }}
            </span>
        </div>

        @if($products->total() > 0 && $products->isEmpty())
            <x-empty-state
                icon="bi-file-earmark-x"
                title="Halaman Tidak Memiliki Produk"
                message="Nomor halaman yang Anda buka melebihi jumlah halaman katalog yang tersedia."
                action-label="Kembali ke Halaman 1"
                :action-url="route('storefront.catalog')"
            />
        @elseif($products->isEmpty())
            <x-empty-state
                icon="bi-box-seam"
                title="Belum Ada Produk Tersedia"
                message="Saat ini belum ada produk aktif dengan stok tersedia di katalog toko."
                action-label="Kembali ke Beranda"
                :action-url="route('storefront.home')"
            />
        @else
            <div class="row g-3 g-lg-4">
                @foreach($products as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                <x-pagination :paginator="$products" />
            </div>
        @endif
    </section>
</div>
@endsection
