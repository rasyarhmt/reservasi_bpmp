# BPMP - Simple.Calculia

Sistem reservasi kamar dan aula untuk **Banua To Dea**. Aplikasi ini menangani pemesanan online maupun walk-in, kalkulasi biaya, pembayaran DP/lunas dengan bukti transfer, check-in/check-out, denda keterlambatan, serta halaman kasir.

**Peran pengguna:**

- **Guest** — melihat beranda, katalog kamar & aula, dan kalkulator biaya.
- **Customer** — membuat reservasi online, melihat reservasi sendiri, dan mengelola profil.
- **Admin/Resepsionis** — mengelola semua reservasi, walk-in, check-in/out, kasir, kamar, aula, dan akun pelanggan.

## Teknologi

- Laravel 13 (PHP 8.3)
- Tailwind CSS 4 + Vite 8 + Alpine.js
- SQLite (default) atau MySQL
- Layanan WhatsApp berbasis [Baileys](https://github.com/WhiskeySockets/Baileys) (`wa-service/`)

## Prasyarat

Pastikan sudah terpasang:

- PHP **8.3+** dengan ekstensi `sqlite3`, `pdo_sqlite`, dan `gd` (untuk pembuatan gambar/receipt)
- Composer
- Node.js **20+** dan npm
- (Opsional) ngrok, untuk mengakses aplikasi dari luar jaringan lokal
- (Opsional) Node.js untuk `wa-service` bila ingin mengaktifkan notifikasi WhatsApp

## Setup Pertama Kali

Jalankan dari root project:

```bash
# 1. Install dependency PHP
composer install

# 2. Salin konfigurasi environment
cp .env.example .env

# 3. Generate application key
php artisan key:generate

# 4. Siapkan database SQLite
touch database/database.sqlite

# 5. Jalankan migrasi dan seeder
php artisan migrate --seed

# 6. Buat symlink storage (untuk bukti pembayaran & receipt)
php artisan storage:link

# 7. Install dependency frontend dan build asset
npm install
npm run build
```

> Jika memakai MySQL, ubah `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` di `.env` sebelum langkah 5.

## Menjalankan Aplikasi

Mode development:

```bash
composer run dev
```

Perintah tersebut menjalankan server, Vite, dan queue sekaligus. Alternatif manual di dua terminal:

```bash
php artisan serve
npm run dev
```

Akses aplikasi di `http://localhost:8000`.

## Akun Hasil Seeder

| Peran | Email | Password |
| --- | --- | --- |
| Admin/Resepsionis | `admincalculia@gmail.com` | `password` |

Akun **customer** dapat dibuat melalui halaman `/register`.

## Queue Worker

Job seperti pengiriman receipt/notifikasi WhatsApp diproses melalui queue (`QUEUE_CONNECTION=database`). Jalankan worker di terminal terpisah:

```bash
php artisan queue:work
```

## Notifikasi WhatsApp (Opsional)

Aktifkan hanya bila diperlukan:

1. Atur `.env`:

   ```dotenv
   WA_NOTIFY_ENABLED=true
   WA_SERVICE_URL=http://127.0.0.1:3001
   WA_SERVICE_TOKEN=token-rahasia
   ```

2. Jalankan layanan WhatsApp:

   ```bash
   cd wa-service
   npm install
   npm start
   ```

3. Scan QR yang muncul di terminal menggunakan WhatsApp, lalu jalankan queue worker.

## Akses dari Luar Jaringan (Opsional)

Untuk mengakses aplikasi dari luar jaringan lokal, jalankan tunnel (misalnya ngrok) ke port aplikasi:

```bash
ngrok http 8000
```

Biarkan `ASSET_URL` kosong di `.env` agar CSS/JS/gambar mengikuti host yang diakses.

## Testing

```bash
php artisan test
```

## Struktur Singkat

```text
app/            Logika aplikasi (controllers, services, models, jobs)
database/       Migrasi, seeder, dan database SQLite
resources/      Views Blade, CSS, dan JavaScript
routes/         Definisi route web
wa-service/     Microservice pengirim WhatsApp (Baileys)
```

Panduan penggunaan untuk tiap peran pengguna dapat dilihat langsung dari dalam aplikasi.
