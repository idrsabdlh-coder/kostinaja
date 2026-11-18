<?php
session_start();
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header("Location: index.php");
    exit;
}

$error   = $_SESSION['reg_error'] ?? '';
$oldNama = $_SESSION['reg_nama'] ?? '';
$oldMail = $_SESSION['reg_email'] ?? '';

unset($_SESSION['reg_error'], $_SESSION['reg_nama'], $_SESSION['reg_email']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar | Kostin Aja</title>
  <link rel="stylesheet" href="css/auth.css">
</head>
<body>

<div class="auth-card">
  <div class="auth-header">
    <div class="logo">🏠 Kostin<span>Aja</span></div>
    <h2>Buat Akun Baru</h2>
    <p>Bergabunglah untuk mencari dan menyimpan kos terbaik</p>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-error">
      <?= htmlspecialchars($error); ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="proses_register.php" class="auth-form">
    <div class="form-group">
      <label for="nama">Nama Lengkap</label>
      <input 
        type="text" 
        id="nama" 
        name="nama" 
        placeholder="Nama Lengkap Anda" 
        value="<?= htmlspecialchars($oldNama); ?>" 
        required 
        autofocus
      >
    </div>

    <div class="form-group">
      <label for="email">Alamat Email</label>
      <input 
        type="email" 
        id="email" 
        name="email" 
        placeholder="nama@email.com" 
        value="<?= htmlspecialchars($oldMail); ?>" 
        required
      >
    </div>

    <div class="form-group">
      <label for="password">Kata Sandi (Minimal 8 karakter)</label>
      <input 
        type="password" 
        id="password" 
        name="password" 
        placeholder="••••••••" 
        minlength="8" 
        required
      >
    </div>

    <button type="submit" class="btn-submit">Daftar Sekarang</button>
  </form>

  <div class="auth-footer">
    <p>Sudah punya akun? <a href="login_form.php">Masuk di sini</a></p>
    <p><a href="index.php">← Kembali ke Beranda</a></p>
  </div>
</div>

</body>
</html>
