<?php
/**
 * ==============================================================================
 * SMK NEGERI 1 LUMAJANG - ENTERPRISE MANAGEMENT SYSTEM PRO MAX
 * File          : kelas.php (Manajemen Ruang Kelas & Program Keahlian)
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

// Pastikan tabel data_kelas ada di database
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS data_kelas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kelas VARCHAR(50) NOT NULL,
    jurusan VARCHAR(50) NOT NULL,
    kapasitas INT DEFAULT 36,
    wali_kelas VARCHAR(100) DEFAULT '-'
)");

// Proses Tambah Kelas Baru
if (isset($_POST['simpan_kelas'])) {
    $nama_kelas = mysqli_real_escape_string($koneksi, $_POST['nama_kelas']);
    $jurusan    = mysqli_real_escape_string($koneksi, $_POST['jurusan']);
    $kapasitas  = intval($_POST['kapasitas']);
    $wali_kelas = mysqli_real_escape_string($koneksi, $_POST['wali_kelas']);

    $insert = mysqli_query($koneksi, "INSERT INTO data_kelas (nama_kelas, jurusan, kapasitas, wali_kelas) VALUES ('$nama_kelas', '$jurusan', '$kapasitas', '$wali_kelas')");
    if ($insert) {
        echo "<script>alert('Kelas baru berhasil ditambahkan!'); window.location='kelas.php';</script>";
        exit();
    } else {
        $error = "Gagal menyimpan kelas: " . mysqli_error($koneksi);
    }
}

// Proses Hapus Kelas
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM data_kelas WHERE id = $id_hapus");
    echo "<script>alert('Kelas berhasil dihapus!'); window.location='kelas.php';</script>";
    exit();
}

// Logika Pencarian
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$query_kelas = "SELECT * FROM data_kelas";
if (!empty($search)) {
    $query_kelas .= " WHERE nama_kelas LIKE '%$search%' OR jurusan LIKE '%$search%' OR wali_kelas LIKE '%$search%'";
}
$query_kelas .= " ORDER BY id DESC";
$result_kelas = mysqli_query($koneksi, $query_kelas);
$total_kelas = $result_kelas ? mysqli_num_rows($result_kelas) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Manajemen Kelas & Jurusan - SMK Negeri 1 Lumajang Pro Max</title>
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
        
        .bento-grid { display: grid; grid-template-columns: 1fr; gap: 24px; }
        .card-box { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 20px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.4); }
        .section-title { font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 18px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; display: flex; align-items: center; gap: 8px; }
        
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 18px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-label { font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .form-input, .form-select {
            padding: 12px 16px; background: #09090b; border: 1px solid var(--border-color);
            border-radius: 12px; color: #fff; font-size: 13.5px; outline: none;
        }
        .form-input:focus, .form-select:focus { border-color: var(--gold-primary); box-shadow: 0 0 10px rgba(212,175,55,0.15); }
        
        .btn-gold {
            background: var(--gold-gradient); color: #000; font-weight: 700; padding: 12px 20px;
            border-radius: 12px; text-decoration: none; border: none; cursor: pointer; font-size: 13.5px;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%;
        }
        .btn-gold:hover { opacity: 0.9; }

        .action-bar { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 16px; flex-wrap: wrap; }
        .search-box { display: flex; gap: 8px; flex: 1; max-width: 350px; }
        
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .custom-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13.5px; min-width: 750px; }
        .custom-table th { background: #18181b; color: var(--gold-primary); padding: 14px 16px; font-weight: 700; border-bottom: 1px solid var(--border-color); text-transform: uppercase; font-size: 11px; letter-spacing: 0.8px; }
        .custom-table td { padding: 14px 16px; border-bottom: 1px solid #1f1f23; color: #e4e4e7; vertical-align: middle; }
        .custom-table tr:hover td { background: rgba(255, 255, 255, 0.02); }
        
        .badge-kelas { background: rgba(212, 175, 55, 0.1); color: var(--gold-primary); border: 1px solid rgba(212, 175, 55, 0.3); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .btn-action { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; text-decoration: none; display: inline-block; }
        .btn-delete { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }

        @media (max-width: 768px) { .main-content { padding: 16px; } .form-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="app-wrapper">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <div class="page-header">
                <div class="header-title">
                    <h1>🏫 Manajemen Ruang Kelas & Program Keahlian</h1>
                    <p>SMK Negeri 1 Lumajang — Pengelolaan Rombongan Belajar & Kapasitas</p>
                </div>
            </div>

            <div class="bento-grid">
                <!-- Form Tambah Kelas Baru -->
                <div class="card-box">
                    <div class="section-title">➕ Tambah Rombongan Kelas Baru</div>
                    <?php if (isset($error)) echo "<div style='color: #f87171; margin-bottom: 15px; font-size: 13px;'>$error</div>"; ?>
                    <form method="POST" action="">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Nama Kelas</label>
                                <input type="text" name="nama_kelas" class="form-input" placeholder="Contoh: X RPL 1" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Program Keahlian</label>
                                <select name="jurusan" class="form-select" required>
                                    <option value="RPL">Rekayasa Perangkat Lunak (RPL)</option>
                                    <option value="TKJ">Teknik Komputer & Jaringan (TKJ)</option>
                                    <option value="DKV">Desain Komunikasi Visual (DKV)</option>
                                    <option value="AKL">Akuntansi & Keuangan Lembaga (AKL)</option>
                                    <option value="MPLB">Manajemen Perkantoran (MPLB)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Wali Kelas</label>
                                <input type="text" name="wali_kelas" class="form-input" placeholder="Nama Guru Wali Kelas" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Kapasitas Maksimal Siswa</label>
                                <input type="number" name="kapasitas" class="form-input" value="36" required>
                            </div>
                        </div>
                        <button type="submit" name="simpan_kelas" class="btn-gold">💾 Simpan Data Kelas</button>
                    </form>
                </div>

                <!-- Daftar Kelas & Kapasitas -->
                <div class="card-box">
                    <div class="action-bar">
                        <div class="section-title" style="margin-bottom: 0; border: none; padding: 0;">📋 Daftar Kelas & Kapasitas (Total: <?= $total_kelas; ?> Kelas)</div>
                        <form method="GET" action="kelas.php" class="search-box">
                            <input type="text" name="search" class="form-input" placeholder="Cari kelas, jurusan, wali kelas..." value="<?= htmlspecialchars($search); ?>" style="padding: 9px 14px; font-size: 12.5px;">
                            <button type="submit" class="btn-gold" style="width: auto; padding: 9px 14px; font-size: 12.5px;">Cari</button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Kelas</th>
                                    <th>Jurusan</th>
                                    <th>Wali Kelas</th>
                                    <th>Kapasitas Maksimal</th>
                                    <th>Aksi Operasional</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if ($result_kelas && $total_kelas > 0) {
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($result_kelas)) {
                                        ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><span class="badge-kelas"><?= htmlspecialchars($row['nama_kelas']); ?></span></td>
                                            <td><strong><?= htmlspecialchars($row['jurusan']); ?></strong></td>
                                            <td><?= htmlspecialchars($row['wali_kelas'] ?? '-'); ?></td>
                                            <td><?= htmlspecialchars($row['kapasitas']); ?> Siswa</td>
                                            <td>
                                                <a href="kelas.php?hapus=<?= $row['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Yakin ingin menghapus kelas ini?');">🗑️ Hapus</a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">Belum ada data kelas yang terdaftar.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>