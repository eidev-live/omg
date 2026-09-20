# Panduan Deployment — Oh My Egg

Panduan ini untuk men-deploy Oh My Egg di VPS (contoh: Biznet Gio NEO Lite, Ubuntu 24.04) yang dikelola dengan **aaPanel**. Aplikasi memakai **SQLite**, jadi tidak perlu server database terpisah dan aman berjalan berdampingan dengan aplikasi lain.

## Prinsip: satu aplikasi, satu folder

- Setiap aplikasi menempati foldernya sendiri: `/www/wwwroot/<app>`.
  - Oh My Egg → `/www/wwwroot/omg`
  - Aplikasi lain → `/www/wwwroot/<app-lain>`
- Setiap aplikasi punya **Site** sendiri di aaPanel, dengan document root ke `.../<app>/public`.
- SQLite tiap aplikasi tersimpan di `.../<app>/database/database.sqlite` sehingga **terisolasi** dari aplikasi lain.
- Karena satu IP dipakai bersama, gunakan **subdomain per aplikasi** (mis. `omg.domainku.com`). Selama domain belum ada, jadikan Oh My Egg sebagai **default site** agar IP mengarah ke Oh My Egg.

## Prasyarat server

1. **Ubuntu 24.04** + aaPanel (Nginx, PHP 8.3).
2. Ekstensi PHP 8.3 yang dibutuhkan: `fileinfo`, `mbstring`, `zip`, `curl`, `bcmath`, `openssl`, `pdo_sqlite`.
   - Pasang lewat *App Store → PHP 8.3 → Settings → Install extensions*.
3. `composer` tersedia di CLI, dan `php -v` (CLI) sama dengan versi PHP-FPM (8.3).
4. Swap 2 GB (disarankan untuk VPS 1 GB RAM).

## Deploy pertama

### 1. Buat Site di aaPanel

*Websites → Add Site*

| Kolom | Nilai |
| --- | --- |
| Domain | IP publik VPS (atau `omg.domainku.com` bila sudah ada) |
| Root Directory | `/www/wwwroot/omg` |
| PHP Version | `8.3` |
| Database | (kosongkan — memakai SQLite) |

### 2. Ambil kode

```bash
cd /www/wwwroot
git clone https://github.com/eidev-live/omg.git omg
cd omg
```

### 3. Pasang dependensi PHP

```bash
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader
```

> Aset frontend (`public/build`) sudah ikut di repositori, jadi **Node.js tidak diperlukan** di server.

### 4. Konfigurasi `.env`

```bash
cp .env.production.example .env
php artisan key:generate
```

Sesuaikan `APP_URL` (dan `DB_DATABASE` bila folder berbeda), lalu:

```bash
touch database/database.sqlite
php artisan migrate --force --seed
php artisan optimize
```

### 5. Buat akun pemilik

```bash
php artisan app:create-owner
```

### 6. Izin berkas

aaPanel menjalankan PHP sebagai user `www`, sedangkan file hasil `git`/upload bisa dimiliki `root`.

```bash
chown -R www:www /www/wwwroot/omg
chmod -R 775 storage bootstrap/cache
```

### 7. Arahkan document root ke `/public`

*Websites → (site) → Conf → Directory* → set **Running directory** ke `/public`.

Tab **URL rewrite**, isi:

```nginx
location / {
    try_files $uri $uri/ /index.php?$args;
}
```

Simpan, lalu buka `http://<IP>` dan login.

### 8. SSL (setelah domain mengarah ke server)

*Websites → (site) → SSL → Let's Encrypt*. Setelah aktif, ubah di `.env`:

```env
APP_URL=https://omg.domainku.com
APP_FORCE_HTTPS=true
SESSION_SECURE_COOKIE=true
ASSET_URL=https://omg.domainku.com
```

lalu:

```bash
php artisan optimize:clear && php artisan optimize
```

## Update / redeploy

Gunakan skrip bawaan:

```bash
cd /www/wwwroot/omg
PHP_BIN=/www/server/php/83/bin/php bash deploy.sh
```

`deploy.sh` akan: `git pull` → `composer install --no-dev` → `migrate --force` → `optimize` → perbaiki izin.

> Setiap ada perubahan tampilan (frontend), jalankan `npm run build` **di komputer lokal**, lalu commit `public/build` sebelum push — agar server tidak perlu Node.

## Backup

Skrip: `scripts/backup-db.sh` (menyimpan ke `backups/` dan menghapus berkas lebih tua dari 7 hari).

Contoh cron aaPanel (*Cron → Add Task → Shell Script*, jalankan sebagai root, harian 02:00):

```bash
bash /www/wwwroot/omg/scripts/backup-db.sh >> /www/wwwroot/omg/backups/backup.log 2>&1
```

**Restore**: hentikan sementara akses, ganti `database/database.sqlite` dengan hasil `gunzip` backup terbaru, lalu perbaiki izin:

```bash
gunzip -c /www/wwwroot/omg/backups/omg-YYYYmmdd-HHMMSS.sqlite.gz > /www/wwwroot/omg/database/database.sqlite
chown www:www /www/wwwroot/omg/database/database.sqlite
```

Sebaiknya unduh backup secara berkala ke komputer lokal (File Manager aaPanel atau `scp`).

## Troubleshooting (khusus aaPanel)

- **Error 500 / "Permission denied"**: file dimiliki `root`. Jalankan `chown -R www:www /www/wwwroot/omg`.
- **Halaman hanya menampilkan file mentah / 404**: document root belum ke `/public`, atau URL rewrite belum diisi.
- **`symlink()` dinonaktifkan**: aplikasi ini tidak memakai `storage:link` (logo disajikan dari `public/images`), jadi tidak terpengaruh.
- **Logo/aset tidak muncul**: pastikan folder `public/build` dan `public/images` ikut ter-commit (`git status`).
- **Composer gagal sebagai root**: tambahkan `COMPOSER_ALLOW_SUPERUSER=1`.
- **Perubahan `.env` tidak berefek**: jalankan `php artisan optimize:clear` lalu `php artisan optimize`.
