#!/usr/bin/env bash
#
# Deploy idempotent untuk Oh My Egg.
# Jalankan di server (dari folder aplikasi): bash deploy.sh
#
# Aset frontend (public/build) sudah ikut di-commit, jadi server tidak butuh Node.
# Sesuaikan PHP_BIN bila PHP CLI di server bukan versi 8.3.
#   contoh: PHP_BIN=/www/server/php/83/bin/php bash deploy.sh
#
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
WEB_USER="${WEB_USER:-www}"

cd "$APP_DIR"

echo "==> Menarik perubahan terbaru"
git pull --ff-only

echo "==> Memasang dependensi produksi"
COMPOSER_ALLOW_SUPERUSER=1 "$COMPOSER_BIN" install --no-dev --optimize-autoloader

echo "==> Memastikan database SQLite tersedia"
mkdir -p database
[ -f database/database.sqlite ] || touch database/database.sqlite

echo "==> Menjalankan migrasi"
"$PHP_BIN" artisan migrate --force

echo "==> Menyimpan cache konfigurasi/route/view"
"$PHP_BIN" artisan optimize

echo "==> Memperbaiki izin berkas"
chown -R "$WEB_USER":"$WEB_USER" "$APP_DIR" 2>/dev/null || true
chmod -R 775 storage bootstrap/cache

echo "==> Selesai"
