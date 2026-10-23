<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$admin_nama = $_SESSION['admin_nama'] ?? 'Rayyan Athar Syazani';

// Buat tabel jika belum ada
@mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS pembayaran_spp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nis VARCHAR(50) NOT NULL,
    nama_siswa VARCHAR(150) NOT NULL,
    kelas VARCHAR(50) NOT NULL,
    jurusan VARCHAR(100) NOT NULL,
    bulan VARCHAR(20) NOT NULL,
    tahun INT(4) NOT NULL,
    nominal DECIMAL(12,2) NOT NULL DEFAULT 150000.00,
    status_bayar ENUM('Lunas', 'Belum Lunas') DEFAULT 'Belum Lunas',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$result_spp = mysqli_query($koneksi, "SELECT * FROM pembayaran_spp ORDER BY id DESC LIMIT 100");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pembayaran SPP - SMK Negeri 1 Lumajang</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="animasi.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #08080a; color: #f4f4f5; display: flex; min-height: 100vh; }
        .main-wrapper { flex: 1; padding: 35px 45px; background: #08080a; }
        h1 { color: #ffffff; font-size: 26px; font-weight: 800; margin-bottom: 20px; }
        .card { background: #111114; border: 1px solid #27272a; border-radius: 18px; padding: 26px; box-shadow: 0 10px 30px rgba(0,0,0,0.6); }
        .card h3 { color: #d4af37; font-size: 16px; font-weight: 800; margin-bottom: 20px; border-bottom: 1px solid #27272a; padding-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: linear-gradient(135deg, #fff2be 0%, #d4af37 100%); color: #09090b; padding: 14px 16px; font-size: 12px; font-weight: 800; text-transform: uppercase; }
        td { padding: 14px 16px; border-bottom: 1px solid #27272a; font-size: 13px; color: #d4d4d8; }
        tr:hover { background: rgba(212, 175, 55, 0.05); }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-wrapper">
        <h1>💰 Manajemen Pembayaran SPP & Keuangan</h1>
        <div class="card">
            <h3>📋 Rekapitulasi Data Pembayaran SPP</h3>
            <table>
                <thead>
                    <tr>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas / Jurusan</th>
                        <th>Periode</th>
                        <th>Nominal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_spp && mysqli_num_rows($result_spp) > 0) {
                        while ($row = mysqli_fetch_assoc($result_spp)) {
                            echo "<tr>";
                            echo "<td style='font-family: monospace; color: #d4af37; font-weight: 700;'>" . htmlspecialchars($row['nis']) . "</td>";
                            echo "<td style='font-weight: 700; color: #fff;'>" . htmlspecialchars($row['nama_siswa']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['kelas']) . " &bull; <span style='color: #d4af37;'>" . htmlspecialchars($row['jurusan']) . "</span></td>";
                            echo "<td><b>" . htmlspecialchars($row['bulan']) . "</b> " . $row['tahun'] . "</td>";
                            echo "<td style='font-family: monospace; font-weight: 700;'>Rp " . number_format($row['nominal'], 0, ',', '.') . "</td>";
                            echo "<td><b style='color: " . ($row['status_bayar'] == 'Lunas' ? '#4ade80' : '#f87171') . ";'>" . htmlspecialchars($row['status_bayar']) . "</b></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center; color: #71717a; padding: 40px;'>Belum ada data pembayaran SPP yang tercatat di database.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>