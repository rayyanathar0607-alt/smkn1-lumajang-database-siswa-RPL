<?php
session_start();
include 'koneksi.php';

// Deteksi Otomatis Tabel Siswa Utama
$tabel_siswa = 'siswa';
$c_d = mysqli_query($koneksi, "SELECT COUNT(*) FROM data_siswa");
if ($c_d && mysqli_fetch_row($c_d)[0] > 0) {$tabel_siswa = 'data_siswa'; }

// 1. Perhitungan Quick Stat Landing Page
$q_siswa = mysqli_query($koneksi, "SELECT COUNT(*) FROM `$tabel_siswa`");
$total_siswa = $q_siswa ? mysqli_fetch_row($q_siswa)[0] : 0;

$q_guru = mysqli_query($koneksi, "SELECT COUNT(*) FROM data_guru");
$total_guru = $q_guru ? mysqli_fetch_row($q_guru)[0] : 0;

$q_kelas = mysqli_query($koneksi, "SELECT COUNT(*) FROM data_kelas");
$total_kelas = $q_kelas ? mysqli_fetch_row($q_kelas)[0] : 0;

// 2. Perhitungan Poin & Presensi Quick Indicator
$q_h = mysqli_query($koneksi, "SELECT COUNT(*) FROM presensi_siswa WHERE status='Hadir'");
$total_hadir = $q_h ? mysqli_fetch_row($q_h)[0] : 0;

$q_p = mysqli_query($koneksi, "SELECT COUNT(*) FROM poin_siswa WHERE tipe='Prestasi'");
$total_prestasi = $q_p ? mysqli_fetch_row($q_p)[0] : 0;

// Status Autentikasi Admin
$admin_logged_in = isset($_SESSION['admin_logged_in']) &&$_SESSION['admin_logged_in'] === true;
$admin_nama =$_SESSION['admin_nama'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMK Negeri 1 Lumajang - Management System Pro</title>
    
    <!-- Google Fonts & Icon Sets -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* ================= GLOBAL RESET & VARS ================= */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg-dark: #08080a;
            --bg-card: #111114;
            --bg-hover: #18181c;
            --accent-gold: #d4af37;
            --accent-gold-gradient: linear-gradient(135deg, #fff2be 0%, #d4af37 50%, #aa8c2c 100%);
            --text-light: #f4f4f5;
            --text-muted: #71717a;
            --border-gold: rgba(212, 175, 55, 0.25);
            --border-subtle: #27272a;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-light);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #08080a;
        }
        ::-webkit-scrollbar-thumb {
            background: #27272a;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--accent-gold);
        }

        /* ================= NAVBAR STYLING ================= */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 60px;
            background: rgba(17, 17, 20, 0.9);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-gold);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.6);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
        }

        .brand-logo-img {
            width: 48px;
            height: 48px;
            object-fit: contain;
            flex-shrink: 0;
            filter: drop-shadow(0 2px 8px rgba(212, 175, 55, 0.35));
            transition: transform 0.3s ease;
        }

        .brand:hover .brand-logo-img {
            transform: scale(1.05) rotate(-2deg);
        }

        .brand-text h2 {
            color: var(--accent-gold);
            font-size: 19px;
            font-weight: 800;
            letter-spacing: 1px;
            line-height: 1.2;
            background: var(--accent-gold-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-text p {
            font-size: 10px;
            color: var(--text-muted);
            letter-spacing: 1.2px;
            text-transform: uppercase;
            font-weight: 600;
            margin-top: 2px;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn-portal {
            background: var(--accent-gold-gradient);
            color: #09090b;
            padding: 10px 24px;
            border-radius: 30px;
            font-weight: 800;
            font-size: 13px;
            text-decoration: none;
            box-shadow: 0 4px 20px rgba(212, 175, 55, 0.35);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-portal:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.5);
        }

        /* ================= HERO SECTION ================= */
        .hero-wrapper {
            position: relative;
            padding: 80px 60px 60px 60px;
            max-width: 1380px;
            margin: 0 auto;
            width: 100%;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.25fr 0.75fr;
            align-items: center;
            gap: 60px;
        }

        .badge-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(212, 175, 55, 0.1);
            color: var(--accent-gold);
            border: 1px solid rgba(212, 175, 55, 0.3);
            padding: 7px 18px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.2px;
            margin-bottom: 24px;
            text-transform: uppercase;
        }

        .hero-title {
            font-size: 46px;
            font-weight: 800;
            line-height: 1.2;
            color: #ffffff;
            margin-bottom: 22px;
            letter-spacing: -0.5px;
        }

        .hero-title span {
            background: var(--accent-gold-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-desc {
            color: #a1a1aa;
            font-size: 15.5px;
            line-height: 1.8;
            margin-bottom: 40px;
            max-width: 620px;
        }

        .btn-group {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .btn-main {
            background: var(--accent-gold-gradient);
            color: #09090b;
            padding: 15px 32px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(212, 175, 55, 0.35);
        }

        .btn-main:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(212, 175, 55, 0.5);
        }

        .btn-sub {
            background: #18181b;
            color: var(--accent-gold);
            border: 1px solid var(--accent-gold);
            padding: 15px 32px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-sub:hover {
            background: var(--accent-gold);
            color: #09090b;
            transform: translateY(-3px);
        }

        /* ================= SCHOOL CARD FEATURE ================= */
        .school-card-box {
            background: linear-gradient(145deg, #141418 0%, #0d0d10 100%);
            border: 2px solid var(--accent-gold);
            border-radius: 24px;
            padding: 40px 32px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(212, 175, 55, 0.15);
            position: relative;
            overflow: hidden;
        }

        .school-card-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--accent-gold-gradient);
        }

        .card-logo-center {
            width: 110px;
            height: 110px;
            object-fit: contain;
            margin-bottom: 20px;
            filter: drop-shadow(0 6px 15px rgba(212, 175, 55, 0.4));
            animation: floatLogo 4s ease-in-out infinite;
        }

        @keyframes floatLogo {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .school-card-title {
            color: var(--accent-gold);
            font-size: 21px;
            font-weight: 800;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .school-card-sub {
            color: #a1a1aa;
            font-size: 12.5px;
            margin-bottom: 24px;
            font-weight: 500;
        }

        .info-pill-container {
            border-top: 1px solid var(--border-subtle);
            padding-top: 20px;
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .info-pill-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: #d4d4d8;
        }

        .status-badge-online {
            margin-top: 22px;
            background: rgba(34, 197, 94, 0.12);
            color: #4ade80;
            border: 1px solid #22c55e;
            padding: 9px 20px;
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 11.5px;
            font-weight: 700;
        }

        .status-dot-green {
            width: 8px;
            height: 8px;
            background-color: #22c55e;
            border-radius: 50%;
            box-shadow: 0 0 10px #22c55e;
        }

        /* ================= QUICK STATS STRIP ================= */
        .stats-strip-wrapper {
            max-width: 1380px;
            margin: 20px auto 60px auto;
            padding: 0 60px;
            width: 100%;
        }

        .stats-strip-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .strip-card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 16px;
            padding: 22px;
            display: flex;
            align-items: center;
            gap: 18px;
            transition: all 0.3s ease;
        }

        .strip-card:hover {
            border-color: var(--accent-gold);
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        }

        .strip-icon {
            font-size: 32px;
            color: var(--accent-gold);
        }

        .strip-val {
            font-size: 24px;
            font-weight: 800;
            color: #ffffff;
        }

        .strip-lbl {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* ================= FEATURES SHOWCASE GRID ================= */
        .features-section {
            padding: 60px;
            max-width: 1380px;
            margin: 0 auto;
            width: 100%;
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-header h2 {
            font-size: 32px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 12px;
        }

        .section-header p {
            color: var(--text-muted);
            font-size: 15px;
            max-width: 600px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
        }

        .feature-card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 18px;
            padding: 30px;
            transition: all 0.3s ease;
            position: relative;
        }

        .feature-card:hover {
            border-color: var(--accent-gold);
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.6);
        }

        .feature-icon {
            width: 52px;
            height: 52px;
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.25);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 20px;
            color: var(--accent-gold);
        }

        .feature-card h3 {
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 10px;
        }

        .feature-card p {
            font-size: 13.5px;
            color: #a1a1aa;
            line-height: 1.6;
        }

        /* ================= FOOTER STYLING ================= */
        .footer {
            text-align: center;
            padding: 28px 60px;
            font-size: 12.5px;
            color: var(--text-muted);
            border-top: 1px solid var(--border-subtle);
            background: #0d0d10;
            margin-top: auto;
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade {
            animation: fadeInUp 0.8s ease-out forwards;
        }
    </style>
</head>
<body>

    <!-- Header / Navbar -->
    <nav class="navbar">
        <a href="welcome.php" class="brand">
            <div class="brand-text">
                <h2>SMK NEGERI 1 LUMAJANG</h2>
                <p>Management System Pro</p>
            </div>
        </a>

        <div class="nav-actions">
            <?php if($admin_logged_in): ?>
                <a href="index.php" class="btn-portal">📊 DASHBOARD UTAMA</a>
            <?php else: ?>
                <a href="login.php" class="btn-portal">🔐 PORTAL ADMIN</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Main Hero Section -->
    <section class="hero-wrapper animate-fade">
        <div class="hero-grid">
            <div>
                <div class="badge-tag">
                    <span>⭐</span> ENTERPRISE ACADEMIC PLATFORM
                </div>
                <h1 class="hero-title">
                    Sistem Pengelolaan <span>Data Sekolah Terpadu</span> & Modern
                </h1>
                <p class="hero-desc">
                    Platform administrasi sekolah berbasis web berstandar tinggi dengan antarmuka mewah bertema <i>Black & Gold</i>. Mengintegrasikan manajemen siswa, pendataan staf pengajar, analisis statistik instansi, hingga pencetak kartu pelajar digital secara instan.
                </p>
                
                <div class="btn-group">
                    <?php if($admin_logged_in): ?>
                        <a href="index.php" class="btn-main">MASUK DASHBOARD ADMIN</a>
                    <?php else: ?>
                        <a href="login.php" class="btn-main">MASUK KE SISTEM</a>
                    <?php endif; ?>
                    <a href="#fitur" class="btn-sub">JELAJAHI MODUL UTAMA</a>
                </div>
            </div>

            <!-- Card Identitas Sekolah Resmi -->
            <div class="school-card-box">
                <h3 class="school-card-title">SMK NEGERI 1 LUMAJANG</h3>
                <p class="school-card-sub">Lumajang, Jawa Timur — Indonesia</p>

                <div class="info-pill-container">
                    <div class="info-pill-row">
                        <span>Status Akreditasi</span>
                        <b style="color: var(--accent-gold);">A (Unggul)</b>
                    </div>
                    <div class="info-pill-row">
                        <span>Tahun Ajaran</span>
                        <b>2025 / 2026</b>
                    </div>
                    <div class="info-pill-row">
                        <span>Platform Engine</span>
                        <b>System Pro v2.5.0</b>
                    </div>
                </div>

                <div class="status-badge-online">
                    <span class="status-dot-green"></span> SECURE SERVER ONLINE
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Stats Strip -->
    <div class="stats-strip-wrapper animate-fade">
        <div class="stats-strip-grid">
            <div class="strip-card">
                <div class="strip-icon">🎓</div>
                <div>
                    <div class="strip-val"><?= number_format($total_siswa, 0, ',', '.'); ?></div>
                    <div class="strip-lbl">Siswa Aktif</div>
                </div>
            </div>
            
            <div class="strip-card">
                <div class="strip-icon">👨‍🏫</div>
                <div>
                    <div class="strip-val"><?= number_format($total_guru, 0, ',', '.'); ?></div>
                    <div class="strip-lbl">Guru & Staf</div>
                </div>
            </div>

            <div class="strip-card">
                <div class="strip-icon">🏫</div>
                <div>
                    <div class="strip-val"><?= number_format($total_kelas, 0, ',', '.'); ?></div>
                    <div class="strip-lbl">Ruang Kelas</div>
                </div>
            </div>

            <div class="strip-card">
                <div class="strip-icon">🏆</div>
                <div>
                    <div class="strip-val"><?= number_format($total_prestasi, 0, ',', '.'); ?></div>
                    <div class="strip-lbl">Poin Prestasi</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Showcase Grid -->
    <section class="features-section" id="fitur">
        <div class="section-header">
            <h2>✨ Modul & Fitur Unggulan Sistem</h2>
            <p>Dirancang khusus dengan standar enterprise untuk kemudahan tata kelola instansi pendidikan modern.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">📚</div>
                <h3>Manajemen Siswa & Guru</h3>
                <p>Pendataan lengkap data induk siswa dan staf pengajar lengkap dengan filter jurusan, kelas, dan pencarian cepat.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">💳</div>
                <h3>Pencetak Kartu Pelajar</h3>
                <p>Fitur generator dan cetak kartu pelajar otomatis berstandar nasional lengkap dengan barcode & foto siswa.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">📅</div>
                <h3>Presensi & Kehadiran</h3>
                <p>Pencatatan absensi harian siswa secara cepat dan otomatis lengkap dengan rekapitulasi status Hadir, Izin, Sakit, atau Alpa.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🏆</div>
                <h3>Poin & Prestasi Siswa</h3>
                <p>Sistem rekapitulasi poin kedisiplinan serta pencatatan portofolio prestasi akademis & non-akademis siswa.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Visual Analytics & Statistik</h3>
                <p>Grafik interaktif Chart.js untuk memantau sebaran jurusan, tren presensi, serta analisis kinerja sekolah secara komprehensif.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">⚙️</div>
                <h3>Pengaturan & Keamanan Admin</h3>
                <p>Kontrol penuh pengaturan akun administrator, manajemen kredensial login, dan pemantauan informasi server secara langsung.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>© 2026 SMK NEGERI 1 LUMAJANG — School Management System Pro | Black & Gold Luxury Edition. All Rights Reserved.</p>
    </footer>

</body>
</html>