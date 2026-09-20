#!/usr/bin/env bash
#
# Backup harian database SQLite Oh My Egg.
# Contoh cron (jalankan sebagai root):
#   0 2 * * * /www/wwwroot/omg/scripts/backup-db.sh >> /www/wwwroot/omg/backups/backup.log 2>&1
#
set -euo pipefail

APP_DIR="${APP_DIR:-/www/wwwroot/omg}"
BACKUP_DIR="${BACKUP_DIR:-$APP_DIR/backups}"
RETENTION_DAYS="${RETENTION_DAYS:-7}"
DB_FILE="$APP_DIR/database/database.sqlite"
STAMP="$(date +%Y%m%d-%H%M%S)"

mkdir -p "$BACKUP_DIR"

if [ ! -f "$DB_FILE" ]; then
    echo "Database tidak ditemukan: $DB_FILE" >&2
    exit 1
fi

if command -v sqlite3 >/dev/null 2>&1; then
    sqlite3 "$DB_FILE" ".backup '$BACKUP_DIR/omg-$STAMP.sqlite'"
else
    cp "$DB_FILE" "$BACKUP_DIR/omg-$STAMP.sqlite"
fi

gzip -f "$BACKUP_DIR/omg-$STAMP.sqlite"

find "$BACKUP_DIR" -name 'omg-*.sqlite.gz' -mtime +"$RETENTION_DAYS" -delete

echo "Backup selesai: $BACKUP_DIR/omg-$STAMP.sqlite.gz"
