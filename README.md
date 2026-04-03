# <p align="center"><img src="public/logo1.png" width="120" alt="Sinergi PAS Logo"><br>Sinergi PAS</p>

<p align="center">
    <strong>Sistem Informasi Kepegawaian Lapas Jombang</strong><br>
    <em>"Solusi Modern untuk Manajemen Kepegawaian yang Aman, Efisien, dan Transparan."</em>
</p>

<p align="center">
    <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel" alt="Laravel 12">
    <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php" alt="PHP 8.2">
    <img src="https://img.shields.io/badge/Tailwind_CSS-4.0-38B2AC?style=for-the-badge&logo=tailwind-css" alt="Tailwind CSS 4">
    <img src="https://img.shields.io/badge/Vite-7.0-646CFF?style=for-the-badge&logo=vite" alt="Vite 7">
    <img src="https://img.shields.io/badge/MySQL-00758F?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
    <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License">
</p>

---

## 🌟 Tentang Sinergi PAS

**Sinergi PAS** adalah platform manajemen kepegawaian internal yang dirancang khusus untuk Lembaga Pemasyarakatan (Lapas) Jombang. Berfokus pada efisiensi operasional dan keamanan data, sistem ini mendigitalisasi seluruh aspek administrasi kepegawaian—mulai dari data profil, manajemen dokumen (Slip Gaji, SKP, SK), hingga pelaporan masalah secara mandiri (Self-Service).

Dengan antarmuka yang modern, responsif, dan premium, Sinergi PAS memberikan pengalaman pengguna yang intuitif baik bagi administrator maupun pegawai.

---

## 🚀 Fitur Utama

### 🛠️ Untuk Superadmin (Control Center)
- **Dashboard Analitik**: Visualisasi data pegawai, dokumen masuk hari ini, status penyimpanan (Storage Analytics), dan pelacakan kepatuhan dokumen (Compliance Tracking).
- **Manajemen Pegawai**: Pengelolaan data lengkap (CRUD), integrasi foto profil, serta fitur Impor/Ekspor massal via Excel & PDF.
- **Verifikasi Dokumen**: Alur verifikasi dokumen yang ketat—terima, tolak dengan alasan, atau minta revisi dengan riwayat versi yang terdokumentasi.
- **Audit Logs**: Rekam jejak aktivitas sistem yang mendetail untuk menjamin akuntabilitas dan keamanan data.
- **Pengaturan Sistem**: Konfigurasi Unit Kerja, Jabatan, dan kustomisasi widget dashboard.
- **Pengumuman**: Broadcast informasi penting secara real-time ke seluruh pegawai.

### 👤 Untuk Pegawai (Self-Service Portal)
- **Personal Dashboard**: Ringkasan dokumen pribadi, progres karir berdasarkan dokumen wajib, dan akses cepat ke slip gaji terbaru.
- **Manajemen Dokumen Mandiri**: Unggah dan kelola dokumen pribadi dengan status verifikasi yang transparan.
- **Profil Modern**: Update informasi profil dan foto secara mandiri.
- **Laporan Masalah**: Fitur pelaporan kendala teknis atau administratif langsung kepada admin.
- **Notifikasi**: Pemberitahuan real-time untuk status dokumen dan pengumuman baru.

---

## 🛠️ Tech Stack

### Backend
- **Framework**: Laravel 12.x
- **Language**: PHP 8.2+
- **Database**: MySQL 8.0 / MariaDB
- **Libraries**:
  - `barryvdh/laravel-dompdf`: Export laporan ke format PDF premium.
  - `maatwebsite/excel`: Manajemen data masif via Excel/CSV.
  - `simplesoftwareio/simple-qrcode`: Integrasi QR Code untuk validasi dokumen.
  - `intervention/image`: Optimasi dan pemrosesan gambar/foto profil.

### Frontend
- **Framework**: Blade Templating Engine
- **Styling**: Tailwind CSS 4.0 (Modern utility-first approach)
- **Build Tool**: Vite 7.0 (Ultra-fast development server)
- **Components**:
  - `Lucide Icons`: Ikonografi yang bersih dan modern.
  - `SweetAlert2`: Notifikasi interaktif dan elegan.
  - `AOS (Animate On Scroll)`: Animasi transisi yang halus.

---

## 📦 Instalasi & Penggunaan

Ikuti langkah-langkah di bawah ini untuk menjalankan project di lingkungan lokal Anda:

### 1. Clone Repositori
```bash
git clone https://github.com/username/sinergi-pas.git
cd sinergi-pas
```

### 2. Setup Otomatis (Recommended)
Project ini telah dilengkapi dengan script setup otomatis untuk mempercepat proses instalasi:
```bash
composer setup
```
*Script ini akan menjalankan: `composer install`, copy `.env`, `key:generate`, `migrate`, `npm install`, dan `npm build`.*

### 3. Konfigurasi Database
Buka file `.env` dan sesuaikan kredensial database Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sinergi_pas
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Jalankan Server Pengembangan
Gunakan perintah berikut untuk menjalankan server PHP, Queue, dan Vite secara bersamaan:
```bash
composer dev
```
Akses aplikasi melalui: `http://localhost:8000`

---

## 📂 Struktur Proyek Utama
```text
sinergi-pas/
├── app/
│   ├── Http/Controllers/    # Logika bisnis & navigasi
│   ├── Models/              # Definisi skema & relasi Eloquent
│   └── Notifications/       # Sistem notifikasi aplikasi
├── database/
│   ├── migrations/          # Struktur database version-controlled
│   └── seeders/             # Data awal & testing
├── public/                  # Aset publik (Logo, Build files)
├── resources/
│   ├── views/               # Antarmuka Blade (Admin & Pegawai)
│   ├── css/                 # Styling Tailwind
│   └── js/                  # Interaktivitas JavaScript
└── routes/
    └── web.php              # Definisi endpoint & middleware
```

---

## 📄 Lisensi
Proyek ini dilisensikan di bawah [MIT License](LICENSE).

---

<p align="center">
    Dikembangkan dengan ❤️ untuk <strong>Lapas Jombang</strong>
</p>
