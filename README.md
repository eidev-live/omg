# Oh My Egg

Aplikasi web untuk mencatat dan memantau bisnis penjualan telur: pembelian, stok, harga jual, customer, transaksi penjualan, pembayaran, pengiriman, hingga laba kotor — dengan perhitungan HPP memakai metode **FIFO (First In First Out)**.

Dirancang untuk pemilik usaha kecil yang tidak ingin berurusan dengan istilah akuntansi rumit.

## Fitur

- Autentikasi (login, logout, ganti password)
- Master data customer dan tipe penjualan
- Pencatatan pembelian yang otomatis menambah stok
- Perhitungan HPP per butir telur
- Stok berbasis inventory layer dengan costing FIFO
- Penjualan multi-item dengan validasi stok
- Status pembayaran (UNPAID, PARTIAL, PAID) dan pengiriman (PENDING, SHIPPED, DELIVERED)
- Perhitungan revenue, COGS, laba kotor, dan margin
- Dashboard ringkas beserta grafik tren
- Laporan penjualan, pembelian, laba, dan stok (dengan ekspor CSV)
- Pembatalan transaksi (void) dengan pengembalian stok

## Tech Stack

- **Laravel 12** — framework backend
- **Inertia.js** — jembatan server dan frontend
- **Vue 3 + TypeScript** — antarmuka pengguna
- **Tailwind CSS** — styling
- **SQLite** — database
- **Vite** — bundler aset
- **Pest** — pengujian

## Persyaratan

- PHP 8.2+ dengan ekstensi `pdo_sqlite`, `mbstring`, `openssl`, `zip`
- Composer 2
- Node.js 18+ dan npm

## Instalasi

```bash
git clone <repository-url> oh-my-egg
cd oh-my-egg

composer install
npm install
```

## Environment Setup

Salin file environment lalu sesuaikan bila perlu:

```bash
cp .env.example .env
php artisan key:generate
```

Nilai default sudah memakai SQLite dan Bahasa Indonesia (`APP_LOCALE=id`, `APP_TIMEZONE=Asia/Jakarta`).

## Database Setup

```bash
touch database/database.sqlite
php artisan migrate
php artisan storage:link
```

> `storage:link` diperlukan agar logo aplikasi (`storage/app/public/omg_background.png`) dapat diakses.

## Seed Data Demo

```bash
php artisan migrate:fresh --seed
```

Seeder membuat satu akun pemilik:

| Email | Password |
| --- | --- |
| `demo@example.com` | `password` |

Selain itu tersedia data demo: customer (Budi, Sari, Andi), tipe penjualan (Pack, Tray, Ikat),
dua batch pembelian dengan HPP berbeda, serta lima transaksi penjualan yang mendemonstrasikan
status pembayaran (lunas, sebagian, belum lunas), status pengiriman (menunggu, dikirim, terkirim),
multi-item, dan konsumsi stok FIFO.

> Kredensial di atas hanya untuk pengembangan. Ganti sebelum dipakai di lingkungan nyata.

## Menjalankan Development Server

Cara cepat (Laravel + queue + log + Vite sekaligus):

```bash
composer dev
```

Atau manual di dua terminal:

```bash
php artisan serve
npm run dev
```

Aplikasi tersedia di `http://localhost:8000`.

## Testing

```bash
composer test
```

Perintah tersebut membersihkan cache konfigurasi lalu menjalankan seluruh test Pest.

Pemeriksaan kualitas kode:

```bash
composer lint         # Pint (PHP)
npm run lint:check    # ESLint
npm run format:check  # Prettier
npm run types:check   # Vue TypeScript check
```

## Struktur Project

```text
app/
├── Http/
│   ├── Controllers/    # Controller tipis
│   └── Requests/       # Validasi server-side
├── Models/             # Eloquent model
└── Services/           # Business logic (FIFO, inventory, penjualan, dst.)

database/
├── migrations/         # Skema database
└── seeders/            # Data demo

resources/js/
├── components/         # Komponen UI reusable
├── layouts/            # Layout aplikasi
├── pages/              # Halaman Inertia
└── types/              # Definisi tipe TypeScript

routes/                 # Definisi route
tests/                  # Pengujian Pest
```

Seluruh logika bisnis berada di service Laravel. Vue hanya menangani presentasi dan interaksi pengguna.

## Penjelasan FIFO

Stok telur dianggap keluar dari batch pembelian paling lama. Sistem tidak menyimpan satu angka stok saja, melainkan **inventory layer** per pembelian.

Contoh:

```text
Pembelian #001 : 100 telur @ Rp2.500
Pembelian #002 : 100 telur @ Rp2.800
Stok           : 200 telur

Penjualan 120 telur:
  100 × Rp2.500 = Rp250.000
   20 × Rp2.800 = Rp56.000
HPP (COGS)     = Rp306.000
Sisa stok      = 80 telur @ Rp2.800
```

Nilai persediaan dihitung dari sisa layer (`quantity_remaining × unit_cost`), bukan dari `stok × harga beli terakhir`.

## Kontribusi

1. Buat branch dari `main` dengan nama `feature/...` atau `fix/...`
2. Gunakan pesan commit bergaya Conventional Commits (`feat:`, `fix:`, `test:`, `docs:`)
3. Pastikan `php artisan test` lulus sebelum membuka pull request

## Lisensi

Dirilis di bawah [MIT License](LICENSE).
