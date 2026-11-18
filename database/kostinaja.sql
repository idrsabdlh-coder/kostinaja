-- ============================================================
-- KostinAja (versi legacy) - skema dan data contoh
-- Task 0.7 | Acuan: PRD 2 (kolom terdeduksi dari kode lama)
--
-- Cara pakai (database kostinaja boleh belum ada, tabelnya harus belum ada):
--   mysql -u kost_legacy -p < database/kostinaja.sql
-- Atau lewat Adminer: menu Import, tanpa perlu memilih database dulu.
--
-- CATATAN:
-- * Hanya data CONTOH. Tidak ada data nyata.
-- * Akun demo: demo@kostinaja.test / demo12345 (HANYA untuk lokal).
-- * Berkas ini untuk versi PHP lama. Versi Laravel memakai migration.
-- ============================================================

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS kostinaja
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kostinaja;

CREATE TABLE users (
  id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama      VARCHAR(100) NOT NULL,
  email     VARCHAR(150) NOT NULL,
  password  VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kos (
  id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama_kos  VARCHAR(150) NOT NULL,
  lokasi    VARCHAR(150) NOT NULL,
  tipe      ENUM('Putra','Putri','Campur') NOT NULL,
  harga     INT UNSIGNED NOT NULL,
  rating    DECIMAL(2,1) NOT NULL DEFAULT 0.0,
  fasilitas VARCHAR(255) NOT NULL DEFAULT '',
  gambar    VARCHAR(255) NOT NULL DEFAULT 'assets/placeholder-kos.svg',
  PRIMARY KEY (id),
  KEY idx_kos_tipe_harga (tipe, harga),
  KEY idx_kos_lokasi (lokasi),
  CONSTRAINT chk_kos_harga  CHECK (harga > 0),
  CONSTRAINT chk_kos_rating CHECK (rating BETWEEN 0 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (nama, email, password) VALUES
('Pengguna Demo', 'demo@kostinaja.test', '$2y$10$FXxXfuLhq8ZuB.o1IONTTeZ1pMqmoCULCMJiQQSt1rsgyO17TeSh2');

INSERT INTO kos (nama_kos, lokasi, tipe, harga, rating, fasilitas) VALUES
('Kos Melati Asri',     'Dekat Kampus, Bandung',      'Putri',  1200000, 4.8, 'WiFi, AC, Kamar mandi dalam, Parkir motor'),
('Kos Mawar Residence', 'Dekat Kampus, Bandung',      'Putri',  1500000, 4.7, 'WiFi, AC, Kasur, Lemari, Dapur bersama'),
('Kos Garuda Putra',    'Pusat Kota, Bandung',        'Putra',   950000, 4.5, 'WiFi, Kipas angin, Parkir motor'),
('Kos Elang Nyaman',    'Pusat Kota, Bandung',        'Putra',  1100000, 4.3, 'WiFi, AC, Kamar mandi dalam'),
('Kos Harmoni',         'Kawasan Industri, Bekasi',   'Campur', 1300000, 4.4, 'WiFi, AC, Parkir mobil, CCTV'),
('Kos Cempaka Indah',   'Kawasan Industri, Bekasi',   'Putri',  1000000, 4.2, 'WiFi, Kipas angin, Dapur bersama'),
('Kos Pelangi',         'Dekat Stasiun, Depok',       'Campur', 1400000, 4.6, 'WiFi, AC, Kamar mandi dalam, Laundry'),
('Kos Bintang Timur',   'Dekat Stasiun, Depok',       'Putra',   850000, 4.0, 'Kipas angin, Parkir motor'),
('Kos Dahlia Premium',  'Perumahan Asri, Yogyakarta', 'Putri',  1800000, 4.9, 'WiFi, AC, Water heater, Kamar mandi dalam, CCTV'),
('Kos Sakura Mandiri',  'Perumahan Asri, Yogyakarta', 'Campur',  900000, 3.9, 'WiFi, Kipas angin, Dapur bersama');
