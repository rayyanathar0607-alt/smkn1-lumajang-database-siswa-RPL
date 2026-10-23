<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Buat tabel guru jika belum ada
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS data_guru (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nip VARCHAR(30) NOT NULL,
    nama_guru VARCHAR(100) NOT NULL,
    mapel VARCHAR(100) NOT NULL,
    telepon VARCHAR(20)
)");

$pesan = '';
if (isset($_POST['tambah_guru'])) {
    $nip = trim($_POST['nip']);
    $nama = trim($_POST['nama']);
    $mapel = trim($_POST['mapel']);
    $telepon = trim($_POST['telepon']);

    if (!empty($nip) && !empty($nama) && !empty($mapel)) {
        $q = mysqli_query($koneksi, "INSERT INTO data_guru (nip, nama_guru, mapel, telepon) VALUES ('$nip', '$nama', '$mapel', '$telepon')");
        if ($q) {
            $pesan = "Data guru berhasil ditambahkan!";
        }
    }
}

// Hapus guru
if (isset($_GET['hapus'])) {
    $id_hps = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM data_guru WHERE id=$id_hps");
    header("Location: guru.php");
    exit;
}

$result_guru = mysqli_query($koneksi, "SELECT * FROM data_guru ORDER BY nama_guru ASC");
$admin_nama = $_SESSION['admin_nama'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Guru & Staf - Black & Gold Pro</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #121212; color: #e0e0e0; display: flex; min-height: 100vh; }
        .sidebar { width: 270px; background-color: #1a1a1a; border-right: 1px solid #d4af37; padding: 20px 15px; display: flex; flex-direction: column; gap: 12px; flex-shrink: 0; }
        .sidebar-brand h2 { color: #d4af37; font-size: 18px; font-weight: 800; letter-spacing: 1px; }
        .sidebar-brand .brand-sub { font-size: 10px; color: #888; text-transform: uppercase; }
        .sidebar-menu { list-style: none; display: flex; flex-direction: column; gap: 4px; }
        .sidebar-menu a { color: #ccc; text-decoration: none; padding: 9px 12px; border-radius: 6px; font-size: 13px; display: flex; align-items: center; gap: 10px; transition: 0.3s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: #d4af37; color: #121212; font-weight: 700; }
        .main-content { flex: 1; padding: 30px; overflow-y: auto; }
        h2 { color: #d4af37; border-bottom: 2px solid #d4af37; padding-bottom: 10px; margin-bottom: 20px; }
        .form-card, .table-card { background: #1e1e1e; border: 1px solid #d4af37; border-radius: 10px; padding: 25px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 15px; }
        label { display: block; font-size: 12px; color: #aaa; text-transform: uppercase; margin-bottom: 5px; }
        input { width: 100%; padding: 10px; background: #252525; border: 1px solid #444; border-radius: 6px; color: #fff; font-size: 13px; }
        input:focus { border-color: #d4af37; outline: none; }
        .btn { background: #d4af37; color: #121212; padding: 10px 18px; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 13px; }
        .btn:hover { background: #b3922f; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #2c2c2c; text-align: left; font-size: 13px; }
        th { background: #d4af37; color: #121212; font-weight: 750; }
        tr:hover { background: #252525; }
        .btn-hapus { background: #a71d2a; color: #fff; padding: 5px 10px; font-size: 11px; border-radius: 4px; text-decoration: none; }
        .alert { background: #1e4620; color: #a3e635; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2>SCHOOL ADMIN</h2>
            <div class="brand-sub">Management System Pro</div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php">📊 Dashboard Utama</a></li>
            <li><a href="index.php">📚 Data Siswa</a></li>
            <li><a href="guru.php" class="active">👨‍🏫 Data Guru & Staf</a></li>
            <li><a href="kelas.php">🏫 Kelas & Jurusan</a></li>
            <li><a href="kartu.php">💳 Cetak Kartu Siswa</a></li>
            <li><a href="laporan.php">🖨️ Cetak Laporan</a></li>
            <li><a href="statistik.php">📈 Statistik Sekolah</a></li>
            <li><a href="pengaturan.php">⚙️ Pengaturan Admin</a></li>
            <li><a href="logout.php" style="color:#ff6b6b; margin-top:10px;">🚪 Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <h2>👨‍🏫 Manajemen Data Guru & Staf Pengajar</h2>
        <?php if($pesan): ?><div class="alert"><?= $pesan; ?></div><?php endif; ?>
        
        <div class="form-card">
            <h3 style="color:#d4af37; margin-bottom:15px; font-size:15px;">Tambah Guru / Staf Baru</h3>
            <form action="" method="POST">
                <div class="form-grid">
                    <div><label>NIP / ID Guru</label><input type="text" name="nip" required placeholder="Contoh: 19850101..."></div>
                    <div><label>Nama Lengkap & Gelar</label><input type="text" name="nama" required placeholder="Contoh: Siti Aminah, S.Pd."></div>
                    <div><label>Bidang Studi / Mapel</label><input type="text" name="mapel" required placeholder="Contoh: Matematika"></div>
                    <div><label>Nomor Telepon</label><input type="text" name="telepon" placeholder="08123456789"></div>
                </div>
                <button type="submit" name="tambah_guru" class="btn">💾 Simpan Data Guru</button>
            </form>
        </div>

        <div class="table-card">
            <h3 style="color:#d4af37; margin-bottom:15px; font-size:15px;">Daftar Guru & Staf Aktif</h3>
            <table>
                <thead>
                    <tr><th>No</th><th>NIP</th><th>Nama Lengkap & Gelar</th><th>Bidang Studi / Mapel</th><th>Telepon</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php $no=1; while($g = mysqli_fetch_assoc($result_guru)): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($g['nip']); ?></td>
                        <td style="font-weight:600; color:#fff;"><?= htmlspecialchars($g['nama_guru']); ?></td>
                        <td><?= htmlspecialchars($g['mapel']); ?></td>
                        <td><?= htmlspecialchars($g['telepon']); ?></td>
                        <td><a href="guru.php?hapus=<?= $g['id']; ?>" class="btn-hapus" onclick="return confirm('Hapus data guru ini?')">Hapus</a></td>
                    </tr>
                    <?php endwhile; if(mysqli_num_rows($result_guru)==0): ?>
                    <tr><td colspan="6" style="text-align:center; color:#888; padding:20px;">Belum ada data guru.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>