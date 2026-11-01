# Backend Sistem Penyewaan Alat Elektronik

Backend aplikasi Sistem Penyewaan Alat Elektronik menggunakan Laravel sebagai REST API untuk mendukung aplikasi web dan mobile.

## Fitur Utama

- Pengelolaan data alat elektronik.
- Pengelolaan kategori alat.
- Pengelolaan data pelanggan.
- Pengelolaan transaksi penyewaan.
- Penyediaan API untuk frontend web dan mobile.

## Teknologi

- PHP
- Laravel
- MySQL
- REST API

## Persyaratan

Pastikan perangkat sudah memiliki:

- PHP sesuai versi yang didukung proyek.
- Composer
- MySQL atau MariaDB
- Git

## Instalasi

1. Clone repository:

   ```bash
   git clone https://github.com/rifqifajar61-lgtm/backend-penyewaan.git
   cd backend-penyewaan
   ```

2. Instal dependensi:

   ```bash
   composer install
   ```

3. Buat file konfigurasi:

   ```bash
   cp .env.example .env
   ```

   Pada Windows, file `.env.example` juga dapat disalin secara manual menjadi `.env`.

4. Buat application key:

   ```bash
   php artisan key:generate
   ```

5. Atur koneksi database pada file `.env`, termasuk `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`.

6. Jalankan migrasi database jika migrasi proyek sudah tersedia:

   ```bash
   php artisan migrate
   ```

7. Jalankan server lokal:

   ```bash
   php artisan serve
   ```

API lokal biasanya tersedia di `http://127.0.0.1:8000`. Endpoint API mengikuti konfigurasi route proyek.

## Struktur Proyek

- `app/` — model, controller, dan logika aplikasi.
- `routes/` — definisi route web dan API.
- `database/` — migrasi dan seeder database.
- `config/` — konfigurasi Laravel.
- `public/` — direktori publik aplikasi.

## Konfigurasi dan Keamanan

- Jangan mengunggah file `.env` atau kredensial rahasia ke repository.
- Gunakan `.env.example` sebagai contoh konfigurasi tanpa password asli.
- Sesuaikan konfigurasi database sebelum menjalankan aplikasi.

## Status Proyek

Dalam pengembangan.

## Lisensi

Belum ditentukan.
