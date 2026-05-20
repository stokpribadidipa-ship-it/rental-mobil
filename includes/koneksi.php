<?php
$host     = 'localhost';
$user     = 'root';
$password = '';
$database = 'rental_mobil';

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("<div style='font-family:sans-serif;padding:20px;color:red;'>
        ❌ Koneksi database gagal: " . mysqli_connect_error() . "
        <br><small>Pastikan XAMPP/Laragon berjalan dan database <b>rental_mobil</b> sudah dibuat.</small>
    </div>");
}

mysqli_set_charset($conn, 'utf8');
