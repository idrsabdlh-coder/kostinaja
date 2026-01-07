<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar | Kostin Aja</title>
  <link rel="stylesheet" href="css/register.css">
</head>
<body>

<div class="register-box">
  <h2>Daftar Akun Kostin Aja</h2>

  <form action="proses_register.php" method="POST">
    <input type="text" name="nama" placeholder="Nama Lengkap" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Daftar</button>
  </form>

  <p>Sudah punya akun? <a href="login.php">Login</a></p>
</div>

</body>
</html>
