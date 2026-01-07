<?php
// proses_login.php
session_start();
include 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    $user = mysqli_fetch_assoc($query);

    if ($user && password_verify($password, $user['password'])) {
        // INI KUNCINYA
        $_SESSION['login'] = true; 
        $_SESSION['user'] = $user; 
        
        header("Location: index.php");
        exit;
    } else {
        echo "<script>alert('Gagal Login'); window.location='login.php';</script>";
    }
}