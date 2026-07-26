# 📁 Sistem Manajemen Arsip Multi-Company by SKYKOM-REY

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat&logo=mysql)
![License](https://img.shields.io/badge/License-MIT-green)

Sistem manajemen arsip dokumen berbasis web untuk mengelola dokumen dari **multi-perusahaan** (PT) dengan fitur keamanan firewall, konversi gambar ke PDF, dan dashboard monitoring.

---

## 🚀 Fitur Utama

| Fitur | Deskripsi |
|-------|-----------|
| 🏢 **Multi-Company** | Kelola dokumen dari berbagai perusahaan (PT) dalam satu platform |
| 📄 **Manajemen Dokumen** | Upload, lihat, cari, dan hapus dokumen (PDF, Word, Excel) |
| 🔐 **Firewall & Sesi** | Whitelist IP dan manajemen sesi aktif untuk keamanan |
| 🖼️ **Image to PDF Converter** | Konversi gambar (JPG/PNG) ke PDF tanpa watermark |
| 📊 **Dashboard Monitoring** | Grafik statistik upload, delete, dan distribusi dokumen |
| 🔍 **Filter & Pencarian** | Cari dokumen berdasarkan judul, bulan, dan tahun |

---

## 📸 Tampilan Aplikasi

### 🏠 Halaman Dashboard
Dashboard menampilkan statistik total sesi, tren laporan bulanan, distribusi dokumen per perusahaan, dan riwayat aktivitas.

![Dashboard](https://raw.githubusercontent.com/raihanryd1801/Dashboard-Arsip-MultiCompany2/main/public/screenshots/dashboard.png)

### 📂 Halaman Manajemen Arsip
Halaman ini adalah pusat pengelolaan dokumen. Kamu bisa upload, filter, dan kelola dokumen per kategori.

![Halaman Arsip](https://raw.githubusercontent.com/raihanryd1801/Dashboard-Arsip-MultiCompany2/main/public/screenshots/arsip.png)
![Halaman Arsip2](https://raw.githubusercontent.com/raihanryd1801/Dashboard-Arsip-MultiCompany2/main/public/screenshots/arsip2.png)

### 🛡️ Firewall & Sesi
Kelola whitelist IP dan pantau sesi aktif pengguna. Fitur "Drop" dapat menendang pengguna yang mencurigakan.

![Firewall](https://raw.githubusercontent.com/raihanryd1801/Dashboard-Arsip-MultiCompany2/main/public/screenshots/firewall.png)

### 🖼️ Image to PDF Converter
Konversi gambar menjadi PDF bersih tanpa watermark atau tulisan tambahan.

![Converter](https://raw.githubusercontent.com/raihanryd1801/Dashboard-Arsip-MultiCompany2/main/public/screenshots/converter.png)

### 🔐 Halaman Login

![Login](https://raw.githubusercontent.com/raihanryd1801/Dashboard-Arsip-MultiCompany2/main/public/screenshots/login.png)

## ⚙️ Persyaratan Sistem

| Komponen | Versi Minimal |
|----------|---------------|
| PHP | 8.2 atau lebih tinggi |
| Composer | 2.x |
| MySQL | 8.0 atau lebih tinggi |
| Node.js (opsional) | 18.x (untuk development assets) |
| Web Server | Apache / Nginx |

---

## 📥 Instalasi

1. Clone Repository

```bash
git clone https://github.com/raihanryd1801/Dashboard-Arsip-MultiCompany2.git
cd noc-arsip

2. Install Dependencies PHP
composer install

3. Buat File Environment
cp .env.example .env

4. Konfigurasi Database
Edit file .env dan sesuaikan dengan database kamu:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=noc_arsip
DB_USERNAME=root
DB_PASSWORD=<Password-Kamu>

5. Generate Application Key
php artisan key:generate

6. Jalankan Migrasi Database
php artisan migrate --seed

7. Link Storage (untuk akses file)
php artisan storage:link

8. Jalankan Server Development
php /var/www/html/finance/artisan serve --host=0.0.0.0 --port=8005

🔑 Akun Default


Setelah menjalankan php artisan migrate --seed, kamu bisa login dengan akun berikut:

```bash
php artisan tinker --execute="\App\Models\User::create(['name' => 'Admin Fans Media', 'email' => 'admin@test.co.id', 'password' => bcrypt('password123'), 'jabatan' => 'Admin']);"
```

Email    : admin@test.co.id
Password : password123

Struktur Direktori Penting : 

├── app/
│   ├── Http/
│   │   ├── Controllers/     # Controller aplikasi
│   │   └── Middleware/      # Middleware Firewall & Auth
│   └── Models/              # Model Eloquent
├── database/
│   ├── migrations/          # Struktur tabel database
│   └── seeders/             # Data awal
├── resources/
│   └── views/               # Blade template
├── public/
│   └── storage/             # Link ke file dokumen
└── routes/
    └── web.php              # Semua route aplikasi

 🛠️ Teknologi yang Digunakan
Teknologi	Fungsi
Laravel 11	Framework PHP utama
Bootstrap 5	UI Framework
Chart.js	Visualisasi data grafik
Flatpickr	Picker tanggal dokumen
jsPDF	Konversi gambar ke PDF di client-side
MySQL	Database sistem   

🔒 Fitur Keamanan
✅ Firewall IP-Based
Membatasi akses web hanya untuk IP yang terdaftar di whitelist.

✅ Session Management
Admin dapat melihat dan menendang sesi aktif pengguna.

✅ Authentication
Sistem login dengan Laravel Auth.

✅ CSRF Protection
Semua form dilindungi dari serangan CSRF.

🚧 Troubleshooting
❌ Masalah: "No application encryption key has been specified"
Solusi: Jalankan php artisan key:generate

❌ Masalah: Error 500 setelah migrasi
Solusi: Cek log di storage/logs/laravel.log untuk detail error

❌ Masalah: File tidak bisa diakses / 404
Solusi: Pastikan sudah menjalankan php artisan storage:link

❌ Masalah: Firewall memblokir akses
Solusi: Tambahkan IP kamu ke whitelist di halaman Firewall & Sesi

🤝 Kontribusi
Kontribusi sangat kami harapkan! Silakan fork repository ini dan buat pull request.

Fork repository

Buat branch fitur baru: git checkout -b fitur-muanteb-gan

Commit perubahan: git commit -m 'Tambahkan fitur muanteb-gan'

Push ke branch: git push origin fitur-keren

Buat Pull Request

📞 Kontak & Support
Developer Muhammad Raihan Riyady
Email	abualiraihan1801@gmail.com
GitHub	github.com/raihanryd1801
Perusahaan	PT. Skykom
📄 Lisensi
Sistem ini dilisensikan di bawah MIT License.

Dibuat dengan ❤️ oleh RaihanSkykom Asyyyeeekkkk
