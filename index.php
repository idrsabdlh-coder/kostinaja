<?php
session_start();
require_once 'config/koneksi.php';

/* ==========================================================
   PENCARIAN DENGAN PREPARED STATEMENT (Task 0.8 / P-03)
========================================================== */
$lokasi = isset($_GET['lokasi']) ? trim($_GET['lokasi']) : '';
$tipe   = isset($_GET['tipe']) ? trim($_GET['tipe']) : '';

$where = "WHERE 1 = 1";
$params = [];
$types  = "";

if ($lokasi !== '') {
    $where .= " AND lokasi LIKE ?";
    $params[] = '%' . $lokasi . '%';
    $types  .= "s";
}

if ($tipe !== '') {
    $where .= " AND tipe = ?";
    $params[] = $tipe;
    $types  .= "s";
}

$sql = "SELECT * FROM kos $where ORDER BY rating DESC";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $query = mysqli_stmt_get_result($stmt);
} else {
    error_log("Gagal menyiapkan query kos: " . mysqli_error($conn));
    $query = false;
}

$isLoggedIn = isset($_SESSION['login']) && $_SESSION['login'] === true;
$userName   = $isLoggedIn ? ($_SESSION['user']['nama'] ?? 'Pengguna') : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kostin Aja | Cari Kos Mudah & Cepat</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
  <div class="logo">🏠 Kostin<span>Aja</span></div>

  <ul class="menu">
    <li><a href="index.php">Beranda</a></li>

    <?php if ($isLoggedIn): ?>
      <li><a href="#">Favorit</a></li>
      <li><a href="#">Halo, <?= htmlspecialchars($userName); ?></a></li>
      <li>
        <form action="logout.php" method="POST" style="display:inline; margin:0; padding:0;">
          <button type="submit" class="btn" style="cursor:pointer; background:none; border:none; color:inherit; font:inherit;">Logout</button>
        </form>
      </li>
    <?php else: ?>
      <li><a href="login_form.php" title="Login untuk melihat favorit">Favorit</a></li>
      <li><a href="login_form.php" class="btn">Masuk</a></li>
      <li><a href="register.php" class="btn" style="background-color:#f5c400; color:#1f3d2b;">Daftar</a></li>
    <?php endif; ?>
  </ul>
</nav>

<section class="hero">
  <h1>Cari Kos Mudah & Cepat</h1>
  <p>Temukan kos terbaik sesuai kebutuhanmu</p>

  <form method="GET" action="index.php" class="search-box">
    <input 
      type="text" 
      name="lokasi" 
      placeholder="Cari lokasi kos..."
      value="<?= htmlspecialchars($lokasi); ?>"
    >

    <select name="tipe">
      <option value="">Semua</option>
      <option value="Putra"  <?= $tipe === 'Putra' ? 'selected' : ''; ?>>Putra</option>
      <option value="Putri"  <?= $tipe === 'Putri' ? 'selected' : ''; ?>>Putri</option>
      <option value="Campur" <?= $tipe === 'Campur' ? 'selected' : ''; ?>>Campur</option>
    </select>

    <button type="submit">Cari Kos</button>
  </form>
</section>

<section class="kos-list">
  <h2>Rekomendasi Kos</h2>

  <div class="grid">
    <?php if ($query && mysqli_num_rows($query) > 0): ?>
      <?php while ($k = mysqli_fetch_assoc($query)): ?>
        <div class="card">
          <img 
            src="<?= htmlspecialchars($k['gambar'] ?: 'assets/placeholder-kos.svg'); ?>" 
            alt="<?= htmlspecialchars($k['nama_kos']); ?>"
          >

          <div class="card-body">
            <h3><?= htmlspecialchars($k['nama_kos']); ?></h3>

            <p>
              ⭐ <?= htmlspecialchars(number_format($k['rating'], 1)); ?> • 
              <?= htmlspecialchars($k['fasilitas']); ?><br>
              📍 <?= htmlspecialchars($k['lokasi']); ?> • 
              <?= htmlspecialchars($k['tipe']); ?>
            </p>

            <span class="price">
              Rp <?= number_format($k['harga'], 0, ',', '.'); ?> / bulan
            </span>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p style="text-align:center; grid-column: 1 / -1;">Kos tidak ditemukan</p>
    <?php endif; ?>
  </div>
</section>

<footer>
  <p>© 2026 Kostin Aja — Platform Pencarian Kos</p>
</footer>

</body>
</html>
<?php
if ($stmt) {
    mysqli_stmt_close($stmt);
}
?>
