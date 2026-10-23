<?php
/**
 * ==============================================================================
 * SMK NEGERI 1 LUMAJANG - ENTERPRISE MANAGEMENT SYSTEM PRO MAX
 * File          : data_siswa.php (Master Data Siswa dengan Fitur Lengkap)
 * ==============================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

if (!isset($_SESSION['admin_nama'])) {
    header("Location: login.php");
    exit();
}

// Logika Pencarian & Filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$filter_kelas = isset($_GET['kelas']) ? mysqli_real_escape_string($koneksi, $_GET['kelas']) : '';

$query_siswa = "SELECT * FROM data_siswa WHERE 1=1";

if (!empty($search)) {
    $query_siswa .= " AND (Nama_Lengkap LIKE '%$search%' OR NISN LIKE '%$search%' OR Jurusan LIKE '%$search%')";
}

if (!empty($filter_kelas)) {
    $query_siswa .= " AND Kelas = '$filter_kelas'";
}

$query_siswa .= " ORDER BY ID ASC";
$result_siswa = mysqli_query($koneksi, $query_siswa);
$total_rows   = mysqli_num_rows($result_siswa);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Master Data Siswa - SMK Negeri 1 Lumajang Pro Max</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #09090b; --card-bg: #141418; --border-color: #27272a;
            --gold-primary: #d4af37; --gold-gradient: linear-gradient(135deg, #fce080 0%, #d4af37 50%, #aa8c2c 100%);
            --text-main: #f4f4f5; --text-muted: #a1a1aa;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        html, body { width: 100%; min-height: 100vh; background-color: var(--bg-dark); color: var(--text-main); overflow-x: hidden; }
        .app-wrapper { display: flex; width: 100%; min-height: 100vh; }
        .main-content { flex: 1; padding: 28px; width: 100%; max-width: 100%; background: radial-gradient(circle at top right, rgba(212, 175, 55, 0.05), transparent 40%); }
        
        .page-header {
            background: linear-gradient(135deg, #18181b 0%, #09090b 100%);
            border: 1px solid var(--border-color); border-radius: 20px; padding: 24px; margin-bottom: 24px;
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;
        }
        .header-title h1 { font-size: 24px; font-weight: 800; background: var(--gold-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .header-title p { color: var(--text-muted); font-size: 13px; margin-top: 4px; }
        
        /* Action Bar / Search & Filter */
        .action-bar { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 20px; flex-wrap: wrap; }
        .search-filter-form { display: flex; gap: 10px; flex: 1; flex-wrap: wrap; max-width: 700px; }
        .search-input, .filter-select {
            padding: 11px 16px; background: #141418; border: 1px solid var(--border-color);
            border-radius: 12px; color: #fff; font-size: 13px; outline: none;
        }
        .search-input { flex: 1; min-width: 220px; }
        .search-input:focus, .filter-select:focus { border-color: var(--gold-primary); }
        
        .btn-gold {
            background: var(--gold-gradient); color: #000; font-weight: 700; padding: 11px 18px;
            border-radius: 12px; text-decoration: none; border: none; cursor: pointer; font-size: 13px;
            white-space: nowrap; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-gold:hover { opacity: 0.9; }

        .card-box { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 20px; padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.4); }
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .custom-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13.5px; min-width: 800px; }
        .custom-table th { background: #18181b; color: var(--gold-primary); padding: 14px 16px; font-weight: 700; border-bottom: 1px solid var(--border-color); text-transform: uppercase; font-size: 11px; letter-spacing: 0.8px; }
        .custom-table td { padding: 14px 16px; border-bottom: 1px solid #1f1f23; color: #e4e4e7; vertical-align: middle; }
        .custom-table tr:hover td { background: rgba(255, 255, 255, 0.02); }
        
        .badge-kelas { background: rgba(212, 175, 55, 0.1); color: var(--gold-primary); border: 1px solid rgba(212, 175, 55, 0.3); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        
        .btn-action { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; text-decoration: none; display: inline-block; margin-right: 4px; margin-bottom: 4px; }
        .btn-detail { background: rgba(212, 175, 55, 0.15); color: var(--gold-primary); border: 1px solid rgba(212, 175, 55, 0.3); }
        .btn-edit { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
        .btn-delete { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }

        @media (max-width: 768px) {
            .main-content { padding: 16px; }
            .action-bar { flex-direction: column; align-items: stretch; }
            .search-filter-form { max-width: 100%; flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="app-wrapper">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            
            <!-- Header Halaman -->
            <div class="page-header">
                <div class="header-title">
                    <h1>👨‍🎓 Master Data Siswa Pro Max</h1>
                    <p>SMK Negeri 1 Lumajang — Database Peserta Didik Aktif (Total Ditampilkan: <?= $total_rows; ?> Siswa)</p>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="tambah_siswa.php" class="btn-gold">➕ Tambah Siswa Baru</a>
                    <button onclick="window.print();" class="btn-gold" style="background: #27272a; color: #fff; border: 1px solid var(--border-color);">🖨️ Cetak Laporan</button>
                </div>
            </div>

            <!-- Bar Pencarian & Filter Kelas -->
            <div class="action-bar">
                <form method="GET" action="data_siswa.php" class="search-filter-form">
                    <input type="text" name="search" class="search-input" placeholder="Cari NISN, Nama Siswa, atau Jurusan..." value="<?= htmlspecialchars($search); ?>">
                    <select name="kelas" class="filter-select">
                        <option value="">-- Semua Kelas --</option>
                        <option value="X" <?= $filter_kelas == 'X' ? 'selected' : ''; ?>>Kelas X</option>
                        <option value="XI" <?= $filter_kelas == 'XI' ? 'selected' : ''; ?>>Kelas XI</option>
                        <option value="XII" <?= $filter_kelas == 'XII' ? 'selected' : ''; ?>>Kelas XII</option>
                    </select>
                    <button type="submit" class="btn-gold">🔍 Filter</button>
                    <?php if (!empty($search) || !empty($filter_kelas)): ?>
                        <a href="data_siswa.php" class="btn-gold" style="background: #27272a; color: #fff;">Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Tabel Data Siswa -->
            <div class="card-box">
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NISN</th>
                                <th>Nama Lengkap Siswa</th>
                                <th>Kelas</th>
                                <th>Jurusan</th>
                                <th>Aksi & Kartu Pelajar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($total_rows > 0) {
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result_siswa)) {
                                    ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><strong style="color: var(--gold-primary);"><?= htmlspecialchars($row['NISN'] ?? ''); ?></strong></td>
                                        <td><strong><?= htmlspecialchars($row['Nama_Lengkap'] ?? ''); ?></strong></td>
                                        <td><span class="badge-kelas"><?= htmlspecialchars($row['Kelas'] ?? ''); ?></span></td>
                                        <td><?= htmlspecialchars($row['Jurusan'] ?? ''); ?></td>
                                        <td>
                                            <a href="detail_siswa.php?id=<?= $row['ID']; ?>" class="btn-action btn-detail">🪪 Kartu & Detail</a>
                                            <a href="edit_siswa.php?id=<?= $row['ID']; ?>" class="btn-action btn-edit">✏️ Edit</a>
                                            <a href="hapus_siswa.php?id=<?= $row['ID']; ?>" class="btn-action btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data siswa ini?');">🗑️ Hapus</a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">Data siswa tidak ditemukan dengan kriteria tersebut.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</body>
</html>