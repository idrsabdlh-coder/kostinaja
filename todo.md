# TODO: KostinAja

| | |
|---|---|
| **Proyek** | KostinAja, platform pencarian kos |
| **Versi dokumen** | 1.0 |
| **Tanggal** | 16 November 2026 |
| **Acuan** | `docs/PRD.md` v1.0 (kebutuhan, aturan bisnis `BR-xx`, tes `T-xx`, keputusan `K-x`). `DESAIN.md` dan `AGENTS.md` belum dibuat, lihat task 0.4 dan 0.5 |
| **Struktur target** | `backend/` (Laravel API), `frontend/` (Vue SPA), `docs/`, `legacy/` (kode PHP lama, sementara) |

## Cara memakai dokumen ini

1. Kerjakan task **berurutan dalam fase**, kecuali dua jalur (BE dan FE) yang boleh paralel sesuai bagian "Gelombang kerja".
2. **Jalur**: `UM` = umum/DevOps/dokumen, `BE` = backend, `FE` = frontend. Kolom **Pemilik** diisi nama bila ada kolaborator. Bila dikerjakan sendiri, cukup centang.
3. **P**: `M` Must, `S` Should, `C` Could (urutan bangun, bukan pemangkasan fitur).
4. Centang `[x]` hanya setelah **kolom Verifikasi benar-benar dijalankan** dan hasilnya dicatat di Log (bagian 10).
5. Fase baru hanya dimulai setelah **Gerbang** fase sebelumnya lulus.
6. Jangan memilih sendiri keputusan `K-x`. Pakai nilai bawaan di PRD bagian 20 dan catat pemakaiannya di Log.
7. Aturan bisnis hanya di backend (Service/Policy/Form Request). Vue hanya menampilkan data dan mengirim tindakan pengguna.

## Gelombang kerja (paralelisme BE dan FE)

| Gelombang | Mulai bila | BE | FE |
|---|---|---|---|
| 0 | Sekarang | Fase 0 (hotfix versi lama) | – |
| 1 | Gerbang 0 lulus | 1.1–1.14 | 1.15–1.18 (scaffold, tanpa menunggu auth selesai) |
| 1b | 1.9 dan 1.15 selesai | 1.20 (bukti integrasi) bersama FE | 1.19–1.21 |
| 2 | Gerbang 1 lulus | 2.1–2.6 | 2.7–2.14, dimulai per endpoint yang sudah di-merge |
| 3 | Gerbang 2 lulus | 3.1–3.2 | 3.3–3.5 |
| 4 | Gerbang 3 lulus | 4.1–4.7 | 4.8–4.14 |
| 5 | Gerbang 4 lulus | 5.1–5.2, 5.8–5.9 | 5.3–5.7 |

**Serah-terima:** FE menyambungkan UI ke sebuah endpoint hanya setelah endpoint itu lulus tesnya dan di-merge. Contoh JSON aktual dicatat di `docs/API.md` pada PR yang memperkenalkan endpoint tersebut. Perubahan kontrak dilakukan lewat PR.

---

# FASE 0. PERSIAPAN DAN HOTFIX VERSI LAMA

Tujuan: versi PHP lama aman, bisa dijalankan dari repo bersih, dan terdokumentasi sebelum dibangun ulang. Merujuk temuan P-01 sampai P-10.

| ✓ | ID | Task | Jalur | P | Rujukan | Verifikasi |
|---|---|---|---|---|---|---|
| [x] | 0.1 | Audit repo `kostinaja` dan susun `PRD.md` v1.0 | UM | M | PRD 2 | `PRD.md` ada (selesai 16 Nov 2026) |
| [ ] | 0.2 | Ekspor database lokal lama dengan `mysqldump`. **Jangan dikomit** (berisi hash dan data nyata) | UM | M | K-6, A-6 | Berkas dump ada di luar repo. Catat nama tabel, kolom, dan format kolom `fasilitas` di Log |
| [x] | 0.3 | Buat `.gitignore` (`.env`, `*.sql` berisi data nyata, `uploads/`, `node_modules/`, `vendor/`) dan template `config/koneksi.example.php` | UM | M | P-06, NFR-BE-02 | `git status` tidak menampilkan berkas rahasia |
| [x] | 0.4 | Buat `AGENTS.md`: aturan kerja agent (baca PRD dan todo dulu, hanya satu task per branch, tidak push, tidak `migrate:fresh` di luar `_dev` dan `_test`, tidak mengarang keputusan `K-x`) | UM | S | – | Agent baru menjawab benar saat ditanya aturan inti |
| [x] | 0.5 | Buat `docs/DESAIN.md` kerangka: arsitektur, struktur folder, konvensi penamaan, daftar endpoint (disalin dari PRD 11) | UM | S | PRD 10, 11 | Berkas ada, endpoint cocok dengan PRD |
| [x] | 0.6 | Rapikan struktur versi lama: buat `config/`, `css/`, `assets/`, `uploads/`, pindahkan berkas sesuai path yang dipakai kode | UM | M | P-01 | Clone bersih: halaman login dan beranda tampil dengan style dan logo |
| [x] | 0.7 | Buat `database/kostinaja.sql` (skema `users`, `kos` + 10 baris contoh, tanpa data nyata) | UM | M | P-02 | `mysql < kostinaja.sql` pada database kosong berhasil |
| [x] | 0.8 | Ganti semua query ke **prepared statement** (`index.php`, login, register) | BE | M | P-03 | Input `' OR 1=1 --` pada kolom lokasi dan email tidak mengubah hasil |
| [x] | 0.9 | Sesi aman: simpan hanya `id`, `nama`; `session_regenerate_id(true)` saat login; logout via `POST` dan hapus cookie sesi | BE | M | P-04, P-05 | Sesi tidak memuat hash. ID sesi berubah setelah login. Logout lewat URL langsung ditolak |
| [x] | 0.10 | Pindahkan kredensial DB ke berkas konfigurasi di luar Git. Hentikan tampilan `mysqli_error()` ke pengguna | BE | M | P-06 | `grep -r "mysqli_error" .` hanya pada log. Tidak ada kredensial di commit baru |
| [x] | 0.11 | Hapus duplikat: pertahankan `login_form.php` dan `proses_register.php`, hapus `auth.php`, `proses_login.php`, `regis` | BE | M | P-07 | Alur daftar lalu login berjalan. Tidak ada tautan rusak |
| [x] | 0.12 | Seragamkan CSS masuk dan daftar dalam satu berkas dengan palet beranda | FE | M | P-08, D-9 | Halaman login dan daftar tampil seragam dengan beranda |
| [x] | 0.13 | Buka listing tanpa login. Login hanya untuk menu Favorit dan akun | BE | M | P-09, D-1 | Beranda terbuka di jendela privat tanpa diarahkan ke login |
| [x] | 0.14 | Perbarui `README.md`: deskripsi, cara instal, struktur, akun demo | UM | M | G6 | Orang lain dapat menjalankan proyek dari README saja |
| [ ] | 0.15 | Beri tag `v0.1-legacy` pada hasil hotfix | UM | S | D-8 | `git tag` menampilkan tag |

**Gerbang 0:**
- [ ] Clone repo bersih berjalan mengikuti README.
- [ ] Tidak ada kredensial atau dump data nyata di repo.
- [ ] Uji injeksi manual (0.8) dan uji sesi (0.9) lulus.
- [ ] Dump database lama (0.2) tersimpan aman di luar repo.

---

# FASE 1. FONDASI (BACKEND DAN FRONTEND)

Tujuan: dua project terpisah, saling terhubung lewat JSON, autentikasi berfungsi dari ujung ke ujung.

## 1A. Backend (Jalur BE)

| ✓ | ID | Task | Jalur | P | Rujukan | Verifikasi |
|---|---|---|---|---|---|---|
| [ ] | 1.1 | Pindahkan kode PHP lama ke `legacy/`. Buat project Laravel 13 di `backend/` (PHP 8.4) | UM | M | D-2, D-8 | `php artisan --version` benar. `legacy/` masih dapat dijalankan |
| [ ] | 1.2 | Konfigurasi `.env.example`, MySQL 8.4, charset `utf8mb4`, mode SQL strict, zona waktu `Asia/Jakarta`. Buat database `_dev` dan `_test` terpisah dengan akun aplikasi berhak minimal | BE | M | NFR-BE-02, NFR-BE-03 | Koneksi berhasil. Akun aplikasi tidak dapat `DROP` dan `ALTER` |
| [ ] | 1.3 | Migration `users` (+ `role`, `aktif`) | BE | M | PRD 10 | `php artisan migrate` pada `_dev` berhasil |
| [ ] | 1.4 | Migration `kos`, `kos_gambar`, `fasilitas`, `kos_fasilitas` termasuk indeks dan `CHECK` | BE | M | PRD 10, NFR-BE-03 | Insert `kamar_tersedia > jumlah_kamar` ditolak database |
| [ ] | 1.5 | Migration `favorit`, `ulasan` (unik `(kos_id, user_id)`, `CHECK rating 1..5`), `audit_logs` | BE | M | PRD 10 | Insert ulasan ganda ditolak database |
| [ ] | 1.6 | Model, relasi, `$fillable`, factory | BE | M | NFR-BE-05 | `tinker`: relasi `kos->fasilitas`, `kos->pemilik` terbaca |
| [ ] | 1.7 | Seeder: fasilitas, satu admin dari `.env`, minimal 20 kos contoh dengan foto placeholder | BE | M | FR-BE-12, BR-13 | **T-BE-13**: `migrate:fresh --seed` (hanya `_dev`/`_test`) berhasil |
| [ ] | 1.8 | Pasang Sanctum mode SPA, atur CORS ke alamat frontend, cookie sesi `httpOnly` | BE | M | D-3, NFR-BE-01 | Permintaan dari origin lain ditolak. Cookie memiliki `HttpOnly` |
| [ ] | 1.9 | API auth: register, login, logout, me | BE | M | FR-BE-01, 02, BR-01, 13 | **T-BE-01**, **T-BE-02** |
| [ ] | 1.10 | Rate limit login: 5 gagal per 15 menit per email+IP | BE | M | FR-BE-03 | **T-BE-03** |
| [ ] | 1.11 | Middleware peran dan Policy dasar (`pencari`, `pemilik`, `admin`) | BE | M | FR-BE-04, BR-04 | Rute uji: tanpa login 401, peran salah 403 |
| [ ] | 1.12 | Format respons seragam `{data, meta, message}` dan penanganan galat JSON (401, 403, 404, 422, 429, 500 tanpa jejak tumpukan di produksi) | BE | M | PRD 11 | Setiap kode galat mengembalikan JSON dengan bentuk sama |
| [ ] | 1.13 | Pasang Pest dan Pint. Tes auth otomatis di database `_test` | BE | M | K-10, NFR-BE-06 | `php artisan test` hijau. `./vendor/bin/pint --test` bersih |
| [ ] | 1.14 | GitHub Actions backend: PHP 8.4, MySQL 8.4, migrate, `artisan test`, `pint --test` | UM | S | NFR-BE-06 | PR memicu workflow dan lulus. Kredensial dari secrets, bukan berkas |

## 1B. Frontend (Jalur FE)

| ✓ | ID | Task | Jalur | P | Rujukan | Verifikasi |
|---|---|---|---|---|---|---|
| [ ] | 1.15 | Scaffold `frontend/`: Vue 3, Vite, Router, Pinia, Axios, Tailwind, ESLint, Prettier. Proxy `/api` dan `/sanctum` ke backend. Pin Node LTS (`.nvmrc`, `engines`) | FE | M | D-2, K-1, K-9 | `npm run dev` jalan. `npm run build` dan `npm run lint` bersih |
| [ ] | 1.16 | Design tokens Tailwind (warna `#1f3d2b`, `#264f37`, `#f5c400`, tipografi, jarak, radius) dan komponen dasar: `Button`, `Input`, `Select`, `Card`, `Badge`, `Modal`, `Toast`, `Skeleton`, `Pagination` | FE | M | D-9, FE 14 | Halaman `/styleguide` sementara menampilkan semua komponen |
| [ ] | 1.17 | Lapisan `services/` (satu klien Axios) dan penanganan galat HTTP terpusat: 401 → login, 419 → refresh CSRF, 422 → galat kolom, 429 → pesan tunggu | FE | M | NFR-FE-06 | Tes unit pada interseptor |
| [ ] | 1.18 | Layout, navbar (status login), footer, router dengan guard, halaman placeholder, 404 | FE | M | FR-FE-08, 11, 12 | Rute terlindungi mengarahkan ke login |
| [ ] | 1.19 | Halaman masuk dan daftar tersambung API: galat inline, tombol memuat, pilihan peran, guard "kembali ke halaman semula" | FE | M | FR-FE-10, 11, 12 | **T-FE-05**, **T-FE-01** (bagian redirect) |
| [ ] | 1.20 | **Bukti integrasi**: cookie Sanctum, CSRF, login dan `me` dari Vue, tanpa galat CORS. Hapus endpoint uji setelah terbukti | BE+FE | M | D-3, NFR-FE-04 | Login berhasil, `me` terbaca, cookie `HttpOnly`, tidak ada token di `localStorage` (**T-FE-12**) |
| [ ] | 1.21 | GitHub Actions frontend: `npm ci`, `npm run lint`, `npm run build` | UM | S | NFR-FE-07 | Workflow lulus pada PR |
| [ ] | 1.22 | Awal `docs/API.md` (atau koleksi Postman) untuk auth | UM | S | NFR-BE-05 | Contoh JSON aktual tercatat |

**Gerbang 1:**
- [ ] Gerbang 0 lulus. Backend dan frontend berjalan terpisah.
- [ ] Daftar, login, `me`, logout berfungsi dari Vue (bukti 1.20).
- [ ] CI backend dan frontend hijau.
- [ ] Tidak ada kredensial atau `.env` di repo.

---

# FASE 2. LISTING PUBLIK

Tujuan: calon pencari dapat mencari dan melihat detail kos **tanpa login** (G2).

## 2A. Backend

| ✓ | ID | Task | Jalur | P | Rujukan | Verifikasi |
|---|---|---|---|---|---|---|
| [ ] | 2.1 | `KosResource` dan `KosDetailResource` (kontak WhatsApp hanya di detail) | BE | M | BR-12 | Respons daftar tidak memuat `kontak_whatsapp` |
| [ ] | 2.2 | `GET /api/kos`: `q`, `lokasi`, `tipe`, `harga_min`, `harga_max`, `fasilitas[]`, `sort`, `page`, `per_page`. Hanya status `aktif`. Sort daftar putih. Pagination bawaan 12, maks 50 | BE | M | FR-BE-05, BR-02, 05, 11 | **T-BE-04**, **T-BE-05**, **T-BE-06** |
| [ ] | 2.3 | `GET /api/kos/{slug}` (galeri, fasilitas, rating, ketersediaan) | BE | M | FR-BE-06, BR-09 | Kos non-aktif membalas 404 |
| [ ] | 2.4 | `GET /api/fasilitas` | BE | M | FR-BE-07 | Daftar sesuai seeder |
| [ ] | 2.5 | Tes otomatis filter, sorting, injeksi, visibilitas status | BE | M | T-BE-04 s.d. 06 | `php artisan test` hijau |
| [ ] | 2.6 | Eager loading dan indeks. Ukur waktu respons pada 500 kos | BE | M | NFR-BE-04, G4 | ≤ 500 ms. Tidak ada N+1 (cek `DB::listen` atau Telescope) |

## 2B. Frontend

| ✓ | ID | Task | Jalur | P | Rujukan | Verifikasi |
|---|---|---|---|---|---|---|
| [ ] | 2.7 | Beranda: hero, kotak pencarian (lokasi, tipe), rekomendasi kos | FE | M | FR-FE-01 | Terbuka tanpa login. Pencarian mengarah ke `/kos?...` |
| [ ] | 2.8 | Komponen `KosCard` (foto, nama, lokasi, tipe, harga format Rupiah, rating, 3 fasilitas) | FE | M | FR-FE-03 | Harga tampil `Rp 1.200.000 / bulan` |
| [ ] | 2.9 | Halaman `/kos` dengan filter dan sorting, **semua tersimpan di URL** | FE | M | FR-FE-02 | **T-FE-02** |
| [ ] | 2.10 | State daftar: skeleton, kosong (dengan saran), galat (coba lagi) | FE | M | FR-FE-04 | **T-FE-03** |
| [ ] | 2.11 | Pagination hasil pencarian | FE | M | FR-FE-05 | Pindah halaman memperbarui URL |
| [ ] | 2.12 | Halaman detail `/kos/{slug}`: galeri, deskripsi, fasilitas, ketersediaan, tombol WhatsApp | FE | M | FR-FE-06 | Tombol membuka `wa.me` dengan nomor pemilik |
| [ ] | 2.13 | Halaman galat 404 dan 500 | FE | S | FR-FE-08 | Slug tidak valid menampilkan 404 ramah |
| [ ] | 2.14 | Responsif 360, 768, 1280 px. Filter di ponsel berupa panel geser | FE | M | FR-FE-02, NFR-FE-05 | **T-FE-04** |

**Gerbang 2:**
- [ ] Gerbang 1 lulus.
- [ ] T-BE-04, 05, 06 dan T-FE-01 (bagian publik), 02, 03, 04 lulus.
- [ ] Respons `GET /api/kos` ≤ 500 ms pada 500 kos.
- [ ] `docs/API.md` memuat contoh JSON endpoint Fase 2.

---

# FASE 3. INTERAKSI PENCARI

| ✓ | ID | Task | Jalur | P | Rujukan | Verifikasi |
|---|---|---|---|---|---|---|
| [ ] | 3.1 | API favorit: `GET /api/favorit`, `PUT` dan `DELETE /api/favorit/{kos_id}` (idempoten) | BE | M | FR-BE-08, BR-07 | **T-BE-10** |
| [ ] | 3.2 | API ulasan: daftar publik, kirim, ubah, hapus milik sendiri. `rating_avg` dan `rating_count` diperbarui **dalam transaksi yang sama** | BE | M | FR-BE-09, BR-06, D-7 | **T-BE-09**, **T-BE-12** |
| [ ] | 3.3 | Ikon favorit di kartu dan detail. Pengunjung → login → kembali ke halaman semula | FE | M | FR-FE-13 | **T-FE-07** |
| [ ] | 3.4 | Halaman `/favorit` | FE | M | FR-FE-13 | Daftar sesuai data. Kos yang dinonaktifkan tidak tampil |
| [ ] | 3.5 | Form ulasan (bintang dan komentar), daftar ulasan dengan pagination, ubah dan hapus ulasan sendiri | FE | M | FR-FE-14 | Rata-rata rating di detail berubah setelah kirim |

**Gerbang 3:**
- [ ] Gerbang 2 lulus.
- [ ] T-BE-09, 10, 12 dan T-FE-07 lulus.
- [ ] Teks `<script>` pada komentar tampil sebagai teks (**T-FE-09**).

---

# FASE 4. PEMILIK DAN ADMIN

## 4A. Backend

| ✓ | ID | Task | Jalur | P | Rujukan | Verifikasi |
|---|---|---|---|---|---|---|
| [ ] | 4.1 | CRUD kos pemilik (`/api/pemilik/kos`): validasi, slug otomatis, soft-delete, status awal `draft` | BE | M | FR-BE-10, BR-03, 04, 05, 09, 10 | Pemilik hanya melihat kos miliknya |
| [ ] | 4.2 | Upload foto: validasi MIME di server, maks 2 MB, maks 8 per kos, satu cover, nama acak, atur urutan | BE | M | FR-BE-11, BR-08 | **T-BE-11** |
| [ ] | 4.3 | Aksi `ajukan` (`draft`/`ditolak` → `menunggu`). Syarat: minimal 3 foto dan data wajib lengkap | BE | M | BR-03, FR-BE-13 | Pengajuan tanpa foto cukup ditolak 422 |
| [ ] | 4.4 | API admin moderasi: daftar `menunggu`, setujui, tolak (catatan wajib), nonaktifkan | BE | M | FR-BE-13, BR-03 | **T-BE-08** |
| [ ] | 4.5 | API admin: kelola fasilitas dan pengguna (cari, nonaktifkan) | BE | S | FR-BE-07, 14 | Akun nonaktif tidak bisa login |
| [ ] | 4.6 | Jejak audit untuk aksi moderasi dan perubahan status | BE | C | FR-BE-17 | Setiap aksi menulis satu baris `audit_logs` |
| [ ] | 4.7 | Tes otorisasi menyeluruh | BE | M | BR-04 | **T-BE-07** |

## 4B. Frontend

| ✓ | ID | Task | Jalur | P | Rujukan | Verifikasi |
|---|---|---|---|---|---|---|
| [ ] | 4.8 | Dasbor pemilik: daftar kos saya dengan status dan catatan penolakan | FE | M | FR-FE-15 | Status dan catatan tampil benar |
| [ ] | 4.9 | Form tambah dan ubah kos (centang fasilitas, validasi sesuai BR-05) | FE | M | FR-FE-16 | Galat server (422) tampil di kolom terkait |
| [ ] | 4.10 | Upload foto: banyak berkas, pratinjau, cover, urutan, hapus, progres | FE | M | FR-FE-17 | **T-FE-08** |
| [ ] | 4.11 | Tombol Ajukan dan ubah cepat `kamar_tersedia` dari daftar | FE | M | FR-FE-18 | Perubahan langsung tampil di detail publik |
| [ ] | 4.12 | Konfirmasi sebelum hapus atau nonaktifkan | FE | S | FR-FE-19 | Modal konfirmasi muncul |
| [ ] | 4.13 | Antrean moderasi admin: pratinjau seperti publik, setujui, tolak dengan catatan | FE | M | FR-FE-20 | Catatan wajib saat menolak. Kos disetujui tampil publik |
| [ ] | 4.14 | Halaman admin: fasilitas dan pengguna | FE | S | FR-FE-21, 22 | CRUD fasilitas berfungsi. Nonaktifkan pengguna berfungsi |

**Gerbang 4:**
- [ ] Gerbang 3 lulus.
- [ ] T-BE-07, 08, 11 dan T-FE-08 lulus.
- [ ] Alur penuh teruji: pemilik daftar → input kos + foto → ajukan → admin setujui → tampil di pencarian publik.

---

# FASE 5. MIGRASI DATA, KUALITAS, DAN RILIS

| ✓ | ID | Task | Jalur | P | Rujukan | Verifikasi |
|---|---|---|---|---|---|---|
| [ ] | 5.1 | Skrip impor data lama (`users`, `kos`) dengan peta kolom eksplisit. `fasilitas` teks dipecah ke relasi | BE | S | FR-BE-15, K-6, D-8 | **T-BE-15**: jumlah baris sama, login dengan password lama berhasil |
| [ ] | 5.2 | Sitemap dinamis `/sitemap.xml` | BE | S | FR-BE-16 | **T-BE-16** |
| [ ] | 5.3 | Meta SEO per halaman (judul, deskripsi, Open Graph, kanonik) | FE | S | FR-FE-09, NFR-FE-03 | Pemeriksa meta menampilkan nilai berbeda per halaman |
| [ ] | 5.4 | Putuskan SPA biasa, prerender, atau SSR (K-3). Catat keputusan di Log | UM | S | K-3 | Keputusan tercatat. Bila perlu, task lanjutan dibuat |
| [ ] | 5.5 | Optimasi performa: lazy-load gambar, WebP, `srcset`, pemecahan kode per rute | FE | M | NFR-FE-01, G4 | **T-FE-11**: Lighthouse seluler ≥ 85. Bundle awal ≤ 250 KB gzip |
| [ ] | 5.6 | Pemeriksaan aksesibilitas: label, kontras, fokus keyboard, `alt` | FE | M | NFR-FE-02 | **T-FE-10** |
| [ ] | 5.7 | Tes alur utama dengan Playwright: cari, login, favorit, ulasan | FE | S | NFR-FE-07 | Tes E2E hijau di CI |
| [ ] | 5.8 | Pemeriksaan keamanan: OWASP Top 10 dasar, `APP_DEBUG=false`, unggahan, XSS, CORS, header | BE+FE | M | G1, NFR-BE-01, NFR-FE-04 | **T-BE-14**, **T-FE-09**, **T-FE-12** |
| [ ] | 5.9 | Backup harian database dan folder foto, uji pulih ke database terpisah | BE | M | NFR-BE-07 | Dump dipulihkan, `migrate:status` dan jumlah baris cocok |
| [ ] | 5.10 | Deploy produksi (K-4): HTTPS, `.env` produksi, `storage:link`, antrean/cache sesuai kebutuhan | UM | M | K-4, A-3 | Situs terbuka lewat HTTPS. Pindai cepat tidak menemukan `.env` terbuka |
| [ ] | 5.11 | UAT dengan pengguna nyata (minimal 3 pencari dan 2 pemilik) | UM | M | US-01 s.d. 09 | Semua user story lulus. Temuan dicatat dan ditindaklanjuti |
| [ ] | 5.12 | Dokumentasi akhir: README, `docs/API.md` lengkap, `DESAIN.md` final, panduan operasional | UM | M | G6 | Instalasi dari repo bersih mengikuti README berhasil |
| [ ] | 5.13 | Hapus folder `legacy/` setelah data lama berhasil dimigrasikan. Tag `v1.0.0` | UM | M | D-8 | Tag ada. `legacy/` tidak ada di `main` |
| [ ] | 5.14 | Peta lokasi di detail kos (jika K-7 diputuskan ada) | FE | C | FR-FE-07, K-7 | Peta menampilkan titik bila koordinat tersedia |

**Gerbang 5 (rilis v1.0):**
- [ ] Gerbang 4 lulus.
- [ ] Semua tes bertanda ⚙ pada PRD bagian 17 lulus.
- [ ] G1 sampai G7 (PRD bagian 3) terpenuhi.
- [ ] Backup-pulih teruji. UAT selesai.
- [ ] Tidak ada kredensial, `.env`, atau dump di repo dan riwayat commit.

---

# 6. Keputusan terbuka (pelacak)

Nilai bawaan dipakai sampai diputuskan. Isi kolom **Keputusan** dan tanggalnya.

| ID | Pertanyaan | Bawaan | Dibutuhkan sebelum | Keputusan |
|---|---|---|---|---|
| K-1 | Vue atau Next.js | Vue 3 + Vite + Tailwind | 1.15 | |
| K-2 | Cakupan wilayah | Satu kota | 2.7 | |
| K-3 | SPA, prerender, atau SSR | SPA + meta dinamis + sitemap | 5.4 | |
| K-4 | Hosting dan domain | VPS Linux, satu domain | 5.10 | |
| K-5 | Pemilik input sendiri atau hanya admin | Pemilik input, admin moderasi | 4.1 | |
| K-6 | Format kolom `fasilitas` lama dan lokasi ekspor DB | Dipisah koma | 0.2 (ekspor), 5.1 (impor) | |
| K-7 | Peta di v1 | Tanpa peta | 5.14 | |
| K-8 | Verifikasi email saat daftar | Tidak di v1 | 1.9 | |
| K-9 | JavaScript atau TypeScript | JavaScript | 1.15 | |
| K-10 | Framework tes backend | Pest | 1.13 | |

# 7. Aturan Git

- `main` adalah cabang stabil, bukan untuk pekerjaan harian. Semua perubahan lewat **Pull Request**.
- Nama branch: `f<fase>/<id-task>-<slug>`, contoh `f2/2.2-api-daftar-kos`.
- Satu PR per task atau gabungan task kecil yang saling terkait. **Squash and merge**.
- Bila ada kolaborator: minimal satu approval dari orang lain sebelum merge. Bila dikerjakan sendiri, tinjau `git diff` dan jalankan tes sebelum merge.
- Agent AI tidak melakukan push. Pemilik memeriksa (`git status`, `git diff`, tes), lalu push dan membuka PR. Jangan `git add .` tanpa memeriksa `git status`.
- Jangan pernah mengomit `.env`, kata sandi, token, dump database, atau berkas SQL berisi data nyata. Bila rahasia pernah ter-commit, rotasi rahasia itu dan bersihkan riwayat Git.
- `package.json` dan `package-lock.json` diubah lewat PR kecil terpisah beserta alasannya. Begitu juga `composer.json` dan `composer.lock`.
- Perintah destruktif (`migrate:fresh`, `db:wipe`) hanya pada database `_dev` dan `_test` milik sendiri.

Contoh alur:

```bash
git switch main
git pull --ff-only origin main
git switch -c f2/2.2-api-daftar-kos

# ... kerjakan, jalankan tes ...
git status --short
git add backend/app/Http/Controllers/KosController.php backend/tests/Feature/KosIndexTest.php
git commit -m "feat: api daftar kos dengan filter dan pagination"
git push -u origin f2/2.2-api-daftar-kos
```

# 8. Definisi selesai untuk setiap task

Sebuah task selesai hanya bila:

- [ ] Implementasi sesuai PRD yang dirujuk (kolom Rujukan).
- [ ] Verifikasi pada task dijalankan dan hasilnya dicatat di Log.
- [ ] Tes otomatis untuk perilaku baru ada dan lulus.
- [ ] Tidak ada aturan bisnis di Controller atau Vue.
- [ ] Tidak ada `dd()`, `console.log`, `alert()`, kode uji yang tertinggal, atau kredensial.
- [ ] Format kode lulus (`pint --test`, `npm run lint`).
- [ ] Baris task dicentang, Log diperbarui, PR di-merge.

# 9. Daftar periksa peninjau

- [ ] Query memakai Eloquent atau parameter terikat, tidak ada SQL yang disambung dari input.
- [ ] Setiap endpoint pemilik atau admin dilindungi middleware peran dan Policy.
- [ ] Respons API tidak memuat hash password atau data pengguna lain.
- [ ] Perubahan rating dan ulasan dalam satu transaksi.
- [ ] Vue tidak menghitung status atau memutuskan hak akses.
- [ ] Galat 401, 419, 422, 429 ditangani di UI.
- [ ] Contoh JSON endpoint baru tercatat di `docs/API.md`.
- [ ] Tidak ada `.env`, dump SQL, atau data nyata di diff.

# 10. Log

Catat tanggal, ID task, hasil satu atau dua kalimat, hasil verifikasi, dan nilai bawaan `K-x` yang dipakai. Satu subbagian per jalur agar tidak bentrok saat merge.

### Umum (UM)

- 16 Nov 2026, 0.1: audit repo `kostinaja` (commit `7dbe6d7`, 15 file PHP native) selesai, `PRD.md` v1.0 dibuat. Verifikasi: berkas PRD ada. K-x memakai nilai bawaan.
- 17 Nov 2026, 0.3: `.gitignore`, `.gitattributes`, dan `config/koneksi.example.php` dibuat; `koneksi.php` dipindah dengan `git mv`. Verifikasi: `git check-ignore -v config/koneksi.php` cocok, `git status` tidak menampilkan berkas rahasia. K-x memakai nilai bawaan.
- 18 Nov 2026, 0.4: `AGENTS.md` aturan kerja agent disusun. Verifikasi: aturan prioritas dokumen, larangan push/migrate fresh produksi, dan format pelaporan aktif.
- 18 Nov 2026, 0.5: `docs/desain.md` kerangka arsitektur, struktur folder, konvensi penamaan, skema relasional, dan daftar endpoint dibuat. Verifikasi: berkas ada dan endpoint cocok dengan PRD bagian 10 & 11.
- 18 Nov 2026, 0.6: Struktur folder dirapikan (`css/`, `assets/`, `uploads/`), aset logo vektor `assets/logo.svg` dibuat. Verifikasi: clone bersih menampilkan gaya dan logo dengan path terpusat.
- 18 Nov 2026, 0.7: Skema SQL `database/kostinaja.sql` (tabel users, kos + 10 data contoh) diuji dengan MySQL phpBro & Adminer. Verifikasi: import database kosong via Adminer & CLI berhasil, 10 kos dan 1 akun demo aktif.
- 18 Nov 2026, 0.14: `README.md` diperbarui lengkap dengan panduan instalasi, konfigurasi koneksi phpBro, manajemen skema lewat Adminer di http://localhost:8080/adminer/, dan akun demo.

### Backend (BE)

- 18 Nov 2026, 0.8: Query pada `index.php`, `login_form.php`, dan `proses_register.php` diganti ke prepared statement (`mysqli_prepare`). Verifikasi: injeksi `' OR 1=1 --` pada pencarian dan login tidak tembus (hasil 0 baris).
- 18 Nov 2026, 0.9: Sesi diamankan: simpan hanya `id`, `nama`, `email` (tanpa hash password), `session_regenerate_id(true)` saat login sukses, logout via method `POST` dan cookie sesi dihancurkan. Verifikasi: akses GET langsung ke logout.php ditolak (HTTP 405), sesi bersih setelah logout.
- 18 Nov 2026, 0.10: Kredensial DB diisolasi di `config/koneksi.php` (diabaikan git). Seluruh `mysqli_error()` dialihkan ke `error_log()`. Verifikasi: `grep -r "mysqli_error" .` hanya muncul pada log server.
- 18 Nov 2026, 0.11: Berkas duplikat `auth.php`, `proses_login.php`, dan `regis` dihapus dengan `git rm`. Verifikasi: alur daftar (`proses_register.php`) lalu masuk (`login_form.php`) berjalan lancar tanpa tautan rusak.
- 18 Nov 2026, 0.13: Proteksi wajib login pada `index.php` dicabut. Calon penyewa dapat mencari kos tanpa login. Verifikasi: jendela privat dapat mengakses `index.php` langsung dan melihat 10 rekomendasi kos.

### Frontend (FE)

- 18 Nov 2026, 0.12: Penyeragaman CSS halaman masuk dan daftar ke dalam `css/auth.css` dan `css/login.css` dengan token palet brand beranda (`#1f3d2b`, `#264f37`, `#f5c400`). Verifikasi: form login dan register tampil seragam dan responsif.

# 11. Ringkasan progres

| Fase | Jumlah task | Selesai | Gerbang |
|---|---:|---:|---|
| 0. Persiapan dan hotfix | 15 | 13 | [ ] |
| 1. Fondasi | 22 | 0 | [ ] |
| 2. Listing publik | 14 | 0 | [ ] |
| 3. Interaksi pencari | 5 | 0 | [ ] |
| 4. Pemilik dan admin | 14 | 0 | [ ] |
| 5. Migrasi, kualitas, rilis | 14 | 0 | [ ] |
| **Total** | **84** | **1** | |