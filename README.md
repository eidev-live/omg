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
git clone https://github.com/eidev-live/omg.git
cd omg

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
```

## Membuat Akun Pemilik

Registrasi publik dinonaktifkan, jadi akun pemilik dibuat lewat Artisan:

```bash
php artisan app:create-owner
```

Atau tanpa interaksi:

```bash
php artisan app:create-owner --name="Nama Anda" --email="email@example.com" --password="password-ku"
```

Seeder hanya menyiapkan pengaturan awal (mis. batas minimum stok) tanpa data contoh:

```bash
php artisan migrate --seed
```

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
└── seeders/            # Pengaturan awal (mis. batas minimum stok)

public/
├── build/              # Aset frontend hasil build (ikut di-commit)
└── images/             # Logo dan gambar statis

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

## Deployment

Aset frontend (`public/build`) ikut disimpan di repositori, sehingga server **tidak memerlukan Node.js**. Di server cukup:

```bash
composer install --no-dev --optimize-autoloader
cp .env.production.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --force --seed
php artisan optimize
```

Untuk update berikutnya, gunakan `bash deploy.sh` (lihat [`docs/DEPLOY.md`](docs/DEPLOY.md) untuk runbook lengkap aaPanel, SSL, backup, dan konfigurasi multi-aplikasi dalam satu VPS). Rencana pekerjaan lanjutan ada di [`docs/TODO.md`](docs/TODO.md).

### Sebelum push (menyiapkan hasil build final)

```bash
composer deploy:prepare
```

Perintah ini menjalankan test, pemeriksaan format/lint/tipe, lalu `npm run build` sehingga `public/build` siap di-commit.

## Kontribusi

1. Buat branch dari `main` dengan nama `feature/...` atau `fix/...`
2. Gunakan pesan commit bergaya Conventional Commits (`feat:`, `fix:`, `test:`, `docs:`)
3. Pastikan `composer test` lulus sebelum membuka pull request

## Lisensi

Dirilis di bawah [MIT License](LICENSE).
