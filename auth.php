<?php
include 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $cek = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
    
    if (mysqli_num_rows($cek) > 0) {
        // GANTI 'auth.php' jadi 'login_form.php' (atau login.php)
        echo "<script>alert('Email sudah terdaftar'); window.location='login_form.php';</script>";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO users (nama, email, password) VALUES ('$nama', '$email', '$password')");
        if ($insert) {
            // GANTI 'auth.php' jadi 'login_form.php' (atau login.php)
            echo "<script>alert('Pendaftaran berhasil! Silakan login'); window.location='login_form.php';</script>";
        } else {
            // Tambahan jika query gagal (misal tabel belum ada)
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>