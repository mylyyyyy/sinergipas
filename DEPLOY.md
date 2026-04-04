# Panduan Deployment - Sinergi PAS (Shared Hosting / cPanel)

Dokumen ini menjelaskan cara melakukan deployment aplikasi Sinergi PAS ke Shared Hosting (seperti Niagahoster Business Hosting).

## Persyaratan Server
- PHP >= 8.2 (Pastikan pilih versi ini di menu "Select PHP Version" cPanel).
- Database MySQL/MariaDB.
- Akses SSH atau Terminal di cPanel (Sangat disarankan).

## Struktur Folder yang Disarankan
Untuk keamanan, letakkan file Laravel di folder terpisah, bukan langsung di `public_html`:
- `/home/user_anda/project-sinergi/` (File aplikasi)
- `/home/user_anda/public_html/` (Hanya isi dari folder `public`)

## Deployment Manual via cPanel

1. **Persiapan Lokal**
   Jalankan build asset di komputer Anda sebelum upload:
   ```bash
   npm install && npm run build
   composer install --no-dev --optimize-autoloader
   ```

2. **Upload File**
   - Kompres semua file (kecuali `node_modules`, `.git`, `.env`) menjadi `project.zip`.
   - Upload ke root hosting Anda (di luar `public_html`) menggunakan File Manager cPanel.
   - Ekstrak file tersebut ke folder (misal: `sinergi-app`).

3. **Konfigurasi Public Folder**
   Pindahkan semua isi dari folder `sinergi-app/public/*` ke dalam `public_html/`.

4. **Edit index.php**
   Edit file `public_html/index.php` dan sesuaikan path autoload:
   ```php
   // Baris 7:
   require __DIR__.'/../sinergi-app/vendor/autoload.php';
   // Baris 19:
   $app = require_once __DIR__.'/../sinergi-app/bootstrap/app.php';
   ```

5. **Database & .env**
   - Buat database dan user di cPanel "MySQL Databases".
   - Salin `.env.example` menjadi `.env` di folder `sinergi-app`.
   - Update `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
   - Jalankan migrasi via Terminal cPanel: `php artisan migrate --force`.

## Otomatisasi dengan GitHub Actions (CD)

Untuk Shared Hosting, workflow terbaik adalah mem-build assets di GitHub, lalu mengirimkan file final ke server.

### 1. Tambahkan Secrets di GitHub
- `FTP_SERVER`: Host FTP (misal: `ftp.domain.com`).
- `FTP_USERNAME`: Username cPanel/FTP.
- `FTP_PASSWORD`: Password FTP.

### 2. Workflow CD (GitHub Actions)
Gunakan workflow yang melakukan build assets terlebih dahulu agar Anda tidak perlu menginstal Node.js di hosting.

## Troubleshooting
- **Symlink Storage:** Jika foto tidak muncul, jalankan `php artisan storage:link` lewat Terminal cPanel. Jika gagal, buat file `routes/web.php` sementara:
  ```php
  Route::get('/install-link', function () {
      Artisan::call('storage:link');
  });
  ```
  Lalu akses `domain.com/install-link` di browser.

