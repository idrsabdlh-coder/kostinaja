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

    $nama     = $_POST['nama'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 3. Cek apakah email sudah terdaftar (prepared statement)
    $cek = mysqli_prepare($conn, "SELECT 1 FROM users WHERE email = ? LIMIT 1");
    if (!$cek) {
        error_log('Register: cek email gagal disiapkan: ' . mysqli_error($conn));
        echo "<script>alert('Terjadi kesalahan. Coba lagi nanti.'); window.history.back();</script>";
        exit;
    }
    mysqli_stmt_bind_param($cek, 's', $email);
    mysqli_stmt_execute($cek);
    mysqli_stmt_store_result($cek);
    $sudahAda = mysqli_stmt_num_rows($cek) > 0;
    mysqli_stmt_close($cek);

    if ($sudahAda) {
        echo "<script>alert('Email sudah terdaftar! Gunakan email lain.'); window.location='register.php';</script>";
        exit;
    } else {
        // 4. Proses Insert (prepared statement)
        $stmt   = mysqli_prepare($conn, "INSERT INTO users (nama, email, password) VALUES (?, ?, ?)");
        $insert = false;
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'sss', $nama, $email, $password);
            $insert = mysqli_stmt_execute($stmt);
        }

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