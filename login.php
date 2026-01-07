<?php
session_start();

// Jika user sudah login, langsung lempar ke index
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Selamat Datang | Kostin Aja</title>
  <link rel="stylesheet" href="css/login.css">
</head>
<body>

<div class="login-container">
  <div class="logo-box">
    <img src="assets/logo.png" alt="Logo Kostin Aja">
  </div>

  <div class="login-content animate">
    <h1 class="title">KOST<span>IN</span>AJA</h1>
    <p class="subtitle">Kos Nyaman, Hidup Aman</p>

    <div class="action-buttons">
      <a href="login_form.php" class="btn btn-primary">Masuk</a>

      <a href="register.php" class="btn btn-secondary">Daftar</a>
    </div>
  </div>
</div>

</body>
</html>