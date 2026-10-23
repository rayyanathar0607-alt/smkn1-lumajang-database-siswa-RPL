<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Deteksi Otomatis Tabel Siswa
$tabel_siswa = 'siswa';
$c_d = mysqli_query($koneksi, "SELECT COUNT(*) FROM data_siswa");
if ($c_d && mysqli_fetch_row($c_d)[0] > 0) {$tabel_siswa = 'data_siswa'; }

// Ambil parameter filter kelas / pencarian
$pencarian  = isset($_GET['cari']) ? trim($_GET['cari']) : "";
$filter_jur = isset($_GET['jurusan']) ? trim($_GET['jurusan']) : "";

$query = "SELECT * FROM `$tabel_siswa` ORDER BY ID ASC";
$result = mysqli_query($koneksi,$query);

$admin_nama =$_SESSION['admin_nama'] ?? 'Rayyan Athar Syazani';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu Pelajar - SMK Negeri 1 Lumajang</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; 
            background-color: #08080a; 
            color: #e2e8f0; 
            display: flex; 
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 285px; background: #111114; border-right: 1px solid rgba(212, 175, 55, 0.25);
            padding: 24px 18px; display: flex; flex-direction: column; gap: 14px; flex-shrink: 0;
            box-shadow: 5px 0 25px rgba(0,0,0,0.6); z-index: 100;
        }
        .sidebar-brand { text-align: center; padding-bottom: 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); }
        .sidebar-brand h2 {
            color: #d4af37; font-size: 20px; font-weight: 800; letter-spacing: 1.5px;
            background: linear-gradient(135deg, #fff2be 0%, #d4af37 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .sidebar-brand .brand-sub { font-size: 10px; color: #71717a; letter-spacing: 1.2px; text-transform: uppercase; }

        .user-profile-card {
            background: linear-gradient(135deg, #1d1d21 0%, #121215 100%);
            border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 12px; padding: 12px 14px; display: flex; align-items: center; gap: 12px;
        }
        .user-avatar { width: 38px; height: 38px; background: #0d0d0d; border: 1.5px solid #d4af37; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #d4af37; }
        .user-info .user-name { font-size: 13px; font-weight: 700; color: #d4af37; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px; }
        .user-info .user-status { font-size: 11px; color: #a1a1aa; display: flex; align-items: center; gap: 6px; }
        .status-dot { width: 8px; height: 8px; background-color: #22c55e; border-radius: 50%; box-shadow: 0 0 8px #22c55e; }

        .sidebar-section-title { color: #52525b; font-size: 10px; text-transform: uppercase; letter-spacing: 1.2px; margin-top: 10px; padding-left: 8px; font-weight: 800; }
        .sidebar-menu { list-style: none; display: flex; flex-direction: column; gap: 5px; }
        .sidebar-menu a {
            color: #a1a1aa; text-decoration: none; padding: 10px 14px; border-radius: 10px; font-size: 13px; font-weight: 600;
            display: flex; align-items: center; gap: 12px; transition: all 0.3s ease;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: linear-gradient(135deg, #d4af37 0%, #aa8c2c 100%); color: #09090b; font-weight: 800; transform: translateX(4px); box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
        }

        .clock-widget { background: #18181b; border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 10px; padding: 12px; text-align: center; margin-top: auto; }
        .clock-time { color: #d4af37; font-size: 16px; font-weight: 800; font-family: monospace; }
        .clock-date { color: #71717a; font-size: 10px; margin-top: 2px; }

        /* Main Content Area */
        .main-content { flex: 1; padding: 30px 40px; overflow-y: auto; }
        .top-navbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 18px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); }
        .page-title { color: #fff; font-size: 26px; font-weight: 800; }

        /* Toolbar Actions */
        .toolbar-box {
            background: #121215; border: 1px solid #27272a; border-radius: 14px; padding: 16px 20px;
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;
        }
        .btn-print {
            background: linear-gradient(135deg, #d4af37 0%, #aa8c2c 100%); color: #09090b;
            padding: 10px 20px; border-radius: 8px; font-weight: 800; font-size: 13px; text-decoration: none; border: none; cursor: pointer; transition: all 0.3s;
        }
        .btn-print:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4); }

        /* Grid Kartu Pelajar */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(420px, 1fr));
            gap: 25px;
        }

        /* Desain Kartu Pelajar Pro Black & Gold */
        .id-card {
            background: #141414;
            border: 2px solid #d4af37;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.6);
            position: relative;
        }

        .card-header-box {
            background: linear-gradient(135deg, #d4af37 0%, #b3922f 100%);
            padding: 14px 20px;
            text-align: center;
            color: #0d0d0d;
        }

        .card-header-box h3 {
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .card-header-box h2 {
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 2px;
            margin-top: 2px;
        }

        .card-body-box {
            padding: 20px;
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .photo-box {
            width: 110px;
            height: 140px;
            background: #1c1c1f;
            border: 2px solid #d4af37;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #71717a;
            font-size: 11px;
            flex-shrink: 0;
        }

        .photo-box .icon-user {
            font-size: 40px;
            margin-bottom: 5px;
            color: #d4af37;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 6px 0;
            font-size: 13.5px;
            color: #e2e8f0;
        }

        .info-table td.label-col {
            color: #d4af37;
            font-weight: 700;
            width: 75px;
        }

        .info-table td.colon-col {
            width: 15px;
            color: #d4af37;
            font-weight: 700;
        }

        .info-table td.value-col {
            font-weight: 700;
            color: #ffffff;
        }

        /* Styling Khusus Cetak / Print Mode */
        @media print {
            .sidebar, .top-navbar, .toolbar-box { display: none !important; }
            body { background: #fff !important; color: #000 !important; }
            .main-content { padding: 0 !important; }
            .card-grid { display: block !important; }
            .id-card { 
                page-break-inside: avoid; 
                margin-bottom: 20px; 
                border: 2px solid #000 !important;
                background: #fff !important;
                color: #000 !important;
            }
            .card-header-box { background: #d4af37 !important; color: #000 !important; }
            .info-table td { color: #000 !important; }
            .info-table td.label-col, .info-table td.colon-col { color: #000 !important; }
            .photo-box { border-color: #000 !important; background: #eee !important; color: #000 !important; }
        }
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2>SCHOOL ADMIN</h2>
            <div class="brand-sub">Management System Pro</div>
        </div>

        <div class="user-profile-card">
            <div class="user-avatar">👨‍💼</div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($admin_nama); ?></div>
                <div class="user-status"><span class="status-dot"></span> Online (Admin)</div>
            </div>
        </div>

        <div class="sidebar-section-title">Menu Utama</div>
        <ul class="sidebar-menu">
            <li><a href="index.php">📊 Dashboard Utama</a></li>
            <li><a href="index.php">📚 Data Siswa</a></li>
            <li><a href="guru.php">👨‍🏫 Data Guru & Staf</a></li>
            <li><a href="kelas.php">🏫 Kelas & Jurusan</a></li>
            <li><a href="kartu.php" class="active">💳 Cetak Kartu Siswa</a></li>
        </ul>

        <div class="sidebar-section-title">Analytics & System</div>
        <ul class="sidebar-menu">
            <li><a href="absensi.php">📅 Rekap Kehadiran</a></li>
            <li><a href="prestasi.php">🏆 Poin & Prestasi</a></li>
            <li><a href="spp.php">💰 Pembayaran SPP</a></li>
            <li><a href="statistik.php">📈 Statistik Sekolah</a></li>
            <li><a href="pengaturan.php">⚙️ Pengaturan Admin</a></li>
            <li><a href="logout.php" style="color:#ef4444; border:1px solid rgba(239,68,68,0.3); margin-top:10px;">🚪 Logout</a></li>
        </ul>

        <div class="clock-widget">
            <div id="sidebar-time" class="clock-time">00:00:00</div>
            <div id="sidebar-date" class="clock-date">Sabtu, 17 Oktober 2026</div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-navbar">
            <div>
                <div style="font-size: 12px; color: #71717a;">Kartu Digital / <span style="color:#d4af37; font-weight:700;">Kartu Pelajar Pro</span></div>
                <h1 class="page-title">💳 Cetak Kartu Pelajar Siswa</h1>
            </div>
        </div>

        <div class="toolbar-box">
            <div style="font-size: 13px; color: #a1a1aa;">
                Menampilkan Kartu Pelajar Resmi <b>SMK Negeri 1 Lumajang</b>
            </div>
            <button onclick="window.print()" class="btn-print">🖨️ CETAK SEMUA KARTU PELAJAR</button>
        </div>

        <div class="card-grid">
            <?php 
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_array($result, MYSQLI_BOTH)) {
                    $nis   =$row['NISN'] ?? $row['nis'] ?? $row['nisn'] ?? $row[2] ?? $row[1] ?? '-';
                    $nama  =$row['Nama_Lengkap'] ?? $row['nama'] ?? $row['nama_lengkap'] ?? $row[1] ?? $row[2] ?? '-';
                    $kelas =$row['Kelas'] ?? $row['kelas'] ?? $row[3] ?? '-';
                    $jur   =$row['Jurusan'] ?? $row['jurusan'] ?? $row[4] ?? '-';
            ?>
                    <div class="id-card">
                        <div class="card-header-box">
                            <h3>SMK NEGERI 1 LUMAJANG</h3>
                            <h2>KARTU PELAJAR</h2>
                        </div>
                        <div class="card-body-box">
                            <div class="photo-box">
                                <div class="icon-user">👤</div>
                                <span>FOTO 3x4</span>
                            </div>
                            <table class="info-table">
                                <tr>
                                    <td class="label-col">Nama</td>
                                    <td class="colon-col">:</td>
                                    <td class="value-col"><?= htmlspecialchars($nama); ?></td>
                                </tr>
                                <tr>
                                    <td class="label-col">NISN</td>
                                    <td class="colon-col">:</td>
                                    <td class="value-col" style="color:#d4af37; font-family:monospace;"><?= htmlspecialchars($nis); ?></td>
                                </tr>
                                <tr>
                                    <td class="label-col">Kelas</td>
                                    <td class="colon-col">:</td>
                                    <td class="value-col"><?= htmlspecialchars($kelas); ?></td>
                                </tr>
                                <tr>
                                    <td class="label-col">Jurusan</td>
                                    <td class="colon-col">:</td>
                                    <td class="value-col"><?= htmlspecialchars($jur); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
            <?php 
                }
            } else {
                echo "<div style='color:#71717a; padding:30px;'>Belum ada data siswa untuk dicetak kartunya.</div>";
            }
            ?>
        </div>
    </div>

    <script>
        function updateSidebarClock() {
            const now = new Date();
            document.getElementById('sidebar-time').textContent = now.toLocaleTimeString('id-ID', { hour12: false });
            document.getElementById('sidebar-date').textContent = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
        setInterval(updateSidebarClock, 1000); updateSidebarClock();
    </script>
</body>
</html>