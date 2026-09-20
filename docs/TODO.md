# Daftar Update Berikutnya (TODO)

Catatan pekerjaan lanjutan setelah deployment awal. Status saat ini:

- **Live**: `http://139.190.98.49` (Ubuntu 24.04, nginx + PHP 8.3-FPM + SQLite)
- **Akun**: `heyerisa@gmail.com`
- **Backup**: cron harian 02:00, retensi 7 hari (`backups/`)
- **HTTPS**: belum aktif (menunggu domain `.my.id`)

---

## A. Domain & HTTPS

1. Beli domain **`.my.id`** di registrar lokal (mis. Domainesia/Rumahweb).
2. Tambah **A record** `@` (dan `www`) → `139.190.98.49`; tunggu propagasi DNS.
3. Pasang **Let's Encrypt** untuk domain (aaPanel *SSL* atau `certbot`) + redirect HTTP→HTTPS.
4. Perbarui `.env`: `APP_URL=https://<domain>`, `APP_FORCE_HTTPS=true`, `SESSION_SECURE_COOKIE=true`, lalu `php artisan optimize`.
5. Verifikasi login via HTTPS dari HP.
6. Opsional: pasang Cloudflare (DNS + proxy/WAF) di depan VPS.

## B. Manajemen aaPanel

7. Putuskan pengelolaan situs:
   - **Biarkan vhost manual** (`/www/server/panel/vhost/nginx/omg.conf`), atau
   - **Buat Site di aaPanel** (domain `<domain>`, root `/www/wwwroot/omg`, PHP 8.3, *Running directory* `/public`) lalu hapus conf manual.

## C. Keamanan

8. Firewall: aktifkan `ufw` (allow 22/80/443); batasi/pindahkan port phpMyAdmin (888).
9. Pasang `fail2ban`.
10. Hardening SSH: `PasswordAuthentication no`, `PermitRootLogin no` (sudah memakai SSH key).
11. Ganti password akun pemilik (sempat dikirim lewat chat).
12. Pertimbangkan menonaktifkan/membatasi phpMyAdmin bila tidak dipakai.

## D. Operasional

13. **Backup offsite**: unduh berkala `backups/*.sqlite.gz` ke komputer lokal.
14. Rapikan warning `Module "zip" is already loaded` (konfigurasi aaPanel memuat `zip` ganda).
15. Monitoring uptime sederhana + rotasi log nginx.
16. Tinjau ulang swap (saat ini 3 GB) bila beban bertambah.

## E. Alur Repo & Deploy

17. Sebelum push: `composer deploy:prepare` (test + lint + types + build).
18. Di server: `cd /www/wwwroot/omg && bash deploy.sh`.
19. Jangan pernah commit rahasia (`.env`, database produksi, kredensial). Catatan akses server disimpan **terpisah di luar Git**.
