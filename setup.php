<?php
// ========================================================
// SETUP.PHP — Jalankan SEKALI untuk inisialisasi database
// ========================================================
$host = 'localhost'; $user = 'root'; $password = ''; $database = 'rental_mobil';

$conn = mysqli_connect($host, $user, $password);
if (!$conn) die('Koneksi gagal: ' . mysqli_connect_error());

$log = [];
function run($conn, $sql, &$log, $label) {
    if (mysqli_multi_query($conn, $sql)) {
        do { $r = mysqli_store_result($conn); if ($r) mysqli_free_result($r); }
        while (mysqli_next_result($conn));
        $log[] = ['ok', $label];
    } else {
        $log[] = ['err', $label . ' — ' . mysqli_error($conn)];
    }
}

run($conn, "CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8 COLLATE utf8_general_ci", $log, 'Database rental_mobil');
run($conn, "USE `$database`", $log, 'USE database');

run($conn, "
CREATE TABLE IF NOT EXISTS user (
  id_user   INT AUTO_INCREMENT PRIMARY KEY,
  nama      VARCHAR(100),
  username  VARCHAR(50) UNIQUE,
  password  VARCHAR(255),
  role      ENUM('admin','penyewa') DEFAULT 'penyewa'
)", $log, 'Tabel user');

run($conn, "
CREATE TABLE IF NOT EXISTS mobil (
  id_mobil    INT AUTO_INCREMENT PRIMARY KEY,
  nama_mobil  VARCHAR(100),
  jumlah      INT DEFAULT 0,
  kondisi     VARCHAR(50),
  harga_sewa  INT DEFAULT 0
)", $log, 'Tabel mobil');

run($conn, "
CREATE TABLE IF NOT EXISTS penyewaan (
  id_sewa      INT AUTO_INCREMENT PRIMARY KEY,
  id_user      INT,
  id_mobil     INT,
  jumlah_sewa  INT,
  tanggal_sewa DATE,
  tanggal_kembali DATE NULL,
  status       ENUM('aktif','selesai') DEFAULT 'aktif',
  FOREIGN KEY (id_user)  REFERENCES user(id_user),
  FOREIGN KEY (id_mobil) REFERENCES mobil(id_mobil)
)", $log, 'Tabel penyewaan');

run($conn, "DROP PROCEDURE IF EXISTS sewa_mobil", $log, 'Drop procedure sewa_mobil');
run($conn, "
CREATE PROCEDURE sewa_mobil(
  IN p_id_user  INT,
  IN p_id_mobil INT,
  IN p_jumlah   INT
)
BEGIN
  INSERT INTO penyewaan(id_user, id_mobil, jumlah_sewa, tanggal_sewa, status)
  VALUES(p_id_user, p_id_mobil, p_jumlah, CURDATE(), 'aktif');
  UPDATE mobil SET jumlah = jumlah - p_jumlah WHERE id_mobil = p_id_mobil;
END", $log, 'Procedure sewa_mobil');

run($conn, "DROP PROCEDURE IF EXISTS kembalikan_mobil", $log, 'Drop procedure kembalikan_mobil');
run($conn, "
CREATE PROCEDURE kembalikan_mobil(IN p_id_sewa INT)
BEGIN
  DECLARE v_id_mobil INT;
  DECLARE v_jumlah   INT;
  SELECT id_mobil, jumlah_sewa INTO v_id_mobil, v_jumlah
  FROM penyewaan WHERE id_sewa = p_id_sewa;
  UPDATE penyewaan SET tanggal_kembali = CURDATE(), status = 'selesai' WHERE id_sewa = p_id_sewa;
  UPDATE mobil SET jumlah = jumlah + v_jumlah WHERE id_mobil = v_id_mobil;
END", $log, 'Procedure kembalikan_mobil');

run($conn, "DROP FUNCTION IF EXISTS status_mobil", $log, 'Drop function status_mobil');
run($conn, "
CREATE FUNCTION status_mobil(jumlah INT)
RETURNS VARCHAR(20) DETERMINISTIC
BEGIN
  DECLARE hasil VARCHAR(20);
  IF jumlah <= 0 THEN SET hasil = 'Tidak Tersedia';
  ELSE SET hasil = 'Tersedia';
  END IF;
  RETURN hasil;
END", $log, 'Function status_mobil');

run($conn, "DROP FUNCTION IF EXISTS hitung_denda", $log, 'Drop function hitung_denda');
run($conn, "
CREATE FUNCTION hitung_denda(p_tanggal_kembali DATE, p_tanggal_sewa DATE, p_harga_sewa INT)
RETURNS INT DETERMINISTIC
BEGIN
  DECLARE hari INT;
  DECLARE denda INT;
  SET hari = DATEDIFF(p_tanggal_kembali, p_tanggal_sewa);
  IF hari > 7 THEN SET denda = (hari - 7) * (p_harga_sewa * 0.1);
  ELSE SET denda = 0;
  END IF;
  RETURN denda;
END", $log, 'Function hitung_denda');

$pw_admin  = password_hash('admin123', PASSWORD_DEFAULT);
$pw_user   = password_hash('user123',  PASSWORD_DEFAULT);

$check = mysqli_query($conn, "SELECT COUNT(*) as c FROM user");
$row   = mysqli_fetch_assoc($check);
if ((int)$row['c'] === 0) {
    $ins = "INSERT INTO user (nama, username, password, role) VALUES
        ('Administrator', 'admin', '$pw_admin', 'admin'),
        ('Budi Santoso',  'budi',  '$pw_user',  'penyewa'),
        ('Siti Rahayu',   'siti',  '$pw_user',  'penyewa')";
    run($conn, $ins, $log, 'Seed data user');

    $ins2 = "INSERT INTO mobil (nama_mobil, jumlah, kondisi, harga_sewa) VALUES
        ('Toyota Avanza', 5, 'Baik', 250000),
        ('Honda Brio', 3, 'Baik', 200000),
        ('Mitsubishi Xpander', 2, 'Baik', 350000),
        ('Daihatsu Xenia', 4, 'Baik', 230000),
        ('Toyota Innova', 2, 'Sangat Baik', 400000),
        ('Suzuki Ertiga', 3, 'Baik', 280000)";
    run($conn, $ins2, $log, 'Seed data mobil');
} else {
    $log[] = ['ok', 'Data user sudah ada, skip seed'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Setup — Rental Mobil</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px;">
<div style="max-width:560px;width:100%">
  <div class="card">
    <h2 style="margin-bottom:20px;color:var(--accent)">
      <i class="bi bi-gear-fill"></i> Setup Database
    </h2>
    <?php foreach ($log as [$status, $msg]): ?>
      <div class="alert <?= $status === 'ok' ? 'alert-success' : 'alert-danger' ?>" style="margin-bottom:8px;">
        <i class="bi <?= $status === 'ok' ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?>"></i>
        <?= htmlspecialchars($msg) ?>
      </div>
    <?php endforeach; ?>
    <hr style="border-color:var(--border);margin:20px 0;">
    <p style="font-size:.85rem;color:var(--muted);margin-bottom:16px;">
      <strong style="color:var(--text)">Akun default:</strong><br>
      Admin → username: <code>admin</code> / password: <code>admin123</code><br>
      Penyewa → username: <code>budi</code> atau <code>siti</code> / password: <code>user123</code>
    </p>
    <a href="login.php" class="btn btn-primary">
      <i class="bi bi-car-front-fill"></i> Ke Halaman Login
    </a>
  </div>
</div>
</body>
</html>