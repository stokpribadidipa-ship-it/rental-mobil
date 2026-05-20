# 🚗 Rental Mobil — Aplikasi PHP MySQL

## Struktur Folder
```
rental_mobil/
├── index.php               ← Redirect otomatis
├── login.php               ← Halaman login
├── setup.php               ← Inisialisasi database (jalankan sekali)
├── includes/
│   ├── koneksi.php         ← Koneksi database
│   ├── auth.php            ← Cek session & role
│   └── logout.php          ← Proses logout
├── assets/
│   └── css/style.css       ← Stylesheet global
├── admin/
│   ├── dashboard.php       ← Dashboard admin + statistik
│   ├── mobil.php           ← CRUD mobil
│   ├── penyewaan.php       ← Kelola penyewaan + kembalikan
│   ├── laporan.php         ← Laporan per tanggal
│   └── user.php            ← Kelola data user
└── penyewa/
    ├── dashboard.php       ← Lihat mobil + sewa
    └── riwayat.php         ← Riwayat sewa + denda
```

## Cara Instalasi

1. **Copy folder** `rental_mobil` ke:
   - XAMPP → `C:/xampp/htdocs/`
   - Laragon → `C:/laragon/www/`

2. **Jalankan XAMPP/Laragon**, aktifkan Apache dan MySQL.

3. **Buka browser**, akses:
   ```
   http://localhost/rental_mobil/setup.php
   ```
   Ini akan membuat database, tabel, procedure, function, dan data contoh secara otomatis.

4. **Login** di:
   ```
   http://localhost/rental_mobil/login.php
   ```

## Akun Default

| Role    | Username | Password  |
|---------|----------|-----------|
| Admin   | admin    | admin123  |
| Penyewa | budi     | user123   |
| Penyewa | siti     | user123   |

## Fitur Lengkap

### Admin
- ✅ Dashboard dengan statistik (mobil, unit, penyewa, sewa aktif)
- ✅ CRUD Mobil (tambah, edit, hapus)
- ✅ Lihat semua penyewaan + filter status
- ✅ Proses pengembalian mobil (stok otomatis bertambah)
- ✅ Laporan penyewaan per periode tanggal
- ✅ Kelola data user

### Penyewa
- ✅ Lihat semua mobil dengan status ketersediaan
- ✅ Sewa mobil (stok otomatis berkurang via `CALL sewa_mobil`)
- ✅ Riwayat penyewaan milik sendiri
- ✅ Informasi denda keterlambatan

## Database Objects

| Type      | Nama                | Fungsi                                      |
|-----------|---------------------|---------------------------------------------|
| Procedure | `sewa_mobil`        | Insert penyewaan + kurangi stok mobil       |
| Procedure | `kembalikan_mobil`  | Update status + tambah stok kembali         |
| Function  | `status_mobil`      | Return 'Tersedia' / 'Tidak Tersedia'        |
| Function  | `hitung_denda`      | Hitung denda jika lebih dari 7 hari         |

## Tugas Praktik (Sudah Terimplementasi)
1. ✅ Fitur pengembalian mobil → `admin/penyewaan.php`
2. ✅ Denda keterlambatan → function `hitung_denda`, tampil di `penyewa/riwayat.php`
3. ✅ Laporan per tanggal → `admin/laporan.php`
