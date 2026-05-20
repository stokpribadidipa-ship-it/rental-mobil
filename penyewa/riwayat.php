<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/koneksi.php';
cek_penyewa();

$id_user = (int)$_SESSION['id_user'];

$q = mysqli_query($conn, "
    SELECT p.*, m.nama_mobil, m.harga_sewa,
           (p.jumlah_sewa * m.harga_sewa) as total_sewa,
           IFNULL(p.denda, 0) as denda,
           DATEDIFF(IFNULL(p.tanggal_kembali, CURDATE()), p.tanggal_sewa) as lama_hari
    FROM penyewaan p
    JOIN mobil m ON p.id_mobil = m.id_mobil
    WHERE p.id_user = $id_user
    ORDER BY p.id_sewa DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Riwayat Sewa — Penyewa</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<div class="wrapper">
  <aside class="sidebar">
    <div class="sidebar-logo">
      <h2><i class="bi bi-car-front-fill"></i> RentalCar</h2>
      <span>Portal Penyewa</span>
    </div>
    <ul class="sidebar-nav">
      <li><a href="dashboard.php"><span class="icon"><i class="bi bi-house-door-fill"></i></span> Daftar Mobil</a></li>
      <li><a href="riwayat.php" class="active"><span class="icon"><i class="bi bi-clipboard2-data-fill"></i></span> Riwayat Sewa</a></li>
    </ul>
    <div class="sidebar-footer">
      <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong> Penyewa
      <br><a href="../includes/logout.php" style="color:var(--danger);font-size:.78rem;">
        <i class="bi bi-box-arrow-left"></i> Logout
      </a>
    </div>
  </aside>

  <main class="main">
    <div class="topbar"><h1>Riwayat Penyewaan</h1></div>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th><th>Mobil</th><th>Jml</th>
              <th>Tgl Sewa</th><th>Tgl Kembali</th><th>Lama</th>
              <th>Total Sewa</th><th>Denda</th><th>Grand Total</th><th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($q) === 0): ?>
              <tr><td colspan="10" style="text-align:center;color:var(--muted);padding:40px">
                Belum ada riwayat penyewaan.
                <br><a href="dashboard.php" class="btn btn-primary btn-sm" style="margin-top:12px">
                  <i class="bi bi-plus-circle-fill"></i> Sewa Sekarang
                </a>
              </td></tr>
            <?php else: ?>
            <?php while ($s = mysqli_fetch_assoc($q)):
              $grand = $s['total_sewa'] + $s['denda'];
              $terlambat = $s['lama_hari'] > 7;
            ?>
            <tr>
              <td><?= $s['id_sewa'] ?></td>
              <td><strong><?= htmlspecialchars($s['nama_mobil']) ?></strong></td>
              <td><?= $s['jumlah_sewa'] ?> unit</td>
              <td><?= date('d/m/Y', strtotime($s['tanggal_sewa'])) ?></td>
              <td>
                <?= $s['tanggal_kembali']
                    ? date('d/m/Y', strtotime($s['tanggal_kembali']))
                    : '<span style="color:var(--muted)"><i class="bi bi-hourglass-split"></i> Belum kembali</span>' ?>
              </td>
              <td>
                <span class="tag <?= $terlambat ? 'tag-danger' : 'tag-info' ?>">
                  <i class="bi <?= $terlambat ? 'bi-exclamation-circle-fill' : 'bi-clock-fill' ?>"></i>
                  <?= $s['lama_hari'] ?> hari
                </span>
              </td>
              <td style="color:var(--accent)">Rp <?= number_format($s['total_sewa'],0,',','.') ?></td>
              <td>
                <?php if ($s['denda'] > 0): ?>
                  <span style="color:var(--danger);font-weight:600">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Rp <?= number_format($s['denda'],0,',','.') ?>
                  </span>
                <?php else: ?>
                  <span style="color:var(--muted)">—</span>
                <?php endif; ?>
              </td>
              <td style="font-weight:700">Rp <?= number_format($grand,0,',','.') ?></td>
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

    <!-- Info Denda -->
    <div class="card" style="margin-top:16px">
      <h4 style="margin-bottom:10px">
        <i class="bi bi-pin-angle-fill"></i> Ketentuan Denda Keterlambatan
      </h4>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px">
        <div style="background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.2);border-radius:10px;padding:16px">
          <div style="font-size:.78rem;color:var(--muted);margin-bottom:4px">Batas Normal</div>
          <div style="font-size:1.2rem;font-weight:700;color:var(--info)">
            <i class="bi bi-calendar2-check-fill"></i> ≤ 7 Hari
          </div>
          <div style="font-size:.82rem;color:var(--muted)">Tidak ada denda</div>
        </div>
        <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:10px;padding:16px">
          <div style="font-size:.78rem;color:var(--muted);margin-bottom:4px">Denda Per Hari</div>
          <div style="font-size:1.2rem;font-weight:700;color:var(--danger)">
            <i class="bi bi-percent"></i> 10%
          </div>
          <div style="font-size:.82rem;color:var(--muted)">dari harga sewa/hari</div>
        </div>
        <div style="background:rgba(240,165,0,.08);border:1px solid rgba(240,165,0,.2);border-radius:10px;padding:16px">
          <div style="font-size:.78rem;color:var(--muted);margin-bottom:4px">Contoh</div>
          <div style="font-size:1rem;font-weight:600;color:var(--accent)">
            <i class="bi bi-calculator-fill"></i> Rp 250rb × 10% × 3 hari
          </div>
          <div style="font-size:.82rem;color:var(--muted)">= Rp 75.000 denda</div>
        </div>
      </div>
    </div>
  </main>
</div>
</body>
</html>