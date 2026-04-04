# Panduan Deployment - Sinergi PAS

Dokumen ini menjelaskan cara melakukan deployment aplikasi Sinergi PAS ke server produksi.

## Persyaratan Server
Aplikasi ini berbasis Laravel 11, sehingga membutuhkan:
- PHP >= 8.2
- Ekstensi PHP: Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, Session, Tokenizer, XML
- MySQL >= 8.0
- Composer 2.x
- Web Server (Nginx atau Apache)

## Deployment Manual (VPS/Dedicated Server)

1. **Clone Repository**
   ```bash
   git clone https://github.com/username/sinergi-pas.git /var/www/sinergi-pas
   cd /var/www/sinergi-pas
   ```

2. **Instalasi Dependency**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm install && npm run build
   ```

3. **Konfigurasi Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate --force
   ```
   *Edit file `.env` dan sesuaikan `APP_ENV=production`, `APP_DEBUG=false`, serta detail database.*

4. **Optimasi & Database**
   ```bash
   php artisan migrate --force
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

5. **Izin Folder**
   ```bash
   chown -R www-data:www-data storage bootstrap/cache
   chmod -R 775 storage bootstrap/cache
   ```

## Otomatisasi dengan GitHub Actions (CD)

Untuk mengaktifkan deployment otomatis saat ada push ke branch `main`, Anda dapat menggunakan workflow berikut.

### 1. Tambahkan Secrets di GitHub
Buka repository Anda di GitHub -> Settings -> Secrets and variables -> Actions. Tambahkan secrets berikut:
- `SSH_HOST`: IP atau domain server Anda.
- `SSH_USER`: Username SSH (misal: `root` atau `ubuntu`).
- `SSH_KEY`: Private key SSH Anda (`id_rsa`).
- `SSH_PASSPHRASE`: (Opsional) Jika private key Anda menggunakan passphrase.

### 2. File Workflow CD
File `.github/workflows/deploy.yml` (akan dibuat) akan menangani proses ini secara otomatis.

## Pemeliharaan
Setiap kali ada pembaruan kode, jalankan perintah optimasi berikut di server:
```bash
php artisan migrate --force
php artisan optimize
```
