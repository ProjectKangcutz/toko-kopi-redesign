@extends('layouts.storefront')

@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('canonical', $seo['canonical'])
@section('robots', $seo['robots'])
@section('og_title', $seo['title'])
@section('og_description', $seo['description'])
@if(!empty($seo['og_image']))
    @section('og_image', $seo['og_image'])
@endif

@push('structured_data')
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
<div class="container py-3 py-md-4">
    <x-breadcrumb :items="$breadcrumbs" />

    <section class="kn-catalog-hero p-4 p-md-5 mb-4 overflow-hidden">
        <div class="row align-items-center g-4">
            <div class="col-12 {{ $categoryImageUrl ? 'col-lg-8' : '' }}">
                <span class="kn-eyebrow mb-2">Kategori Pilihan</span>
                <h1 class="display-6 fw-bold mb-2" style="color: var(--kn-espresso); letter-spacing: -0.025em;">{{ $category->name }}</h1>
                @if(!empty($category->description))
                    <p class="text-mlm-muted mb-3" style="max-width: 700px;">{{ $category->description }}</p>
                @endif
                <div class="bg-white rounded-3 px-3 py-2 border d-inline-flex align-items-center gap-2 kn-tabular shadow-sm" style="border-color: var(--kn-border) !important;">
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

            @if($categoryImageUrl)
                <div class="col-12 col-lg-4">
                    <img
                        src="{{ $categoryImageUrl }}"
                        alt="{{ $category->name }}"
                        class="w-100 rounded-4 object-fit-cover shadow-sm"
                        style="max-height: 210px; border: 1px solid rgba(166, 107, 67, 0.2);"
                        loading="eager"
                        decoding="async"
                    >
                </div>
            @endif
        </div>
    </section>

    @if($subcategories->isNotEmpty())
        <section class="mb-4" aria-label="Subkategori {{ $category->name }}">
            <h2 class="kn-eyebrow kn-eyebrow-forest mb-2">Subkategori {{ $category->name }}</h2>
            <div class="kn-chip-rail">
                @foreach($subcategories as $subcategory)
                    <a href="{{ route('storefront.category', $subcategory->slug) }}" class="mlm-category-chip">
                        <span>{{ $subcategory->name }}</span>
                        <span class="badge rounded-pill bg-mlm-secondary text-mlm-primary ms-2 kn-tabular">{{ (int) ($subcategory->active_products_count ?? 0) }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="pb-4">
        <div class="mlm-card p-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h2 class="h6 fw-bold mb-0" style="color: var(--kn-espresso);">Produk Kategori {{ $category->name }}</h2>
            <a href="{{ route('storefront.catalog') }}" class="btn btn-sm btn-outline-primary">Semua Produk</a>
        </div>

        @if($products->total() > 0 && $products->isEmpty())
            <x-empty-state
                icon="bi-file-earmark-x"
                title="Halaman Tidak Memiliki Produk"
                message="Nomor halaman yang Anda buka melebihi jumlah halaman pada kategori ini."
                action-label="Kembali ke Halaman 1"
                :action-url="route('storefront.category', $category->slug)"
            />
        @elseif($products->isEmpty())
            <x-empty-state
                icon="bi-grid"
                title="Belum Ada Produk di Kategori Ini"
                message="Saat ini belum ada produk aktif dengan stok tersedia pada kategori {{ $category->name }}."
                action-label="Lihat Semua Produk"
                :action-url="route('storefront.catalog')"
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
