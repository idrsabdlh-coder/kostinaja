# KostinAja 🏠

**KostinAja** adalah platform pencarian kos modern yang memudahkan calon pencari menemukan kos idaman, membantu pemilik kos mengelola listing kamar, dan memberi admin kendali penuh untuk memoderasi kualitas listing.

Proyek ini sedang bertransisi dari versi PHP native (Fase 0 - Hotfix & Pengamanan) menuju arsitektur *decoupled* modern dengan **Laravel 13 API** dan **Vue 3 SPA** (Fase 1–5).

---

## 📋 Persyaratan Sistem

- **PHP**: Versi 8.2 atau 8.4+ (dengan ekstensi `mysqli`, `pdo_mysql`, `mbstring`)
- **Database**: MySQL 8.4+ / 9.x atau MariaDB (dapat dijalankan via **phpBro** atau MySQL lokal)
- **Database Management Web**: **Adminer** (tersedia langsung pada phpBro di `http://localhost:8080/adminer/`)
- **Web Server Lokal**: PHP Built-in Server (`php -S`) atau Nginx / Apache

---

## 🚀 Panduan Instalasi & Menjalankan Proyek

### 1. Clone Repositori

```bash
git clone https://github.com/idrsabdlh-coder/kostinaja.git
cd kostinaja
```

### 2. Konfigurasi Database dengan phpBro & Adminer

Aplikasi menggunakan phpBro sebagai lingkungan pengembangan lokal yang menyediakan MySQL dan Adminer.

1. **Jalankan phpBro** dan pastikan layanan **MySQL** dan **Nginx/Web** aktif.
2. **Buka Adminer** melalui browser:
   - URL: `http://localhost:8080/adminer/`
3. **Login ke Adminer**:
   - **System**: `MySQL`
   - **Server**: `127.0.0.1` (atau `localhost`)
   - **Username**: `root`
   - **Password**: `root` (kredensial bawaan phpBro)
   - **Database**: *(kosongkan atau isi `kostinaja`)*
4. **Import Skema Database**:
   - Klik menu **Import** pada Adminer (atau jalankan via tab **SQL command**).
   - Pilih berkas `database/kostinaja.sql` dari direktori proyek.
   - Klik **Execute** / **Jalankan**.
   - Skema tabel `users`, `kos`, serta 10 data kos contoh dan 1 akun demo akan terbuat otomatis di database `kostinaja`.

> **Alternatif via Terminal / CLI:**
> ```bash
> mysql -h 127.0.0.1 -P 3306 -u root -p kostinaja < database/kostinaja.sql
> ```

### 3. Konfigurasi Koneksi Aplikasi

Salin template konfigurasi dan sesuaikan kredensial koneksi:

```bash
cp config/koneksi.example.php config/koneksi.php
```

Pastikan isi `config/koneksi.php` sesuai dengan konfigurasi phpBro Anda:
```php
<?php
$host = "127.0.0.1";
$user = "root";
$pass = "root";
$db   = "kostinaja";
$port = 3306;

$conn = mysqli_connect($host, $user, $pass, $db, $port);
...
```

> **Catatan Keamanan:** Berkas `config/koneksi.php` sudah masuk ke `.gitignore` sehingga kredensial lokal Anda tidak akan pernah ter-commit ke Git.

### 4. Jalankan Web Server Lokal

Jalankan PHP built-in web server di direktori root proyek:

```bash
php -S localhost:8000
```

Buka browser dan akses:
- **Beranda / Listing Kos**: `http://localhost:8000/index.php` (dapat diakses langsung tanpa login)
- **Halaman Masuk**: `http://localhost:8000/login_form.php`
- **Halaman Daftar**: `http://localhost:8000/register.php`

---

## 👤 Akun Pengujian / Demo

| Peran | Email | Kata Sandi | Keterangan |
|---|---|---|---|
| **Demo User** | `demo@kostinaja.test` | `demo12345` | Tersedia langsung dari `database/kostinaja.sql` |

---

## 📁 Struktur Direktori

```
kostinaja/
├── config/
│   ├── koneksi.example.php   # Template konfigurasi koneksi DB (masuk Git)
│   └── koneksi.php           # Konfigurasi aktif lokal (diabaikan Git)
├── css/
│   ├── style.css             # Gaya tampilan beranda & komponen kartu kos
│   ├── auth.css              # Gaya terpadu form masuk & daftar (palet hijau brand)
│   └── login.css             # Gaya halaman selamat datang
├── assets/
│   ├── logo.svg              # Logo vektor resmi KostinAja
│   └── placeholder-kos.svg   # Placeholder gambar kos jika belum ada foto
├── database/
│   └── kostinaja.sql         # Skema database legacy & 10 data kos contoh
├── docs/
│   ├── prd.md                # Product Requirements Document v1.0
│   ├── desain.md             # Dokumen Arsitektur & Desain Sistem
│   └── API.md                # Kontrak & Spesifikasi Endpoint API
├── uploads/                  # Direktori unggahan berkas pengguna
├── index.php                 # Halaman utama rekomendasi & pencarian kos (publik)
├── login.php                 # Landing page selamat datang
├── login_form.php            # Form masuk pengguna (aman dengan prepared statement)
├── register.php              # Form pendaftaran akun baru
├── proses_register.php       # Handler pendaftaran (validasi & password hashing)
├── logout.php                # Handler keluar sesi aman (metode POST)
├── todo.md                   # Pelacak tahapan task, verifikasi, dan log kerja
└── AGENTS.md                 # Panduan aturan kerja agen AI
```

---

## 🔒 Fitur Keamanan yang Diterapkan (Fase 0)

1. **Prepared Statements**: Seluruh kueri pada pencarian `index.php`, login, dan registrasi menggunakan parameter terikat (`mysqli_prepare`), mencegah SQL Injection (`P-03`).
2. **Sesi Aman**: Sesi hanya menyimpan data publik minimal (`id`, `nama`, `email`) tanpa menyimpan hash password. Pemanggilan `session_regenerate_id(true)` saat login mencegah session fixation (`P-04`).
3. **Logout Terproteksi**: Logout mewajibkan metode HTTP `POST` serta menghapus cookie sesi di peramban (`P-05`).
4. **Isolasi Kredensial & Error**: Kredensial database terisolasi di luar Git (`.gitignore`). Pesan teknis `mysqli_error()` dicatat ke server error log dan tidak pernah ditampilkan ke pengguna (`P-06`).
5. **Palet Warna Seragam**: Desain visual form masuk dan daftar telah diseragamkan dengan palet brand beranda (`#1f3d2b`, `#264f37`, `#f5c400`) (`P-08`, `D-9`).
6. **Akses Publik**: Calon penyewa dapat mencari dan memfilter kos tanpa diwajibkan login terlebih dahulu (`P-09`, `D-1`).

---

## 📚 Dokumen Acuan

- [Product Requirements Document (PRD)](docs/prd.md)
- [Dokumen Desain & Arsitektur](docs/desain.md)
- [Todo & Log Perkembangan](todo.md)
- [Aturan Kerja Agen AI](AGENTS.md)
