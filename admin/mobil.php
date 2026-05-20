<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/koneksi.php';
cek_admin();

$msg   = '';
$error = '';

// ── TAMBAH MOBIL ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'tambah') {
    $nama_mobil = trim($_POST['nama_mobil'] ?? '');
    $jumlah     = (int)($_POST['jumlah'] ?? 0);
    $kondisi    = trim($_POST['kondisi'] ?? '');
    $harga      = (int)($_POST['harga_sewa'] ?? 0);

    if ($nama_mobil && $kondisi && $harga > 0) {
        $stmt = mysqli_prepare($conn, "INSERT INTO mobil (nama_mobil, jumlah, kondisi, harga_sewa) VALUES (?,?,?,?)");
        mysqli_stmt_bind_param($stmt, 'sisi', $nama_mobil, $jumlah, $kondisi, $harga);
        if (mysqli_stmt_execute($stmt)) {
            $msg = 'Mobil berhasil ditambahkan.';
        } else {
            $error = 'Gagal menambah mobil.';
        }
    } else {
        $error = 'Semua field wajib diisi dengan benar.';
    }
}

// ── EDIT MOBIL ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    $id         = (int)$_POST['id_mobil'];
    $nama_mobil = trim($_POST['nama_mobil'] ?? '');
    $jumlah     = (int)($_POST['jumlah'] ?? 0);
    $kondisi    = trim($_POST['kondisi'] ?? '');
    $harga      = (int)($_POST['harga_sewa'] ?? 0);

    if ($id && $nama_mobil && $kondisi && $harga > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE mobil SET nama_mobil=?, jumlah=?, kondisi=?, harga_sewa=? WHERE id_mobil=?");
        mysqli_stmt_bind_param($stmt, 'sisii', $nama_mobil, $jumlah, $kondisi, $harga, $id);
        if (mysqli_stmt_execute($stmt)) {
            $msg = 'Data mobil berhasil diperbarui.';
        } else {
            $error = 'Gagal memperbarui data.';
        }
    }
}

// ── HAPUS MOBIL ──
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = mysqli_prepare($conn, "DELETE FROM mobil WHERE id_mobil=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $msg = 'Mobil berhasil dihapus.';
}

$q_mobil = mysqli_query($conn, "SELECT *, status_mobil(jumlah) as status FROM mobil ORDER BY id_mobil DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Kelola Mobil — Admin</title>
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
      <li><a href="mobil.php" class="active"><span class="icon"><i class="bi bi-car-front"></i></span> Kelola Mobil</a></li>
      <li><a href="penyewaan.php"><span class="icon"><i class="bi bi-clipboard2-data-fill"></i></span> Penyewaan</a></li>
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
    <div class="topbar">
      <h1>Kelola Mobil</h1>
      <button class="btn btn-primary" onclick="document.getElementById('modalTambah').classList.add('open')">
        <i class="bi bi-plus-circle-fill"></i> Tambah Mobil
      </button>
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

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Nama Mobil</th>
              <th>Stok</th>
              <th>Kondisi</th>
              <th>Harga/Hari</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($q_mobil) === 0): ?>
              <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:30px">Belum ada data mobil.</td></tr>
            <?php else: ?>
            <?php while ($m = mysqli_fetch_assoc($q_mobil)): ?>
            <tr>
              <td><?= $m['id_mobil'] ?></td>
              <td><strong><?= htmlspecialchars($m['nama_mobil']) ?></strong></td>
              <td><?= $m['jumlah'] ?> unit</td>
              <td><?= htmlspecialchars($m['kondisi']) ?></td>
              <td style="color:var(--accent);font-weight:600">Rp <?= number_format($m['harga_sewa'],0,',','.') ?>/hari</td>
              <td>
                <span class="tag <?= $m['status'] === 'Tersedia' ? 'tag-success' : 'tag-danger' ?>">
                  <i class="bi <?= $m['status'] === 'Tersedia' ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?>"></i>
                  <?= $m['status'] ?>
                </span>
              </td>
              <td style="display:flex;gap:6px;flex-wrap:wrap;">
                <button class="btn btn-info btn-sm"
                  onclick="editMobil(<?= htmlspecialchars(json_encode($m)) ?>)">
                  <i class="bi bi-pencil-fill"></i> Edit
                </button>
                <a href="?hapus=<?= $m['id_mobil'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Hapus mobil <?= htmlspecialchars($m['nama_mobil']) ?>?')">
                  <i class="bi bi-trash-fill"></i> Hapus
                </a>
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

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalTambah">
  <div class="modal-box">
    <div class="modal-header">
      <h3><i class="bi bi-plus-circle-fill"></i> Tambah Mobil Baru</h3>
      <button class="modal-close" onclick="document.getElementById('modalTambah').classList.remove('open')">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="tambah">
      <div class="form-group">
        <label>Nama Mobil</label>
        <input type="text" name="nama_mobil" class="form-control" placeholder="Toyota Avanza" required>
      </div>
      <div class="form-group">
        <label>Jumlah Unit</label>
        <input type="number" name="jumlah" class="form-control" min="0" value="1" required>
      </div>
      <div class="form-group">
        <label>Kondisi</label>
        <select name="kondisi" class="form-control" required>
          <option value="">-- Pilih Kondisi --</option>
          <option>Sangat Baik</option>
          <option>Baik</option>
          <option>Cukup</option>
        </select>
      </div>
      <div class="form-group">
        <label>Harga Sewa / Hari (Rp)</label>
        <input type="number" name="harga_sewa" class="form-control" min="0" placeholder="250000" required>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn-ghost" onclick="document.getElementById('modalTambah').classList.remove('open')">Batal</button>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-floppy-fill"></i> Simpan
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal-overlay" id="modalEdit">
  <div class="modal-box">
    <div class="modal-header">
      <h3><i class="bi bi-pencil-fill"></i> Edit Mobil</h3>
      <button class="modal-close" onclick="document.getElementById('modalEdit').classList.remove('open')">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id_mobil" id="edit_id">
      <div class="form-group">
        <label>Nama Mobil</label>
        <input type="text" name="nama_mobil" id="edit_nama" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Jumlah Unit</label>
        <input type="number" name="jumlah" id="edit_jumlah" class="form-control" min="0" required>
      </div>
      <div class="form-group">
        <label>Kondisi</label>
        <select name="kondisi" id="edit_kondisi" class="form-control" required>
          <option>Sangat Baik</option>
          <option>Baik</option>
          <option>Cukup</option>
        </select>
      </div>
      <div class="form-group">
        <label>Harga Sewa / Hari (Rp)</label>
        <input type="number" name="harga_sewa" id="edit_harga" class="form-control" min="0" required>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn-ghost" onclick="document.getElementById('modalEdit').classList.remove('open')">Batal</button>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-arrow-clockwise"></i> Update
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function editMobil(data) {
  document.getElementById('edit_id').value     = data.id_mobil;
  document.getElementById('edit_nama').value   = data.nama_mobil;
  document.getElementById('edit_jumlah').value = data.jumlah;
  document.getElementById('edit_harga').value  = data.harga_sewa;
  const sel = document.getElementById('edit_kondisi');
  for (let i = 0; i < sel.options.length; i++) {
    if (sel.options[i].value === data.kondisi) sel.selectedIndex = i;
  }
  document.getElementById('modalEdit').classList.add('open');
}
</script>
</body>
</html>