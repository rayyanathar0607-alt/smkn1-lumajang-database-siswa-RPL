<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$pesan = '';
if (isset($_POST['nisn_scan'])) {$nisn = trim($_POST['nisn_scan']);$tgl  = date('Y-m-d');

    $tabel_siswa = 'siswa';
    $c_d = mysqli_query($koneksi, "SELECT COUNT(*) FROM data_siswa");
    if ($c_d && mysqli_fetch_row($c_d)[0] > 0) {$tabel_siswa = 'data_siswa'; }

    $q = mysqli_query($koneksi, "SELECT * FROM `$tabel_siswa` WHERE NISN='$nisn' OR nis='$nisn' OR nisn='$nisn'");
    if ($q && mysqli_num_rows($q) > 0) {$s = mysqli_fetch_array($q, MYSQLI_BOTH);$s_id   = $s['ID'] ?? $s['id'] ?? $s[0];$s_nama = $s['Nama_Lengkap'] ?? $s['nama'] ?? $s[2];$s_k    = $s['Kelas'] ?? $s['kelas'] ?? 'X';

        $q_c = mysqli_query($koneksi, "SELECT id FROM presensi_siswa WHERE tanggal='$tgl' AND id_siswa=$s_id");
        if (mysqli_num_rows($q_c) > 0) {
            mysqli_query($koneksi, "UPDATE presensi_siswa SET status='Hadir', keterangan='Scan QR Auto' WHERE tanggal='$tgl' AND id_siswa=$s_id");
        } else {
            mysqli_query($koneksi, "INSERT INTO presensi_siswa (tanggal, id_siswa, nama_siswa, kelas, status, keterangan) VALUES ('$tgl',$s_id, '$s_nama', '$s_k', 'Hadir', 'Scan QR Auto')");
        }
        $pesan = "✅ PRESENSI BERHASIL: $s_nama ($nisn) TERCATAT HADIR!";
    } else {
        $pesan = "⚠️ NISN Kartu Tidak Ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Scan Presensi Otomatis - Black & Gold Pro</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #0d0d0d; color: #e0e0e0; display: flex; min-height: 100vh; }
        .sidebar { width: 270px; background-color: #141414; border-right: 1px solid rgba(212, 175, 55, 0.3); padding: 20px 15px; display: flex; flex-direction: column; gap: 12px; flex-shrink: 0; }
        .sidebar-brand h2 { color: #d4af37; font-size: 18px; font-weight: 800; }
        .sidebar-brand .brand-sub { font-size: 10px; color: #888; text-transform: uppercase; }
        .sidebar-menu { list-style: none; display: flex; flex-direction: column; gap: 4px; }
        .sidebar-menu a { color: #ccc; text-decoration: none; padding: 9px 12px; border-radius: 6px; font-size: 13px; display: flex; align-items: center; gap: 10px; transition: 0.3s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: #d4af37; color: #121212; font-weight: 700; }
        .main-content { flex: 1; padding: 30px; display: flex; flex-direction: column; align-items: center; }
        h2 { color: #d4af37; border-bottom: 2px solid #d4af37; padding-bottom: 10px; margin-bottom: 20px; font-size: 22px; width: 100%; }
        .scan-box { background: #161616; border: 2px solid #d4af37; border-radius: 16px; padding: 40px; text-align: center; max-width: 480px; width: 100%; box-shadow: 0 10px 30px rgba(212, 175, 55, 0.2); }
        input[type="text"] { width: 100%; padding: 14px; background: #222; border: 1px solid #444; border-radius: 8px; color: #fff; font-size: 16px; text-align: center; margin-top: 20px; font-weight: bold; }
        input[type="text"]:focus { border-color: #d4af37; outline: none; box-shadow: 0 0 15px rgba(212, 175, 55, 0.4); }
        .alert { background: #1e4620; color: #a3e635; padding: 14px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 700; text-align: center; width: 100%; max-width: 480px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2>SCHOOL ADMIN</h2>
            <div class="brand-sub">Management System Pro</div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php">📊 Dashboard Utama</a></li>
            <li><a href="index.php">📚 Data Siswa</a></li>
            <li><a href="kartu.php">💳 Cetak Kartu Siswa</a></li>
            <li><a href="scan_qr.php" class="active">📷 Scan Presensi QR</a></li>
            <li><a href="absensi.php">📅 Rekap Kehadiran</a></li>
            <li><a href="logout.php" style="color:#ff6b6b; margin-top:10px;">🚪 Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h2>📷 Pemindai Kartu Pelajar & Presensi Otomatis</h2>

        <?php if($pesan): ?><div class="alert"><?= $pesan; ?></div><?php endif; ?>

        <div class="scan-box">
            <div style="font-size: 70px; margin-bottom: 10px;">💳</div>
            <h3 style="color:#d4af37;">TEMPILKAN / SCAN KARTU PELAJAR</h3>
            <p style="color:#888; font-size:12px; margin-top:5px;">Tempelkan Barcode / QR Code Kartu Pelajar pada Scanner</p>

            <form action="" method="POST">
                <input type="text" name="nisn_scan" placeholder="Scan / Ketik NISN disini..." autofocus autocomplete="off">
            </form>
        </div>
    </div>
</body>
</html>