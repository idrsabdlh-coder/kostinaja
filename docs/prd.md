# PRD: KostinAja, Platform Pencarian Kos

| | |
|---|---|
| **Versi** | 1.0 (draf untuk persetujuan) |
| **Tanggal** | 16 November 2026 |
| **Asal** | Audit repo `github.com/idrsabdlh-coder/kostinaja` (commit `7dbe6d7`, 15 file, PHP native). Kode lama menjadi acuan fungsional, bukan dasar arsitektur |
| **Stack target** | Backend: Laravel 13 (API JSON, PHP 8.4) + MySQL 8.4 InnoDB. Frontend: Vue 3 (Vite) + Tailwind CSS |
| **Struktur repo** | `backend/` (API), `frontend/` (SPA), `docs/` (PRD, skema, kontrak API) |
| **Dokumen pendamping** | `DESAIN.md` (arsitektur dan UI detail), `AGENTS.md` (aturan kerja agent), `todo.md` (task per fase). Ketiganya belum dibuat |
| **Pembaca** | Pengembang, pemilik kos, admin platform |

> **Cara membaca.** Dokumen terbagi empat bagian. **Bagian A** berlaku untuk seluruh proyek. **Bagian B** hanya untuk **Backend**. **Bagian C** hanya untuk **Frontend**. **Bagian D** memuat pengujian, risiko, dan roadmap untuk keduanya. Kode ID: `P` temuan audit, `G` tujuan, `D` keputusan, `A` asumsi, `FR-BE` / `FR-FE` kebutuhan fungsional, `BR` aturan bisnis, `US` user story, `T` tes, `K` keputusan terbuka.

---

# BAGIAN A. UMUM

## 1. Ringkasan

KostinAja adalah website untuk mencari kos-kosan. Pencari kos menemukan, memfilter, menyimpan, dan mengulas kos. Pemilik kos mengelola listing sendiri. Admin memoderasi listing sebelum tampil.

Target pembaruan:

1. **Memperbaiki** kode lama yang rusak dan tidak aman (Fase 0).
2. **Memisahkan** frontend dan backend, hanya berkomunikasi lewat JSON.
3. **Melengkapi** fitur inti yang belum ada: detail kos, filter lanjutan, favorit, ulasan, dasbor pemilik, moderasi.

## 2. Latar belakang dan hasil audit

Kondisi saat ini: PHP native prosedural, MySQL lewat `mysqli`, autentikasi sesi, CSS biasa. Logika backend dan markup frontend tercampur dalam satu file `.php`. Satu commit ("Add files via upload"), semua file di root.

| ID | Temuan | Dampak | Ditangani di |
|---|---|---|---|
| P-01 | Kode memanggil `config/koneksi.php`, `css/*.css`, `assets/logo.png`, tetapi semua file ada di root repo | Hasil clone repo error atau tampil tanpa style | Fase 0 |
| P-02 | Tidak ada skema SQL untuk tabel `users` dan `kos`, tidak ada gambar kos | Proyek tidak bisa direproduksi di komputer lain | Fase 0, FR-BE-12 |
| P-03 | Query berupa string yang disambung, hanya dilindungi `mysqli_real_escape_string` | Risiko SQL injection | Fase 0, NFR-BE-01 |
| P-04 | Seluruh baris user (termasuk hash password) disimpan di `$_SESSION['user']`. Tidak ada `session_regenerate_id()` | Kebocoran data sesi, session fixation | Fase 0, FR-BE-02 |
| P-05 | Tidak ada token CSRF, tidak ada pembatasan percobaan login, logout lewat GET | Rawan CSRF dan brute force | FR-BE-03 |
| P-06 | Kredensial DB (`root`, tanpa password) tertulis di repo. `mysqli_error()` ditampilkan ke pengguna | Kebocoran informasi | Fase 0, NFR-BE-02 |
| P-07 | Duplikasi: `auth.php` = `proses_register.php`, `login_form.php` = `proses_login.php`, file kosong `regis` | Sulit dirawat, tidak jelas file mana yang dipakai | Fase 0 |
| P-08 | CSS tidak seragam: `login.css` hijau, `auth.css` dan `register.css` oranye. `auth.css` memuat kelas yang tidak terpakai (`.auth-container`, `.switch`, `.hidden`) | Tampilan masuk dan daftar tidak konsisten | Bagian C |
| P-09 | Listing kos **wajib login** untuk dilihat | Calon pencari harus mendaftar dulu, buruk untuk konversi dan SEO | D-1 |
| P-10 | Menu "Favorit" dan "Halo, user" hanya `#`. Tidak ada detail kos, pagination, atau tampilan responsif (grid selalu 3 kolom) | Fitur inti belum ada | Bagian B dan C |

Kolom yang terdeduksi dari kode lama: `users(id, nama, email, password)` dan `kos(nama_kos, lokasi, tipe, harga, rating, fasilitas, gambar)`. Tipe kos bernilai `Putra`, `Putri`, atau `Campur`.

## 3. Tujuan dan ukuran keberhasilan

| ID | Tujuan | Ukuran |
|---|---|---|
| G1 | Aplikasi aman dari kerentanan dasar | 0 temuan kritis pada pemeriksaan OWASP Top 10 dasar. Tidak ada kredensial di repo |
| G2 | Listing dapat diakses calon pencari tanpa hambatan | Beranda, pencarian, dan detail kos terbuka tanpa login |
| G3 | Frontend dan backend benar-benar terpisah | Frontend tidak mengakses database. Seluruh data lewat `/api/*` |
| G4 | Pencarian relevan dan cepat | Respons `GET /api/kos` ≤ 500 ms pada 500 kos. LCP beranda < 2,5 detik di 4G |
| G5 | Data listing terkelola | 100% listing baru melewati moderasi sebelum tampil |
| G6 | Mudah dipasang | Berjalan dari repo bersih dengan `composer install`, `npm install`, `.env`, `php artisan migrate --seed` |
| G7 | Kualitas terjaga | Cakupan tes backend ≥ 70% pada modul auth dan kos. CI hijau sebelum merge |

## 4. Pengguna dan hak akses

| Peran | Deskripsi | Kebutuhan utama |
|---|---|---|
| **Pengunjung** | Belum login | Mencari dan melihat detail kos |
| **Pencari** | Mahasiswa atau pekerja yang mencari kos | Filter akurat, simpan favorit, beri ulasan, hubungi pemilik |
| **Pemilik** | Pemilik atau pengelola kos | Input listing, foto, harga, ketersediaan kamar |
| **Admin** | Pengelola platform | Moderasi listing dan pengguna |

| Fitur | Pengunjung | Pencari | Pemilik | Admin |
|---|---|---|---|---|
| Lihat daftar dan detail kos aktif | Ya | Ya | Ya | Ya |
| Daftar dan login | Ya | – | – | – |
| Favorit | – | Ya | Ya | – |
| Ulasan | – | Ya | Ya (bukan kos sendiri) | – |
| Kelola kos | – | – | Milik sendiri | Semua |
| Setujui atau tolak listing | – | – | – | Ya |
| Kelola pengguna dan fasilitas | – | – | – | Ya |

## 5. Ruang lingkup

### 5.1 Dalam cakupan
Autentikasi, daftar kos dengan filter dan pagination, detail kos, galeri foto, fasilitas, favorit, ulasan dan rating, dasbor pemilik, upload foto, moderasi admin, seeder data contoh, perbaikan keamanan, tes otomatis.

### 5.2 Di luar cakupan v1
Pembayaran online, booking atau sewa langsung, chat di dalam aplikasi, notifikasi email atau WhatsApp otomatis, aplikasi mobile native, kontrak sewa digital, banyak level admin.

## 6. Keputusan produk

| ID | Keputusan |
|---|---|
| D-1 | **Daftar dan detail kos publik tanpa login.** Login hanya untuk favorit, ulasan, dan pengelolaan listing. Ini mengubah perilaku kode lama |
| D-2 | Backend **Laravel sebagai API JSON** di `backend/`, frontend **Vue + Tailwind** di `frontend/`. Hanya backend yang mengakses MySQL |
| D-3 | Login memakai **Laravel Sanctum mode SPA** (cookie `httpOnly`, `SameSite=Lax`), tidak menyimpan token di `localStorage` |
| D-4 | Fasilitas menjadi **master data relasional**, bukan teks bebas, agar bisa difilter |
| D-5 | Kontak pemilik lewat **tautan WhatsApp**. Tidak ada booking dan pembayaran di v1 |
| D-6 | Listing baru dari pemilik berstatus `menunggu` sampai admin menyetujui |
| D-7 | Rating ditampilkan dari data `ulasan`. `rating_avg` dan `rating_count` disimpan sebagai angka turunan yang diperbarui dalam transaksi yang sama |
| D-8 | Kode lama **diperbaiki dulu (Fase 0)** supaya aman dan bisa dijalankan, lalu **dibangun ulang** di arsitektur baru. Data `users` lama dimigrasikan. Hash `password_hash()` PHP (bcrypt, awalan `$2y$`) kompatibel dengan Laravel sehingga pengguna tidak perlu mereset password |
| D-9 | Satu design system untuk seluruh halaman. Palet acuan dari beranda lama: hijau tua `#1f3d2b`, hijau `#264f37`, kuning `#f5c400`. Versi oranye di halaman daftar dan masuk dihapus |

## 7. Asumsi

| ID | Asumsi |
|---|---|
| A-1 | Skala awal: ≤ 500 kos aktif, ≤ 5.000 pengguna, ≤ 50 pengguna bersamaan |
| A-2 | Mayoritas pengguna mengakses lewat ponsel |
| A-3 | Hosting berupa VPS atau layanan yang mendukung PHP 8.4, MySQL 8.4, dan HTTPS |
| A-4 | Ada admin yang memoderasi listing dalam 1×24 jam |
| A-5 | Foto disimpan di disk server (`storage/app/public`), bukan layanan penyimpanan eksternal |
| A-6 | Data `kos` dan `users` lama ada di database lokal pengembang dan dapat diekspor. Skemanya belum ada di repo |

---

# BAGIAN B. BACKEND

## 8. Kebutuhan fungsional backend

Prioritas: **M** = Must, **S** = Should, **C** = Could. Prioritas hanya menentukan urutan pembangunan.

| ID | Kebutuhan | BR | P |
|---|---|---|---|
| FR-BE-01 | **Registrasi.** Nama (2–100 karakter), email, password (min. 8 karakter). Pilihan peran `pencari` atau `pemilik`. Peran `admin` tidak dapat dibuat lewat registrasi | 01, 13 | M |
| FR-BE-02 | **Login dan logout** (Sanctum SPA). Sesi baru dibuat saat login. Respons tidak memuat hash password. Endpoint `me` mengembalikan `id`, `nama`, `email`, `role` | 01 | M |
| FR-BE-03 | **Pembatasan percobaan login**: 5 gagal per 15 menit per kombinasi email dan IP, balasan 429 | – | M |
| FR-BE-04 | **Otorisasi berbasis peran** lewat middleware dan Policy. Pemilik hanya mengakses kos miliknya | 04 | M |
| FR-BE-05 | **Daftar kos publik** dengan pencarian, filter, sorting, pagination. Hanya status `aktif` | 02, 11 | M |
| FR-BE-06 | **Detail kos** lewat `slug`: deskripsi, alamat, galeri, fasilitas, rating, jumlah ulasan, kontak WhatsApp, ketersediaan kamar | 02, 12 | M |
| FR-BE-07 | **Master fasilitas**: daftar publik, kelola oleh admin | – | M |
| FR-BE-08 | **Favorit**: tambah, hapus, daftar milik sendiri. Operasi tambah bersifat idempoten | 07 | M |
| FR-BE-09 | **Ulasan dan rating**: kirim, ubah, hapus milik sendiri. Daftar ulasan publik dengan pagination | 06 | M |
| FR-BE-10 | **CRUD kos oleh pemilik**, termasuk `kamar_tersedia`. Hapus berupa soft-delete | 03, 04, 05, 10 | M |
| FR-BE-11 | **Upload dan kelola foto**: maksimum 8 per kos, satu cover, urutan dapat diubah | 08 | M |
| FR-BE-12 | **Migration dan seeder**: migration sebagai satu-satunya sumber skema. Seeder memuat fasilitas, satu akun admin dari `.env`, dan minimal 20 kos contoh | – | M |
| FR-BE-13 | **Moderasi admin**: daftar kos `menunggu`, setujui atau tolak dengan catatan, nonaktifkan kos aktif | 03 | M |
| FR-BE-14 | **Kelola pengguna** oleh admin: daftar, cari, nonaktifkan akun | – | S |
| FR-BE-15 | **Skrip impor** data `kos` dan `users` lama ke skema baru (fasilitas teks dipecah menjadi relasi) | – | S |
| FR-BE-16 | **Sitemap** dinamis (`/sitemap.xml`) berisi kos aktif | – | S |
| FR-BE-17 | **Jejak audit** untuk aksi moderasi dan perubahan status | – | C |
| FR-BE-18 | **Pesan minat** (calon penyewa mengirim pertanyaan ke pemilik, tersimpan di dasbor) | – | C |

## 9. Aturan bisnis

| ID | Aturan |
|---|---|
| BR-01 | Email unik tanpa membedakan huruf besar dan kecil. Password minimal 8 karakter dan disimpan sebagai hash |
| BR-02 | Hanya kos berstatus `aktif` yang muncul di API publik, favorit, dan sitemap |
| BR-03 | Status kos: `draft` → `menunggu` → `aktif` atau `ditolak`. Kos aktif dapat `nonaktif`. Hanya admin yang mengubah status menjadi `aktif` atau `ditolak`. Penolakan wajib catatan |
| BR-04 | Pemilik hanya dapat melihat dan mengubah kos dengan `pemilik_id` miliknya. Admin dapat semua |
| BR-05 | `harga_per_bulan` bilangan bulat rupiah > 0. `0 ≤ kamar_tersedia ≤ jumlah_kamar`. `tipe` salah satu dari `putra`, `putri`, `campur` |
| BR-06 | Satu pengguna satu ulasan per kos. Rating bilangan bulat 1–5. Pemilik tidak boleh mengulas kos sendiri. `rating_avg` dan `rating_count` dihitung ulang dalam transaksi yang sama dengan perubahan ulasan |
| BR-07 | Favorit unik per pasangan `(user, kos)` |
| BR-08 | Foto: JPG, PNG, atau WebP, maksimum 2 MB, tipe diverifikasi di server (bukan dari ekstensi), nama file acak, maksimum 8 per kos, tepat satu cover |
| BR-09 | `slug` unik, dibuat dari nama dan kota, tidak berubah ketika nama diedit agar URL tetap stabil |
| BR-10 | Hapus kos adalah soft-delete. Ulasan dan favorit tetap tersimpan tetapi tidak ditampilkan |
| BR-11 | Pencarian `q` mencocokkan nama, lokasi, kota, dan alamat. `harga_min ≤ harga_max`. Kolom sorting dibatasi daftar putih: `rating`, `harga`, `terbaru` |
| BR-12 | Nomor WhatsApp pemilik hanya muncul pada detail kos, tidak pada daftar |
| BR-13 | Akun `admin` hanya dibuat lewat seeder atau perintah artisan |

## 10. Model data

```sql
users(
  id, nama, email UNIQUE, password, role ENUM('pencari','pemilik','admin'),
  aktif BOOL DEFAULT 1, created_at, updated_at
)

kos(
  id, pemilik_id FK→users, nama_kos, slug UNIQUE, deskripsi TEXT,
  alamat, kota, lokasi, latitude NULL, longitude NULL,
  tipe ENUM('putra','putri','campur'), harga_per_bulan INT UNSIGNED,
  jumlah_kamar SMALLINT, kamar_tersedia SMALLINT, kontak_whatsapp,
  status ENUM('draft','menunggu','aktif','ditolak','nonaktif'),
  catatan_moderasi NULL,
  rating_avg DECIMAL(2,1) DEFAULT 0, rating_count INT DEFAULT 0,
  created_at, updated_at, deleted_at NULL
)

kos_gambar(id, kos_id FK, path, is_cover BOOL, urutan TINYINT)
fasilitas(id, nama UNIQUE, ikon NULL)
kos_fasilitas(kos_id FK, fasilitas_id FK, PRIMARY KEY(kos_id, fasilitas_id))
favorit(user_id FK, kos_id FK, created_at, PRIMARY KEY(user_id, kos_id))
ulasan(id, kos_id FK, user_id FK, rating TINYINT, komentar TEXT NULL,
       created_at, updated_at, UNIQUE(kos_id, user_id))
audit_logs(id, user_id FK, aksi, entitas, entitas_id, created_at)
```

Indeks: `kos(status, tipe, harga_per_bulan)`, `kos(kota, lokasi)`, `kos(pemilik_id)`. Di database: `CHECK (rating BETWEEN 1 AND 5)`, `CHECK (kamar_tersedia <= jumlah_kamar)` (MySQL ≥ 8.0.16). Kolom lama `kos.fasilitas` (teks) dihapus setelah migrasi data.

## 11. Kontrak API

Semua respons JSON. Bentuk sukses: `{ "data": …, "meta": { pagination }, "message": "…" }`. Validasi gagal: HTTP 422 dengan daftar kesalahan per kolom. Tidak login: 401. Tidak berhak: 403. Terlalu banyak percobaan: 429.

| Method | Endpoint | Akses | FR |
|---|---|---|---|
| POST | `/api/auth/register` | Publik | FR-BE-01 |
| POST | `/api/auth/login` | Publik | FR-BE-02 |
| POST | `/api/auth/logout` | Login | FR-BE-02 |
| GET | `/api/auth/me` | Login | FR-BE-02 |
| GET | `/api/kos` (`q`, `lokasi`, `tipe`, `harga_min`, `harga_max`, `fasilitas[]`, `sort`, `page`, `per_page`) | Publik | FR-BE-05 |
| GET | `/api/kos/{slug}` | Publik | FR-BE-06 |
| GET | `/api/fasilitas` | Publik | FR-BE-07 |
| GET | `/api/kos/{slug}/ulasan` | Publik | FR-BE-09 |
| POST / PUT / DELETE | `/api/kos/{slug}/ulasan` | Login | FR-BE-09 |
| GET | `/api/favorit` | Login | FR-BE-08 |
| PUT / DELETE | `/api/favorit/{kos_id}` | Login | FR-BE-08 |
| GET / POST | `/api/pemilik/kos` | Pemilik | FR-BE-10 |
| GET / PUT / DELETE | `/api/pemilik/kos/{id}` | Pemilik (milik sendiri) | FR-BE-10 |
| POST | `/api/pemilik/kos/{id}/gambar` | Pemilik | FR-BE-11 |
| PATCH / DELETE | `/api/pemilik/kos/{id}/gambar/{gid}` | Pemilik | FR-BE-11 |
| PATCH | `/api/pemilik/kos/{id}/ajukan` | Pemilik | FR-BE-13 |
| GET | `/api/admin/kos?status=menunggu` | Admin | FR-BE-13 |
| PATCH | `/api/admin/kos/{id}/status` | Admin | FR-BE-13 |
| GET / PATCH | `/api/admin/pengguna` | Admin | FR-BE-14 |
| POST / PUT / DELETE | `/api/admin/fasilitas` | Admin | FR-BE-07 |
| GET | `/sitemap.xml` | Publik | FR-BE-16 |

## 12. Kebutuhan non-fungsional backend

| Aspek | Kebutuhan |
|---|---|
| **NFR-BE-01 Keamanan** | Semua query lewat Eloquent atau query builder berparameter. Password bcrypt atau argon2id. Proteksi CSRF Sanctum. Validasi lewat Form Request. Perlindungan mass assignment. Rate limit login dan registrasi. CORS hanya untuk alamat frontend. Header keamanan dasar. Unggahan divalidasi MIME di server dan tidak dieksekusi. Tidak ada hash password pada respons atau sesi |
| **NFR-BE-02 Konfigurasi** | Semua kredensial dan alamat di `.env`. `.env.example` masuk repo, `.env` dan dump SQL masuk `.gitignore`. `APP_DEBUG=false` di produksi. Pesan galat database tidak pernah sampai ke pengguna. Akun database aplikasi berhak minimal (tanpa `DROP` dan `ALTER`) |
| **NFR-BE-03 Integritas data** | Perubahan ulasan dan rating dalam satu transaksi. Kunci unik dan `CHECK` di database sebagai pengaman kedua. Mode SQL strict. Charset `utf8mb4` |
| **NFR-BE-04 Kinerja** | `GET /api/kos` ≤ 500 ms pada 500 kos. Pagination bawaan 12 item, maksimum 50. Eager loading untuk mencegah N+1 |
| **NFR-BE-05 Pemeliharaan** | Pemisahan Controller, Form Request, Service, Resource, Policy. Migration satu-satunya sumber skema. Dokumentasi API OpenAPI atau koleksi Postman di `docs/` |
| **NFR-BE-06 Kualitas** | Tes otomatis (Pest) untuk auth, filter kos, otorisasi, ulasan. Lint (Pint) dan tes berjalan di CI sebelum merge |
| **NFR-BE-07 Operasional** | Log galat terstruktur. Backup harian database dan folder foto. Uji pulih berkala |
| **NFR-BE-08 Privasi** | Hanya data minimum yang disimpan (nama, email). Email dan data pengguna tidak tampil di API publik. Nomor WhatsApp hanya pada detail kos |

---

# BAGIAN C. FRONTEND

## 13. Kebutuhan fungsional frontend

### 13.1 Halaman publik

| ID | Kebutuhan | BR | P |
|---|---|---|---|
| FR-FE-01 | **Beranda.** Hero, kotak pencarian (lokasi dan tipe), bagian "Rekomendasi Kos" (urut rating). Tanpa login | – | M |
| FR-FE-02 | **Halaman pencarian** `/kos`. Filter: kata kunci, lokasi, tipe, rentang harga, fasilitas. Sorting: rating, harga, terbaru. **Seluruh filter tersimpan di URL** sehingga hasil dapat dibagikan dan tombol Back berfungsi | 11 | M |
| FR-FE-03 | **Kartu kos**: foto cover, nama, lokasi, tipe, harga `Rp 1.200.000 / bulan`, rating, tiga fasilitas utama, ikon favorit | 12 | M |
| FR-FE-04 | **State antarmuka** lengkap pada setiap daftar: memuat (skeleton), kosong ("Kos tidak ditemukan" dengan saran melonggarkan filter), galat dengan tombol coba lagi | – | M |
| FR-FE-05 | **Pagination** pada hasil pencarian | – | M |
| FR-FE-06 | **Detail kos** `/kos/{slug}`: galeri foto, deskripsi, alamat, daftar fasilitas, ketersediaan kamar, rating dan ulasan, tombol **Hubungi via WhatsApp** | 12 | M |
| FR-FE-07 | **Peta lokasi** pada detail kos bila koordinat tersedia | – | C |
| FR-FE-08 | **Halaman galat** 404 dan 500 yang ramah | – | S |
| FR-FE-09 | **Meta SEO** per halaman: judul, deskripsi, Open Graph, URL bersih `/kos/{slug}` | – | S |

### 13.2 Autentikasi dan pencari

| ID | Kebutuhan | BR | P |
|---|---|---|---|
| FR-FE-10 | **Halaman masuk dan daftar** dalam satu gaya visual. Validasi di klien, pesan galat **inline di bawah kolom** (bukan `alert()`). Pilihan peran saat daftar (Pencari kos / Pemilik kos). Indikator kekuatan password | 01 | M |
| FR-FE-11 | **Status login di navbar**: nama pengguna dengan menu (Favorit, Dasbor jika pemilik, Keluar). Logout memakai `POST`, bukan tautan | – | M |
| FR-FE-12 | **Guard rute.** Halaman yang butuh login mengarahkan ke masuk, lalu **kembali ke halaman semula** setelah berhasil | – | M |
| FR-FE-13 | **Favorit.** Ikon hati di kartu dan detail. Halaman `/favorit` memuat daftar. Pengunjung yang menekan ikon diarahkan ke login | 07 | M |
| FR-FE-14 | **Ulasan.** Form bintang dan komentar di detail kos, daftar ulasan dengan pagination, tombol ubah dan hapus pada ulasan sendiri | 06 | M |

### 13.3 Dasbor pemilik

| ID | Kebutuhan | BR | P |
|---|---|---|---|
| FR-FE-15 | **Daftar kos saya** dengan status (draft, menunggu, aktif, ditolak, nonaktif) dan catatan penolakan | 03 | M |
| FR-FE-16 | **Form tambah dan ubah kos**: seluruh kolom, pemilihan fasilitas (centang), validasi sesuai BR-05 | 05 | M |
| FR-FE-17 | **Upload foto**: banyak file, pratinjau, tandai cover, atur urutan, hapus. Validasi tipe dan ukuran di klien (server tetap memvalidasi). Indikator progres | 08 | M |
| FR-FE-18 | **Ajukan untuk moderasi** dan **ubah jumlah kamar tersedia** dengan cepat dari daftar | 03, 05 | M |
| FR-FE-19 | **Konfirmasi** sebelum hapus atau nonaktifkan kos | 10 | S |

### 13.4 Panel admin

| ID | Kebutuhan | BR | P |
|---|---|---|---|
| FR-FE-20 | **Antrean moderasi**: daftar kos `menunggu`, pratinjau seperti tampilan publik, tombol Setujui dan Tolak (catatan wajib saat menolak) | 03 | M |
| FR-FE-21 | **Kelola fasilitas** (tambah, ubah, hapus) | – | S |
| FR-FE-22 | **Kelola pengguna**: cari dan nonaktifkan | – | S |

## 14. Desain dan pengalaman pengguna

- **Design system tunggal.** Token warna, tipografi, jarak, dan radius didefinisikan sekali di konfigurasi Tailwind. Komponen dasar dipakai ulang: `Button`, `Input`, `Select`, `Card`, `Badge`, `Modal`, `Toast`, `Skeleton`, `Pagination`.
- **Mobile-first.** Grid kartu 1 kolom (ponsel), 2 (tablet), 3 (desktop). Filter di ponsel berupa panel geser. Elemen sentuh minimal 44×44 px.
- **Konsistensi.** Halaman masuk dan daftar memakai palet yang sama dengan beranda (D-9). Kelas CSS lama yang tidak terpakai tidak dibawa.
- **Umpan balik.** Tombol kirim menampilkan status memuat dan dinonaktifkan selama permintaan berjalan. Toast untuk keberhasilan, pesan inline untuk galat validasi.
- **Bahasa dan format.** Bahasa Indonesia. Harga `Rp 1.200.000`. Pesan galat spesifik dan tidak menyalahkan.

## 15. Kebutuhan non-fungsional frontend

| Aspek | Kebutuhan |
|---|---|
| **NFR-FE-01 Kinerja** | LCP < 2,5 detik di jaringan 4G. Gambar `loading="lazy"`, format WebP, ukuran responsif (`srcset`). Pemecahan kode per rute. Bundle awal ≤ 250 KB (gzip) |
| **NFR-FE-02 Aksesibilitas** | Kontras minimal WCAG AA. Label pada semua kolom. Navigasi keyboard dengan fokus terlihat. `alt` bermakna pada foto kos. Pesan galat dikaitkan ke kolomnya |
| **NFR-FE-03 SEO** | Judul dan meta per halaman, URL bersih, tag kanonik, sitemap dari backend (FR-BE-16). Keputusan SSR atau prerender di K-3 |
| **NFR-FE-04 Keamanan** | Tidak ada rahasia di bundle. Tidak ada token di `localStorage` (cookie `httpOnly`). Output dirender sebagai teks (tidak memakai `v-html` untuk konten pengguna). Alamat API dari `VITE_API_URL` |
| **NFR-FE-05 Kompatibilitas** | Dua versi terbaru Chrome, Edge, Firefox, Safari (desktop dan seluler). Lebar layar uji 360, 768, 1280 px |
| **NFR-FE-06 Pemeliharaan** | Struktur `src/{pages,components,composables,stores,services,router}`. Semua panggilan API lewat satu lapisan `services/`. Penanganan galat HTTP terpusat (401 → login, 422 → galat kolom, 429 → pesan tunggu) |
| **NFR-FE-07 Kualitas** | Lint (ESLint) dan format (Prettier). Tes unit untuk komposabel kritis. Tes uji alur utama (cari, login, favorit) dengan Playwright |

---

# BAGIAN D. PENGUJIAN, RISIKO, ROADMAP

## 16. User story

| ID | Cerita | Penerimaan |
|---|---|---|
| US-01 | Sebagai pengunjung, saya ingin mencari kos tanpa mendaftar | Beranda dan hasil pencarian terbuka tanpa login |
| US-02 | Sebagai pencari, saya ingin memfilter berdasarkan lokasi, tipe, harga, fasilitas | Hasil sesuai semua filter. URL memuat filter |
| US-03 | Sebagai pencari, saya ingin melihat detail dan menghubungi pemilik | Detail menampilkan galeri, fasilitas, ulasan. Tombol WhatsApp membuka percakapan |
| US-04 | Sebagai pencari, saya ingin menyimpan kos favorit | Ikon hati menyimpan. Halaman favorit memuatnya. Pengunjung diarahkan login |
| US-05 | Sebagai pencari, saya ingin mengulas kos | Satu ulasan per kos. Rating rata-rata ikut berubah |
| US-06 | Sebagai pemilik, saya ingin memasang listing dengan foto | Listing baru berstatus menunggu dan belum tampil publik |
| US-07 | Sebagai pemilik, saya ingin memperbarui jumlah kamar kosong | Perubahan langsung tampil pada detail |
| US-08 | Sebagai admin, saya ingin menyetujui atau menolak listing | Kos disetujui tampil publik. Penolakan wajib catatan yang terlihat pemilik |
| US-09 | Sebagai pengguna, saya ingin login yang aman | Percobaan gagal dibatasi. Sesi berakhir saat logout |

## 17. Kriteria penerimaan

Tes bertanda ⚙ wajib otomatis. Tes lain diuji manual.

### 17.1 Backend

| ID | Skenario | Lulus jika |
|---|---|---|
| T-BE-01 ⚙ | Daftar | Email ganda ditolak (422). Password < 8 karakter ditolak. Peran `admin` lewat registrasi ditolak |
| T-BE-02 ⚙ | Login | Kredensial benar membuat sesi. Salah memberi 422 dengan pesan umum. Hash tidak pernah ada di respons |
| T-BE-03 ⚙ | Pembatasan login | Percobaan gagal ke-6 dalam 15 menit membalas 429 |
| T-BE-04 ⚙ | Daftar publik | Hanya kos `aktif`. Kos `menunggu`, `ditolak`, `nonaktif`, dan soft-delete tidak muncul |
| T-BE-05 ⚙ | Filter dan sorting | Setiap kombinasi filter mengembalikan hasil benar. Kolom sort di luar daftar putih ditolak. `harga_min > harga_max` ditolak |
| T-BE-06 ⚙ | Injeksi | Input `' OR 1=1 --` pada `q` dan `lokasi` diperlakukan sebagai teks biasa |
| T-BE-07 ⚙ | Otorisasi pemilik | Pemilik A mengubah kos pemilik B → 403. Pencari mengakses `/api/pemilik/*` → 403. Tanpa login → 401 |
| T-BE-08 ⚙ | Moderasi | Kos baru berstatus `menunggu`. Hanya admin yang dapat mengubah ke `aktif` atau `ditolak`. Penolakan tanpa catatan ditolak |
| T-BE-09 ⚙ | Ulasan | Ulasan kedua pada kos yang sama ditolak. Pemilik mengulas kos sendiri ditolak. `rating_avg` dan `rating_count` sesuai setelah tambah, ubah, hapus |
| T-BE-10 ⚙ | Favorit | Dua permintaan tambah yang sama menghasilkan satu baris |
| T-BE-11 ⚙ | Upload | File `.php` yang diberi ekstensi `.jpg` ditolak. File > 2 MB ditolak. Foto ke-9 ditolak. Nama file tersimpan acak |
| T-BE-12 ⚙ | Rollback | Kegagalan di tengah pembaruan ulasan tidak menyisakan `rating_avg` yang salah |
| T-BE-13 ⚙ | Seeder dan migration | `php artisan migrate:fresh --seed` berhasil pada database kosong |
| T-BE-14 | Konfigurasi | Tidak ada kredensial di repo atau riwayat commit baru. `APP_DEBUG=false` pada produksi. Galat database tidak tampil ke klien |
| T-BE-15 ⚙ | Impor data lama | Skrip impor memindahkan semua `users` (login dengan password lama tetap berhasil) dan semua `kos` ke skema baru tanpa kehilangan baris |
| T-BE-16 ⚙ | Sitemap | `/sitemap.xml` hanya memuat kos aktif |

### 17.2 Frontend

| ID | Skenario | Lulus jika |
|---|---|---|
| T-FE-01 ⚙ | Akses tanpa login | Beranda, pencarian, dan detail terbuka. `/favorit` dan `/dasbor` mengarahkan ke login lalu kembali ke tujuan setelah masuk |
| T-FE-02 ⚙ | Filter di URL | Memuat ulang atau membagikan URL `/kos?tipe=putri&harga_max=1000000` menampilkan filter dan hasil yang sama |
| T-FE-03 ⚙ | State daftar | Skeleton muncul saat memuat. Hasil kosong memberi pesan dan saran. Galat jaringan memberi tombol coba lagi |
| T-FE-04 | Responsif | Beranda, pencarian, detail, dan dasbor pemilik tidak terpotong pada 360, 768, dan 1280 px. Tidak ada gulir horizontal |
| T-FE-05 ⚙ | Form masuk dan daftar | Galat validasi tampil inline. Tidak ada `alert()`. Tombol kirim nonaktif saat memuat |
| T-FE-06 | Konsistensi visual | Halaman masuk, daftar, beranda memakai palet dan komponen yang sama |
| T-FE-07 ⚙ | Favorit | Menekan hati sebagai pengunjung → login → kembali ke halaman semula dengan kos tersimpan |
| T-FE-08 | Upload foto | Pratinjau, cover, urutan, dan hapus berfungsi. File tidak valid menampilkan pesan jelas |
| T-FE-09 ⚙ | XSS | Nama kos atau komentar berisi `<script>` tampil sebagai teks, tidak dieksekusi |
| T-FE-10 | Aksesibilitas | Seluruh form dapat dioperasikan dengan keyboard. Tidak ada pelanggaran kritis pada pemeriksaan otomatis (axe atau Lighthouse) |
| T-FE-11 | Kinerja | Skor Lighthouse performa ≥ 85 pada beranda (profil seluler) |
| T-FE-12 | Keamanan sisi klien | Tidak ada token atau rahasia di `localStorage` dan bundle |

## 18. Risiko

| Risiko | Kemungkinan | Dampak | Mitigasi |
|---|---|---|---|
| Skema DB lama tidak ada di repo, data lama tidak terbaca | Sedang | Sedang | Ekspor `mysqldump` dari lingkungan lokal sebelum Fase 1 (K-6). Skrip impor memakai peta kolom eksplisit |
| Pemilik tidak mengisi data lengkap atau foto buruk | Tinggi | Sedang | Validasi wajib, minimal 3 foto sebelum bisa diajukan, panduan di form |
| Listing palsu atau menipu | Sedang | Tinggi | Moderasi sebelum tayang, tombol laporkan, nonaktifkan akun |
| Info "kamar tersedia" kedaluwarsa | Tinggi | Sedang | Tanggal pembaruan terakhir di detail, pengingat ke pemilik (setelah v1) |
| SPA kurang ramah mesin pencari | Sedang | Sedang | Meta dinamis, sitemap, prerender atau SSR bila perlu (K-3) |
| Brute force login | Sedang | Tinggi | Rate limit FR-BE-03, pesan galat umum |
| Unggahan berbahaya | Sedang | Tinggi | Validasi MIME di server, nama acak, folder tidak mengeksekusi skrip |
| Migrasi menunda rilis | Sedang | Sedang | Fase 0 membuat versi lama aman terlebih dahulu sehingga tidak mendesak |
| Satu pengembang, ruang lingkup melebar | Tinggi | Sedang | Prioritas M dibangun lebih dulu, fitur C setelah v1.0 |

## 19. Roadmap

| Fase | Isi | Hasil |
|---|---|---|
| **0. Hotfix versi lama** | Lihat daftar di bawah | Kode lama aman dan bisa dijalankan dari repo bersih |
| **1. Fondasi** | Struktur `backend/` dan `frontend/`, migration, model, seeder, Sanctum, FR-BE-01 sampai 04, FR-BE-12, CI | Daftar dan login via API, walking skeleton Vue terhubung |
| **2. Listing publik** | FR-BE-05 sampai 07, FR-FE-01 sampai 06, FR-FE-10 sampai 12, design system | Calon pencari dapat mencari dan melihat detail tanpa login |
| **3. Interaksi pencari** | FR-BE-08, 09, FR-FE-13, 14 | Favorit dan ulasan |
| **4. Pemilik dan admin** | FR-BE-10, 11, 13, 14, FR-FE-15 sampai 22 | Listing dikelola pemilik, dimoderasi admin |
| **5. Migrasi, kualitas, rilis** | FR-BE-15, 16, SEO, performa, aksesibilitas, backup, deploy, uji penerimaan | v1.0 rilis |

**Daftar kerja Fase 0 (versi PHP lama):**

1. Buat folder `config/`, `css/`, `assets/`, `uploads/`, pindahkan file sesuai path yang dipakai kode (P-01).
2. Tambahkan `database/kostinaja.sql` berisi skema dan data contoh (P-02).
3. Ganti seluruh query ke prepared statement (P-03).
4. Simpan hanya `id`, `nama`, `role` di sesi. Panggil `session_regenerate_id(true)` saat login. Logout lewat `POST` dan hapus cookie sesi (P-04, P-05).
5. Pindahkan kredensial DB ke berkas konfigurasi di luar Git dan tambahkan `.gitignore`. Hentikan tampilan `mysqli_error()` ke pengguna (P-06).
6. Hapus duplikat: pertahankan `login_form.php` dan `proses_register.php`, hapus `auth.php`, `proses_login.php`, `regis` (P-07).
7. Seragamkan CSS masuk dan daftar (P-08).
8. Buka listing tanpa login (P-09).
9. Perbarui `README.md`: cara instal, struktur, akun demo.

**Setelah v1.0:** peta interaktif, pesan minat dengan notifikasi, pengingat pembaruan ketersediaan, laporan listing, pencarian berdasarkan radius, bandingkan kos, SSR penuh, multi-bahasa.

## 20. Keputusan terbuka

Agent tidak boleh memilih sendiri keputusan di tabel ini. Nilai bawaan dipakai sementara.

| ID | Pertanyaan | Bawaan sementara | Dibutuhkan sebelum |
|---|---|---|---|
| K-1 | Frontend memakai Vue atau Next.js? | Vue 3 + Vite + Tailwind | Fase 1 |
| K-2 | Cakupan wilayah: satu kota atau nasional? | Satu kota, kolom `kota` sudah disiapkan | Fase 2 |
| K-3 | SEO: SPA biasa, prerender, atau SSR (Nuxt)? | SPA + meta dinamis + sitemap | Fase 5 |
| K-4 | Hosting dan domain produksi (VPS atau shared hosting)? | VPS Linux, satu domain, API di sub-path atau subdomain | Fase 5 |
| K-5 | Apakah pemilik menginput sendiri, atau hanya admin yang mengelola data? | Pemilik menginput sendiri, admin memoderasi | Fase 4 |
| K-6 | Format kolom `fasilitas` pada data lama (dipisah koma atau teks bebas) dan lokasi berkas ekspor database lama | Dipisah koma | Fase 5 (impor), berkas ekspor dibutuhkan sebelum Fase 1 |
| K-7 | Peta: Leaflet + OpenStreetMap atau tanpa peta di v1? | Tanpa peta (FR-FE-07 berprioritas C) | Fase 4 |
| K-8 | Verifikasi email saat daftar? | Tidak di v1 | Fase 1 |
| K-9 | JavaScript atau TypeScript untuk frontend? | JavaScript (`<script setup>`) | Fase 1 |
| K-10 | Framework tes backend | Pest | Fase 1 |

## 21. Riwayat revisi

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.0 | 16 Nov 2026 | Dokumen pertama. Disusun dari audit repo `kostinaja` (commit `7dbe6d7`): temuan P-01 sampai P-10, pemisahan Backend (Bagian B) dan Frontend (Bagian C), skema data, kontrak API, kriteria penerimaan, roadmap dengan Fase 0 hotfix |