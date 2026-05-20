<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/koneksi.php';
cek_admin();

$total_mobil   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM mobil"))['c'];
$total_unit    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) as c FROM mobil"))['c'] ?? 0;
$total_penyewa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM user WHERE role='penyewa'"))['c'];
$total_aktif   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM penyewaan WHERE status='aktif'"))['c'];

$q_sewa = mysqli_query($conn, "
    SELECT p.*, u.nama as nama_user, m.nama_mobil, m.harga_sewa
    FROM penyewaan p
    JOIN user u ON p.id_user = u.id_user
    JOIN mobil m ON p.id_mobil = m.id_mobil
    ORDER BY p.id_sewa DESC
    LIMIT 8
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Dashboard Admin — Rental Mobil</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<div class="wrapper">
  <aside class="sidebar">
    <div class="sidebar-logo">
      <h2><i class="bi bi-car-front-fill"></i> RentalCar</h2>
      <span>Admin Panel</span>
    </div>
    <ul class="sidebar-nav">
      <li><a href="dashboard.php" class="active"><span class="icon"><i class="bi bi-speedometer2"></i></span> Dashboard</a></li>
      <li><a href="mobil.php"><span class="icon"><i class="bi bi-car-front"></i></span> Kelola Mobil</a></li>
      <li><a href="penyewaan.php"><span class="icon"><i class="bi bi-clipboard2-data-fill"></i></span> Penyewaan</a></li>
      <li><a href="laporan.php"><span class="icon"><i class="bi bi-bar-chart-fill"></i></span> Laporan</a></li>
      <li><a href="user.php"><span class="icon"><i class="bi bi-people-fill"></i></span> Data User</a></li>
    </ul>
    <div class="sidebar-footer">
      <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong>
      Administrator
      <br><a href="../includes/logout.php" style="color:var(--danger);font-size:.78rem;">
        <i class="bi bi-box-arrow-left"></i> Logout
      </a>
    </div>
  </aside>

  <main class="main">
    <div class="topbar">
      <h1>Dashboard</h1>
      <span class="badge">ADMIN</span>
    </div>

    <div class="stat-grid">
      <div class="stat-card">
        <div class="label">Jenis Mobil</div>
        <div class="value"><?= $total_mobil ?></div>
      </div>
      <div class="stat-card blue">
        <div class="label">Total Unit</div>
        <div class="value"><?= $total_unit ?></div>
      </div>
      <div class="stat-card green">
        <div class="label">Data Penyewa</div>
        <div class="value"><?= $total_penyewa ?></div>
      </div>
      <div class="stat-card red">
        <div class="label">Sewa Aktif</div>
        <div class="value"><?= $total_aktif ?></div>
      </div>
    </div>

    <div class="card">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
        <h3>Penyewaan Terbaru</h3>
        <a href="penyewaan.php" class="btn btn-ghost btn-sm">Lihat Semua →</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Penyewa</th>
              <th>Mobil</th>
              <th>Jumlah</th>
              <th>Tanggal Sewa</th>
              <th>Total</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($q_sewa) === 0): ?>
              <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:30px">Belum ada data penyewaan.</td></tr>
            <?php else: ?>
              <?php while ($s = mysqli_fetch_assoc($q_sewa)): ?>
              <tr>
                <td><?= $s['id_sewa'] ?></td>
                <td><?= htmlspecialchars($s['nama_user']) ?></td>
                <td><?= htmlspecialchars($s['nama_mobil']) ?></td>
                <td><?= $s['jumlah_sewa'] ?> unit</td>
                <td><?= date('d M Y', strtotime($s['tanggal_sewa'])) ?></td>
                <td style="color:var(--accent);font-weight:600">
                  Rp <?= number_format($s['jumlah_sewa'] * $s['harga_sewa'], 0, ',', '.') ?>
                </td>
                <td>
                  <?php if ($s['status'] === 'aktif'): ?>
                    <span class="tag tag-warn">
                      <i class="bi bi-arrow-repeat"></i> Aktif
                    </span>
                  <?php else: ?>
                    <span class="tag tag-success">
                      <i class="bi bi-check-circle-fill"></i> Selesai
                    </span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endwhile; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
</body>
</html>