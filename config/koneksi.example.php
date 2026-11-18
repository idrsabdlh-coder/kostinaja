<?php
// Konfigurasi koneksi database KostinAja
// Salin berkas ini ke config/koneksi.php dan sesuaikan kredensial Anda.
//
// Untuk phpBro:
//   Host: 127.0.0.1
//   Port: 3306
//   User: root
//   Pass: root (bawaan phpBro)
//   DB  : kostinaja (import skema dari database/kostinaja.sql via Adminer di http://localhost:8080/adminer/)

$host = "127.0.0.1";
$user = "root";
$pass = "root";
$db   = "kostinaja";
$port = 3306;

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    error_log("Koneksi database gagal: " . mysqli_connect_error());
    die("Koneksi database gagal. Silakan periksa konfigurasi pada config/koneksi.php.");
}

mysqli_set_charset($conn, "utf8mb4");
?>