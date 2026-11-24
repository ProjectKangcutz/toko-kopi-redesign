@extends('layouts.storefront')

@php
    $storeName = $storeSettings['store_name'] ?? 'Pasar Lokal Nusantara';
    $catalogUrl = Route::has('storefront.catalog') ? route('storefront.catalog') : url('/produk');
    $articlesUrl = Route::has('storefront.articles') ? route('storefront.articles') : url('/artikel');
    $hasAnyProducts = $featuredProducts->isNotEmpty()
        || $latestProducts->isNotEmpty()
        || $bestsellingProducts->isNotEmpty()
        || $promoProducts->isNotEmpty();

    $firstHero = $heroBanners->first();
    $heroOgImage = $firstHero
        ? \App\Services\Storefront\HomepageService::mediaUrl($firstHero->image_desktop_path)
        : ($heroFallback['image_url'] ?? null);
@endphp

@section('title', $storeName.' — Beranda')
@section('meta_description', $seoMetaDescription)
@section('canonical', route('storefront.home'))
@section('og_title', $storeName.' — Beranda')
@section('og_description', $seoMetaDescription)
@if(!empty($heroOgImage))
    @section('og_image', $heroOgImage)
@endif

@section('content')
{{-- 1. Hero Section (Dikonfigurasi via Banner placement=hero atau Pengaturan Toko) --}}
<section class="container pt-3 pt-md-4 pb-4">
    @if($heroBanners->isNotEmpty())
        <div id="heroBannerCarousel" class="carousel slide kn-hero-frame mlm-hero-carousel" data-bs-ride="carousel">
            @if($heroBanners->count() > 1)
                <div class="carousel-indicators">
                    @foreach($heroBanners as $banner)
                        <button
                            type="button"
                            data-bs-target="#heroBannerCarousel"
                            data-bs-slide-to="{{ $loop->index }}"
                            class="{{ $loop->first ? 'active' : '' }}"
                            aria-current="{{ $loop->first ? 'true' : 'false' }}"
                            aria-label="Slide {{ $loop->iteration }}: {{ $banner->title }}"
                        ></button>
                    @endforeach
                </div>
            @endif

            <div class="carousel-inner">
                @foreach($heroBanners as $banner)
                    @php
                        $desktopImg = \App\Services\Storefront\HomepageService::mediaUrl($banner->image_desktop_path);
                        $mobileImg = \App\Services\Storefront\HomepageService::mediaUrl($banner->image_mobile_path);
                        $ctaUrl = \App\Services\Storefront\HomepageService::sanitizeUrl($banner->cta_url);
                    @endphp
                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                        <div class="mlm-hero-slide position-relative">
                            @if($desktopImg)
                                <picture>
                                    @if($mobileImg)
                                        <source media="(max-width: 767.98px)" srcset="{{ $mobileImg }}">
                                    @endif
                                    <img
                                        src="{{ $desktopImg }}"
                                        alt="{{ $banner->alt_text ?: $banner->title }}"
                                        class="mlm-hero-img w-100"
                                        loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                        @if($loop->first) fetchpriority="high" @endif
                                        decoding="async"
                                    >
                                </picture>
                            @endif

                            <div class="mlm-hero-overlay d-flex align-items-center">
                                <div class="p-4 p-md-5 text-white" style="max-width: 680px;">
                                    <div class="kn-hero-pill mb-3">
                                        <i class="bi bi-cup-hot" aria-hidden="true"></i>
                                        <span>Satu Nusantara, Seribu Cerita Kopi.</span>
                                    </div>

                                    @if($loop->first)
                                        <h1 class="display-5 fw-bold mb-3 lh-sm" style="letter-spacing: -0.025em;">{{ $banner->title }}</h1>
                                    @else
                                        <h2 class="display-5 fw-bold mb-3 lh-sm" style="letter-spacing: -0.025em;">{{ $banner->title }}</h2>
                                    @endif

                                    @if(!empty($banner->subtitle))
                                        <p class="fs-6 mb-4 opacity-90" style="color: var(--kn-cream);">{{ $banner->subtitle }}</p>
                                    @endif

                                    @if($ctaUrl && !empty($banner->cta_label))
                                        <a href="{{ $ctaUrl }}" class="btn btn-accent px-4">
                                            <span>{{ $banner->cta_label }}</span>
                                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($heroBanners->count() > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#heroBannerCarousel" data-bs-slide="prev" aria-label="Banner sebelumnya">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#heroBannerCarousel" data-bs-slide="next" aria-label="Banner berikutnya">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
            @endif
        </div>
    @else
        <div class="kn-hero-fallback p-4 p-md-5">
            <div class="row align-items-center g-4 g-lg-5">
                <div class="col-12 col-lg-7">
                    <div class="kn-hero-pill mb-3">
                        <i class="bi bi-patch-check-fill" aria-hidden="true"></i>
                        <span>{{ $heroFallback['badge'] }}</span>
                    </div>

                    <h1 class="display-5 fw-bold mb-3 lh-sm text-white" style="letter-spacing: -0.03em;">
                        {{ $heroFallback['title'] }}
                    </h1>

                    @if(!empty($heroFallback['subtitle']))
                        <p class="fs-6 mb-4 opacity-90" style="color: var(--kn-cream); max-width: 580px;">{{ $heroFallback['subtitle'] }}</p>
                    @endif

                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <a href="{{ $heroFallback['cta_url'] }}" class="btn btn-accent px-4">
                            <i class="bi bi-bag" aria-hidden="true"></i>
                            <span>{{ $heroFallback['cta_label'] }}</span>
                        </a>
                        @guest('customer')
                            <a href="{{ route('customer.register') }}" class="btn px-4 fw-semibold" style="background-color: rgba(255,255,255,0.1); border: 1px solid rgba(213,183,138,0.35); color: var(--kn-cream); min-height: 46px; display: inline-flex; align-items: center;">
                                Daftar Pelanggan
                            </a>
                        @endguest
                    </div>

                    <div class="row g-2 pt-2">
                        <div class="col-12 col-sm-4">
                            <div class="kn-hero-feature-tile d-flex align-items-center gap-2 small">
                                <i class="bi bi-cup-hot text-warning" aria-hidden="true"></i>
                                <span>Kurasi Pilihan Nusantara</span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="kn-hero-feature-tile d-flex align-items-center gap-2 small">
                                <i class="bi bi-shield-check text-warning" aria-hidden="true"></i>
                                <span>Transaksi Aman Midtrans</span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="kn-hero-feature-tile d-flex align-items-center gap-2 small">
                                <i class="bi bi-truck text-warning" aria-hidden="true"></i>
                                <span>Kurir Resmi Indonesia</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    @if(!empty($heroFallback['image_url']))
                        <img
                            src="{{ $heroFallback['image_url'] }}"
                            alt="{{ $heroFallback['title'] }}"
                            class="img-fluid rounded-4 shadow-lg w-100 object-fit-cover"
                            style="max-height: 360px; border: 1px solid rgba(213, 183, 138, 0.28);"
                            loading="eager"
                            fetchpriority="high"
                            decoding="async"
                        >
                    @else
                        <div class="rounded-4 p-4 p-md-5 text-center" style="background-color: rgba(244, 235, 221, 0.08); border: 1px solid rgba(213, 183, 138, 0.25);">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 72px; height: 72px; background-color: rgba(213, 183, 138, 0.16); color: var(--kn-sand);">
                                <i class="bi bi-cup-hot-fill fs-1" aria-hidden="true"></i>
                            </span>
                            <h2 class="h5 fw-bold text-white mb-2">Satu Nusantara, Seribu Cerita Kopi.</h2>
                            <p class="small mb-0 opacity-85" style="color: var(--kn-cream);">
                                Produk pilihan langsung dari mitra &amp; produsen UMKM Indonesia dengan pengiriman resmi ke seluruh nusantara.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</section>

{{-- 2. Kategori Unggulan --}}
@if($categories->isNotEmpty())
    <section class="container py-4 py-md-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
            <div>
                <span class="kn-eyebrow">Eksplorasi Koleksi</span>
                <h2 class="kn-section-title mt-1 mb-1">Kategori Unggulan</h2>
                <p class="kn-section-subtitle mb-0">Temukan produk lokal berdasarkan kategori pilihan</p>
            </div>
            <a href="{{ $catalogUrl }}" class="btn btn-sm btn-outline-primary flex-shrink-0 align-self-start align-self-md-auto">
                <span>Semua Produk</span>
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
        </div>

        <div class="row g-3">
            @foreach($categories as $category)
                @php
                    $categoryUrl = Route::has('storefront.category')
                        ? route('storefront.category', $category->slug)
                        : url('/kategori/'.$category->slug);
                    $catImage = \App\Services\Storefront\HomepageService::mediaUrl($category->image_path);
                @endphp
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="{{ $categoryUrl }}" class="kn-category-tile h-100 d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3 text-decoration-none text-reset">
                        @if($catImage)
                            <img
                                src="{{ $catImage }}"
                                alt="{{ $category->name }}"
                                class="kn-category-thumb object-fit-cover"
                                loading="lazy"
                                decoding="async"
                            >
                        @else
                            <span class="kn-category-thumb d-inline-flex align-items-center justify-content-center">
                                <i class="bi bi-grid fs-4" aria-hidden="true"></i>
                            </span>
                        @endif
                        <div class="min-w-0 flex-grow-1">
                            <h3 class="h6 fw-bold mb-1 text-truncate" style="color: var(--kn-espresso);">{{ $category->name }}</h3>
                            <span class="kn-count-pill">{{ (int) ($category->active_products_count ?? 0) }} produk</span>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </section>
@endif

@if(!$hasAnyProducts)
    {{-- 12. Empty State Saat Belum Ada Produk Aktif Terjual/Tersedia --}}
    <section class="container py-4">
        <x-empty-state
            icon="bi-box-seam"
            title="Katalog Produk Belum Tersedia"
            message="Saat ini belum ada produk aktif dengan stok tersedia untuk ditampilkan. Silakan kembali lagi nanti."
        />
    </section>
@else
    {{-- 3. Produk Promo Aktif --}}
    @if($promoProducts->isNotEmpty())
        <section class="kn-band-cream py-5">
            <div class="container">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
                    <div>
                        <span class="kn-eyebrow">Penawaran Terbatas</span>
                        <h2 class="kn-section-title mt-1 mb-1">Promo Spesial Hari Ini</h2>
                        <p class="kn-section-subtitle mb-0">Penawaran harga hemat yang sedang berlaku</p>
                    </div>
                    <a href="{{ $catalogUrl }}?promo=1" class="btn btn-sm btn-outline-primary flex-shrink-0 align-self-start align-self-md-auto">
                        <span>Lihat Promo</span>
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
                <div class="row g-3 g-lg-4">
                    @foreach($promoProducts as $product)
                        <div class="col-6 col-md-4 col-lg-3">
                            <x-product-card :product="$product" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- 4. Produk Unggulan --}}
    @if($featuredProducts->isNotEmpty())
        <section id="katalog-unggulan" class="container py-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
                <div>
                    <span class="kn-eyebrow kn-eyebrow-forest">Kurasi Utama</span>
                    <h2 class="kn-section-title mt-1 mb-1">Produk Pilihan</h2>
                    <p class="kn-section-subtitle mb-0">Rekomendasi terbaik pilihan pengelola toko</p>
                </div>
                <a href="{{ $catalogUrl }}" class="btn btn-sm btn-outline-primary flex-shrink-0 align-self-start align-self-md-auto">
                    <span>Lihat Semua</span>
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
            <div class="row g-3 g-lg-4">
                @foreach($featuredProducts as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- 5. Banner Promosi Tengah (placement=promo_middle) --}}
    @if($promoBanners->isNotEmpty())
        <section class="container py-3">
            <div class="row g-3 g-lg-4">
                @foreach($promoBanners as $banner)
                    @php
                        $desktopImg = \App\Services\Storefront\HomepageService::mediaUrl($banner->image_desktop_path);
                        $mobileImg = \App\Services\Storefront\HomepageService::mediaUrl($banner->image_mobile_path);
                        $ctaUrl = \App\Services\Storefront\HomepageService::sanitizeUrl($banner->cta_url);
                    @endphp
                    <div class="{{ $promoBanners->count() === 1 ? 'col-12' : 'col-12 col-md-6' }}">
                        <div class="mlm-card mlm-promo-banner overflow-hidden position-relative border-0" style="min-height: 230px; background-color: var(--kn-espresso);">
                            @if($desktopImg)
                                <picture>
                                    @if($mobileImg)
                                        <source media="(max-width: 767.98px)" srcset="{{ $mobileImg }}">
                                    @endif
                                    <img
                                        src="{{ $desktopImg }}"
                                        alt="{{ $banner->alt_text ?: $banner->title }}"
                                        class="w-100 object-fit-cover"
                                        style="height: 240px;"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                </picture>
                            @endif
                            <div class="mlm-promo-banner-overlay p-4 d-flex flex-column justify-content-end text-white" style="background: linear-gradient(0deg, rgba(32, 21, 16, 0.90) 0%, rgba(48, 32, 25, 0.35) 75%);">
                                <h3 class="h4 fw-bold mb-1">{{ $banner->title }}</h3>
                                @if(!empty($banner->subtitle))
                                    <p class="small mb-3 opacity-90" style="color: var(--kn-cream);">{{ $banner->subtitle }}</p>
                                @endif
                                @if($ctaUrl && !empty($banner->cta_label))
                                    <div>
                                        <a href="{{ $ctaUrl }}" class="btn btn-sm btn-accent px-3">{{ $banner->cta_label }}</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- 6. Produk Terlaris Berdasarkan Transaksi Valid --}}
    @if($bestsellingProducts->isNotEmpty())
        <section class="kn-band-sage py-5 mt-3">
            <div class="container">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
                    <div>
                        <span class="kn-eyebrow kn-eyebrow-forest">Favorit Pelanggan</span>
                        <h2 class="kn-section-title mt-1 mb-1">Produk Terlaris</h2>
                        <p class="kn-section-subtitle mb-0">Paling banyak dipesan oleh pelanggan kami</p>
                    </div>
                    <a href="{{ $catalogUrl }}?sort=bestseller" class="btn btn-sm btn-outline-primary flex-shrink-0 align-self-start align-self-md-auto">
                        <span>Lihat Katalog</span>
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
                <div class="row g-3 g-lg-4">
                    @foreach($bestsellingProducts as $product)
                        <div class="col-6 col-md-4 col-lg-3">
                            <x-product-card :product="$product" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- 7. Produk Terbaru --}}
    @if($latestProducts->isNotEmpty())
        <section class="container py-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
                <div>
                    <span class="kn-eyebrow">Rilisan Terkini</span>
                    <h2 class="kn-section-title mt-1 mb-1">Produk Terbaru</h2>
                    <p class="kn-section-subtitle mb-0">Koleksi terbaru yang baru saja hadir di toko</p>
                </div>
                <a href="{{ $catalogUrl }}?sort=latest" class="btn btn-sm btn-outline-primary flex-shrink-0 align-self-start align-self-md-auto">
                    <span>Jelajahi Semua</span>
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
            <div class="row g-3 g-lg-4">
                @foreach($latestProducts as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endif

{{-- 8. Keunggulan Toko & Informasi Layanan --}}
<section id="keunggulan-toko" class="container py-5">
    <div class="text-center mx-auto mb-4" style="max-width: 640px;">
        <span class="kn-eyebrow kn-eyebrow-forest">Komitmen Layanan</span>
        <h2 class="kn-section-title mt-1 mb-2">Mengapa Belanja di {{ $storeName }}</h2>
        <p class="kn-section-subtitle mb-0">Standar layanan dan jaminan kualitas transaksi untuk pelanggan</p>
    </div>
    <div class="row g-3 g-lg-4">
        @foreach($advantages as $advantage)
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="mlm-card p-4 h-100">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-3 mb-3" style="width: 48px; height: 48px; background-color: var(--kn-cream); color: var(--kn-espresso); border: 1px solid rgba(166, 107, 67, 0.25);">
                        <i class="bi {{ $advantage['icon'] }} fs-4" aria-hidden="true"></i>
                    </span>
                    <h3 class="h6 fw-bold mb-2" style="color: var(--kn-espresso);">{{ $advantage['title'] }}</h3>
                    <p class="small text-mlm-muted mb-0">{{ $advantage['description'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>

{{-- 9. Testimoni Disetujui --}}
@if($testimonials->isNotEmpty())
    <section class="kn-band-cream py-5">
        <div class="container">
            <div class="mb-4">
                <span class="kn-eyebrow">Pengalaman Pelanggan</span>
                <h2 class="kn-section-title mt-1 mb-1">Kata Pelanggan Kami</h2>
                <p class="kn-section-subtitle mb-0">Pengalaman belanja nyata dari pelanggan di berbagai kota</p>
            </div>
            <div class="row g-3 g-lg-4">
                @foreach($testimonials as $testimonial)
                    @php
                        $rating = max(1, min(5, (int) $testimonial->rating));
                        $avatarUrl = \App\Services\Storefront\HomepageService::mediaUrl($testimonial->avatar_path);
                    @endphp
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="mlm-card p-4 h-100 d-flex flex-column">
                            <div class="text-mlm-accent mb-3" aria-label="Rating {{ $rating }} dari 5">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi {{ $i <= $rating ? 'bi-star-fill' : 'bi-star' }}" aria-hidden="true"></i>
                                @endfor
                            </div>
                            <blockquote class="small text-mlm-text mb-4 flex-grow-1 lh-lg">
                                &ldquo;{{ $testimonial->quote }}&rdquo;
                            </blockquote>
                            <div class="d-flex align-items-center gap-3 pt-3 border-top" style="border-color: var(--kn-border-soft) !important;">
                                @if($avatarUrl)
                                    <img
                                        src="{{ $avatarUrl }}"
                                        alt="{{ $testimonial->customer_name }}"
                                        class="rounded-circle object-fit-cover flex-shrink-0"
                                        style="width: 42px; height: 42px;"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                @else
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold flex-shrink-0" style="width: 42px; height: 42px; background-color: var(--kn-espresso); color: var(--kn-cream);">
                                        {{ mb_strtoupper(mb_substr($testimonial->customer_name, 0, 1)) }}
                                    </span>
                                @endif
                                <div class="min-w-0">
                                    <div class="fw-bold small text-truncate">{{ $testimonial->customer_name }}</div>
                                    @if(!empty($testimonial->customer_role_or_city))
                                        <div class="text-mlm-muted" style="font-size: 0.78rem;">{{ $testimonial->customer_role_or_city }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- 10. Artikel Terbaru --}}
@if($articles->isNotEmpty())
    <section class="container py-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
            <div>
                <span class="kn-eyebrow kn-eyebrow-forest">Jurnal &amp; Panduan</span>
                <h2 class="kn-section-title mt-1 mb-1">Artikel &amp; Cerita UMKM</h2>
                <p class="kn-section-subtitle mb-0">Tips produk, inspirasi lokal, dan kabar terbaru dari toko</p>
            </div>
            <a href="{{ $articlesUrl }}" class="btn btn-sm btn-outline-primary flex-shrink-0 align-self-start align-self-md-auto">
                <span>Semua Artikel</span>
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
        </div>
        <div class="row g-3 g-lg-4">
            @foreach($articles as $article)
                @php
                    $articleUrl = Route::has('storefront.article')
                        ? route('storefront.article', $article->slug)
                        : url('/artikel/'.$article->slug);
                    $articleImage = \App\Services\Storefront\HomepageService::mediaUrl($article->featured_image_path);
                @endphp
                <div class="col-12 col-md-4">
                    <article class="mlm-card h-100 d-flex flex-column overflow-hidden">
                        @if($articleImage)
                            <a href="{{ $articleUrl }}" class="d-block overflow-hidden" style="background-color: var(--kn-cream);">
                                <img
                                    src="{{ $articleImage }}"
                                    alt="{{ $article->featured_image_alt ?: $article->title }}"
                                    class="w-100 object-fit-cover"
                                    style="height: 205px;"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </a>
                        @endif
                        <div class="p-3 p-md-4 d-flex flex-column flex-grow-1">
                            <div class="small text-mlm-muted mb-2">
                                {{ ($article->published_at ?? $article->created_at)?->translatedFormat('d M Y') }}
                                @if($article->author)
                                    &bull; {{ $article->author->name }}
                                @endif
                            </div>
                            <h3 class="h6 fw-bold mb-2">
                                <a href="{{ $articleUrl }}" class="text-decoration-none text-reset">{{ $article->title }}</a>
                            </h3>
                            @if(!empty($article->excerpt))
                                <p class="small text-mlm-muted mb-3 flex-grow-1">{{ \Illuminate\Support\Str::limit($article->excerpt, 120) }}</p>
                            @endif
                            <a href="{{ $articleUrl }}" class="small fw-semibold text-mlm-primary text-decoration-none mt-auto">
                                Baca selengkapnya <i class="bi bi-arrow-right" aria-hidden="true"></i>
                            </a>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </section>
@endif

{{-- 11. CTA Penutup Menuju Katalog Produk --}}
<section class="container pb-5 pt-2">
    <div class="kn-band-espresso rounded-4 p-4 p-md-5 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-4 shadow-sm">
        <div style="max-width: 620px;">
            <span class="kn-hero-pill mb-2">Satu Nusantara, Seribu Cerita Kopi.</span>
            <h2 class="h3 fw-bold text-white mb-2">Temukan Produk Lokal Favorit Anda</h2>
            <p class="small mb-0 opacity-85" style="color: var(--kn-cream);">
                Jelajahi seluruh koleksi produk UMKM siap kirim dari {{ $storeName }}.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2 flex-shrink-0">
            <a href="{{ $catalogUrl }}" class="btn btn-accent px-4">
                <i class="bi bi-grid" aria-hidden="true"></i>
                <span>Buka Katalog Produk</span>
            </a>
        </div>
    </div>
</section>
@endsection
