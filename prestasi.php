<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Buat tabel prestasi & pelanggaran
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS poin_siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_siswa INT NOT NULL,
    nama_siswa VARCHAR(100) NOT NULL,
    tipe ENUM('Prestasi', 'Pelanggaran') NOT NULL,
    judul VARCHAR(150) NOT NULL,
    poin INT NOT NULL,
    tanggal DATE NOT NULL,
    keterangan TEXT
)");

$pesan = '';
if (isset($_POST['tambah_catatan'])) {
    $id_s  = intval($_POST['id_siswa']);
    $tipe  =$_POST['tipe'];
    $judul = trim($_POST['judul']);
    $poin  = intval($_POST['poin']);
    $tgl   =$_POST['tanggal'];
    $ket   = trim($_POST['keterangan']);

    // Ambil nama siswa
    $tabel_siswa = 'siswa';
    $c_d = mysqli_query($koneksi, "SELECT COUNT(*) FROM data_siswa");
    if ($c_d && mysqli_fetch_row($c_d)[0] > 0) {$tabel_siswa = 'data_siswa'; }

    $q_s = mysqli_query($koneksi, "SELECT * FROM `$tabel_siswa` WHERE id=$id_s OR ID=$id_s");
    if ($q_s && mysqli_num_rows($q_s) > 0) {$ds = mysqli_fetch_array($q_s, MYSQLI_BOTH);$nama_s = $ds['Nama_Lengkap'] ?? $ds['nama'] ?? 'Siswa';

        mysqli_query($koneksi, "INSERT INTO poin_siswa (id_siswa, nama_siswa, tipe, judul, poin, tanggal, keterangan) VALUES ($id_s, '$nama_s', '$tipe', '$judul',$poin, '$tgl', '$ket')");
        $pesan = "Catatan kedisiplinan/prestasi berhasil ditambahkan!";
    }
}

if (isset($_GET['hapus'])) {
    $hps_id = intval($_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM poin_siswa WHERE id=$hps_id");
    header("Location: prestasi.php");
    exit;
}

$tabel_siswa = 'siswa';
$c_d = mysqli_query($koneksi, "SELECT COUNT(*) FROM data_siswa");
if ($c_d && mysqli_fetch_row($c_d)[0] > 0) {$tabel_siswa = 'data_siswa'; }
$siswa_all = mysqli_query($koneksi, "SELECT * FROM `$tabel_siswa` ORDER BY Nama_Lengkap ASC");

$poin_list = mysqli_query($koneksi, "SELECT * FROM poin_siswa ORDER BY tanggal DESC, id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Poin & Prestasi Siswa - Black & Gold Pro</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #0d0d0d; color: #e0e0e0; display: flex; min-height: 100vh; }
        .sidebar { width: 270px; background-color: #141414; border-right: 1px solid rgba(212, 175, 55, 0.3); padding: 20px 15px; display: flex; flex-direction: column; gap: 12px; flex-shrink: 0; }
        .sidebar-brand h2 { color: #d4af37; font-size: 18px; font-weight: 800; }
        .sidebar-brand .brand-sub { font-size: 10px; color: #888; text-transform: uppercase; }
        .sidebar-menu { list-style: none; display: flex; flex-direction: column; gap: 4px; }
        .sidebar-menu a { color: #ccc; text-decoration: none; padding: 9px 12px; border-radius: 6px; font-size: 13px; display: flex; align-items: center; gap: 10px; transition: 0.3s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: #d4af37; color: #121212; font-weight: 700; }
        .main-content { flex: 1; padding: 30px; overflow-y: auto; }
        h2 { color: #d4af37; border-bottom: 2px solid #d4af37; padding-bottom: 10px; margin-bottom: 20px; font-size: 22px; }
        .form-card, .table-card { background: #161616; border: 1px solid #d4af37; border-radius: 12px; padding: 25px; margin-bottom: 25px; box-shadow: 0 6px 20px rgba(0,0,0,0.6); }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 15px; }
        label { display: block; font-size: 11px; color: #aaa; text-transform: uppercase; margin-bottom: 5px; font-weight: 700; }
        input, select, textarea { width: 100%; padding: 10px; background: #1f1f1f; border: 1px solid #383838; border-radius: 6px; color: #fff; font-size: 13px; }
        input:focus, select:focus, textarea:focus { border-color: #d4af37; outline: none; }
        .btn { background: linear-gradient(135deg, #d4af37 0%, #aa8c2c 100%); color: #121212; padding: 11px 22px; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 13px; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #262626; text-align: left; font-size: 13px; }
        th { background: #d4af37; color: #121212; font-weight: 800; }
        .badge-prestasi { background: rgba(40, 167, 69, 0.2); color: #4ade80; border: 1px solid #28a745; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .badge-pelanggaran { background: rgba(220, 53, 69, 0.2); color: #f87171; border: 1px solid #dc3545; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .btn-hapus { background: #a71d2a; color: #fff; padding: 5px 10px; font-size: 11px; border-radius: 4px; text-decoration: none; }
        .alert { background: #1e4620; color: #a3e635; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; font-weight: 600; }
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
            <li><a href="guru.php">👨‍🏫 Data Guru & Staf</a></li>
            <li><a href="kelas.php">🏫 Kelas & Jurusan</a></li>
            <li><a href="kartu.php">💳 Cetak Kartu Siswa</a></li>
            <li><a href="absensi.php">📅 Rekap Kehadiran</a></li>
            <li><a href="prestasi.php" class="active">🏆 Poin & Prestasi</a></li>
            <li><a href="spp.php">💰 Pembayaran SPP</a></li>
            <li><a href="laporan.php">🖨️ Cetak Laporan</a></li>
            <li><a href="logout.php" style="color:#ff6b6b; margin-top:10px;">🚪 Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h2>🏆 Poin Kedisiplinan & Catatan Prestasi Siswa</h2>
        
        <?php if($pesan): ?><div class="alert">✅ <?= $pesan; ?></div><?php endif; ?>

        <div class="form-card">
            <h3 style="color:#d4af37; margin-bottom:15px; font-size:15px;">Input Record Kedisiplinan / Prestasi Baru</h3>
            <form action="" method="POST">
                <div class="form-grid">
                    <div>
                        <label>Pilih Siswa</label>
                        <select name="id_siswa" required>
                            <option value="">-- Pilih Siswa --</option>
                            <?php while($s = mysqli_fetch_array($siswa_all, MYSQLI_BOTH)): 
                                $s_id =$s['ID'] ?? $s['id'] ?? $s[0];
                                $s_nama =$s['Nama_Lengkap'] ?? $s['nama'] ?? $s[2];
                                $s_nis =$s['NISN'] ?? $s['nis'] ?? $s[1];
                            ?>
                                <option value="<?= $s_id; ?>"><?= htmlspecialchars($s_nama); ?> (NISN: <?= htmlspecialchars($s_nis); ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div>
                        <label>Kategori Record</label>
                        <select name="tipe" required>
                            <option value="Prestasi">🏆 Prestasi (+ Poin)</option>
                            <option value="Pelanggaran">⚠️ Pelanggaran (- Poin)</option>
                        </select>
                    </div>

                    <div>
                        <label>Judul / Nama Kejadian</label>
                        <input type="text" name="judul" required placeholder="Contoh: Juara 1 LKS / Terlambat Masuk">
                    </div>

                    <div>
                        <label>Jumlah Bobot Poin</label>
                        <input type="number" name="poin" required placeholder="Contoh: 50">
                    </div>

                    <div>
                        <label>Tanggal Kejadian</label>
                        <input type="date" name="tanggal" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <div style="margin-bottom:15px;">
                    <label>Keterangan Tambahan / Catatan Guru BK</label>
                    <textarea name="keterangan" rows="2" placeholder="Masukkan detail tambahan..."></textarea>
                </div>

                <button type="submit" name="tambah_catatan" class="btn">💾 SIMPAN RECORD SISWA</button>
            </form>
        </div>

        <div class="table-card">
            <h3 style="color:#d4af37; margin-bottom:15px; font-size:15px;">Riwayat Poin & Catatan Kedisiplinan</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;">No</th>
                        <th style="width:100px;">Tanggal</th>
                        <th>Nama Siswa</th>
                        <th style="width:120px;">Tipe</th>
                        <th>Judul Kejadian</th>
                        <th style="width:80px; text-align:center;">Poin</th>
                        <th>Keterangan</th>
                        <th style="width:70px; text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; while($p = mysqli_fetch_assoc($poin_list)): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($p['tanggal']); ?></td>
                        <td style="font-weight:600; color:#fff;"><?= htmlspecialchars($p['nama_siswa']); ?></td>
                        <td>
                            <?php if($p['tipe'] == 'Prestasi'): ?>
                                <span class="badge-prestasi">🏆 PRESTASI</span>
                            <?php else: ?>
                                <span class="badge-pelanggaran">⚠️ PELANGGARAN</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['judul']); ?></td>
                        <td style="text-align:center; font-weight:bold; color:<?= $p['tipe']=='Prestasi'?'#4ade80':'#f87171'; ?>;">
                            <?= $p['tipe']=='Prestasi'?'+':'-'; ?><?= htmlspecialchars($p['poin']); ?>
                        </td>
                        <td style="color:#aaa; font-size:12px;"><?= htmlspecialchars($p['keterangan']); ?></td>
                        <td style="text-align:center;">
                            <a href="prestasi.php?hapus=<?= $p['id']; ?>" class="btn-hapus" onclick="return confirm('Hapus catatan ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; if(mysqli_num_rows($poin_list) == 0): ?>
                    <tr><td colspan="8" style="text-align:center; color:#888; padding:20px;">Belum ada riwayat poin kedisiplinan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>