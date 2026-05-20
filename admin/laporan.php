<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/koneksi.php';
cek_admin();

$dari   = $_GET['dari']   ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

$dari_esc   = mysqli_real_escape_string($conn, $dari);
$sampai_esc = mysqli_real_escape_string($conn, $sampai);

$q = mysqli_query($conn, "
    SELECT p.*, u.nama as nama_user, m.nama_mobil, m.harga_sewa,
           (p.jumlah_sewa * m.harga_sewa) as total_sewa,
           IFNULL(p.denda, 0) as denda,
           DATEDIFF(IFNULL(p.tanggal_kembali, CURDATE()), p.tanggal_sewa) as lama_hari
    FROM penyewaan p
    JOIN user u ON p.id_user = u.id_user
    JOIN mobil m ON p.id_mobil = m.id_mobil
    WHERE p.tanggal_sewa BETWEEN '$dari_esc' AND '$sampai_esc'
    ORDER BY p.tanggal_sewa DESC
");

$rows = [];
$total_pendapatan = 0;
$total_denda      = 0;
$total_transaksi  = 0;

while ($r = mysqli_fetch_assoc($q)) {
    $rows[] = $r;
    $total_pendapatan += $r['total_sewa'];
    $total_denda      += $r['denda'];
    $total_transaksi++;
}
$grand_total = $total_pendapatan + $total_denda;

$q_per_mobil = mysqli_query($conn, "
    SELECT m.nama_mobil,
           COUNT(p.id_sewa) as jumlah_transaksi,
           SUM(p.jumlah_sewa) as total_unit,
           SUM(p.jumlah_sewa * m.harga_sewa) as pendapatan
    FROM penyewaan p
    JOIN mobil m ON p.id_mobil = m.id_mobil
    WHERE p.tanggal_sewa BETWEEN '$dari_esc' AND '$sampai_esc'
    GROUP BY m.id_mobil
    ORDER BY pendapatan DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Laporan Penyewaan — Admin</title>
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
      <li><a href="dashboard.php"><span class="icon"><i class="bi bi-speedometer2"></i></span> Dashboard</a></li>
      <li><a href="mobil.php"><span class="icon"><i class="bi bi-car-front"></i></span> Kelola Mobil</a></li>
      <li><a href="penyewaan.php"><span class="icon"><i class="bi bi-clipboard2-data-fill"></i></span> Penyewaan</a></li>
      <li><a href="laporan.php" class="active"><span class="icon"><i class="bi bi-bar-chart-fill"></i></span> Laporan</a></li>
      <li><a href="user.php"><span class="icon"><i class="bi bi-people-fill"></i></span> Data User</a></li>
    </ul>
    <div class="sidebar-footer">
      <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong> Administrator
      <br><a href="../includes/logout.php" style="color:var(--danger);font-size:.78rem;">
        <i class="bi bi-box-arrow-left"></i> Logout
      </a>
    </div>
  </aside>

  <main class="main">
    <div class="topbar"><h1>Laporan Penyewaan</h1></div>

    <!-- Filter Tanggal -->
    <div class="card" style="margin-bottom:20px">
      <form method="GET" style="display:flex;gap:16px;align-items:flex-end;flex-wrap:wrap;">
        <div class="form-group" style="margin:0;flex:1;min-width:150px">
          <label>Dari Tanggal</label>
          <input type="date" name="dari" class="form-control" value="<?= htmlspecialchars($dari) ?>">
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:150px">
          <label>Sampai Tanggal</label>
          <input type="date" name="sampai" class="form-control" value="<?= htmlspecialchars($sampai) ?>">
        </div>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-search"></i> Tampilkan
        </button>
        <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
          <a href="?dari=<?= date('Y-m-01') ?>&sampai=<?= date('Y-m-d') ?>" class="btn btn-ghost btn-sm">Bulan Ini</a>
          <a href="?dari=<?= date('Y-m-01', strtotime('last month')) ?>&sampai=<?= date('Y-m-t', strtotime('last month')) ?>" class="btn btn-ghost btn-sm">Bulan Lalu</a>
          <a href="?dari=<?= date('Y-01-01') ?>&sampai=<?= date('Y-12-31') ?>" class="btn btn-ghost btn-sm">Tahun Ini</a>
        </div>
      </form>
    </div>

    <!-- Stat Cards -->
    <div class="stat-grid" style="margin-bottom:20px">
      <div class="stat-card green">
        <div class="label">Total Transaksi</div>
        <div class="value"><?= $total_transaksi ?></div>
      </div>
      <div class="stat-card">
        <div class="label">Pendapatan Sewa</div>
        <div class="value" style="font-size:1.3rem">Rp <?= number_format($total_pendapatan,0,',','.') ?></div>
      </div>
      <div class="stat-card red">
        <div class="label">Total Denda</div>
        <div class="value" style="font-size:1.3rem">Rp <?= number_format($total_denda,0,',','.') ?></div>
      </div>
      <div class="stat-card blue">
        <div class="label">Grand Total</div>
        <div class="value" style="font-size:1.3rem">Rp <?= number_format($grand_total,0,',','.') ?></div>
      </div>
    </div>

    <!-- Ringkasan Per Mobil -->
    <?php if (mysqli_num_rows($q_per_mobil) > 0): ?>
    <div class="card" style="margin-bottom:20px">
      <h3 style="margin-bottom:16px">
        <i class="bi bi-pie-chart-fill"></i> Ringkasan Per Mobil
      </h3>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Nama Mobil</th><th>Transaksi</th><th>Unit Disewa</th><th>Pendapatan</th></tr>
          </thead>
          <tbody>
            <?php while ($pm = mysqli_fetch_assoc($q_per_mobil)): ?>
            <tr>
              <td><strong><?= htmlspecialchars($pm['nama_mobil']) ?></strong></td>
              <td><?= $pm['jumlah_transaksi'] ?> kali</td>
              <td><?= $pm['total_unit'] ?> unit</td>
              <td style="color:var(--accent);font-weight:600">Rp <?= number_format($pm['pendapatan'],0,',','.') ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- Detail Transaksi -->
    <div class="card">
      <h3 style="margin-bottom:4px">Detail Transaksi</h3>
      <p style="color:var(--muted);font-size:.83rem;margin-bottom:16px">
        Periode: <?= date('d M Y', strtotime($dari)) ?> — <?= date('d M Y', strtotime($sampai)) ?>
      </p>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th><th>Tgl Sewa</th><th>Penyewa</th><th>Mobil</th>
              <th>Jml</th><th>Lama</th><th>Total Sewa</th><th>Denda</th><th>Grand Total</th><th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($rows) === 0): ?>
              <tr><td colspan="10" style="text-align:center;color:var(--muted);padding:36px">
                Tidak ada transaksi pada periode ini.
              </td></tr>
            <?php else: ?>
            <?php foreach ($rows as $s):
              $gt = $s['total_sewa'] + $s['denda'];
            ?>
            <tr>
              <td><?= $s['id_sewa'] ?></td>
              <td><?= date('d/m/Y', strtotime($s['tanggal_sewa'])) ?></td>
              <td><?= htmlspecialchars($s['nama_user']) ?></td>
              <td><?= htmlspecialchars($s['nama_mobil']) ?></td>
              <td><?= $s['jumlah_sewa'] ?></td>
              <td>
                <span class="tag <?= $s['lama_hari'] > 7 ? 'tag-danger' : 'tag-info' ?>">
                  <i class="bi <?= $s['lama_hari'] > 7 ? 'bi-exclamation-circle-fill' : 'bi-clock-fill' ?>"></i>
                  <?= $s['lama_hari'] ?> hari
                </span>
              </td>
              <td style="color:var(--accent)">Rp <?= number_format($s['total_sewa'],0,',','.') ?></td>
              <td>
                <?php if ($s['denda'] > 0): ?>
                  <span style="color:var(--danger)">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Rp <?= number_format($s['denda'],0,',','.') ?>
                  </span>
                <?php else: ?>
                  <span style="color:var(--muted)">—</span>
                <?php endif; ?>
              </td>
              <td style="font-weight:700">Rp <?= number_format($gt,0,',','.') ?></td>
              <td>
                <span class="tag <?= $s['status']==='aktif' ? 'tag-warn':'tag-success' ?>">
                  <i class="bi <?= $s['status']==='aktif' ? 'bi-arrow-repeat' : 'bi-check-circle-fill' ?>"></i>
                  <?= ucfirst($s['status']) ?>
                </span>
              </td>
            </tr>
            <?php endforeach; ?>
            <tr style="background:rgba(240,165,0,.06);font-family:'Syne',sans-serif">
              <td colspan="6" style="text-align:right;font-weight:700;padding:14px 16px">TOTAL</td>
              <td style="color:var(--accent);font-weight:700">Rp <?= number_format($total_pendapatan,0,',','.') ?></td>
              <td style="color:var(--danger);font-weight:700">Rp <?= number_format($total_denda,0,',','.') ?></td>
              <td style="color:var(--text);font-weight:700;font-size:1rem">Rp <?= number_format($grand_total,0,',','.') ?></td>
              <td></td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
</body>
</html>