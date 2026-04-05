# <p align="center"><img src="public/logo1.png" width="120" alt="Sinergi PAS Logo"><br>Sinergi PAS</p>

<p align="center">
    <strong>Sistem Informasi Kepegawaian Lapas Jombang</strong><br>
    <em>"Solusi Enterprise Manajemen Kepegawaian yang Aman, Elegan, dan Real-Time."</em>
</p>

<p align="center">
    <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel" alt="Laravel 11">
    <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php" alt="PHP 8.2">
    <img src="https://img.shields.io/badge/Tailwind_CSS-4.0-38B2AC?style=for-the-badge&logo=tailwind-css" alt="Tailwind CSS 4">
    <img src="https://img.shields.io/badge/PWA-Ready-orange?style=for-the-badge&logo=pwa" alt="PWA Ready">
    <img src="https://img.shields.io/badge/MySQL-00758F?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
</p>

---

## 🌟 Tentang Sinergi PAS

**Sinergi PAS** adalah platform manajemen kepegawaian internal mutakhir yang dirancang khusus untuk **Lembaga Pemasyarakatan (Lapas) Kelas IIB Jombang**. Aplikasi ini tidak hanya sekadar database, melainkan sebuah ekosistem digital yang menghubungkan administrasi kepegawaian dengan setiap petugas melalui portal mandiri yang premium.

Dibangun dengan standar UI/UX modern yang terinspirasi dari identitas resmi instansi, Sinergi PAS mengedepankan kemudahan akses, keamanan data sensitif, dan performa tinggi melalui teknologi Progressive Web App (PWA).

---

## ✨ Fitur Unggulan Utama

### 📱 Progressive Web App (PWA) Support
Aplikasi dapat diinstal langsung di perangkat **Android, iOS, maupun Desktop** tanpa melalui PlayStore/AppStore.
- **Akses Cepat**: Membuka aplikasi langsung dari Home Screen.
- **Native Experience**: UI yang responsif dan animasi yang halus layaknya aplikasi mobile sungguhan.
- **Offline Caching**: Memuat aset utama secara instan berkat teknologi Service Worker.

### 🏛️ Identitas & Branding Kustom
- **Kop Surat Dinamis**: Konfigurasi nama instansi dan satuan kerja yang otomatis tersinkronisasi ke seluruh dokumen PDF dan Excel.
- **Executive Report**: Export laporan dashboard dan daftar nominatif dengan Header resmi lengkap dengan Logo Instansi.
- **Live Preview**: Lihat perubahan identitas instansi secara real-time di halaman pengaturan sebelum disimpan.

### 🛠️ Untuk Superadmin (Command Center)
- **Advanced Dashboard**: Visualisasi real-time dokumen masuk, kepatuhan pegawai (Compliance Tracking), dan analitik penyimpanan server.
- **Bulk Employee Management**: Fitur Registrasi, Edit, dan Hapus Massal (Bulk Delete) yang intuitif.
- **Arsip Digital Terpusat**: Grid basis data pegawai dengan indikator dokumen pending yang rapi.
- **Sistem Pengumuman**: Kendali penuh atas Running Text (Marquee) dan Popup Modal dengan kustomisasi warna serta kecepatan.
- **Helpdesk Support**: Manajemen laporan masalah dari pegawai dengan status penanganan terorganisir (Open, Resolved, Closed).
- **Security Audit Log**: Rekam jejak aktivitas sensitif setiap user lengkap dengan detail IP Address dan timestamp.

### 👤 Untuk Pegawai (Self-Service Portal)
- **Portal Mandiri**: Pantau progres kelengkapan dokumen wajib melalui persentase keterisian yang interaktif.
- **Quick Download**: Akses instan untuk mengunduh slip gaji terbaru dalam format PDF resmi.
- **Manajemen Profil**: Update foto profil dan informasi pribadi secara mandiri dengan keamanan ganda.
- **Real-Time Notification Foundation**: Siap menerima notifikasi pop-up instan (toast) saat dokumen diverifikasi atau laporan dibalas.

---

## 🛠️ Tech Stack

### Core Engine
- **Backend**: Laravel 11 (Latest Stable)
- **Frontend**: Blade Engine + **Plus Jakarta Sans** Typography
- **Styling**: Tailwind CSS 4.0 (Custom Premium Theme)
- **Database**: MySQL 8.0

### Advanced Modules
- **Service Worker**: Untuk fungsionalitas PWA dan Offline Readiness.
- **Excel Power**: `maatwebsite/excel` dengan dukungan Drawing Logo & Advanced Styling.
- **PDF Engine**: `barryvdh/laravel-dompdf` dengan optimasi Base64 Images.
- **UX Components**: Lucide Icons, SweetAlert2, NProgress, & 3D Animation Effects.

---

## 📦 Panduan Instalasi Lokal

### 1. Persiapan Awal
Pastikan Anda memiliki PHP 8.2+, Composer, dan Node.js terinstal.
```bash
git clone https://github.com/aryadians/sinergi-pas.git
cd sinergi-pas
```

### 2. Instalasi Dependensi
```bash
composer install
npm install
```

### 3. Konfigurasi Lingkungan
Copy file `.env.example` ke `.env` dan sesuaikan kredensial database Anda:
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Migrasi & Seeding
```bash
php artisan migrate --seed
php artisan storage:link
```

### 5. Jalankan Aplikasi
```bash
npm run dev
# Di terminal baru
php artisan serve
```

---

## 📂 Struktur Penting (Internal Workflow)
- `app/Exports/`: Logika ekspor Excel dengan branding logo.
- `public/sw.js`: Service Worker untuk kapabilitas PWA.
- `resources/views/layouts/app.blade.php`: Global layout dengan sistem animasi 3D.
- `resources/views/settings/`: Konfigurasi identitas sistem & live preview.

---

## 📄 Lisensi & Kontribusi
Proyek ini bersifat internal untuk Lapas Jombang. Namun, secara teknis menggunakan lisensi [MIT](LICENSE). Kontribusi untuk perbaikan bug sangat disambut melalui sistem Pull Request.

---

<p align="center">
    Dikembangkan dengan dedikasi tinggi oleh <strong>Arya Dian</strong> untuk <strong>Lapas Jombang</strong><br>
    © 2026 Sinergi PAS Platform
</p>
