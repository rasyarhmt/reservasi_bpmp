<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Aktifkan notifikasi WhatsApp
    |--------------------------------------------------------------------------
    |
    | Jika false, job pengiriman tidak akan dijalankan sehingga tidak ada
    | pesan yang dikirim ke layanan WhatsApp.
    |
    */
    'enabled' => (bool) env('WA_NOTIFY_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Alamat layanan Baileys (wa-service)
    |--------------------------------------------------------------------------
    |
    | URL HTTP dari layanan Node.js yang menjalankan Baileys. Laravel akan
    | memanggil endpoint POST /send pada layanan ini untuk mengirim pesan.
    |
    */
    'service_url' => env('WA_SERVICE_URL', 'http://127.0.0.1:3001'),

    'token' => env('WA_SERVICE_TOKEN'),

    'timeout' => (int) env('WA_SERVICE_TIMEOUT', 15),

    /*
    |--------------------------------------------------------------------------
    | Kode negara untuk normalisasi nomor telepon
    |--------------------------------------------------------------------------
    */
    'country_code' => env('WA_COUNTRY_CODE', '62'),

    /*
    |--------------------------------------------------------------------------
    | Font untuk menggambar gambar resi
    |--------------------------------------------------------------------------
    |
    | Kosongkan untuk memakai font bawaan sistem (di-deteksi otomatis dari
    | daftar lokasi umum DejaVu/Liberation/Carlito pada Linux).
    |
    */
    'font_path' => env('WA_FONT_PATH'),

    'font_bold_path' => env('WA_FONT_BOLD_PATH'),

    'font_italic_path' => env('WA_FONT_ITALIC_PATH'),

    /*
    |--------------------------------------------------------------------------
    | Lokasi penyimpanan gambar resi
    |--------------------------------------------------------------------------
    */
    'receipt_disk' => env('WA_RECEIPT_DISK', 'public'),

    'receipt_path' => env('WA_RECEIPT_PATH', 'receipts'),

];
