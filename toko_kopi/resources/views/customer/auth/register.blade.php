@extends('layouts.auth')

@section('title', 'Daftar Akun Pelanggan')
@section('body_class', 'kn-auth-body d-flex align-items-center justify-content-center min-vh-100 p-3 p-md-4')

@push('styles')
    @php
        $knAuthCssPath = public_path('assets/css/kopi-nusantara-storefront.css');
        $knAuthCssVer = file_exists($knAuthCssPath) ? filemtime($knAuthCssPath) : time();
    @endphp
    <link href="{{ asset('assets/css/kopi-nusantara-storefront.css') }}?v={{ $knAuthCssVer }}" rel="stylesheet">
@endpush

@section('full_auth_layout')
@php
    $storeName = $storeSettings['store_name'] ?? 'Pasar Lokal Nusantara';
    $storeLogo = !empty($storeSettings['logo_path'])
        ? asset('storage/'.$storeSettings['logo_path'])
        : null;
@endphp

<div class="kn-auth-shell">
    <div class="row g-0">
        <div class="col-12 col-lg-5 kn-auth-aside p-4 p-lg-5 d-flex flex-column justify-content-between">
            <div>
                <a href="{{ route('storefront.home') }}" class="text-decoration-none d-inline-flex align-items-center gap-2 text-white mb-4">
                    @if($storeLogo)
                        <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="rounded-3 bg-white object-fit-contain p-1" style="width: 40px; height: 40px;">
                    @else
                        <span class="rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(244, 235, 221, 0.14); color: var(--kn-sand);">
                            <i class="bi bi-cup-hot-fill fs-5" aria-hidden="true"></i>
                        </span>
                    @endif
                    <div class="d-flex flex-column lh-sm">
                        <span class="fw-bold fs-6">{{ $storeName }}</span>
                        <span class="small" style="color: var(--kn-sand); font-size: 0.72rem;">Satu Nusantara, Seribu Cerita Kopi.</span>
                    </div>
                </a>

                <div class="mb-3">
                    <span class="kn-auth-aside-badge mb-3">
                        <i class="bi bi-person-plus" aria-hidden="true"></i> Pendaftaran Gratis
                    </span>
                    <h2 class="h4 fw-bold text-white mb-2">Bergabung dengan {{ $storeName }}</h2>
                    <p class="small mb-0" style="color: rgba(244, 235, 221, 0.84);">
                        Daftarkan akun Anda untuk pengalaman belanja kopi &amp; produk unggulan Nusantara yang lebih cepat dan terpantau.
                    </p>
                </div>
            </div>

            <div class="d-none d-lg-flex flex-column gap-3 mt-4 pt-4" style="border-top: 1px solid rgba(213, 183, 138, 0.22);">
                <div class="d-flex align-items-center gap-2 small" style="color: var(--kn-cream);">
                    <i class="bi bi-check-circle-fill" style="color: var(--kn-sand);" aria-hidden="true"></i>
                    <span>Proses pendaftaran ringkas &amp; aman</span>
                </div>
                <div class="d-flex align-items-center gap-2 small" style="color: var(--kn-cream);">
                    <i class="bi bi-truck" style="color: var(--kn-sand);" aria-hidden="true"></i>
                    <span>Hitung ongkir otomatis ke seluruh Indonesia</span>
                </div>
                <div class="d-flex align-items-center gap-2 small" style="color: var(--kn-cream);">
                    <i class="bi bi-chat-dots-fill" style="color: var(--kn-sand);" aria-hidden="true"></i>
                    <span>Riwayat pesanan &amp; dukungan pelanggan terintegrasi</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7 p-4 p-md-5 bg-white">
            <x-notification />

            <div class="mb-4">
                <span class="kn-eyebrow mb-1">Pelanggan Baru</span>
                <h1 class="h4 fw-bold mb-1" style="color: var(--kn-espresso);">Buat Akun Pelanggan</h1>
                <p class="small text-mlm-muted mb-0">Belanja lebih cepat dan pantau pengiriman pesanan Anda.</p>
            </div>

            <form method="POST" action="{{ route('customer.register.submit') }}">
                @csrf
                <x-form.input name="name" label="Nama Lengkap" required placeholder="Contoh: Budi Santoso" />
                <div class="row g-2">
                    <div class="col-12 col-sm-6">
                        <x-form.input name="email" label="Alamat Email" type="email" required placeholder="nama@email.com" />
                    </div>
                    <div class="col-12 col-sm-6">
                        <x-form.input name="phone" label="Nomor HP / WhatsApp" required placeholder="0812xxxxxxxx" />
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-12 col-sm-6">
                        <x-form.input name="password" label="Kata Sandi" type="password" required placeholder="Min. 8 karakter (huruf & angka)" />
                    </div>
                    <div class="col-12 col-sm-6">
                        <x-form.input name="password_confirmation" label="Konfirmasi Kata Sandi" type="password" required placeholder="Ulangi kata sandi" />
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-2 mb-3">
                    <i class="bi bi-person-check me-1" aria-hidden="true"></i> Daftar Akun
                </button>
            </form>

            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 pt-3 border-top small text-mlm-muted">
                <div>
                    Sudah memiliki akun?
                    <a href="{{ route('customer.login') }}" class="text-mlm-primary fw-semibold text-decoration-none">Masuk di sini</a>
                </div>
                <a href="{{ route('storefront.home') }}" class="text-decoration-none text-mlm-muted fw-medium">
                    <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Kembali ke Toko
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
