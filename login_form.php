<?php
session_start();
include 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    $user = mysqli_fetch_assoc($query);

    // Pastikan password di DB sudah di-hash saat register
    if ($user && password_verify($password, $user['password'])) {
        
        // SINKRONKAN DENGAN index.php
        $_SESSION['login'] = true;
        $_SESSION['user'] = $user; // Menyimpan seluruh data user (id, nama, email)

        header("Location: index.php");
        exit;
    } else {
        echo "<script>alert('Email atau password salah'); window.location='login.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Masuk | Kostin Aja</title>
  <link rel="stylesheet" href="css/auth.css">
</head>
<body>

<div class="login-container">

  <div class="login-content">

    <h2>Masuk Akun</h2>

    <form method="POST">

      <input type="email" name="email" placeholder="Email" required>

      <input type="password" name="password" placeholder="Password" required>

      <button type="submit" class="btn btn-primary">
        Login
      </button>

    </form>
  </div>

</div>

</body>
</html>
