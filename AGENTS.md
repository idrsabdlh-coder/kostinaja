# AGENTS.md: Aturan Kerja Agent AI untuk KostinAja

| | |
|---|---|
| **Versi** | 1.0 |
| **Tanggal** | 16 November 2026 |
| **Berlaku untuk** | Semua agent AI yang bekerja di repo ini (Claude Code, Codex, Cursor, Copilot, dan sejenisnya) |
| **Dokumen acuan** | [`docs/PRD.md`](docs/PRD.md) (apa dan mengapa), [`docs/DESAIN.md`](docs/DESAIN.md) (bagaimana), [`todo.md`](todo.md) (urutan, verifikasi, gerbang) |
| **Stack** | Backend: Laravel 13 (API JSON), PHP 8.4, MySQL 8.4 InnoDB. Frontend: Vue 3 + Vite + Pinia + Tailwind CSS |

Aturan di sini **wajib**. Bila sebuah aturan tampak menghambat, hentikan dan tanyakan kepada pemilik repo. Jangan melanggarnya diam-diam.

## 1. Peta dokumen dan prioritas

| Pertanyaan | Dokumen yang berlaku |
|---|---|
| Fitur apa yang dibangun, aturan bisnis `BR-xx`, kriteria lulus `T-xx`, keputusan `D-x`, `K-x` | `docs/PRD.md` |
| Skema, status, Service, API, struktur folder, UI | `docs/DESAIN.md` |
| Task mana yang dikerjakan, urutan, verifikasi, gerbang | `todo.md` |
| Cara agent bekerja, apa yang dilarang | `AGENTS.md` (berkas ini) |

Bila dokumen saling bertentangan atau ada kekosongan spesifikasi: **hentikan bagian yang terdampak, laporkan, dan tunggu keputusan pemilik.** Jangan memilih sendiri, jangan mengubah PRD atau DESAIN supaya cocok dengan kode.

Instruksi langsung dari pemilik di percakapan saat ini mengalahkan berkas ini, **kecuali** untuk bagian 8 (rahasia) dan bagian 9 (perintah terlarang), yang tetap perlu persetujuan eksplisit per perintah.

## 2. Sebelum mengerjakan task apa pun

1. Baca `AGENTS.md`, `todo.md` (baris task dan Gerbang fase), bagian PRD yang dirujuk kolom **Rujukan**, dan bagian DESAIN yang relevan.
2. Pastikan task berada di fase yang **gerbang sebelumnya sudah lulus** dan task sebelumnya dalam jalur yang sama sudah dicentang.
3. Awali dengan ringkasan singkat: ID task, FR/BR/T yang dirujuk, berkas yang akan disentuh, dan asumsi. Bila ada keputusan `K-x` yang menyentuh task, sebutkan nilai bawaan yang akan dipakai.
4. Kerjakan **satu task per sesi dan satu branch**. Jangan menggabungkan task lintas fase.

## 3. Aturan kerja

- Kerjakan **bertahap**: satu perubahan kecil, jalankan verifikasi, tunjukkan hasilnya, lalu minta konfirmasi sebelum lanjut untuk perubahan yang menyentuh banyak berkas.
- Hanya kerjakan task milik **jalur** yang ditugaskan (`BE`, `FE`, `UM`). Task jalur lain hanya disentuh bila pemilik menyetujui pengalihan.
- **Jangan mengarang** keputusan bisnis atau nilai `K-x`. Pakai nilai bawaan di PRD bagian 20, lalu catat pemakaiannya di Log `todo.md`.
- Jangan menambah fitur di luar task. Ide tambahan ditulis sebagai catatan di ringkasan akhir, bukan diimplementasikan.
- Jangan membuka kembali keputusan yang sudah tercatat di PRD (`D-x`) atau di kolom **Keputusan** pelacak K-x.
- Perubahan pada `docs/PRD.md` atau `docs/DESAIN.md` hanya atas permintaan pemilik. Usulan perubahan ditulis sebagai catatan di PR atau Log.
- Kerjakan secukupnya: perubahan sekecil mungkin yang memenuhi task. Tidak ada refactor besar di luar cakupan.
- Penamaan: istilah domain berbahasa Indonesia (`Kos`, `Ulasan`, `Favorit`, `Fasilitas`, kolom `nama_kos`, `harga_per_bulan`), akhiran teknis berbahasa Inggris (`Controller`, `Service`, `Resource`, `Request`). Komentar kode dan pesan untuk pengguna dalam Bahasa Indonesia.

## 4. Aturan backend (`backend/`)

**Wajib:**

1. **Controller tipis.** Hanya menerima request tervalidasi, memanggil Service atau Policy, dan mengembalikan Resource. Tidak ada aturan bisnis di Controller.
2. **Aturan bisnis di `app/Services`.** Setiap `BR-xx` ditegakkan di Service, Policy, Form Request, atau constraint database sesuai tabel penegakan di DESAIN bagian 2.
3. **Validasi di Form Request**, satu per aksi.
4. **Query hanya lewat Eloquent atau query builder berparameter.** Wildcard `LIKE` dari input pengguna di-escape (`%`, `_`, `\`).
5. **Bentuk JSON lewat API Resource.** Jangan pernah mengembalikan model langsung. Hash password dan kolom internal tidak boleh keluar.
6. **Perlindungan mass assignment** dengan `$fillable`. Kolom `role`, `status`, `pemilik_id`, `rating_*` tidak boleh ikut mass assignment dari input pengguna.
7. **Otorisasi eksplisit** di setiap rute non-publik: middleware `auth:sanctum`, middleware peran, dan Policy untuk kepemilikan.
8. **Transaksi database** untuk operasi multi-baris (ulasan + rating, unggah foto + cover, moderasi + audit).
9. **Respons dan galat seragam** sesuai DESAIN bagian 7 (`{data, meta, message}` dan `{kode, message, errors?}`).
10. **Gunakan `config()`**, bukan `env()`, di luar berkas `config/*.php`.

**Dilarang:**

- Menyambung string SQL dari input. Menjalankan SQL mentah tanpa binding.
- `dd()`, `dump()`, `var_dump()`, `Log::debug` berisi data pribadi di kode yang dikomit.
- Menampilkan pesan galat database atau jejak tumpukan ke klien.
- Mengubah `config/database.php` atau `.env` agar menunjuk database produksi.

## 5. Aturan frontend (`frontend/`)

**Wajib:**

1. **Tidak ada aturan bisnis di Vue.** Frontend menampilkan data dan mengirim tindakan. Tombol yang tidak berlaku boleh disembunyikan, tetapi server tetap yang menentukan.
2. Semua panggilan API lewat satu lapisan `src/services/` dan klien Axios tunggal. Penanganan 401, 419, 422, 429 terpusat di interseptor.
3. Gunakan **token desain** Tailwind (`brand`, `accent`, `surface`, dan seterusnya). Jangan menulis warna heksadesimal langsung di komponen.
4. Pakai ulang komponen dasar (`Button`, `Input`, `Select`, `Card`, `Badge`, `Modal`, `Toast`, `Skeleton`, `Pagination`). Jangan membuat varian baru tanpa alasan.
5. Setiap daftar dan halaman data punya tiga keadaan: memuat (skeleton), kosong, galat dengan tombol coba lagi.
6. Filter pencarian tersimpan di URL (DESAIN bagian 9.3).
7. Aksesibilitas: label pada semua input, fokus terlihat, `alt` bermakna, toast memakai `aria-live`, kontras WCAG AA.

**Dilarang:**

- Menyimpan token, kata sandi, atau data pengguna di `localStorage` atau `sessionStorage`.
- `v-html` untuk konten pengguna (nama kos, deskripsi, komentar).
- `alert()`, `confirm()`, `prompt()`. Gunakan `Toast` dan `ModalKonfirmasi`.
- `console.log` yang tertinggal. Rahasia atau alamat internal di kode klien.
- Menghitung rating, status, atau hak akses di klien.

## 6. Database dan migration

- **Migration adalah satu-satunya sumber skema.** Jangan mengubah skema lewat alat SQL.
- Migration yang sudah di-merge tidak diubah. Perubahan lewat migration baru, kecuali pemilik mengumumkan sebaliknya (belum ada data produksi sebelum Gerbang 5).
- Semua FK `RESTRICT`. Tidak ada `CASCADE`. Data kos dihapus dengan soft-delete.
- `CHECK` constraint dibuat lewat `DB::statement` bila Blueprint tidak menyediakannya.
- **`migrate:fresh`, `migrate:reset`, `db:wipe`, `DROP`, `TRUNCATE` hanya boleh pada database berakhiran `_dev` atau `_test`.** Periksa `DB_DATABASE` sebelum menjalankannya. Bila nama tidak berakhiran `_dev` atau `_test`, berhenti dan tanya.
- Tes hanya berjalan pada database `_test`. Jangan mengubah konfigurasi tes supaya menunjuk database lain.
- Jangan mengubah data produksi lewat SQL manual. Koreksi lewat aplikasi atau perintah artisan yang ditinjau.

## 7. Pengujian

- Setiap perilaku baru punya **tes otomatis** (Pest di backend, Vitest dan Playwright di frontend). Sebutkan ID `T-xx` yang dipenuhi.
- Sebelum menyatakan task selesai, jalankan dan tampilkan hasil ringkas:

| Area | Perintah |
|---|---|
| Backend tes | `cd backend && php artisan test` |
| Backend gaya kode | `cd backend && ./vendor/bin/pint --test` |
| Frontend lint | `cd frontend && npm run lint` |
| Frontend tes unit | `cd frontend && npm run test` |
| Frontend build | `cd frontend && npm run build` |

- **Jangan** menonaktifkan, melewati (`skip`), atau melemahkan tes yang gagal agar hasil hijau. Perbaiki penyebabnya atau laporkan.
- Jangan menulis tes yang hanya meniru implementasi. Tes memeriksa perilaku sesuai PRD.
- Bila verifikasi tidak dapat dijalankan (lingkungan belum siap), katakan terus terang. Jangan mengklaim lulus.

## 8. Rahasia dan keamanan

- **Jangan pernah** mengomit atau mencetak: `.env`, kata sandi, token, kunci API, dump database, berkas `*.sql` berisi data nyata, `backup.cnf`.
- Dump database lama berisi hash password pengguna nyata. Simpan di luar repo, jangan ditempel ke percakapan, Log, atau PR.
- Hanya `.env.example` (tanpa nilai rahasia) yang masuk Git. `.env`, `*.sql` data nyata, `storage/app/public/*`, `node_modules/`, `vendor/`, `dist/` masuk `.gitignore`.
- Bila menemukan rahasia yang sudah terlanjur ter-commit: berhenti, laporkan, sarankan rotasi rahasia dan pembersihan riwayat. Jangan menghapusnya diam-diam.
- Akun admin dibuat lewat seeder atau `php artisan kostinaja:buat-admin`, tidak pernah lewat registrasi publik (BR-13). Kata sandi admin awal datang dari `.env` dan tidak ditulis di kode, README, atau Log.
- Unggahan: validasi tipe berdasarkan isi berkas (bukan ekstensi), nama file acak, tidak pernah dieksekusi.
- `APP_DEBUG=false` di lingkungan selain lokal.

## 9. Git

- Branch: `f<fase>/<id-task>-<slug>`, contoh `f2/2.2-api-daftar-kos`. Mulai dari `main` yang mutakhir.
- Commit kecil dan bermakna dengan awalan **Conventional Commits**: `feat:`, `fix:`, `test:`, `docs:`, `refactor:`, `chore:`. Contoh: `feat: api daftar kos dengan filter dan pagination`.
- Tambahkan berkas **secara eksplisit** (`git add <berkas>`). Jangan `git add .` atau `git add -A` tanpa memeriksa `git status`.
- **Agent tidak melakukan `git push`, merge, atau membuka PR.** Pemilik memeriksa (`git status`, `git diff`, tes), lalu push dan membuka PR.
- **Dilarang** tanpa persetujuan eksplisit: `git push --force`, `git reset --hard`, `git rebase` pada branch bersama, `git clean -fd`, mengubah riwayat commit, bekerja langsung di `main`.
- Jangan menjalankan dua agent pada branch yang sama secara bersamaan.
- Perubahan `package.json`, `package-lock.json`, `composer.json`, `composer.lock` dibuat sebagai commit terpisah dengan alasan dan pemeriksaan kompatibilitas (Laravel 13, PHP 8.4, Node LTS yang dipin di `.nvmrc`).

## 10. Perintah yang perlu izin eksplisit

Hentikan dan minta persetujuan sebelum menjalankan:

- `composer update` penuh, `npm audit fix --force`, atau perubahan massal versi dependensi.
- Perintah apa pun yang menjangkau server produksi atau layanan eksternal berbayar.
- `rm -rf` di luar folder sementara milik task (`node_modules`, `vendor`, `dist`, `storage/framework` boleh dibersihkan).
- Memasang paket global atau mengubah konfigurasi sistem (`my.ini`, `nginx.conf`, `php.ini`).
- Perintah yang menghapus atau menimpa berkas unggahan pengguna.

Perintah yang aman dijalankan tanpa bertanya: membaca berkas, `git status/diff/log`, `php artisan test`, `pint`, `npm run lint/test/build`, `php artisan route:list`, `php artisan migrate` dan `migrate:fresh --seed` **pada `_dev`/`_test`**.

## 11. Aturan khusus Fase 0 (kode PHP lama)

- Fase 0 memperbaiki kode lama di root repo dengan **perubahan minimal**. Tidak ada refactor besar atau perubahan arsitektur.
- Setelah task 1.1, folder `legacy/` hanya dibaca. Ia hanya diubah lagi oleh task 5.13 (dihapus).
- Kerjakan perbaikan sesuai ID `P-xx` di PRD bagian 2. Jangan memperbaiki hal lain "sekalian".

## 12. Pelaporan

Setiap task diakhiri dengan ringkasan berisi:

1. **Task**: ID dan judul.
2. **Perubahan**: daftar berkas dan satu kalimat per berkas.
3. **Verifikasi**: perintah yang dijalankan dan hasil ringkas (mis. `php artisan test`: 24 lulus).
4. **Keputusan dan asumsi**: nilai bawaan `K-x` yang dipakai, usulan yang belum ada di PRD.
5. **Perlu dicek pemilik**: risiko, hal yang tidak dapat diverifikasi, langkah berikutnya.

Lalu, **hanya pada baris task sendiri**, centang `todo.md` setelah verifikasi benar-benar lulus, dan tambahkan satu entri di Log jalur yang sesuai:

```
- 2026-11-20, 2.2: API daftar kos dengan filter dan pagination selesai. Verifikasi: php artisan test (24 lulus), pint bersih. K-2 bawaan (satu kota).
```

Jangan mengedit baris task atau Log milik jalur lain.

## 13. Definisi selesai

Task selesai bila **semua** terpenuhi:

- [ ] Sesuai PRD dan DESAIN yang dirujuk.
- [ ] Verifikasi pada kolom **Verifikasi** `todo.md` dijalankan dan lulus.
- [ ] Tes otomatis untuk perilaku baru ada dan hijau.
- [ ] Tidak ada aturan bisnis di Controller atau Vue.
- [ ] Tidak ada `dd()`, `console.log`, `alert()`, kode uji tertinggal, atau rahasia.
- [ ] Gaya kode lulus (`pint --test`, `npm run lint`).
- [ ] Contoh JSON endpoint baru dicatat di `docs/API.md`.
- [ ] `todo.md` dan Log diperbarui. Commit siap ditinjau pemilik.

## 14. Ringkasan larangan

Jangan: mengarang keputusan, melompati gerbang, mengerjakan task jalur lain, mengubah PRD/DESAIN diam-diam, menyambung SQL, menaruh logika bisnis di Controller atau Vue, menyimpan token di browser, memakai `v-html` untuk konten pengguna, mengomit rahasia atau dump, menjalankan perintah merusak di luar `_dev`/`_test`, melemahkan tes, `git push`, `git add .` tanpa memeriksa, mengklaim lulus tanpa menjalankan verifikasi.