<?php
/**
 * ==============================================================================
 * SMK NEGERI 1 LUMAJANG - ENTERPRISE MANAGEMENT SYSTEM PRO MAX
 * File          : poin_prestasi.php (Manajemen Poin & Catatan Kedisiplinan Siswa)
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

// Cek struktur tabel data_siswa secara dinamis
$cek_kolom = mysqli_query($koneksi, "DESCRIBE data_siswa");
$kolom_tersedia = [];
while($col = mysqli_fetch_assoc($cek_kolom)) {
    $kolom_tersedia[] = $col['Field'];
}

$pk_col = in_array('ID', $kolom_tersedia) ? 'ID' : (in_array('id', $kolom_tersedia) ? 'id' : 'nisn');
$nama_col = in_array('Nama_Lengkap', $kolom_tersedia) ? 'Nama_Lengkap' : (in_array('nama', $kolom_tersedia) ? 'nama' : 'nama_siswa');
$nisn_col = in_array('NISN', $kolom_tersedia) ? 'NISN' : 'nisn';
$kelas_col = in_array('Kelas', $kolom_tersedia) ? 'Kelas' : 'kelas';

// Buat tabel poin jika belum ada
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS data_poin_siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_siswa VARCHAR(50) NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    judul VARCHAR(150) NOT NULL,
    poin INT NOT NULL,
    tanggal DATE NOT NULL,
    keterangan TEXT
)");

// Proses Simpan Record Baru
if (isset($_POST['simpan_poin'])) {
    $id_siswa   = mysqli_real_escape_string($koneksi, $_POST['id_siswa']);
    $kategori   = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $judul      = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $poin       = intval($_POST['poin']);
    $tanggal    = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    $insert = mysqli_query($koneksi, "INSERT INTO data_poin_siswa (id_siswa, kategori, judul, poin, tanggal, keterangan) VALUES ('$id_siswa', '$kategori', '$judul', $poin, '$tanggal', '$keterangan')");
    
    if ($insert) {
        echo "<script>alert('Record berhasil disimpan!'); window.location='poin_prestasi.php';</script>";
        exit();
    } else {
        $error = "Gagal menyimpan: " . mysqli_error($koneksi);
    }
}

// Proses Hapus
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM data_poin_siswa WHERE id = $id_hapus");
    echo "<script>alert('Record berhasil dihapus!'); window.location='poin_prestasi.php';</script>";
    exit();
}

// Ambil daftar siswa
$query_siswa_list = mysqli_query($koneksi, "SELECT * FROM data_siswa ORDER BY $nama_col ASC");

// Ambil riwayat poin
$query_riwayat = "SELECT p.*, s.$nama_col as nama_siswa, s.$nisn_col as nisn_siswa, s.$kelas_col as kelas_siswa FROM data_poin_siswa p JOIN data_siswa s ON p.id_siswa = s.$pk_col ORDER BY p.id DESC";
$result_riwayat = mysqli_query($koneksi, $query_riwayat);
$total_riwayat = $result_riwayat ? mysqli_num_rows($result_riwayat) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Poin & Prestasi Siswa - SMK Negeri 1 Lumajang Pro Max</title>
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
        
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 16px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-label { font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .form-input, .form-select, .form-textarea {
            padding: 12px 16px; background: #09090b; border: 1px solid var(--border-color);
            border-radius: 12px; color: #fff; font-size: 13.5px; outline: none; width: 100%;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: var(--gold-primary); box-shadow: 0 0 10px rgba(212,175,55,0.15); }
        .form-textarea { resize: vertical; min-height: 80px; }
        
        .btn-gold {
            background: var(--gold-gradient); color: #000; font-weight: 700; padding: 12px 20px;
            border-radius: 12px; text-decoration: none; border: none; cursor: pointer; font-size: 13.5px;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%;
        }
        .btn-gold:hover { opacity: 0.9; }

        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .custom-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13.5px; min-width: 850px; }
        .custom-table th { background: #18181b; color: var(--gold-primary); padding: 14px 16px; font-weight: 700; border-bottom: 1px solid var(--border-color); text-transform: uppercase; font-size: 11px; letter-spacing: 0.8px; }
        .custom-table td { padding: 14px 16px; border-bottom: 1px solid #1f1f23; color: #e4e4e7; vertical-align: middle; }
        .custom-table tr:hover td { background: rgba(255, 255, 255, 0.02); }
        
        .badge-prestasi { background: rgba(74, 222, 128, 0.15); color: #4ade80; border: 1px solid rgba(74, 222, 128, 0.3); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-pelanggaran { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        
        .btn-action { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; text-decoration: none; display: inline-block; }
        .btn-delete { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }

        @media (max-width: 768px) { .main-content { padding: 16px; } .form-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar Universal Pro Max -->
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <div class="header-title">
                    <h1>🏆 Poin Kedisiplinan & Catatan Prestasi Siswa</h1>
                    <p>SMK Negeri 1 Lumajang — Sistem Manajemen Bimbingan Konseling (BK)</p>
                </div>
            </div>

            <div class="bento-grid">
                <!-- Form Input Record -->
                <div class="card-box">
                    <div class="section-title">📝 Input Record Kedisiplinan / Prestasi Baru</div>
                    <?php if (isset($error)) echo "<div style='color: #f87171; margin-bottom: 15px; font-size: 13px;'>$error</div>"; ?>
                    <form method="POST" action="">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Pilih Siswa</label>
                                <select name="id_siswa" class="form-select" required>
                                    <option value="">-- Pilih Siswa --</option>
                                    <?>
                                    <?php 
                                    if ($query_siswa_list && mysqli_num_rows($query_siswa_list) > 0) {
                                        while ($s = mysqli_fetch_assoc($query_siswa_list)) {
                                            $sid = $s[$pk_col];
                                            $snama = $s[$nama_col];
                                            $skelas = isset($s[$kelas_col]) ? $s[$kelas_col] : '';
                                            echo "<option value='$sid'>" . htmlspecialchars("$snama ($skelas)") . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Kategori Record</label>
                                <select name="kategori" class="form-select" required>
                                    <option value="Prestasi">🏆 Prestasi (+ Poin)</option>
                                    <option value="Pelanggaran">⚠️ Pelanggaran (- Poin)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Judul / Nama Kejadian</label>
                                <input type="text" name="judul" class="form-input" placeholder="Contoh: Juara 1 LKS / Terlambat" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Jumlah Bobot Poin</label>
                                <input type="number" name="poin" class="form-input" placeholder="Contoh: 15" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tanggal Kejadian</label>
                                <input type="date" name="tanggal" class="form-input" value="<?= date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label class="form-label">Keterangan Tambahan / Catatan Guru BK</label>
                            <textarea name="keterangan" class="form-textarea" placeholder="Masukkan detail tambahan atau tindakan pembinaan..."></textarea>
                        </div>
                        <button type="submit" name="simpan_poin" class="btn-gold">💾 Simpan Record Siswa</button>
                    </form>
                </div>

                <!-- Tabel Riwayat -->
                <div class="card-box">
                    <div class="section-title">📋 Riwayat Poin & Catatan Kedisiplinan (Total: <?= $total_riwayat; ?> Record)</div>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Nama Siswa & Kelas</th>
                                    <th>Tipe Kategori</th>
                                    <th>Judul Kejadian</th>
                                    <th>Poin</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if ($result_riwayat && $total_riwayat > 0) {
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($result_riwayat)) {
                                        $is_pres = ($row['kategori'] == 'Prestasi');
                                        ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($row['tanggal']); ?></td>
                                            <td><strong><?= htmlspecialchars($row['nama_siswa']); ?></strong><br><span style="font-size: 11px; color: var(--text-muted);">Kelas: <?= htmlspecialchars($row['kelas_siswa']); ?></span></td>
                                            <td><?= $is_pres ? '<span class="badge-prestasi">🏆 Prestasi</span>' : '<span class="badge-pelanggaran">⚠️ Pelanggaran</span>'; ?></td>
                                            <td><strong><?= htmlspecialchars($row['judul']); ?></strong></td>
                                            <td><span style="font-weight: 800; color: <?= $is_pres ? '#4ade80' : '#f87171'; ?>;"><?= $is_pres ? '+' : '-'; ?><?= $row['poin']; ?></span></td>
                                            <td><?= htmlspecialchars($row['keterangan'] ?: '-'); ?></td>
                                            <td>
                                                <a href="poin_prestasi.php?hapus=<?= $row['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Yakin ingin menghapus record ini?');">🗑️ Hapus</a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="8" style="text-align: center; padding: 30px; color: var(--text-muted);">Belum ada riwayat poin kedisiplinan yang tercatat.</td></tr>';
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