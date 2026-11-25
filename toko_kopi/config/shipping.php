<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Whitelist Kurir & Layanan Pengiriman Checkout
    |--------------------------------------------------------------------------
    |
    | Mengatur kurir dan kode layanan resmi yang diizinkan untuk diminta ke
    | API RajaOngkir dan ditampilkan pada halaman checkout pelanggan.
    | Kurir dengan daftar layanan kosong tidak akan diminta maupun ditampilkan.
    |
    */
    'whitelist' => [
        'jne' => [
            'REG',
            'YES',
        ],
        'jnt' => [
            'EZ',
        ],
        'sicepat' => [
            'REG',
        ],
        'pos' => [
            'Pos Reguler',
        ],
        'anteraja' => [
            'REG',
            'Reguler',
        ],
    ],
];
