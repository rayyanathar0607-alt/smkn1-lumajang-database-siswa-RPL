<?php
/**
 * ==============================================================================
 * SMK NEGERI 1 LUMAJANG - ENTERPRISE MANAGEMENT SYSTEM PRO
 * File          : laporan.php (Modul Laporan Keuangan, Akademik & Arsip Lengkap)
 * Branding      : Black & Gold Edition (Pro Max Standard)
 * Versi         : 3.2.0 Enterprise
 * ==============================================================================
 */

session_start();
include 'koneksi.php';

// Validasi Keamanan Sesi Admin
if (!isset($_SESSION['admin_logged_in']) \vert{}\vert{}$_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

date_default_timezone_set('Asia/Jakarta');
$admin_nama =$_SESSION['admin_nama'] ?? 'Rayyan Athar Syazani';
$admin_role =$_SESSION['admin_role'] ?? 'Super Administrator';
$pesan_sukses = '';$pesan_error = '';

// Parameter Filter Utama
$jenis_laporan =$_GET['jenis'] ?? 'spp';
$tahun_ajaran   =$_GET['tahun'] ?? '2025/2026';
$filter_jurusan =$_GET['jurusan'] ?? '';
$filter_bulan   =$_GET['bulan'] ?? '';

// Inisialisasi Tabel Log Aktivitas Laporan Jika Belum Ada
$sql_log_table = "CREATE TABLE IF NOT EXISTS log_cetak_laporan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_nama VARCHAR(150) NOT NULL,
    jenis_laporan VARCHAR(100) NOT NULL,
    keterangan TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
@mysqli_query($koneksi,$sql_log_table);

// Logika Perekaman Riwayat Cetak Dokumen (Audit Trail)
if (isset($_POST['catat_log_cetak'])) {$j_lap = mysqli_real_escape_string($koneksi,$_POST['jenis_dokumen']);
    $ket   = mysqli_real_escape_string($koneksi, "Mencetak dokumen resmi instansi jenis: " . $j_lap);$adm   = mysqli_real_escape_string($koneksi,$admin_nama);
    
    $ins_log = mysqli_prepare($koneksi, "INSERT INTO log_cetak_laporan (admin_nama, jenis_laporan, keterangan) VALUES (?, ?, ?)");
    if ($ins_log) {
        mysqli_stmt_bind_param($ins_log, "sss", $adm, $j_lap,$ket);
        mysqli_stmt_execute($ins_log);
        mysqli_stmt_close($ins_log);$pesan_sukses = "Riwayat cetak dokumen berhasil dicatat ke dalam audit trail sistem.";
    }
}

// Mengambil Data Berdasarkan Kategori Laporan
$data_tabel = [];$total_nominal_rekap = 0;

if ($jenis_laporan == 'spp') {$sql_spp = "SELECT * FROM pembayaran_spp WHERE 1=1";
    if (!empty($filter_jurusan)) {
        $j_esc = mysqli_real_escape_string($koneksi, $filter_jurusan);$sql_spp .= " AND jurusan = '$j_esc'";
    }
    if (!empty($filter_bulan)) {
        $b_esc = mysqli_real_escape_string($koneksi, $filter_bulan);$sql_spp .= " AND bulan = '$b_esc'";
    }
    $sql_spp .= " ORDER BY id DESC";
    $q_spp = mysqli_query($koneksi,$sql_spp);
    if ($q_spp) {
        while ($row = mysqli_fetch_assoc($q_spp)) {
            $data_tabel[] =$row;
            if (isset($row['status_bayar']) &&$row['status_bayar'] == 'Lunas') {
                $total_nominal_rekap += floatval($row['nominal'] ?? 0);
            }
        }
    }
} elseif ($jenis_laporan == 'siswa') {$t_s = 'siswa';
    $chk = @mysqli_query($koneksi, "SELECT COUNT(*) FROM data_siswa");
    if ($chk && @mysqli_fetch_row($chk)[0] > 0) { $t_s = 'data_siswa'; }$sql_sis = "SELECT * FROM `$t_s` WHERE 1=1";
    if (!empty($filter_jurusan)) {
        $j_esc = mysqli_real_escape_string($koneksi, $filter_jurusan);$sql_sis .= " AND jurusan = '$j_esc'";
    }
    $sql_sis .= " ORDER BY id DESC";
    $q_sis = mysqli_query($koneksi,$sql_sis);
    if ($q_sis) {
        while ($row = mysqli_fetch_assoc($q_sis)) {
            $data_tabel[] =$row;
        }
    }
} elseif ($jenis_laporan == 'guru') {$sql_gur = "SELECT * FROM data_guru ORDER BY id DESC";
    $q_gur = mysqli_query($koneksi,$sql_gur);
    if ($q_gur) {
        while ($row = mysqli_fetch_assoc($q_gur)) {
            $data_tabel[] =$row;
        }
    }
} elseif ($jenis_laporan == 'absensi') {$sql_abs = "SELECT * FROM pembayaran_spp ORDER BY id DESC LIMIT 50"; // Simulasi data rekap absensi
    $q_abs = mysqli_query($koneksi,$sql_abs);
    if ($q_abs) {
        while ($row = mysqli_fetch_assoc($q_abs)) {
            $data_tabel[] =$row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Cetak Laporan Resmi - SMK Negeri 1 Lumajang</title>
    
    <!-- Google Fonts Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    
    <!-- Memanggil CSS Animasi Eksternal Pro Max -->
    <link rel="stylesheet" href="animasi.css">

    <style>
        /* ==============================================================================
           STYLING UTAMA LAPORAN - BLACK & GOLD ENTERPRISE PRO MAX
           ============================================================================== */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg-primary: #08080a;
            --bg-card: #111114;
            --bg-hover: #18181c;
            --gold-primary: #d4af37;
            --gold-light: #fff2be;
            --gold-dark: #aa8c2c;
            --gold-gradient: linear-gradient(135deg, #fff2be 0%, #d4af37 50%, #aa8c2c 100%);
            --text-main: #f4f4f5;
            --text-muted: #71717a;
            --border-gold: rgba(212, 175, 55, 0.25);
            --border-subtle: #27272a;
            --success: #22c55e;
            --danger: #ef4444;
            --shadow-enterprise: 0 10px 30px rgba(0, 0, 0, 0.6);
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Container Konten Sebelah Kanan Sidebar */
        .main-wrapper {
            flex: 1;
            padding: 35px 50px;
            overflow-y: auto;
            background-color: var(--bg-primary);
        }

        /* Navbar Atas */
        .top-navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--border-subtle);
        }
        .page-title {
            color: #ffffff;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .breadcrumb {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 4px;
            font-weight: 500;
        }
        .breadcrumb span {
            color: var(--gold-primary);
            font-weight: 700;
        }

        /* Notifikasi Alert */
        .alert-box {
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 600;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .alert-success { background: #14331e; color: #4ade80; border: 1px solid var(--success); }
        .alert-error { background: #3d1414; color: #f87171; border: 1px solid var(--danger); }

        /* Toolbar Filter & Kontrol Cetak */
        .report-control-panel {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 16px;
            padding: 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            box-shadow: var(--shadow-enterprise);
        }
        .control-group {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .control-group label {
            font-size: 11px;
            text-transform: uppercase;
            color: var(--text-muted);
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .control-group select, .control-group input {
            background: #18181b;
            border: 1px solid #3f3f46;
            color: #ffffff;
            padding: 11px 15px;
            border-radius: 10px;
            font-size: 13px;
            font-family: inherit;
            transition: all 0.3s ease;
        }
        .control-group select:focus, .control-group input:focus {
            border-color: var(--gold-primary);
            outline: none;
            box-shadow: 0 0 12px rgba(212, 175, 55, 0.25);
        }

        .btn-gold-action {
            background: var(--gold-gradient);
            color: #09090b;
            border: none;
            padding: 12px 26px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 13.5px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 18px rgba(212, 175, 55, 0.35);
            transition: all 0.3s ease;
        }
        .btn-gold-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.5);
        }

        /* Lembar Dokumen Resmi (Pratinjau Kertas A4) */
        .document-paper-sheet {
            background: #ffffff;
            color: #000000;
            padding: 55px 60px;
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.7);
            max-width: 1000px;
            margin: 0 auto 40px auto;
            position: relative;
        }

        /* Kop Surat Instansi Resmi */
        .official-kop-surat {
            display: flex;
            align-items: center;
            border-bottom: 3.5px double #000000;
            padding-bottom: 20px;
            margin-bottom: 30px;
            text-align: center;
        }
        .kop-logo-img {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }
        .kop-text-center {
            flex: 1;
        }
        .kop-text-center h3 {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #111;
        }
        .kop-text-center h2 {
            font-size: 18px;
            font-weight: 900;
            text-transform: uppercase;
            margin: 3px 0;
            color: #000;
            letter-spacing: 0.5px;
        }
        .kop-text-center p {
            font-size: 10.5px;
            color: #333;
            font-weight: 500;
        }

        /* Judul Laporan di dalam Surat */
        .document-title-section {
            text-align: center;
            margin-bottom: 30px;
        }
        .document-title-section h4 {
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
            text-decoration: underline;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }
        .document-title-section p {
            font-size: 11.5px;
            color: #555;
            font-weight: 600;
        }

        /* Ringkasan Statistik Laporan */
        .doc-summary-strip {
            display: flex;
            justify-content: space-between;
            background: #f3f4f6;
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid #e5e7eb;
        }

        /* Tabel Cetak Dokumen */
        .document-data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11.5px;
        }
        .document-data-table th {
            background-color: #000000;
            color: #ffffff;
            padding: 10px 12px;
            text-transform: uppercase;
            font-weight: 700;
            border: 1px solid #000000;
            text-align: left;
            letter-spacing: 0.4px;
        }
        .document-data-table td {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            color: #1f2937;
        }
        .document-data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        /* Tanda Tangan Pejabat Sekolah */
        .official-signature-area {
            margin-top: 50px;
            display: flex;
            justify-content: flex-end;
            text-align: center;
        }
        .signature-block {
            width: 260px;
            font-size: 12px;
            color: #111;
        }
        .signature-block .space-line {
            height: 75px;
        }
        .signature-block .official-name {
            font-weight: 800;
            text-decoration: underline;
            font-size: 13px;
        }

        /* ==============================================================================
           PRINT MEDIA QUERY (Pengaturan Otomatis Saat Tombol Cetak Ditekan)
           ============================================================================== */
        @media print {
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                display: block !important;
            }
            .sidebar, .top-navbar, .report-control-panel, .alert-box {
                display: none !important;
            }
            .main-wrapper {
                padding: 0 !important;
                max-width: 100% !important;
                background-color: #ffffff !important;
            }
            .document-paper-sheet {
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                border-radius: 0 !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body>

    <!-- Memanggil Komponen Sidebar Eksternal -->
    <?php include 'sidebar.php'; ?>

    <!-- Wrapper Konten Utama -->
    <div class="main-wrapper">
        
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div>
                <div class="breadcrumb">Administrasi Instansi / <span>Pusat Laporan Resmi</span></div>
                <h1 class="page-title">🖨️ Pusat Dokumentasi & Pelaporan Resmi Instansi</h1>
            </div>
            <div>
                <span style="font-size: 13px; color: var(--text-muted);">Status Sesi: <b style="color: var(--gold-primary);">Aktif (<?= htmlspecialchars($admin_role); ?>)</b></span>
            </div>
        </div>

        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert-box alert-success">✅ <?= htmlspecialchars($pesan_sukses); ?></div>
        <?php endif; ?>

        <?php if (!empty($pesan_error)): ?>
            <div class="alert-box alert-error">⚠️ <?= htmlspecialchars($pesan_error); ?></div>
        <?php endif; ?>

        <!-- Panel Kontrol Filter & Aksi Cetak -->
        <form method="GET" class="report-control-panel">
            <div class="control-group">
                <label>Jenis Dokumen:</label>
                <select name="jenis" onchange="this.form.submit()">
                    <option value="spp" <?= ($jenis_laporan == 'spp') ? 'selected' : ''; ?>>Laporan Keuangan SPP Siswa</option>
                    <option value="siswa" <?= ($jenis_laporan == 'siswa') ? 'selected' : ''; ?>>Laporan Data Induk Siswa</option>
                    <option value="guru" <?= ($jenis_laporan == 'guru') ? 'selected' : ''; ?>>Laporan Data Induk Guru & Staf</option>
                    <option value="absensi" <?= ($jenis_laporan == 'absensi') ? 'selected' : ''; ?>>Laporan Rekapitulasi Presensi</option>
                </select>

                <label>Tahun Ajaran:</label>
                <select name="tahun" onchange="this.form.submit()">
                    <option value="2025/2026" <?= ($tahun_ajaran == '2025/2026') ? 'selected' : ''; ?>>2025/2026</option>
                    <option value="2024/2025" <?= ($tahun_ajaran == '2024/2025') ? 'selected' : ''; ?>>2024/2025</option>
                </select>

                <?php if ($jenis_laporan == 'spp' \vert{}\vert{}$jenis_laporan == 'siswa'): ?>
                    <label>Jurusan:</label>
                    <select name="jurusan" onchange="this.form.submit()">
                        <option value="">Semua Jurusan</option>
                        <option value="RPL" <?= ($filter_jurusan == 'RPL') ? 'selected' : ''; ?>>RPL</option>
                        <option value="TKJ" <?= ($filter_jurusan == 'TKJ') ? 'selected' : ''; ?>>TKJ</option>
                        <option value="PSPTV" <?= ($filter_jurusan == 'PSPTV') ? 'selected' : ''; ?>>PSPTV</option>
                        <option value="Teknik Grafika" <?= ($filter_jurusan == 'Teknik Grafika') ? 'selected' : ''; ?>>Teknik Grafika</option>
                        <option value="Akuntansi" <?= ($filter_jurusan == 'Akuntansi') ? 'selected' : ''; ?>>Akuntansi</option>
                    </select>
                <?php endif; ?>
            </div>

            <div class="control-group">
                <!-- Tombol Print Dokumen -->
                <button type="button" onclick="window.print();" class="btn-gold-action">
                    🖨️ CETAK DOKUMEN RESMI
                </button>
            </div>
        </form>

        <!-- Lembar Pratinjau Dokumen Siap Cetak (A4 Standard) -->
        <div class="document-paper-sheet">
            
            <!-- Kop Surat Resmi Instansi -->
            <div class="official-kop-surat">
                <img src="smk_negeri_1_lumajang-removebg-preview.png" alt="Logo SMKN 1 Lumajang" class="kop-logo-img" onerror="this.onerror=null; this.src='logo.png';">
                <div class="kop-text-center">
                    <h3>Pemerintah Provinsi Jawa Timur &bull; Dinas Pendidikan</h3>
                    <h2>SMK Negeri 1 Lumajang</h2>
                    <p>Jl. Brantas No. 4, Lumajang, Jawa Timur &bull; Telepon: (0334) 881123 &bull; Email: admin@smkn1lumajang.sch.id</p>
                </div>
            </div>

            <!-- Judul & Keterangan Laporan -->
            <div class="document-title-section">
                <h4>
                    <?php
                    if ($jenis_laporan == 'spp') echo "LAPORAN REKAPITULASI PEMBAYARAN SPP SISWA";
                    elseif ($jenis_laporan == 'siswa') echo "LAPORAN DATA INDUK SISWA AKTIF INSTANSI";
                    elseif ($jenis_laporan == 'guru') echo "LAPORAN DATA GURU DAN TENAGA KEPENDIDIKAN";
                    else echo "LAPORAN REKAPITULASI PRESENSI & KEDISIPLINAN";
                    ?>
                </h4>
                <p>Tahun Ajaran: <?= htmlspecialchars($tahun_ajaran); ?> &bull; Tanggal Dokumen: <?= date('d F Y'); ?></p>
            </div>

            <?php if ($jenis_laporan == 'spp'): ?>
                <div class="doc-summary-strip">
                    <span>Total Rekod Data: <?= count($data_tabel); ?> Baris Tagihan</span>
                    <span style="color: #047857;">Total SPP Terkumpul (Lunas): Rp <?= number_format($total_nominal_rekap, 0, ',', '.'); ?></span>
                </div>
            <?php endif; ?>

            <!-- Tabel Data Utama Laporan -->
            <table class="document-data-table">
                <thead>
                    <?php if ($jenis_laporan == 'spp'): ?>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 15%;">NIS</th>
                            <th style="width: 25%;">Nama Siswa</th>
                            <th style="width: 20%;">Kelas / Jurusan</th>
                            <th style="width: 15%;">Periode</th>
                            <th style="width: 10%;">Nominal</th>
                            <th style="width: 10%;">Status</th>
                        </tr>
                    <?php elseif ($jenis_laporan == 'siswa'): ?>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 20%;">NIS / NISN</th>
                            <th style="width: 35%;">Nama Lengkap Siswa</th>
                            <th style="width: 15%;">Kelas</th>
                            <th style="width: 15%;">Jurusan</th>
                            <th style="width: 10%;">JK</th>
                        </tr>
                    <?php elseif ($jenis_laporan == 'guru'): ?>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 25%;">NIP / ID Pegawai</th>
                            <th style="width: 35%;">Nama Guru & Gelar</th>
                            <th style="width: 20%;">Mata Pelajaran</th>
                            <th style="width: 15%;">Jabatan</th>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 20%;">NIS</th>
                            <th style="width: 30%;">Nama Siswa</th>
                            <th style="width: 20%;">Periode</th>
                            <th style="width: 25%;">Keterangan Kehadiran</th>
                        </tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php
                    if (!empty($data_tabel)) {$no = 1;
                        foreach ($data_tabel as$row) {
                            echo "<tr>";
                            echo "<td>" . $no++ . "</td>";
                            if ($jenis_laporan == 'spp') {
                                echo "<td style='font-family: monospace; font-weight: 700;'>" . htmlspecialchars($row['nis']) . "</td>";
                                echo "<td><b>" . htmlspecialchars($row['nama_siswa']) . "</b></td>";
                                echo "<td>" . htmlspecialchars($row['kelas']) . " &bull; " . htmlspecialchars($row['jurusan']) . "</td>";
                                echo "<td><b>" . htmlspecialchars($row['bulan']) . "</b> " . $row['tahun'] . "</td>";
                                echo "<td style='font-family: monospace;'>Rp " . number_format($row['nominal'], 0, ',', '.') . "</td>";
                                echo "<td><b>" . htmlspecialchars($row['status_bayar']) . "</b></td>";
                            } elseif ($jenis_laporan == 'siswa') {
                                echo "<td style='font-family: monospace; font-weight: 700;'>" . htmlspecialchars($row['nis'] ?? $row['nisn'] ?? '-') . "</td>";
                                echo "<td><b>" . htmlspecialchars($row['nama'] ?? $row['nama_siswa'] ?? '-') . "</b></td>";
                                echo "<td>" . htmlspecialchars($row['kelas'] ?? '-') . "</td>";
                                echo "<td>" . htmlspecialchars($row['jurusan'] ?? '-') . "</td>";
                                echo "<td>" . htmlspecialchars($row['jk'] ?? $row['jenis_kelamin'] ?? '-') . "</td>";
                            } elseif ($jenis_laporan == 'guru') {
                                echo "<td style='font-family: monospace; font-weight: 700;'>" . htmlspecialchars($row['nip'] ?? '-') . "</td>";
                                echo "<td><b>" . htmlspecialchars($row['nama'] ?? '-') . "</b></td>";
                                echo "<td>" . htmlspecialchars($row['mapel'] ?? '-') . "</td>";
                                echo "<td>" . htmlspecialchars($row['jabatan'] ?? 'Guru Mata Pelajaran') . "</td>";
                            } else {
                                echo "<td style='font-family: monospace;'>" . htmlspecialchars($row['nis'] ?? '-') . "</td>";
                                echo "<td><b>" . htmlspecialchars($row['nama_siswa'] ?? '-') . "</b></td>";
                                echo "<td>" . htmlspecialchars($row['bulan'] ?? '-') . " " . ($row['tahun'] ?? '') . "</td>";
                                echo "<td><span style='color: #047857; font-weight: 700;'>● Hadir Tepat Waktu</span></td>";
                            }
                            echo "</tr>";
                        }
                    } else {
                        $col_cnt = ($jenis_laporan == 'spp') ? 7 : 6;
                        echo "<tr><td colspan='$col_cnt' style='text-align: center; padding: 30px; color: #6b7280;'>Belum terdapat catatan data yang tersedia untuk kategori laporan ini di database.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <!-- Area Tanda Tangan Pengesahan Resmi -->
            <div class="official-signature-area">
                <div class="signature-block">
                    <p>Lumajang, <?= date('d F Y'); ?></p>
                    <p>Mengetahui,<br>Kepala SMK Negeri 1 Lumajang</p>
                    <div class="space-line"></div>
                    <p class="official-name"><b>Dr. H. Administrasi, M.Pd.</b></p>
                    <p>NIP. 19720512 199803 1 004</p>
                </div>
            </div>

        </div>

    </div>
</body>
</html>