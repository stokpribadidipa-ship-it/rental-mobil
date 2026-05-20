<?php
function cek_login() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['id_user'])) {
        header('Location: ../login.php');
        exit;
    }
}

function cek_admin() {
    cek_login();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: ../penyewa/dashboard.php');
        exit;
    }
}

function cek_penyewa() {
    cek_login();
    if ($_SESSION['role'] !== 'penyewa') {
        header('Location: ../admin/dashboard.php');
        exit;
    }
}
