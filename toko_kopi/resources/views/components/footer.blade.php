@props([
    'storeSettings' => [],
    'storefrontNav' => [],
])

@php
    $storeName = $storeSettings['store_name'] ?? 'Pasar Lokal Nusantara';
    $footerCol1Menus = collect($storefrontNav['footerCol1Menus'] ?? []);
    $footerCol2Menus = collect($storefrontNav['footerCol2Menus'] ?? []);
    $footerPolicyMenus = collect($storefrontNav['footerPolicyMenus'] ?? []);
    $waNumber = preg_replace('/\D+/', '', (string) ($storeSettings['store_whatsapp'] ?? ''));
    $catalogUrl = Route::has('storefront.catalog') ? route('storefront.catalog') : url('/produk');

    $socialLinks = array_filter([
        'Instagram' => ['url' => \App\Services\Storefront\HomepageService::sanitizeUrl($storeSettings['social_instagram'] ?? null), 'icon' => 'bi-instagram'],
        'Facebook' => ['url' => \App\Services\Storefront\HomepageService::sanitizeUrl($storeSettings['social_facebook'] ?? null), 'icon' => 'bi-facebook'],
        'TikTok' => ['url' => \App\Services\Storefront\HomepageService::sanitizeUrl($storeSettings['social_tiktok'] ?? null), 'icon' => 'bi-tiktok'],
        'YouTube' => ['url' => \App\Services\Storefront\HomepageService::sanitizeUrl($storeSettings['social_youtube'] ?? null), 'icon' => 'bi-youtube'],
    ], fn ($item) => !empty($item['url']));
@endphp

<footer class="kn-footer mt-auto">
    {{-- Trust Guarantee Ribbon --}}
    <div class="kn-footer-ribbon py-4">
        <div class="container">
            <div class="row g-3 g-lg-4">
                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-center gap-3">
                        <span class="kn-footer-ribbon-icon">
                            <i class="bi bi-patch-check fs-5" aria-hidden="true"></i>
                        </span>
                        <div>
                            <div class="fw-bold small text-white">Kurasi Produk Asli Nusantara</div>
                            <div class="small opacity-75" style="font-size: 0.8rem;">Dikurasi langsung dari mitra &amp; produsen lokal terpercaya.</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-center gap-3">
                        <span class="kn-footer-ribbon-icon">
                            <i class="bi bi-shield-lock fs-5" aria-hidden="true"></i>
                        </span>
                        <div>
                            <div class="fw-bold small text-white">Pembayaran Terintegrasi Midtrans</div>
                            <div class="small opacity-75" style="font-size: 0.8rem;">Mendukung Virtual Account, QRIS, e-Wallet, &amp; kartu resmi.</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-center gap-3">
                        <span class="kn-footer-ribbon-icon">
                            <i class="bi bi-truck fs-5" aria-hidden="true"></i>
                        </span>
                        <div>
                            <div class="fw-bold small text-white">Pengiriman Resmi Se-Indonesia</div>
                            <div class="small opacity-75" style="font-size: 0.8rem;">Perhitungan tarif transparan dan pelacakan nomor resi langsung.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container pt-5 pb-4">
        <div class="row g-4 g-lg-5">
            <div class="col-12 col-lg-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    @if(!empty($storeSettings['logo_path']))
                        <img
                            src="{{ asset('storage/'.$storeSettings['logo_path']) }}"
                            alt="{{ $storeName }}"
                            class="rounded-2 object-fit-contain bg-white p-1"
                            style="height: 38px; max-width: 128px;"
                        >
                    @else
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width: 38px; height: 38px; background-color: rgba(213, 183, 138, 0.16); color: var(--kn-sand);">
                            <i class="bi bi-cup-hot fs-5" aria-hidden="true"></i>
                        </span>
                    @endif
                    <div>
                        <h5 class="fw-bold text-white mb-0">{{ $storeName }}</h5>
                        <span class="small" style="color: var(--kn-sand); font-size: 0.76rem;">Satu Nusantara, Seribu Cerita Kopi.</span>
                    </div>
                </div>

                @if(!empty($storeSettings['store_description']))
                    <p class="small opacity-75 mb-3 mt-2">{{ $storeSettings['store_description'] }}</p>
                @endif

                <div class="d-flex flex-column gap-2 small opacity-85 mt-3">
                    @if(!empty($storeSettings['store_address']))
                        <div class="d-flex gap-2">
                            <i class="bi bi-geo-alt flex-shrink-0" style="color: var(--kn-sand);" aria-hidden="true"></i>
                            <span>{{ $storeSettings['store_address'] }}</span>
                        </div>
                    @endif
                    @if(!empty($storeSettings['store_phone']))
                        <div class="d-flex gap-2">
                            <i class="bi bi-telephone flex-shrink-0" style="color: var(--kn-sand);" aria-hidden="true"></i>
                            <span>{{ $storeSettings['store_phone'] }}</span>
                        </div>
                    @endif
                    @if(!empty($storeSettings['store_email']))
                        <div class="d-flex gap-2">
                            <i class="bi bi-envelope flex-shrink-0" style="color: var(--kn-sand);" aria-hidden="true"></i>
                            <a href="mailto:{{ $storeSettings['store_email'] }}" class="text-decoration-none text-reset">{{ $storeSettings['store_email'] }}</a>
                        </div>
                    @endif
                    @if($waNumber !== '')
                        <div class="d-flex gap-2">
                            <i class="bi bi-whatsapp flex-shrink-0" style="color: var(--kn-sand);" aria-hidden="true"></i>
                            <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-reset">WhatsApp Layanan Pelanggan</a>
                        </div>
                    @endif
                    @if(!empty($storeSettings['store_hours']))
                        <div class="d-flex gap-2">
                            <i class="bi bi-clock flex-shrink-0" style="color: var(--kn-sand);" aria-hidden="true"></i>
                            <span>{{ $storeSettings['store_hours'] }}</span>
                        </div>
                    @endif
                </div>

                @if(count($socialLinks) > 0)
                    <div class="d-flex flex-wrap gap-2 mt-3 pt-1">
                        @foreach($socialLinks as $label => $item)
                            <a
                                href="{{ $item['url'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn btn-sm d-inline-flex align-items-center gap-1"
                                style="background-color: rgba(255,255,255,0.08); border-color: rgba(213,183,138,0.25); color: var(--kn-cream);"
                                aria-label="{{ $label }}"
                            >
                                <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                                <span>{{ $label }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <h6 class="kn-footer-heading mb-3">Jelajah Toko</h6>
                <ul class="list-unstyled small mb-0">
                    @forelse($footerCol1Menus as $menu)
                        <li><a href="{{ url($menu->url) }}" target="{{ $menu->target ?? '_self' }}" class="mlm-footer-link">{{ $menu->label }}</a></li>
                    @empty
                        <li><a href="{{ route('storefront.home') }}" class="mlm-footer-link">Beranda</a></li>
                        <li><a href="{{ $catalogUrl }}" class="mlm-footer-link">Katalog Produk</a></li>
                        <li><a href="{{ url('/artikel') }}" class="mlm-footer-link">Artikel &amp; Panduan</a></li>
                    @endforelse
                </ul>
            </div>

            <div class="col-6 col-md-4 col-lg-3">
                <h6 class="kn-footer-heading mb-3">Layanan Pelanggan</h6>
                <ul class="list-unstyled small mb-0">
                    @forelse($footerCol2Menus as $menu)
                        <li><a href="{{ url($menu->url) }}" target="{{ $menu->target ?? '_self' }}" class="mlm-footer-link">{{ $menu->label }}</a></li>
                    @empty
                        <li><a href="{{ url('/tentang-kami') }}" class="mlm-footer-link">Tentang Kami</a></li>
                        <li><a href="{{ url('/faq') }}" class="mlm-footer-link">FAQ</a></li>
                        <li><a href="{{ url('/kontak') }}" class="mlm-footer-link">Hubungi Kami</a></li>
                        <li><a href="{{ route('customer.login') }}" class="mlm-footer-link">Akun Pelanggan</a></li>
                    @endforelse
                </ul>
            </div>

            <div class="col-12 col-md-4 col-lg-3">
                <h6 class="kn-footer-heading mb-3">Kebijakan Toko</h6>
                <ul class="list-unstyled small mb-3">
                    @forelse($footerPolicyMenus as $menu)
                        <li><a href="{{ url($menu->url) }}" target="{{ $menu->target ?? '_self' }}" class="mlm-footer-link">{{ $menu->label }}</a></li>
                    @empty
                        @unless($storefrontNav['hasManagedFooterPages'] ?? false)
                            <li><a href="{{ url('/kebijakan-privasi') }}" class="mlm-footer-link">Kebijakan Privasi</a></li>
                            <li><a href="{{ url('/syarat-ketentuan') }}" class="mlm-footer-link">Syarat &amp; Ketentuan</a></li>
                            <li><a href="{{ url('/kebijakan-pengiriman') }}" class="mlm-footer-link">Kebijakan Pengiriman</a></li>
                            <li><a href="{{ url('/kebijakan-pengembalian') }}" class="mlm-footer-link">Kebijakan Pengembalian</a></li>
                        @else
                            <li class="opacity-75">Tidak ada halaman kebijakan aktif.</li>
                        @endunless
                    @endforelse
                </ul>

                <div class="pt-2">
                    <div class="kn-footer-heading mb-2" style="font-size: 0.7rem;">Pembayaran &amp; Pengiriman</div>
                    <div class="d-flex flex-wrap gap-1">
                        <span class="kn-footer-badge">Midtrans</span>
                        <span class="kn-footer-badge">QRIS</span>
                        <span class="kn-footer-badge">Virtual Account</span>
                        <span class="kn-footer-badge">Kurir Nusantara</span>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color: rgba(213, 183, 138, 0.2);">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 small opacity-75">
            <span>&copy; {{ date('Y') }} {{ $storeName }}. Seluruh hak cipta dilindungi.</span>
            <span>Pembayaran aman Midtrans &amp; Pengiriman Kurir Resmi Indonesia</span>
        </div>
    </div>
</footer>
