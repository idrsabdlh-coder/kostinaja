<?php
session_start();
require_once 'config/koneksi.php';

// Jika sudah login, langsung alihkan ke beranda
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email dan kata sandi wajib diisi.';
    } else {
        // Prepared statement untuk mencegah SQL Injection (P-03 / Task 0.8)
        $stmt = mysqli_prepare($conn, "SELECT id, nama, email, password FROM users WHERE email = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt);

            if ($user && password_verify($password, $user['password'])) {
                // Sesi aman: regenerate ID sesi (Task 0.9 / P-04)
                session_regenerate_id(true);

                $_SESSION['login'] = true;
                // Simpan hanya data minimum, jangan simpan hash password (P-04)
                $_SESSION['user'] = [
                    'id'    => $user['id'],
                    'nama'  => $user['nama'],
                    'email' => $user['email']
                ];

                header("Location: index.php");
                exit;
            } else {
                $error = 'Email atau kata sandi tidak cocok.';
            }
        } else {
            error_log("Login prepare statement gagal: " . mysqli_error($conn));
            $error = 'Terjadi kesalahan sistem. Silakan coba beberapa saat lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk | Kostin Aja</title>
  <link rel="stylesheet" href="css/auth.css">
</head>
<body>

<div class="auth-card">
  <div class="auth-header">
    <div class="logo">🏠 Kostin<span>Aja</span></div>
    <h2>Masuk ke Akun Anda</h2>
    <p>Temukan dan simpan kos impian Anda</p>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-error">
      <?= htmlspecialchars($error); ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="login_form.php" class="auth-form">
    <div class="form-group">
      <label for="email">Alamat Email</label>
      <input 
        type="email" 
        id="email" 
        name="email" 
        placeholder="nama@email.com" 
        value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>" 
        required 
        autofocus
      >
    </div>

    <div class="form-group">
      <label for="password">Kata Sandi</label>
      <input 
        type="password" 
        id="password" 
        name="password" 
        placeholder="••••••••" 
        required
      >
    </div>

    <button type="submit" class="btn-submit">Masuk</button>
  </form>

  <div class="auth-footer">
    <p>Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
    <p><a href="index.php">← Kembali ke Beranda</a></p>
  </div>
</div>

</body>
</html>
