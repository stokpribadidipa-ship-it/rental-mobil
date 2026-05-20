<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/koneksi.php';
cek_admin();

$msg   = '';
$error = '';

// ── KEMBALIKAN MOBIL ──
if (isset($_GET['kembali'])) {
    $id_sewa = (int)$_GET['kembali'];

    $data = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT p.id_mobil, p.jumlah_sewa, p.tanggal_sewa, m.harga_sewa
         FROM penyewaan p
         JOIN mobil m ON p.id_mobil = m.id_mobil
         WHERE p.id_sewa = $id_sewa AND p.status = 'aktif'"
    ));

    if ($data) {
        $id_mobil = (int)$data['id_mobil'];
        $jumlah   = (int)$data['jumlah_sewa'];

        $hari = (int)mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT DATEDIFF(CURDATE(), '{$data['tanggal_sewa']}') as hari"
        ))['hari'];
        $denda = 0;
        if ($hari > 7) {
            $denda = ($hari - 7) * ($data['harga_sewa'] * 0.1);
        }

        mysqli_query($conn,
            "UPDATE penyewaan
             SET tanggal_kembali = CURDATE(), status = 'selesai', denda = $denda
             WHERE id_sewa = $id_sewa"
        );
        mysqli_query($conn,
            "UPDATE mobil SET jumlah = jumlah + $jumlah WHERE id_mobil = $id_mobil"
        );

        $msg = "Mobil berhasil dikembalikan ($hari hari). Stok bertambah $jumlah unit.";
        if ($denda > 0) {
            $msg .= " Denda keterlambatan: Rp " . number_format($denda, 0, ',', '.');
        }
    } else {
        $error = 'Data sewa tidak ditemukan atau sudah dikembalikan.';
    }
}

$filter_status = $_GET['status'] ?? 'semua';
$where = '';
if ($filter_status === 'aktif')   $where = "WHERE p.status = 'aktif'";
if ($filter_status === 'selesai') $where = "WHERE p.status = 'selesai'";

$q = mysqli_query($conn, "
    SELECT p.*, u.nama as nama_user, m.nama_mobil, m.harga_sewa,
           DATEDIFF(IFNULL(p.tanggal_kembali, CURDATE()), p.tanggal_sewa) as lama_hari
    FROM penyewaan p
    JOIN user u ON p.id_user = u.id_user
    JOIN mobil m ON p.id_mobil = m.id_mobil
    $where
    ORDER BY p.id_sewa DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Data Penyewaan — Admin</title>
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
      <li><a href="penyewaan.php" class="active"><span class="icon"><i class="bi bi-clipboard2-data-fill"></i></span> Penyewaan</a></li>
      <li><a href="laporan.php"><span class="icon"><i class="bi bi-bar-chart-fill"></i></span> Laporan</a></li>
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
    <div class="topbar"><h1>Data Penyewaan</h1></div>

    <?php if ($msg):  ?>
      <div class="alert alert-success">
        <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger">
        <i class="bi bi-x-circle-fill"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <div style="display:flex;gap:8px;margin-bottom:20px">
      <a href="?status=semua"   class="btn btn-sm <?= $filter_status==='semua'   ? 'btn-primary':'btn-ghost' ?>">Semua</a>
      <a href="?status=aktif"   class="btn btn-sm <?= $filter_status==='aktif'   ? 'btn-primary':'btn-ghost' ?>">
        <i class="bi bi-arrow-repeat"></i> Aktif
      </a>
      <a href="?status=selesai" class="btn btn-sm <?= $filter_status==='selesai' ? 'btn-primary':'btn-ghost' ?>">
        <i class="bi bi-check-circle-fill"></i> Selesai
      </a>
    </div>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th><th>Penyewa</th><th>Mobil</th><th>Jml</th>
              <th>Tgl Sewa</th><th>Tgl Kembali</th><th>Lama</th>
              <th>Total Sewa</th><th>Denda</th><th>Status</th><th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($q) === 0): ?>
              <tr><td colspan="11" style="text-align:center;color:var(--muted);padding:30px">Tidak ada data.</td></tr>
            <?php else: ?>
            <?php while ($s = mysqli_fetch_assoc($q)): ?>
            <?php
              $total     = $s['jumlah_sewa'] * $s['harga_sewa'];
              $denda     = $s['denda'] ?? 0;
              $lama      = $s['lama_hari'];
              $terlambat = $lama > 7;
            ?>
            <tr>
              <td><?= $s['id_sewa'] ?></td>
              <td><?= htmlspecialchars($s['nama_user']) ?></td>
              <td><?= htmlspecialchars($s['nama_mobil']) ?></td>
              <td><?= $s['jumlah_sewa'] ?></td>
              <td><?= date('d/m/Y', strtotime($s['tanggal_sewa'])) ?></td>
              <td>
                <?= $s['tanggal_kembali']
                    ? date('d/m/Y', strtotime($s['tanggal_kembali']))
                    : '<span style="color:var(--muted)"><i class="bi bi-hourglass-split"></i> Belum</span>' ?>
              </td>
              <td>
                <span class="tag <?= $terlambat ? 'tag-danger' : 'tag-info' ?>">
                  <i class="bi <?= $terlambat ? 'bi-exclamation-circle-fill' : 'bi-clock-fill' ?>"></i>
                  <?= $lama ?> hari
                </span>
              </td>
              <td style="color:var(--accent);font-weight:600">Rp <?= number_format($total,0,',','.') ?></td>
              <td>
                <?php if ($denda > 0): ?>
                  <span style="color:var(--danger);font-weight:600">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Rp <?= number_format($denda,0,',','.') ?>
                  </span>
                <?php else: ?>
                  <span style="color:var(--muted)">—</span>
                <?php endif; ?>
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
              <td>
                <?php if ($s['status'] === 'aktif'): ?>
                  <a href="?kembali=<?= $s['id_sewa'] ?>&status=<?= $filter_status ?>"
                     class="btn btn-success btn-sm"
                     onclick="return confirm('Konfirmasi pengembalian mobil ini?')">
                    <i class="bi bi-arrow-return-left"></i> Kembalikan
                  </a>
                <?php else: ?>
                  <span style="color:var(--muted);font-size:.8rem">
                    <i class="bi bi-check"></i> Selesai
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

    <div class="card" style="margin-top:16px;font-size:.83rem;color:var(--muted)">
      <i class="bi bi-lightbulb-fill" style="color:var(--accent)"></i>
      <strong style="color:var(--text)"> Aturan Denda:</strong>
      Denda dikenakan jika lama sewa &gt; 7 hari. Besaran: 10% dari harga sewa/hari × jumlah hari keterlambatan.
    </div>
  </main>
</div>
</body>
</html>