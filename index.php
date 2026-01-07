<?php
session_start();
include 'config/koneksi.php';

/* =======================
   PROTEKSI LOGIN
======================= */
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

/* =======================
   PENCARIAN
======================= */
$lokasi = isset($_GET['lokasi']) ? trim($_GET['lokasi']) : '';
$tipe   = isset($_GET['tipe']) ? trim($_GET['tipe']) : '';

$where = "WHERE 1 = 1";

if ($lokasi !== '') {
    $lokasi_safe = mysqli_real_escape_string($conn, $lokasi);
    $where .= " AND lokasi LIKE '%$lokasi_safe%'";
}

if ($tipe !== '') {
    $tipe_safe = mysqli_real_escape_string($conn, $tipe);
    $where .= " AND tipe = '$tipe_safe'";
}

$sql = "SELECT * FROM kos $where ORDER BY rating DESC";
$query = mysqli_query($conn, $sql);

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kostin Aja | Cari Kos Mudah</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
  <div class="logo">🏠 Kostin<span>Aja</span></div>

  <ul class="menu">
    <li><a href="index.php">Beranda</a></li>
    <li><a href="#">Favorit</a></li>

    <li>
      <a href="#">
        Halo, <?= htmlspecialchars($_SESSION['user']['nama'] ?? 'User'); ?>
      </a>
    </li>

    <li>
      <a href="logout.php" class="btn">Logout</a>
    </li>
  </ul>
</nav>

<section class="hero">
  <h1>Cari Kos Mudah & Cepat</h1>
  <p>Temukan kos terbaik sesuai kebutuhanmu</p>

  <form method="GET" class="search-box">

    <input 
      type="text" 
      name="lokasi" 
      placeholder="Cari lokasi kos..."
      value="<?= htmlspecialchars($lokasi); ?>"
    >

    <select name="tipe">
      <option value="">Semua</option>
      <option value="Putra"  <?= $tipe=='Putra'?'selected':''; ?>>Putra</option>
      <option value="Putri"  <?= $tipe=='Putri'?'selected':''; ?>>Putri</option>
      <option value="Campur" <?= $tipe=='Campur'?'selected':''; ?>>Campur</option>
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
            src="<?= htmlspecialchars($k['gambar']); ?>" 
            alt="<?= htmlspecialchars($k['nama_kos']); ?>"
          >

          <div class="card-body">

            <h3><?= htmlspecialchars($k['nama_kos']); ?></h3>

            <p>
              ⭐ <?= htmlspecialchars($k['rating']); ?> • 
              <?= htmlspecialchars($k['fasilitas']); ?><br>
              📍 <?= htmlspecialchars($k['lokasi']); ?> • 
              <?= htmlspecialchars($k['tipe']); ?>
            </p>

            <span class="price">
              Rp <?= number_format($k['harga'],0,',','.'); ?> / bulan
            </span>

          </div>

        </div>

      <?php endwhile; ?>

    <?php else: ?>

      <p style="text-align:center;">Kos tidak ditemukan</p>

    <?php endif; ?>

  </div>
</section>

<footer>
  © 2026 Kostin Aja
</footer>

</body>
</html>
