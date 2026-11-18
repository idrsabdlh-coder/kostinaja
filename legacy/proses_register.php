<?php
session_start();
require_once 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit;
}

$nama     = trim($_POST['nama'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Simpan data lama untuk form jika gagal
$_SESSION['reg_nama']  = $nama;
$_SESSION['reg_email'] = $email;

// 1. Validasi input
if ($nama === '' || $email === '' || $password === '') {
    $_SESSION['reg_error'] = 'Semua kolom wajib diisi.';
    header("Location: register.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['reg_error'] = 'Format email tidak valid.';
    header("Location: register.php");
    exit;
}

if (strlen($password) < 8) {
    $_SESSION['reg_error'] = 'Kata sandi minimal 8 karakter.';
    header("Location: register.php");
    exit;
}

// 2. Cek apakah email sudah terdaftar (prepared statement - Task 0.8 / P-03)
$cekStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1");
if (!$cekStmt) {
    error_log("Register cek email statement error: " . mysqli_error($conn));
    $_SESSION['reg_error'] = 'Terjadi kesalahan pada sistem. Silakan coba lagi.';
    header("Location: register.php");
    exit;
}

mysqli_stmt_bind_param($cekStmt, 's', $email);
mysqli_stmt_execute($cekStmt);
mysqli_stmt_store_result($cekStmt);
$sudahAda = mysqli_stmt_num_rows($cekStmt) > 0;
mysqli_stmt_close($cekStmt);

if ($sudahAda) {
    $_SESSION['reg_error'] = 'Email sudah terdaftar. Silakan gunakan email lain atau masuk.';
    header("Location: register.php");
    exit;
}

// 3. Simpan data user dengan password hash (prepared statement - Task 0.8 / BR-01)
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$insertStmt = mysqli_prepare($conn, "INSERT INTO users (nama, email, password) VALUES (?, ?, ?)");

if (!$insertStmt) {
    error_log("Register insert statement error: " . mysqli_error($conn));
    $_SESSION['reg_error'] = 'Pendaftaran gagal karena kendala sistem. Silakan coba lagi.';
    header("Location: register.php");
    exit;
}

mysqli_stmt_bind_param($insertStmt, 'sss', $nama, $email, $hashedPassword);
$sukses = mysqli_stmt_execute($insertStmt);
mysqli_stmt_close($insertStmt);

if ($sukses) {
    unset($_SESSION['reg_nama'], $_SESSION['reg_email'], $_SESSION['reg_error']);
    echo "<script>alert('Pendaftaran berhasil! Silakan masuk.'); window.location='login_form.php';</script>";
    exit;
} else {
    error_log("Gagal mengeksekusi registrasi: " . mysqli_error($conn));
    $_SESSION['reg_error'] = 'Gagal menyimpan pendaftaran. Silakan coba lagi.';
    header("Location: register.php");
    exit;
}
?>
