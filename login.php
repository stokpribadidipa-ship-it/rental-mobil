<?php
session_start();
if (isset($_SESSION['id_user'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: penyewa/dashboard.php');
    }
    exit;
}

require_once 'includes/koneksi.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM user WHERE username = ?");
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user   = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['role']    = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: penyewa/dashboard.php');
            }
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Rental Mobil</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
  .decor {
    position: fixed; top: -100px; right: -100px;
    width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(240,165,0,.12) 0%, transparent 70%);
    pointer-events: none;
  }
  .decor2 {
    position: fixed; bottom: -80px; left: -80px;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(224,92,26,.1) 0%, transparent 70%);
    pointer-events: none;
  }
</style>
</head>
<body>
<div class="decor"></div>
<div class="decor2"></div>

<div class="login-page">
  <div class="login-box">
    <div class="login-logo">
      <h1><i class="bi bi-car-front-fill"></i> RentalCar</h1>
      <p>Sistem Manajemen Rental Mobil</p>
    </div>

    <div class="card">
      <?php if ($error): ?>
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" class="form-control"
                 placeholder="Masukkan username"
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                 autofocus required>
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" class="form-control"
                 placeholder="Masukkan password" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:13px;">
          <i class="bi bi-box-arrow-in-right"></i> Masuk
        </button>
      </form>
    </div>

    <p style="text-align:center;margin-top:20px;font-size:.82rem;color:var(--muted);">
      Belum punya akun? Hubungi administrator.<br>
      <a href="setup.php" style="font-size:.78rem;">
        <i class="bi bi-gear-fill"></i> Setup Database (sekali pakai)
      </a>
    </p>
  </div>
</div>
</body>
</html>