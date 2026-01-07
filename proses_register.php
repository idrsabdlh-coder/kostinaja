<?php
// 1. Pastikan session dimulai jika diperlukan nanti
session_start();
include 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. Validasi input agar tidak kosong
    if (empty($_POST['nama']) || empty($_POST['email']) || empty($_POST['password'])) {
        echo "<script>alert('Semua data wajib diisi!'); window.history.back();</script>";
        exit;
    }

    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 3. Cek apakah email sudah terdaftar
    $cek = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
    
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Email sudah terdaftar! Gunakan email lain.'); window.location='register.php';</script>";
        exit;
    } else {
        // 4. Proses Insert
        $sql = "INSERT INTO users (nama, email, password) VALUES ('$nama', '$email', '$password')";
        $insert = mysqli_query($conn, $sql);

        if ($insert) {
            // PASTIKAN 'login_form.php' adalah nama file form login Anda
            echo "<script>alert('Pendaftaran berhasil! Silakan login'); window.location='login_form.php';</script>";
            exit;
        } else {
            // Jika gagal insert (misal: kolom database tidak sesuai)
            echo "<script>alert('Gagal mendaftar: " . mysqli_error($conn) . "'); window.history.back();</script>";
            exit;
        }
    }
} else {
    // Jika mencoba akses file ini secara langsung tanpa POST, lempar ke register
    header("Location: register.php");
    exit;
}
?>