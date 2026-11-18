# DESAIN: KostinAja, Platform Pencarian Kos

| | |
|---|---|
| **Versi** | 1.0 |
| **Tanggal** | 18 November 2026 |
| **Acuan** | `docs/PRD.md` v1.0, `AGENTS.md`, `todo.md` |
| **Stack Target** | Backend: Laravel 13 (API JSON, PHP 8.4) + MySQL 8.4 InnoDB. Frontend: Vue 3 (Vite) + Pinia + Tailwind CSS |
| **Struktur Repo** | `backend/` (API), `frontend/` (SPA), `docs/` (PRD, Desain, API), `legacy/` (kode PHP lama, sementara) |

---

## 1. Arsitektur Sistem

KostinAja dirancang dengan arsitektur terpisah (*decoupled architecture*):
1. **Frontend (SPA)**: Dibangun dengan Vue 3 (Composition API `<script setup>`), Vite, Pinia, Vue Router, dan Tailwind CSS. Berjalan sepenuhnya di sisi klien tanpa logika bisnis langsung.
2. **Backend (API JSON)**: Dibangun dengan Laravel 13 (PHP 8.4), menyediakan API RESTful murni dengan format respons dan galat seragam. Seluruh akses database MySQL 8.4 InnoDB dikelola melalui lapisan Eloquent dan query builder berparameter.
3. **Autentikasi**: Laravel Sanctum mode SPA (cookie `httpOnly`, `SameSite=Lax`, proteksi CSRF). Frontend tidak menyimpan token di `localStorage` atau `sessionStorage`.
4. **Alat Bantu Database Lokal**: phpBro dengan layanan MySQL 9.7/8.4 dan antarmuka web Adminer (`http://localhost:8080/adminer/`) untuk manajemen skema lokal.

---

## 2. Struktur Direktori Repo

```
kostinaja/
├── AGENTS.md               # Aturan kerja agent AI
├── README.md               # Panduan proyek, instalasi, dan operasi
├── todo.md                 # Pelacak tahapan dan gerbang rilis
├── docs/                   # Dokumentasi teknis
│   ├── prd.md              # Product Requirements Document
│   ├── desain.md           # Dokumen desain & arsitektur (berkas ini)
│   └── API.md              # Kontrak API aktual & contoh payload
├── legacy/                 # Kode PHP native lama (arsip selama Fase 1-4, dihapus pada Fase 5)
├── backend/                # Project Laravel 13 API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/   # Controller tipis (request -> service/policy -> resource)
│   │   │   ├── Requests/      # Form Request untuk validasi input per aksi
│   │   │   ├── Resources/     # API Resource untuk transformasi JSON
│   │   │   └── Middleware/    # Middleware autentikasi dan peran
│   │   ├── Models/            # Model Eloquent dengan $fillable ketat
│   │   ├── Policies/          # Policy otorisasi kepemilikan dan peran
│   │   └── Services/          # Lapisan aturan bisnis (BR-01 s/d BR-13)
│   ├── config/                # Konfigurasi aplikasi
│   ├── database/
│   │   ├── migrations/        # Migrasi database (sumber tunggal skema)
│   │   └── seeders/           # Seeder admin, fasilitas, dan kos contoh
│   └── tests/
│       ├── Feature/           # Tes Pest untuk endpoint & otorisasi
│       └── Unit/              # Tes Pest untuk aturan bisnis & kalkulasi
└── frontend/               # Project Vue 3 SPA
    ├── src/
    │   ├── assets/            # Aset statis & logo
    │   ├── components/        # Komponen UI yang dapat digunakan kembali
    │   ├── composables/       # Logika UI reaktif
    │   ├── pages/             # Tampilan halaman per rute
    │   ├── router/            # Definisi rute & navigation guard
    │   ├── services/          # Klien Axios tunggal & interseptor API
    │   └── stores/            # State management Pinia (auth, filter)
    ├── index.html
    ├── tailwind.config.js     # Token desain (brand, accent, surface)
    └── vite.config.js         # Konfigurasi Vite & proxy /api
```

---

## 3. Konvensi Penamaan

- **Istilah Domain**: Bahasa Indonesia (`Kos`, `Ulasan`, `Favorit`, `Fasilitas`, kolom `nama_kos`, `harga_per_bulan`, `kamar_tersedia`).
- **Akhiran Teknis**: Bahasa Inggris (`KosController`, `KosService`, `KosResource`, `StoreKosRequest`, `KosPolicy`).
- **Pesan Pengguna & Komentar**: Bahasa Indonesia yang jelas dan ramah.

---

## 4. Model Data & Skema Database

Skema sesuai PRD bagian 10:

```sql
users(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('pencari','pemilik','admin') NOT NULL DEFAULT 'pencari',
  aktif BOOLEAN NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

kos(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pemilik_id INT UNSIGNED NOT NULL,
  nama_kos VARCHAR(150) NOT NULL,
  slug VARCHAR(200) NOT NULL UNIQUE,
  deskripsi TEXT NOT NULL,
  alamat VARCHAR(255) NOT NULL,
  kota VARCHAR(100) NOT NULL,
  lokasi VARCHAR(150) NOT NULL,
  latitude DECIMAL(10, 8) NULL,
  longitude DECIMAL(11, 8) NULL,
  tipe ENUM('putra','putri','campur') NOT NULL,
  harga_per_bulan INT UNSIGNED NOT NULL,
  jumlah_kamar SMALLINT UNSIGNED NOT NULL,
  kamar_tersedia SMALLINT UNSIGNED NOT NULL,
  kontak_whatsapp VARCHAR(30) NOT NULL,
  status ENUM('draft','menunggu','aktif','ditolak','nonaktif') NOT NULL DEFAULT 'draft',
  catatan_moderasi TEXT NULL,
  rating_avg DECIMAL(2,1) NOT NULL DEFAULT 0.0,
  rating_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  deleted_at TIMESTAMP NULL,
  FOREIGN KEY (pemilik_id) REFERENCES users(id) ON DELETE RESTRICT,
  CONSTRAINT chk_kos_harga CHECK (harga_per_bulan > 0),
  CONSTRAINT chk_kos_kamar CHECK (kamar_tersedia <= jumlah_kamar)
);

kos_gambar(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kos_id INT UNSIGNED NOT NULL,
  path VARCHAR(255) NOT NULL,
  is_cover BOOLEAN NOT NULL DEFAULT 0,
  urutan TINYINT UNSIGNED NOT NULL DEFAULT 0,
  FOREIGN KEY (kos_id) REFERENCES kos(id) ON DELETE RESTRICT
);

fasilitas(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL UNIQUE,
  ikon VARCHAR(100) NULL
);

kos_fasilitas(
  kos_id INT UNSIGNED NOT NULL,
  fasilitas_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (kos_id, fasilitas_id),
  FOREIGN KEY (kos_id) REFERENCES kos(id) ON DELETE RESTRICT,
  FOREIGN KEY (fasilitas_id) REFERENCES fasilitas(id) ON DELETE RESTRICT
);

favorit(
  user_id INT UNSIGNED NOT NULL,
  kos_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL,
  PRIMARY KEY (user_id, kos_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
  FOREIGN KEY (kos_id) REFERENCES kos(id) ON DELETE RESTRICT
);

ulasan(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kos_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  komentar TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY uq_ulasan_kos_user (kos_id, user_id),
  FOREIGN KEY (kos_id) REFERENCES kos(id) ON DELETE RESTRICT,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
  CONSTRAINT chk_ulasan_rating CHECK (rating BETWEEN 1 AND 5)
);

audit_logs(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  aksi VARCHAR(50) NOT NULL,
  entitas VARCHAR(50) NOT NULL,
  entitas_id INT UNSIGNED NOT NULL,
  catatan TEXT NULL,
  created_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
);
```

---

## 5. Penegakan Aturan Bisnis (Tabel BR-xx)

| Aturan | Deskripsi | Tempat Penegakan |
|---|---|---|
| **BR-01** | Email unik case-insensitive, password minimal 8 karakter di-hash | `RegisterRequest`, Database (`UNIQUE`), `User` model |
| **BR-02** | Hanya kos status `aktif` muncul di listing publik, favorit, sitemap | `KosService::scopeAktif()`, Query filter |
| **BR-03** | Alur status kos: `draft` -> `menunggu` -> `aktif`/`ditolak` | `KosModerasiService`, `AdminKosController`, Policy |
| **BR-04** | Pemilik hanya mengelola kos miliknya (`pemilik_id`) | `KosPolicy`, Middleware peran |
| **BR-05** | Harga > 0, `0 <= kamar_tersedia <= jumlah_kamar`, tipe putra/putri/campur | `StoreKosRequest`, Database `CHECK` constraint |
| **BR-06** | 1 ulasan per kos per pengguna. Rating 1-5. Pemilik dilarang ulas kos sendiri. Update rating dalam transaksi database | `UlasanService`, Database `UNIQUE (kos_id, user_id)`, `DB::transaction` |
| **BR-07** | Favorit unik per (user, kos), operasi tambah idempoten | `FavoritService`, Database PK `(user_id, kos_id)` |
| **BR-08** | Foto max 8, max 2MB, MIME divalidasi server, nama acak, tepat 1 cover | `UploadFotoService`, Form Request validasi MIME |
| **BR-09** | Slug unik dibuat saat pembuatan, stabil tidak berubah | `KosService`, mutator slug |
| **BR-10** | Soft delete untuk kos (`deleted_at`), ulasan & favorit tetap aman | Eloquent `SoftDeletes` |
| **BR-11** | Filter pencarian q, lokasi, tipe, harga_min <= harga_max, whitelist sort | `KosFilterRequest`, `KosQueryService` |
| **BR-12** | Kontak WhatsApp hanya tampil di detail kos publik, bukan di daftar | `KosResource` vs `KosDetailResource` |
| **BR-13** | Akun admin hanya via seeder atau perintah CLI artisan | Policy registrasi, seeder |

---

## 6. Daftar Endpoint API (Sesuai PRD Bagian 11)

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `POST` | `/api/auth/register` | Publik | Registrasi pengguna (pencari / pemilik) |
| `POST` | `/api/auth/login` | Publik | Login & inisialisasi sesi Sanctum SPA |
| `POST` | `/api/auth/logout` | Login | Hancurkan sesi & cookie |
| `GET` | `/api/auth/me` | Login | Profil pengguna yang sedang login |
| `GET` | `/api/kos` | Publik | Daftar kos aktif (search, filter, sort, pagination) |
| `GET` | `/api/kos/{slug}` | Publik | Detail lengkap kos aktif |
| `GET` | `/api/fasilitas` | Publik | Daftar master fasilitas |
| `GET` | `/api/kos/{slug}/ulasan` | Publik | Daftar ulasan kos dengan pagination |
| `POST` | `/api/kos/{slug}/ulasan` | Login | Kirim ulasan baru |
| `PUT` | `/api/kos/{slug}/ulasan` | Login | Ubah ulasan sendiri |
| `DELETE` | `/api/kos/{slug}/ulasan` | Login | Hapus ulasan sendiri |
| `GET` | `/api/favorit` | Login | Daftar kos favorit pengguna |
| `PUT` | `/api/favorit/{kos_id}` | Login | Tambah kos ke favorit (idempoten) |
| `DELETE` | `/api/favorit/{kos_id}` | Login | Hapus kos dari favorit |
| `GET` | `/api/pemilik/kos` | Pemilik | Daftar kos milik pemilik yang login |
| `POST` | `/api/pemilik/kos` | Pemilik | Tambah draf kos baru |
| `GET` | `/api/pemilik/kos/{id}` | Pemilik | Detail kos milik sendiri |
| `PUT` | `/api/pemilik/kos/{id}` | Pemilik | Perbarui kos milik sendiri |
| `DELETE` | `/api/pemilik/kos/{id}` | Pemilik | Soft delete kos milik sendiri |
| `POST` | `/api/pemilik/kos/{id}/gambar` | Pemilik | Unggah foto kos |
| `PATCH` | `/api/pemilik/kos/{id}/gambar/{gid}` | Pemilik | Atur cover / urutan foto |
| `DELETE` | `/api/pemilik/kos/{id}/gambar/{gid}` | Pemilik | Hapus foto kos |
| `PATCH` | `/api/pemilik/kos/{id}/ajukan` | Pemilik | Ajukan status `draft` -> `menunggu` |
| `GET` | `/api/admin/kos?status=menunggu` | Admin | Antrean moderasi listing kos |
| `PATCH` | `/api/admin/kos/{id}/status` | Admin | Setujui (`aktif`) atau tolak dengan catatan |
| `GET` | `/api/admin/pengguna` | Admin | Daftar pengguna (pencarian & filter) |
| `PATCH` | `/api/admin/pengguna/{id}` | Admin | Nonaktifkan / aktifkan akun pengguna |
| `POST` | `/api/admin/fasilitas` | Admin | Tambah master fasilitas |
| `PUT` | `/api/admin/fasilitas/{id}` | Admin | Ubah master fasilitas |
| `DELETE` | `/api/admin/fasilitas/{id}` | Admin | Hapus master fasilitas |
| `GET` | `/sitemap.xml` | Publik | Sitemap XML untuk SEO |

---

## 7. Format Respons JSON & Penanganan Galat

### 7.1 Format Sukses
```json
{
  "data": { ... },
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 12,
    "total": 52
  },
  "message": "Operasi berhasil"
}
```

### 7.2 Format Galat Validasi (HTTP 422)
```json
{
  "kode": "VALIDATION_ERROR",
  "message": "Data yang dikirimkan tidak valid",
  "errors": {
    "email": ["Email sudah digunakan."],
    "password": ["Password minimal 8 karakter."]
  }
}
```

### 7.3 Format Galat Umum (401, 403, 404, 429, 500)
```json
{
  "kode": "UNAUTHORIZED",
  "message": "Anda belum masuk atau sesi telah berakhir"
}
```

---

## 8. Desain UI & Token Desain Tailwind

Sesuai PRD Bagian 14 dan Keputusan D-9:
- **Brand Primary**: Hijau Tua `#1f3d2b`, Hijau Sedang `#264f37`
- **Brand Accent**: Kuning Emas `#f5c400`
- **Background / Surface**: Putih `#ffffff`, Abu-abu terang `#f8fafc`
- **Tipografi**: Inter / Plus Jakarta Sans
- **Radius**: `rounded-md` (6px) s/d `rounded-xl` (12px)
- **Komponen Inti**: `Button`, `Input`, `Select`, `Card`, `Badge`, `Modal`, `Toast`, `Skeleton`, `Pagination`
