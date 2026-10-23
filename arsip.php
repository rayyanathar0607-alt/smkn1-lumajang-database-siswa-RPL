<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

date_default_timezone_set('Asia/Jakarta');
$pesan = '';
$error = '';

// Buat folder uploads jika belum ada
$upload_dir = 'uploads_arsip/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Buat tabel arsip otomatis
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS arsip_dokumen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_dokumen VARCHAR(255) NOT NULL,
    kategori VARCHAR(100) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    ukuran_file VARCHAR(50) NOT NULL,
    tanggal_upload DATE NOT NULL,
    uploader VARCHAR(100) NOT NULL
)");

// Upload Dokumen
if (isset($_POST['upload_arsip'])) {
    $nama_dok  = trim($_POST['nama_dokumen']);
    $kategori  = trim($_POST['kategori']);
    $uploader  = $_SESSION['admin_nama'] ?? 'Administrator';
    $tanggal   = date('Y-m-d');

    if (!empty($_FILES['file_dokumen']['name']) && !empty($nama_dok)) {
        $file_name = time() . '_' . basename($_FILES['file_dokumen']['name']);
        $target_file = $upload_dir . $file_name;
        $file_size = round($_FILES['file_dokumen']['size'] / 1024, 1) . ' KB';

        if (move_uploaded_file($_FILES['file_dokumen']['tmp_name'], $target_file)) {
            $stmt = mysqli_prepare($koneksi, "INSERT INTO arsip_dokumen (nama_dokumen, kategori, file_path, ukuran_file, tanggal_upload, uploader) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssssss", $nama_dok, $kategori, $target_file, $file_size, $tanggal, $uploader);
            if (mysqli_stmt_execute($stmt)) {
                $pesan = "Dokumen arsip berhasil diunggah ke sistem!";
            } else {
                $error = "Gagal menyimpan data arsip ke database.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Gagal mengunggah berkas ke server.";
        }
    } else {
        $error = "Nama dokumen dan berkas wajib diisi!";
    }
}

// Hapus Dokumen Arsip
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);
    $q_f = mysqli_query($koneksi, "SELECT file_path FROM arsip_dokumen WHERE id = $id_hapus");
    if ($q_f && $r_f = mysqli_fetch_assoc($q_f)) {
        if (file_exists($r_f['file_path'])) { unlink($r_f['file_path']); }
    }
    mysqli_query($koneksi, "DELETE FROM arsip_dokumen WHERE id = $id_hapus");
    header("Location: arsip.php");
    exit;
}

$admin_nama = $_SESSION['admin_nama'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Arsip Dokumen - SMK Negeri 1 Lumajang</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background-color: #08080a; color: #e2e8f0; display: flex; min-height: 100vh; }
        .sidebar { width: 285px; background: #111114; border-right: 1px solid rgba(212, 175, 55, 0.25); padding: 24px 18px; display: flex; flex-direction: column; gap: 14px; flex-shrink: 0; }
        .sidebar-brand h2 { color: #d4af37; font-size: 20px; font-weight: 800; background: linear-gradient(135deg, #fff2be 0%, #d4af37 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .sidebar-brand .brand-sub { font-size: 10px; color: #71717a; text-transform: uppercase; }
        .user-profile-card { background: #121215; border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 12px; padding: 12px; display: flex; align-items: center; gap: 10px; }
        .sidebar-section-title { color: #52525b; font-size: 10px; text-transform: uppercase; font-weight: 800; margin-top: 10px; }
        .sidebar-menu { list-style: none; display: flex; flex-direction: column; gap: 5px; }
        .sidebar-menu a { color: #a1a1aa; text-decoration: none; padding: 10px 14px; border-radius: 10px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 12px; transition: 0.3s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: linear-gradient(135deg, #d4af37 0%, #aa8c2c 100%); color: #09090b; font-weight: 800; }
        
        .main-content { flex: 1; padding: 30px 40px; overflow-y: auto; }
        .top-navbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding-bottom: 18px; }
        .page-title { color: #fff; font-size: 26px; font-weight: 800; }

        .layout-grid { display: grid; grid-template-columns: 1fr 1.6fr; gap: 24px; }
        .card { background: #121215; border: 1px solid #27272a; border-radius: 18px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .card h3 { color: #d4af37; font-size: 16px; margin-bottom: 20px; font-weight: 800; border-bottom: 1px solid #27272a; padding-bottom: 12px; }

        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 11px; color: #a1a1aa; text-transform: uppercase; margin-bottom: 6px; font-weight: 700; }
        .form-group input, .form-group select { width: 100%; padding: 12px; background: #18181b; border: 1px solid #3f3f46; border-radius: 10px; color: #fff; font-size: 13.5px; }
        .form-group input:focus, .form-group select:focus { border-color: #d4af37; outline: none; }

        .btn-submit { width: 100%; padding: 13px; background: linear-gradient(135deg, #d4af37 0%, #aa8c2c 100%); color: #09090b; border: none; border-radius: 10px; font-weight: 800; font-size: 14px; cursor: pointer; }

        .arsip-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .arsip-table th { background: linear-gradient(135deg, #d4af37 0%, #aa8c2c 100%); color: #09090b; padding: 12px 16px; font-size: 12px; font-weight: 800; text-transform: uppercase; text-align: left; }
        .arsip-table td { padding: 14px 16px; border-bottom: 1px solid #27272a; font-size: 13px; }
        .btn-download { background: #22c55e; color: #000; padding: 5px 12px; border-radius: 6px; font-weight: 700; text-decoration: none; font-size: 11px; }
        .btn-delete { color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); padding: 4px 10px; border-radius: 6px; text-decoration: none; font-size: 11px; font-weight: 700; margin-left: 6px; }

        .alert { padding: 12px; border-radius: 10px; font-size: 13px; font-weight: 600; margin-bottom: 20px; }
        .alert-success { background: #1e4620; color: #4ade80; border: 1px solid #22c55e; }
        .alert-error { background: #4a1212; color: #f87171; border: 1px solid #ef4444; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2>SCHOOL ADMIN</h2>
            <div class="brand-sub">Management System Pro</div>
        </div>
        <div class="user-profile-card">
            <div style="font-size:20px;">👨‍💼</div>
            <div>
                <div style="font-size:13px; font-weight:700; color:#d4af37;"><?= htmlspecialchars($admin_nama); ?></div>
                <div style="font-size:11px; color:#22c55e;">● Online Admin</div>
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
            <li><a href="statistik.php">📈 Statistik Sekolah</a></li>
            <li><a href="pengumuman.php">📢 Papan Pengumuman</a></li>
            <li><a href="arsip.php" class="active">📂 Arsip & Dokumen</a></li>
            <li><a href="pengaturan.php">⚙️ Pengaturan Admin</a></li>
            <li><a href="logout.php" style="color:#ef4444; margin-top:10px;">🚪 Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-navbar">
            <div>
                <div style="font-size: 12px; color: #71717a;">Dokumen Instansi / <span style="color:#d4af37; font-weight:700;">Arsip Digital</span></div>
                <h1 class="page-title">📂 Manajemen Arsip & Berkas Sekolah</h1>
            </div>
        </div>

        <?php if($pesan): ?><div class="alert alert-success">✅ <?= $pesan; ?></div><?php endif; ?>
        <?php if($error): ?><div class="alert alert-error">⚠️ <?= $error; ?></div><?php endif; ?>

        <div class="layout-grid">
            <!-- Form Upload -->
            <div class="card">
                <h3>📤 Upload Berkas Dokumen</h3>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Nama Dokumen / SK</label>
                        <input type="text" name="nama_dokumen" placeholder="Contoh: SK Pembagian Tugas Guru..." required autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label>Kategori Dokumen</label>
                        <select name="kategori" required>
                            <option value="Kurikulum">Kurikulum & Pembelajaran</option>
                            <option value="Kepegawaian">Kepegawaian & SK Guru</option>
                            <option value="Kesiswaan">Kesiswaan & Ekstrakurikuler</option>
                            <option value="Surat Resmi">Surat Keluar/Masuk Resmi</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Pilih File Berkas (PDF, Docx, PNG, JPG)</label>
                        <input type="file" name="file_dokumen" required style="padding:8px;">
                    </div>

                    <button type="submit" name="upload_arsip" class="btn-submit">💾 SIMPAN & UNGGAH BERKAS</button>
                </form>
            </div>

            <!-- Tabel Arsip -->
            <div class="card">
                <h3>📋 Daftar Berkas Terarsip (SMK Negeri 1 Lumajang)</h3>
                <div style="overflow-x: auto;">
                    <table class="arsip-table">
                        <thead>
                            <tr>
                                <th>Nama Dokumen</th>
                                <th>Kategori</th>
                                <th>Ukuran</th>
                                <th>Tanggal</th>
                                <th style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q_a = mysqli_query($koneksi, "SELECT * FROM arsip_dokumen ORDER BY id DESC");
                            if ($q_a && mysqli_num_rows($q_a) > 0) {
                                while ($r = mysqli_fetch_assoc($q_a)) {
                            ?>
                                <tr>
                                    <td style="font-weight:700; color:#fff;"><?= htmlspecialchars($r['nama_dokumen']); ?></td>
                                    <td><span style="background:rgba(212,175,55,0.15); color:#d4af37; padding:3px 8px; border-radius:10px; font-size:11px;"><?= htmlspecialchars($r['kategori']); ?></span></td>
                                    <td style="color:#a1a1aa; font-family:monospace;"><?= $r['ukuran_file']; ?></td>
                                    <td style="color:#a1a1aa; font-size:12px;"><?= $r['tanggal_upload']; ?></td>
                                    <td style="text-align:center; white-space:nowrap;">
                                        <a href="<?= $r['file_path']; ?>" download class="btn-download">⬇️ Unduh</a>
                                        <a href="arsip.php?hapus=<?= $r['id']; ?>" class="btn-delete" onclick="return confirm('Hapus dokumen arsip ini?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='5' style='text-align:center; color:#71717a; padding:30px;'>Belum ada berkas arsip yang diunggah.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>