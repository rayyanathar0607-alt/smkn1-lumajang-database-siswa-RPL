<?php
/**
 * ==============================================================================
 * SMK NEGERI 1 LUMAJANG - ENTERPRISE MANAGEMENT SYSTEM PRO MAX
 * File          : index.php (Dashboard Utama Mobile-Responsive & Bento Grid)
 * ==============================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

// Verifikasi Sesi Login
if (!isset($_SESSION['admin_nama'])) {
    header("Location: login.php");
    exit();
}

// Hitung Statistik Real-Time dari Database
$total_siswa    = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM data_siswa"));
$total_guru     = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM data_guru"));
$total_kelas    = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM data_kelas"));
$total_prestasi = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM poin_siswa"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard Utama - SMK Negeri 1 Lumajang Pro Max</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #09090b;
            --card-bg: #141418;
            --border-color: #27272a;
            --gold-primary: #d4af37;
            --gold-gradient: linear-gradient(135deg, #fce080 0%, #d4af37 50%, #aa8c2c 100%);
            --text-main: #f4f4f5;
            --text-muted: #a1a1aa;
        }

        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
        }

        html, body {
            width: 100%;
            min-height: 100vh;
            background-color: var(--bg-dark); 
            color: var(--text-main);
            overflow-x: hidden;
        }

        /* Container Layout Utama */
        .app-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .main-content { 
            flex: 1; 
            padding: 28px; 
            width: 100%;
            max-width: 100%;
            background: radial-gradient(circle at top right, rgba(212, 175, 55, 0.05), transparent 40%); 
            overflow-x: hidden;
        }
        
        /* Header Banner */
        .dashboard-header {
            background: linear-gradient(135deg, #18181b 0%, #09090b 100%);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            gap: 16px;
        }

        .header-title h1 { 
            font-size: 24px; 
            font-weight: 800; 
            background: var(--gold-gradient); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            line-height: 1.3;
        }

        .header-title p { 
            color: var(--text-muted); 
            font-size: 13px; 
            margin-top: 4px; 
        }

        .badge-pro { 
            background: var(--gold-gradient); 
            color: #000; 
            font-weight: 800; 
            padding: 6px 14px; 
            border-radius: 30px; 
            font-size: 11px; 
            text-transform: uppercase; 
            letter-spacing: 1px;
            white-space: nowrap;
        }

        /* Bento Grid Stats */
        .bento-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); 
            gap: 18px; 
            margin-bottom: 24px; 
        }

        .bento-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 18px;
            padding: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .bento-card:hover { 
            border-color: rgba(212, 175, 55, 0.5); 
            transform: translateY(-3px); 
            box-shadow: 0 12px 24px rgba(0,0,0,0.4); 
        }

        .bento-card::before { 
            content: ''; 
            position: absolute; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 3px; 
            background: var(--gold-gradient); 
            opacity: 0; 
            transition: 0.3s; 
        }

        .bento-card:hover::before { opacity: 1; }
        
        .card-icon { font-size: 28px; margin-bottom: 10px; }
        .card-value { font-size: 32px; font-weight: 800; color: #ffffff; }
        .card-label { font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }

        /* Content Section Grid */
        .content-grid { 
            display: grid; 
            grid-template-columns: 2fr 1fr; 
            gap: 20px; 
        }

        .section-box { 
            background: var(--card-bg); 
            border: 1px solid var(--border-color); 
            border-radius: 18px; 
            padding: 20px; 
            width: 100%;
            overflow-x: auto;
        }

        .section-title { 
            font-size: 15px; 
            font-weight: 700; 
            color: #fff; 
            margin-bottom: 16px; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
        }

        /* Activity List */
        .activity-item { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            padding: 12px 0; 
            border-bottom: 1px solid #1f1f23; 
            gap: 12px;
        }

        .activity-item:last-child { border-bottom: none; }
        .activity-info h5 { font-size: 13px; font-weight: 600; color: #e4e4e7; }
        .activity-info p { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
        .badge-status { 
            background: rgba(34, 197, 94, 0.15); 
            color: #4ade80; 
            border: 1px solid rgba(34, 197, 94, 0.3); 
            font-size: 10px; 
            font-weight: 700; 
            padding: 4px 8px; 
            border-radius: 20px; 
            white-space: nowrap;
        }

        /* Responsive Optimization Khusus HP */
        @media (max-width: 992px) {
            .app-wrapper {
                flex-direction: column;
            }

            .main-content { 
                padding: 16px; 
            }

            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                padding: 18px;
            }

            .header-title h1 {
                font-size: 20px;
            }

            .bento-grid { 
                grid-template-columns: repeat(2, 1fr); 
                gap: 12px;
            }

            .content-grid { 
                grid-template-columns: 1fr; 
                gap: 16px;
            }
        }

        @media (max-width: 480px) {
            .bento-grid { 
                grid-template-columns: 1fr; 
            }

            .activity-item {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>

    <div class="app-wrapper">
        <!-- Sidebar Terpadu Pro Max -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content Wrapper -->
        <main class="main-content">
            
            <!-- Header Banner -->
            <div class="dashboard-header">
                <div class="header-title">
                    <h1>Selamat Datang di Dashboard Utama</h1>
                    <p>SMK Negeri 1 Lumajang — Pusat Kontrol Operasional & Informasi Terpadu</p>
                </div>
                <span class="badge-pro">PRO MAX v4.0</span>
            </div>

            <!-- Bento Grid Stats -->
            <div class="bento-grid">
                <div class="bento-card">
                    <div class="card-icon">👨‍🎓</div>
                    <div class="card-value"><?= number_format($total_siswa); ?></div>
                    <div class="card-label">Total Siswa Terdaftar</div>
                </div>
                <div class="bento-card">
                    <div class="card-icon">👨‍🏫</div>
                    <div class="card-value"><?= number_format($total_guru); ?></div>
                    <div class="card-label">Tenaga Pendidik & Staf</div>
                </div>
                <div class="bento-card">
                    <div class="card-icon">🏫</div>
                    <div class="card-value"><?= number_format($total_kelas); ?></div>
                    <div class="card-label">Ruang Kelas & Jurusan</div>
                </div>
                <div class="bento-card">
                    <div class="card-icon">🏆</div>
                    <div class="card-value"><?= number_format($total_prestasi); ?></div>
                    <div class="card-label">Poin & Prestasi Siswa</div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <div class="section-box">
                    <div class="section-title"><span>📢</span> Pengumuman Penting Sekolah</div>
                    <div class="activity-item">
                        <div class="activity-info">
                            <h5>Persiapan Ujian Semester Ganjil</h5>
                            <p>Diharapkan seluruh wali kelas memperbarui rekapitulasi presensi siswa.</p>
                        </div>
                        <span class="badge-status">PENTING</span>
                    </div>
                    <div class="activity-item">
                        <div class="activity-info">
                            <h5>Penyelarasan Kurikulum Berbasis Industri</h5>
                            <p>Kerjasama baru dengan mitra industri untuk jurusan Rekayasa Perangkat Lunak.</p>
                        </div>
                        <span class="badge-status">INFO</span>
                    </div>
                </div>

                <div class="section-box">
                    <div class="section-title"><span>⚡</span> Akses Cepat Sistem</div>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <a href="spp.php" style="color: var(--gold-primary); text-decoration: none; font-size: 13px; font-weight: 600; background: #18181b; padding: 12px 14px; border-radius: 10px; border: 1px solid var(--border-color); display: block;">💳 Pembayaran SPP & Keuangan &rarr;</a>
                        <a href="absensi.php" style="color: var(--gold-primary); text-decoration: none; font-size: 13px; font-weight: 600; background: #18181b; padding: 12px 14px; border-radius: 10px; border: 1px solid var(--border-color); display: block;">📅 Input Presensi Kehadiran &rarr;</a>
                        <a href="kartu.php" style="color: var(--gold-primary); text-decoration: none; font-size: 13px; font-weight: 600; background: #18181b; padding: 12px 14px; border-radius: 10px; border: 1px solid var(--border-color); display: block;">🪪 Cetak Kartu Pelajar Digital &rarr;</a>
                    </div>
                </div>
            </div>

        </main>
    </div>

</body>
</html>