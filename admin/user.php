<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/koneksi.php';
cek_admin();

$msg   = '';
$error = '';

// Tambah user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'tambah') {
    $nama     = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = $_POST['role'] ?? 'penyewa';

    if ($nama && $username && $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO user (nama, username, password, role) VALUES (?,?,?,?)");
        mysqli_stmt_bind_param($stmt, 'ssss', $nama, $username, $hash, $role);
        if (mysqli_stmt_execute($stmt)) {
            $msg = "User '$username' berhasil ditambahkan.";
        } else {
            $error = 'Username sudah digunakan atau terjadi kesalahan.';
        }
    } else {
        $error = 'Semua field wajib diisi.';
    }
}

// Hapus user
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id !== $_SESSION['id_user']) {
        $stmt = mysqli_prepare($conn, "DELETE FROM user WHERE id_user=?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $msg = 'User berhasil dihapus.';
    } else {
        $error = 'Tidak bisa menghapus akun sendiri.';
    }
}

$q_user = mysqli_query($conn, "SELECT * FROM user ORDER BY role, nama");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Data User — Admin</title>
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
      <li><a href="laporan.php"><span class="icon"><i class="bi bi-bar-chart-fill"></i></span> Laporan</a></li>
      <li><a href="user.php" class="active"><span class="icon"><i class="bi bi-people-fill"></i></span> Data User</a></li>
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
      <h1>Data User</h1>
      <button class="btn btn-primary" onclick="document.getElementById('modalTambah').classList.add('open')">
        <i class="bi bi-person-plus-fill"></i> Tambah User
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
            <tr><th>#</th><th>Nama</th><th>Username</th><th>Role</th><th>Aksi</th></tr>
          </thead>
          <tbody>
            <?php while ($u = mysqli_fetch_assoc($q_user)): ?>
            <tr>
              <td><?= $u['id_user'] ?></td>
              <td><?= htmlspecialchars($u['nama']) ?></td>
              <td><?= htmlspecialchars($u['username']) ?></td>
              <td>
                <span class="tag <?= $u['role']==='admin' ? 'tag-info' : 'tag-success' ?>">
                  <i class="bi <?= $u['role']==='admin' ? 'bi-shield-fill' : 'bi-person-fill' ?>"></i>
                  <?= ucfirst($u['role']) ?>
                </span>
              </td>
              <td>
                <?php if ($u['id_user'] !== $_SESSION['id_user']): ?>
                  <a href="?hapus=<?= $u['id_user'] ?>" class="btn btn-danger btn-sm"
                     onclick="return confirm('Hapus user <?= htmlspecialchars($u['username']) ?>?')">
                    <i class="bi bi-trash-fill"></i> Hapus
                  </a>
                <?php else: ?>
                  <span style="color:var(--muted);font-size:.8rem">
                    <i class="bi bi-person-check-fill"></i> (Anda)
                  </span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- Modal Tambah User -->
<div class="modal-overlay" id="modalTambah">
  <div class="modal-box">
    <div class="modal-header">
      <h3><i class="bi bi-person-plus-fill"></i> Tambah User Baru</h3>
      <button class="modal-close" onclick="document.getElementById('modalTambah').classList.remove('open')">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="tambah">
      <div class="form-group">
        <label>Nama Lengkap</label>
        <input type="text" name="nama" class="form-control" placeholder="Budi Santoso" required>
      </div>
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" class="form-control" placeholder="budi" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control" placeholder="Min. 6 karakter" required>
      </div>
      <div class="form-group">
        <label>Role</label>
        <select name="role" class="form-control">
          <option value="penyewa">Penyewa</option>
          <option value="admin">Admin</option>
        </select>
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
</body>
</html>