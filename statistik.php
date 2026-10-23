<?php
session_start();
include 'koneksi.php';

// Proteksi Halaman Login
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// 1. Deteksi Otomatis Tabel Siswa Utama
$tabel_siswa = 'siswa';
$c_d = mysqli_query($koneksi, "SELECT COUNT(*) FROM data_siswa");
if ($c_d && mysqli_fetch_row($c_d)[0] > 0) {$tabel_siswa = 'data_siswa'; }

// 2. Hitung Statistik Keseluruhan
$q_s = mysqli_query($koneksi, "SELECT COUNT(*) FROM `$tabel_siswa`");
$total_siswa = $q_s ? mysqli_fetch_row($q_s)[0] : 0;
$total_siswa_calc = $total_siswa > 0 ?$total_siswa : 1; // Mencegah devision by zero

$q_g = mysqli_query($koneksi, "SELECT COUNT(*) FROM data_guru");
$total_guru = $q_g ? mysqli_fetch_row($q_g)[0] : 0;

$q_k = mysqli_query($koneksi, "SELECT COUNT(*) FROM data_kelas");
$total_kelas = $q_k ? mysqli_fetch_row($q_k)[0] : 0;

// 3. Perhitungan Sebaran 5 Jurusan
$q_rpl = mysqli_query($koneksi, "SELECT COUNT(*) FROM `$tabel_siswa` WHERE Jurusan LIKE '%RPL%' OR jurusan LIKE '%RPL%'");
$jml_rpl = $q_rpl ? mysqli_fetch_row($q_rpl)[0] : 0;

$q_tkj = mysqli_query($koneksi, "SELECT COUNT(*) FROM `$tabel_siswa` WHERE Jurusan LIKE '%TKJ%' OR jurusan LIKE '%TKJ%'");
$jml_tkj = $q_tkj ? mysqli_fetch_row($q_tkj)[0] : 0;

$q_psptv = mysqli_query($koneksi, "SELECT COUNT(*) FROM `$tabel_siswa` WHERE Jurusan LIKE '%PSPTV%' OR jurusan LIKE '%PSPTV%'");
$jml_psptv = $q_psptv ? mysqli_fetch_row($q_psptv)[0] : 0;

$q_grafika = mysqli_query($koneksi, "SELECT COUNT(*) FROM `$tabel_siswa` WHERE Jurusan LIKE '%Grafika%' OR jurusan LIKE '%Grafika%'");
$jml_grafika = $q_grafika ? mysqli_fetch_row($q_grafika)[0] : 0;

$q_akuntansi = mysqli_query($koneksi, "SELECT COUNT(*) FROM `$tabel_siswa` WHERE Jurusan LIKE '%Akuntansi%' OR jurusan LIKE '%Akuntansi%'");
$jml_akuntansi = $q_akuntansi ? mysqli_fetch_row($q_akuntansi)[0] : 0;

// Persentase Sebaran Jurusan
$p_rpl       = round(($jml_rpl / $total_siswa_calc) * 100, 1);
$p_tkj       = round(($jml_tkj / $total_siswa_calc) * 100, 1);$p_psptv     = round(($jml_psptv / $total_siswa_calc) * 100, 1);
$p_grafika   = round(($jml_grafika / $total_siswa_calc) * 100, 1);$p_akuntansi = round(($jml_akuntansi / $total_siswa_calc) * 100, 1);

// 4. Analytics Data Presensi
$q_h = mysqli_query($koneksi, "SELECT COUNT(*) FROM presensi_siswa WHERE status='Hadir'");
$jml_h = $q_h ? mysqli_fetch_row($q_h)[0] : 0;

$q_i = mysqli_query($koneksi, "SELECT COUNT(*) FROM presensi_siswa WHERE status='Izin'");
$jml_i = $q_i ? mysqli_fetch_row($q_i)[0] : 0;

$q_s_abs = mysqli_query($koneksi, "SELECT COUNT(*) FROM presensi_siswa WHERE status='Sakit'");
$jml_s = $q_s_abs ? mysqli_fetch_row($q_s_abs)[0] : 0;

$q_a = mysqli_query($koneksi, "SELECT COUNT(*) FROM presensi_siswa WHERE status='Alpa'");
$jml_a = $q_a ? mysqli_fetch_row($q_a)[0] : 0;

$total_presensi = $jml_h +$jml_i + $jml_s +$jml_a;
$persen_hadir =$total_presensi > 0 ? round(($jml_h / $total_presensi) * 100, 1) : 100;

// 5. Analytics Poin & Kedisiplinan
$q_p = mysqli_query($koneksi, "SELECT COUNT(*) FROM poin_siswa WHERE tipe='Prestasi'");
$jml_prestasi = $q_p ? mysqli_fetch_row($q_p)[0] : 0;

$q_l = mysqli_query($koneksi, "SELECT COUNT(*) FROM poin_siswa WHERE tipe='Pelanggaran'");
$jml_pelanggaran = $q_l ? mysqli_fetch_row($q_l)[0] : 0;

// 6. Analytics Kas Keuangan SPP
$q_spp = mysqli_query($koneksi, "SELECT SUM(nominal) FROM spp_siswa WHERE status='Lunas'");
$total_spp = $q_spp ? mysqli_fetch_row($q_spp)[0] : 0;

$admin_nama =$_SESSION['admin_nama'] ?? 'Rayyan Athar Syazani';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Executive Analytics Pro - Black & Gold Luxury</title>
    
    <!-- Script Chart.js Core & Plugin -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body { 
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; 
            background-color: #08080a; 
            color: #e2e8f0; 
            display: flex; 
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ================= SIDEBAR LUXURY STYLING ================= */
        .sidebar {
            width: 285px;
            background: #111114;
            border-right: 1px solid rgba(212, 175, 55, 0.25);
            padding: 24px 18px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            flex-shrink: 0;
            box-shadow: 5px 0 25px rgba(0, 0, 0, 0.6);
            z-index: 100;
        }

        .sidebar-brand {
            text-align: center;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-brand h2 {
            color: #d4af37;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 1.5px;
            background: linear-gradient(135deg, #fff2be 0%, #d4af37 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-brand .brand-sub {
            font-size: 10px;
            color: #71717a;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .user-profile-card {
            background: linear-gradient(135deg, #1d1d21 0%, #121215 100%);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 12px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            background: #0d0d0d;
            border: 1.5px solid #d4af37;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #d4af37;
            font-size: 18px;
        }

        .user-info .user-name {
            font-size: 13px;
            font-weight: 700;
            color: #d4af37;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 170px;
        }

        .user-info .user-status {
            font-size: 11px;
            color: #a1a1aa;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background-color: #22c55e;
            border-radius: 50%;
            box-shadow: 0 0 8px #22c55e;
        }

        .sidebar-section-title {
            color: #52525b;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-top: 10px;
            padding-left: 8px;
            font-weight: 800;
        }

        .sidebar-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .sidebar-menu a {
            color: #a1a1aa;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: linear-gradient(135deg, #d4af37 0%, #aa8c2c 100%);
            color: #09090b;
            font-weight: 800;
            transform: translateX(4px);
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
        }

        .clock-widget {
            background: #18181b;
            border: 1px solid rgba(212, 175, 55, 0.25);
            border-radius: 10px;
            padding: 12px;
            text-align: center;
            margin-top: auto;
        }

        .clock-time {
            color: #d4af37;
            font-size: 16px;
            font-weight: 800;
            font-family: monospace;
        }

        .clock-date {
            color: #71717a;
            font-size: 10px;
            margin-top: 2px;
        }

        /* ================= MAIN CONTENT STYLING ================= */
        .main-content { 
            flex: 1; 
            padding: 30px 40px; 
            overflow-y: auto; 
            animation: fadeInUp 0.6s ease-out; 
        }

        .top-navbar { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px; 
            padding-bottom: 18px; 
            border-bottom: 1px solid rgba(255, 255, 255, 0.08); 
        }

        .page-title { 
            color: #fff; 
            font-size: 26px; 
            font-weight: 800; 
            margin-top: 4px;
        }

        /* KPI Cards Summary */
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); 
            gap: 18px; 
            margin-bottom: 30px; 
        }

        .stat-card {
            background: linear-gradient(145deg, #151518 0%, #0d0d0f 100%); 
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 16px; 
            padding: 22px; 
            display: flex; 
            align-items: center; 
            justify-content: space-between;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5); 
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .stat-card:hover { 
            transform: translateY(-5px); 
            border-color: #d4af37; 
            box-shadow: 0 12px 30px rgba(212, 175, 55, 0.25); 
        }

        .stat-info h4 { 
            color: #a1a1aa; 
            font-size: 11px; 
            text-transform: uppercase; 
            letter-spacing: 0.8px; 
            margin-bottom: 6px; 
        }

        .stat-info .number { 
            color: #fff; 
            font-size: 26px; 
            font-weight: 800; 
        }

        .stat-info .sub-text {
            font-size: 11px;
            color: #22c55e;
            margin-top: 4px;
            font-weight: 600;
        }

        /* Card Sebaran Jurusan (Visual Progress) */
        .jurusan-card {
            background: #121215; 
            border: 1px solid #27272a; 
            border-radius: 18px; 
            padding: 28px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.5); 
            margin-bottom: 30px;
        }

        .jurusan-card h3 { 
            color: #d4af37; 
            font-size: 16px; 
            margin-bottom: 22px; 
            font-weight: 800; 
            border-bottom: 1px solid #27272a; 
            padding-bottom: 12px; 
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .progress-item { margin-bottom: 18px; }

        .progress-info { 
            display: flex; 
            justify-content: space-between; 
            font-size: 13px; 
            font-weight: 700; 
            margin-bottom: 7px; 
            color: #e2e8f0; 
        }

        .progress-bar-bg { 
            width: 100%; 
            height: 12px; 
            background: #1c1c1f; 
            border-radius: 8px; 
            overflow: hidden; 
            border: 1px solid #27272a; 
        }

        .progress-bar-fill { 
            height: 100%; 
            background: linear-gradient(135deg, #d4af37 0%, #aa8c2c 100%); 
            border-radius: 8px; 
            transition: width 1.2s cubic-bezier(0.16, 1, 0.3, 1); 
        }

        /* Multi-Chart Section Grid */
        .charts-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); 
            gap: 24px; 
            margin-bottom: 30px; 
        }

        .chart-card { 
            background: #121215; 
            border: 1px solid #27272a; 
            border-radius: 18px; 
            padding: 25px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.5); 
            transition: all 0.3s;
        }

        .chart-card:hover { border-color: rgba(212, 175, 55, 0.5); }

        .chart-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 20px; 
            border-bottom: 1px solid #27272a; 
            padding-bottom: 12px; 
        }

        .chart-header h3 { 
            color: #d4af37; 
            font-size: 15px; 
            font-weight: 800; 
        }

        /* Ranking Matrix Table */
        .rank-card {
            background: #121215; 
            border: 1px solid #27272a; 
            border-radius: 18px; 
            padding: 25px; 
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .rank-table { 
            width: 100%; 
            border-collapse: collapse; 
            text-align: left; 
            margin-top: 15px; 
        }

        .rank-table th { 
            background: linear-gradient(135deg, #d4af37 0%, #aa8c2c 100%); 
            color: #09090b; 
            padding: 14px 18px; 
            font-size: 12px; 
            font-weight: 800; 
            text-transform: uppercase; 
        }

        .rank-table td { 
            padding: 14px 18px; 
            border-bottom: 1px solid #27272a; 
            font-size: 13px; 
        }

        /* Keyframes Animation */
        @keyframes fadeInUp { 
            from { opacity: 0; transform: translateY(20px); } 
            to { opacity: 1; transform: translateY(0); } 
        }
    </style>
</head>
<body>

    <!-- SIDEBAR NAVIGATION -->
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
            <li><a href="kartu.php">💳 Cetak Kartu Siswa</a></li>
        </ul>

        <div class="sidebar-section-title">Analytics & System</div>
        <ul class="sidebar-menu">
            <li><a href="absensi.php">📅 Rekap Kehadiran</a></li>
            <li><a href="prestasi.php">🏆 Poin & Prestasi</a></li>
            <li><a href="spp.php">💰 Pembayaran SPP</a></li>
            <li><a href="statistik.php" class="active">📈 Statistik Sekolah</a></li>
            <li><a href="pengaturan.php">⚙️ Pengaturan Admin</a></li>
            <li><a href="logout.php" style="color:#ef4444; border:1px solid rgba(239,68,68,0.3); margin-top:10px;">🚪 Logout</a></li>
        </ul>

        <div class="clock-widget">
            <div id="sidebar-time" class="clock-time">00:00:00</div>
            <div id="sidebar-date" class="clock-date">Sabtu, 17 Oktober 2026</div>
        </div>
    </div>

    <!-- MAIN CONTENT AREA -->
    <div class="main-content">
        
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div>
                <div style="font-size: 12px; color: #71717a;">Executive Intelligence / <span style="color:#d4af37; font-weight:700;">Statistik Pro</span></div>
                <h1 class="page-title">📈 Analytics & Visualisasi Data Instansi</h1>
            </div>
            <div style="background:#18181b; border:1px solid #27272a; padding:8px 18px; border-radius:30px; font-size:12px; color:#4ade80; font-weight:700;">
                🟢 REAL-TIME ANALYTICS SINKRON
            </div>
        </div>

        <!-- Summary KPI Cards Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h4>Total Siswa Terdaftar</h4>
                    <div class="number"><?= $total_siswa; ?></div>
                    <div class="sub-text">▲ +100% Terverifikasi</div>
                </div>
                <div style="font-size:34px;">🎓</div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h4>Persentase Presensi</h4>
                    <div class="number" style="color:#22c55e;"><?= $persen_hadir; ?>%</div>
                    <div class="sub-text" style="color:#a1a1aa;">● Rekapitulasi Kehadiran</div>
                </div>
                <div style="font-size:34px;">📅</div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h4>Tenaga Pendidik & Staf</h4>
                    <div class="number"><?= $total_guru; ?></div>
                    <div class="sub-text" style="color:#d4af37;">● Staf Aktif Terdaftar</div>
                </div>
                <div style="font-size:34px;">👨‍🏫</div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h4>Total Kas SPP Siswa</h4>
                    <div class="number" style="color:#d4af37; font-size:20px;">Rp <?= number_format($total_spp, 0, ',', '.'); ?></div>
                    <div class="sub-text">▲ Kas Terkumpul</div>
                </div>
                <div style="font-size:34px;">💰</div>
            </div>
        </div>

        <!-- Detail Persentase Sebaran 5 Jurusan Baru -->
        <div class="jurusan-card">
            <h3>⚡ Detail Persentase Sebaran Jurusan (RPL, TKJ, PSPTV, Teknik Grafika, Akuntansi)</h3>
            
            <div class="progress-item">
                <div class="progress-info">
                    <span>Rekayasa Perangkat Lunak (RPL)</span>
                    <span><?= $jml_rpl; ?> Siswa (<?=$p_rpl; ?>%)</span>
                </div>
                <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: <?= $p_rpl; ?>%;"></div></div>
            </div>

            <div class="progress-item">
                <div class="progress-info">
                    <span>Teknik Komputer & Jaringan (TKJ)</span>
                    <span><?= $jml_tkj; ?> Siswa (<?=$p_tkj; ?>%)</span>
                </div>
                <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: <?= $p_tkj; ?>%; background: linear-gradient(135deg, #22c55e 0%, #15803d 100%);"></div></div>
            </div>

            <div class="progress-item">
                <div class="progress-info">
                    <span>Produksi & Siaran Program TV (PSPTV)</span>
                    <span><?= $jml_psptv; ?> Siswa (<?=$p_psptv; ?>%)</span>
                </div>
                <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: <?= $p_psptv; ?>%; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);"></div></div>
            </div>

            <div class="progress-item">
                <div class="progress-info">
                    <span>Teknik Grafika</span>
                    <span><?= $jml_grafika; ?> Siswa (<?=$p_grafika; ?>%)</span>
                </div>
                <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: <?= $p_grafika; ?>%; background: linear-gradient(135deg, #a855f7 0%, #7e22ce 100%);"></div></div>
            </div>

            <div class="progress-item" style="margin-bottom:0;">
                <div class="progress-info">
                    <span>Akuntansi & Keuangan Lembaga (AKL)</span>
                    <span><?= $jml_akuntansi; ?> Siswa (<?=$p_akuntansi; ?>%)</span>
                </div>
                <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: <?= $p_akuntansi; ?>%; background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);"></div></div>
            </div>
        </div>

        <!-- 4 Multi-Chart Grid Section -->
        <div class="charts-grid">
            
            <!-- Chart 1: Bar Chart Jurusan -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>📊 Grafik Sebaran Jurusan</h3>
                </div>
                <canvas id="chartBarJurusan" height="230"></canvas>
            </div>

            <!-- Chart 2: Doughnut Presensi -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>📅 Rekapitulasi Status Presensi</h3>
                </div>
                <canvas id="chartDoughnutPresensi" height="230"></canvas>
            </div>

            <!-- Chart 3: Radar Performa Sekolah -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>🎯 Performa Mutu & Akreditasi</h3>
                </div>
                <canvas id="chartRadarAkreditasi" height="230"></canvas>
            </div>

            <!-- Chart 4: Line Chart Tren Poin Siswa -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>📈 Tren Kedisiplinan & Prestasi</h3>
                </div>
                <canvas id="chartLineTren" height="230"></canvas>
            </div>

        </div>

        <!-- Table Matriks Prestasi & Poin Terbaru -->
        <div class="rank-card">
            <h3 style="color:#d4af37; font-size:16px; font-weight:800; border-bottom:1px solid #27272a; padding-bottom:12px;">
                ⭐ Ringkasan Poin & Prestasi Terbaru Siswa
            </h3>
            <table class="rank-table">
                <thead>
                    <tr>
                        <th style="width:50px; text-align:center;">No</th>
                        <th>Nama Siswa</th>
                        <th style="width:130px;">Kategori</th>
                        <th>Judul Capaian / Catatan</th>
                        <th style="width:100px; text-align:center;">Poin</th>
                        <th style="width:130px; text-align:center;">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $q_top = mysqli_query($koneksi, "SELECT * FROM poin_siswa ORDER BY id DESC LIMIT 5");
                    $no = 1;
                    if ($q_top && mysqli_num_rows($q_top) > 0) {
                        while ($r = mysqli_fetch_assoc($q_top)) {
                    ?>
                        <tr>
                            <td style="text-align:center; color:#71717a;"><?= $no++; ?></td>
                            <td style="font-weight:700; color:#fff;"><?= htmlspecialchars($r['nama_siswa']); ?></td>
                            <td>
                                <span style="background:<?= $r['tipe']=='Prestasi'?'rgba(34,197,94,0.2)':'rgba(239,68,68,0.2)'; ?>; color:<?=$r['tipe']=='Prestasi'?'#4ade80':'#f87171'; ?>; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:700;">
                                    <?= strtoupper($r['tipe']); ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($r['judul']); ?></td>
                            <td style="text-align:center; font-weight:800; color:<?= $r['tipe']=='Prestasi'?'#4ade80':'#f87171'; ?>;">
                                <?= $r['tipe']=='Prestasi'?'+':'-'; ?><?=$r['poin']; ?>
                            </td>
                            <td style="text-align:center; color:#71717a; font-size:12px;"><?= $r['tanggal']; ?></td>
                        </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center; color:#71717a; padding:20px;'>Belum ada data prestasi/pelanggaran yang dicatat dalam sistem.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- SCRIPT CHART.JS CONFIGURATION -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            
            // 1. Bar Chart Sebaran 5 Jurusan
            const ctxBar = document.getElementById('chartBarJurusan').getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: ['RPL', 'TKJ', 'PSPTV', 'Teknik Grafika', 'Akuntansi'],
                    datasets: [{
                        label: 'Siswa',
                        data: [<?= $jml_rpl; ?>, <?= $jml_tkj; ?>, <?=$jml_psptv; ?>, <?= $jml_grafika; ?>, <?=$jml_akuntansi; ?>],
                        backgroundColor: ['#d4af37', '#22c55e', '#3b82f6', '#a855f7', '#ec4899'],
                        borderRadius: 8
                    }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color: '#e0e0e0' }, grid: { display: false } },
                        y: { ticks: { color: '#e0e0e0' }, grid: { color: '#27272a' } }
                    }
                }
            });

            // 2. Doughnut Chart Presensi
            const ctxDoughnut = document.getElementById('chartDoughnutPresensi').getContext('2d');
            new Chart(ctxDoughnut, {
                type: 'doughnut',
                data: {
                    labels: ['Hadir', 'Izin', 'Sakit', 'Alpa'],
                    datasets: [{
                        data: [<?= $jml_h; ?>, <?=$jml_i; ?>, <?= $jml_s; ?>, <?=$jml_a; ?>],
                        backgroundColor: ['#22c55e', '#eab308', '#06b6d4', '#ef4444'],
                        borderWidth: 0
                    }]
                },
                options: {
                    plugins: { 
                        legend: { labels: { color: '#e0e0e0', font: { family: 'Segoe UI' } } } 
                    }
                }
            });

            // 3. Radar Chart Akreditasi / Mutu Sekolah
            const ctxRadar = document.getElementById('chartRadarAkreditasi').getContext('2d');
            new Chart(ctxRadar, {
                type: 'radar',
                data: {
                    labels: ['Kurikulum', 'Sarpras', 'Kedisiplinan', 'Keuangan', 'Ekstrakurikuler'],
                    datasets: [{
                        label: 'Skor Mutu Sekolah (%)',
                        data: [95, 88, 92, 90, 85],
                        backgroundColor: 'rgba(212, 175, 55, 0.25)',
                        borderColor: '#d4af37',
                        pointBackgroundColor: '#d4af37'
                    }]
                },
                options: {
                    scales: {
                        r: {
                            angleLines: { color: '#27272a' },
                            grid: { color: '#27272a' },
                            pointLabels: { color: '#e0e0e0' },
                            ticks: { display: false }
                        }
                    },
                    plugins: { legend: { display: false } }
                }
            });

            // 4. Line Chart Tren Kedisiplinan & Prestasi
            const ctxLine = document.getElementById('chartLineTren').getContext('2d');
            new Chart(ctxLine, {
                type: 'line',
                data: {
                    labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
                    datasets: [
                        {
                            label: 'Prestasi (+)',
                            data: [5, 12, 18, <?= $jml_prestasi; ?>],
                            borderColor: '#4ade80',
                            backgroundColor: 'rgba(74, 222, 128, 0.1)',
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Pelanggaran (-)',
                            data: [2, 4, 3, <?= $jml_pelanggaran; ?>],
                            borderColor: '#f87171',
                            backgroundColor: 'rgba(248, 113, 113, 0.1)',
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    plugins: { legend: { labels: { color: '#e0e0e0' } } },
                    scales: {
                        x: { ticks: { color: '#e0e0e0' }, grid: { display: false } },
                        y: { ticks: { color: '#e0e0e0' }, grid: { color: '#27272a' } }
                    }
                }
            });

        });

        // Script Jam Digital Real-Time
        function updateSidebarClock() {
            const now = new Date();
            document.getElementById('sidebar-time').textContent = now.toLocaleTimeString('id-ID', { hour12: false });
            document.getElementById('sidebar-date').textContent = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
        setInterval(updateSidebarClock, 1000); 
        updateSidebarClock();
    </script>

</body>
</html>