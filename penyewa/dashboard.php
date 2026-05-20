<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/koneksi.php';
cek_penyewa();

$msg   = '';
$error = '';

// ── PROSES SEWA ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'sewa') {
    $id_mobil = (int)$_POST['id_mobil'];
    $jumlah   = (int)$_POST['jumlah'];

    $stok = mysqli_fetch_assoc(mysqli_query($conn, "SELECT jumlah FROM mobil WHERE id_mobil=$id_mobil"));
    if (!$stok) {
        $error = 'Mobil tidak ditemukan.';
    } elseif ($jumlah < 1) {
        $error = 'Jumlah sewa minimal 1.';
    } elseif ($jumlah > $stok['jumlah']) {
        $error = 'Stok tidak mencukupi. Tersedia: ' . $stok['jumlah'] . ' unit.';
    } else {
        $id_user = $_SESSION['id_user'];
        $stmt = mysqli_prepare($conn, "CALL sewa_mobil(?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iii', $id_user, $id_mobil, $jumlah);
        if (mysqli_stmt_execute($stmt)) {
            $msg = "Berhasil menyewa $jumlah unit mobil. Selamat menikmati perjalanan!";
        } else {
            $error = 'Gagal melakukan penyewaan: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

$q_mobil = mysqli_query($conn, "SELECT *, status_mobil(jumlah) as status FROM mobil ORDER BY nama_mobil");

$id_user = $_SESSION['id_user'];
$aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM penyewaan WHERE id_user=$id_user AND status='aktif'"))['c'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Dashboard Penyewa — Rental Mobil</title>
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
      <li><a href="dashboard.php" class="active"><span class="icon"><i class="bi bi-house-door-fill"></i></span> Daftar Mobil</a></li>
      <li><a href="riwayat.php"><span class="icon"><i class="bi bi-clipboard2-data-fill"></i></span> Riwayat Sewa</a></li>
    </ul>
    <div class="sidebar-footer">
      <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong>
      Penyewa
      <br><a href="../includes/logout.php" style="color:var(--danger);font-size:.78rem;">
        <i class="bi bi-box-arrow-left"></i> Logout
      </a>
    </div>
  </aside>

  <main class="main">
    <div class="topbar">
      <div>
        <h1>Halo, <?= htmlspecialchars($_SESSION['nama']) ?>!</h1>
        <p style="color:var(--muted);font-size:.88rem;margin-top:4px">Pilih mobil yang ingin Anda sewa</p>
      </div>
      <?php if ($aktif > 0): ?>
        <span class="tag tag-warn" style="font-size:.85rem;padding:8px 16px">
          <i class="bi bi-car-front-fill"></i> <?= $aktif ?> Sewa Aktif
        </span>
      <?php endif; ?>
    </div>

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

    <!-- Grid Mobil -->
    <div class="mobil-grid">
      <?php
      $i = 0;
      while ($m = mysqli_fetch_assoc($q_mobil)):
        $available = $m['status'] === 'Tersedia';
        $i++;
      ?>
      <div class="mobil-card">
        <div class="mobil-thumb" style="<?= !$available ? 'opacity:.5;filter:grayscale(1)' : '' ?>">
          <i class="bi bi-car-front-fill" style="font-size:3rem;color:var(--accent)"></i>
        </div>
        <div class="mobil-body">
          <h3><?= htmlspecialchars($m['nama_mobil']) ?></h3>
          <div class="mobil-meta">
            <i class="bi bi-wrench"></i> <?= htmlspecialchars($m['kondisi']) ?> &nbsp;·&nbsp;
            <i class="bi bi-box-seam"></i> Stok: <?= $m['jumlah'] ?> unit
          </div>
          <div class="mobil-price">
            Rp <?= number_format($m['harga_sewa'],0,',','.') ?>
            <small>/hari</small>
          </div>
          <div style="display:flex;align-items:center;justify-content:space-between;gap:10px">
            <span class="tag <?= $available ? 'tag-success' : 'tag-danger' ?>">
              <i class="bi <?= $available ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?>"></i>
              <?= $m['status'] ?>
            </span>
            <?php if ($available): ?>
              <button class="btn btn-primary btn-sm"
                onclick="bukaSewa(<?= $m['id_mobil'] ?>, '<?= htmlspecialchars($m['nama_mobil']) ?>', <?= $m['jumlah'] ?>, <?= $m['harga_sewa'] ?>)">
                <i class="bi bi-key-fill"></i> Sewa
              </button>
            <?php else: ?>
              <button class="btn btn-ghost btn-sm" disabled style="opacity:.5;cursor:not-allowed">
                <i class="bi bi-slash-circle"></i> Habis
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>

    <?php if ($i === 0): ?>
      <div class="card" style="text-align:center;padding:48px">
        <p style="font-size:3rem;margin-bottom:12px">
          <i class="bi bi-car-front" style="color:var(--muted)"></i>
        </p>
        <p style="color:var(--muted)">Belum ada mobil tersedia saat ini.</p>
      </div>
    <?php endif; ?>
  </main>
</div>

<!-- Modal Sewa -->
<div class="modal-overlay" id="modalSewa">
  <div class="modal-box">
    <div class="modal-header">
      <h3><i class="bi bi-key-fill"></i> Sewa Mobil</h3>
      <button class="modal-close" onclick="document.getElementById('modalSewa').classList.remove('open')">×</button>
    </div>
    <div style="margin-bottom:16px">
      <p id="sewa_info" style="color:var(--muted);font-size:.88rem"></p>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="sewa">
      <input type="hidden" name="id_mobil" id="sewa_id_mobil">
      <div class="form-group">
        <label>Nama Mobil</label>
        <input type="text" id="sewa_nama" class="form-control" disabled>
      </div>
      <div class="form-group">
        <label>Jumlah Unit yang Disewa</label>
        <input type="number" name="jumlah" id="sewa_jumlah" class="form-control" min="1" value="1"
               oninput="hitungTotal()">
      </div>
      <div class="form-group">
        <label>Estimasi Total (1 hari)</label>
        <input type="text" id="sewa_total" class="form-control" disabled
               style="color:var(--accent);font-weight:600">
      </div>
      <p style="font-size:.78rem;color:var(--muted);margin-bottom:16px">
        <i class="bi bi-exclamation-triangle-fill"></i>
        Tanggal sewa: hari ini. Pengembalian dikonfirmasi oleh admin.
      </p>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn-ghost" onclick="document.getElementById('modalSewa').classList.remove('open')">Batal</button>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-circle-fill"></i> Konfirmasi Sewa
        </button>
      </div>
    </form>
  </div>
</div>

<script>
let hargaPerUnit = 0;
function bukaSewa(id, nama, stok, harga) {
  hargaPerUnit = harga;
  document.getElementById('sewa_id_mobil').value = id;
  document.getElementById('sewa_nama').value = nama;
  document.getElementById('sewa_jumlah').max = stok;
  document.getElementById('sewa_jumlah').value = 1;
  document.getElementById('sewa_info').textContent = `Stok tersedia: ${stok} unit`;
  hitungTotal();
  document.getElementById('modalSewa').classList.add('open');
}
function hitungTotal() {
  const jml = parseInt(document.getElementById('sewa_jumlah').value) || 0;
  const total = jml * hargaPerUnit;
  document.getElementById('sewa_total').value = 'Rp ' + total.toLocaleString('id-ID');
}
</script>
</body>
</html>