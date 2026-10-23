<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Buat tabel presensi jika belum ada
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS presensi_siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    id_siswa INT NOT NULL,
    nama_siswa VARCHAR(100) NOT NULL,
    kelas VARCHAR(20) NOT NULL,
    status ENUM('Hadir', 'Izin', 'Sakit', 'Alpa') NOT NULL,
    keterangan VARCHAR(255)
)");

$pesan = '';
$tgl_input = $_POST['tanggal'] ?? date('Y-m-d');

if (isset($_POST['simpan_presensi'])) {
    $tgl     = $_POST['tanggal'];
    $statuses = $_POST['status'] ?? [];
    $ket_arr  = $_POST['keterangan'] ?? [];

    foreach ($statuses as $id_s => $st) {
        $q_s = mysqli_query($koneksi, "SELECT * FROM data_siswa WHERE id=$id_s OR ID=$id_s");
        if ($q_s && mysqli_num_rows($q_s) > 0) {
            $ds = mysqli_fetch_array($q_s, MYSQLI_BOTH);
            $nama_s = $ds['Nama_Lengkap'] ?? $ds['nama'] ?? 'Siswa';
            $kelas_s = $ds['Kelas'] ?? $ds['kelas'] ?? 'X';
            $ket = mysqli_real_escape_string($koneksi, $ket_arr[$id_s] ?? '');

            // Cek apakah sudah ada catatan di tanggal yang sama
            $q_cek = mysqli_query($koneksi, "SELECT id FROM presensi_siswa WHERE tanggal='$tgl' AND id_siswa=$id_s");
            if (mysqli_num_rows($q_cek) > 0) {
                mysqli_query($koneksi, "UPDATE presensi_siswa SET status='$st', keterangan='$ket' WHERE tanggal='$tgl' AND id_siswa=$id_s");
            } else {
                mysqli_query($koneksi, "INSERT INTO presensi_siswa (tanggal, id_siswa, nama_siswa, kelas, status, keterangan) VALUES ('$tgl', $id_s, '$nama_s', '$kelas_s', '$st', '$ket')");
            }
        }
    }
    $pesan = "Rekap presensi tanggal $tgl berhasil diperbarui!";
}

// Ambil data siswa
$tabel_siswa = 'siswa';
$c_d = mysqli_query($koneksi, "SELECT COUNT(*) FROM data_siswa");
if ($c_d && mysqli_fetch_row($c_d)[0] > 0) { $tabel_siswa = 'data_siswa'; }

$siswa_list = mysqli_query($koneksi, "SELECT * FROM `$tabel_siswa` ORDER BY Kelas ASC, Nama_Lengkap ASC");

// Ringkasan Statistik Hari Ini
$q_h = mysqli_query($koneksi, "SELECT COUNT(*) FROM presensi_siswa WHERE tanggal='$tgl_input' AND status='Hadir'");
$jml_h = $q_h ? mysqli_fetch_row($q_h)[0] : 0;

$q_i = mysqli_query($koneksi, "SELECT COUNT(*) FROM presensi_siswa WHERE tanggal='$tgl_input' AND status='Izin'");
$jml_i = $q_i ? mysqli_fetch_row($q_i)[0] : 0;

$q_s = mysqli_query($koneksi, "SELECT COUNT(*) FROM presensi_siswa WHERE tanggal='$tgl_input' AND status='Sakit'");
$jml_s = $q_s ? mysqli_fetch_row($q_s)[0] : 0;

$q_a = mysqli_query($koneksi, "SELECT COUNT(*) FROM presensi_siswa WHERE tanggal='$tgl_input' AND status='Alpa'");
$jml_a = $q_a ? mysqli_fetch_row($q_a)[0] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Kehadiran Siswa - Black & Gold Pro</title>
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
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .stat-card { background: linear-gradient(135deg, #1a1a1a 0%, #121212 100%); border: 1px solid #333; border-radius: 12px; padding: 16px; text-align: center; }
        .stat-card.hadir { border-color: #28a745; }
        .stat-card.izin { border-color: #ffc107; }
        .stat-card.sakit { border-color: #17a2b8; }
        .stat-card.alpa { border-color: #dc3545; }
        .stat-card .num { font-size: 24px; font-weight: 800; margin-top: 5px; }
        .form-card { background: #161616; border: 1px solid #d4af37; border-radius: 12px; padding: 25px; box-shadow: 0 6px 20px rgba(0,0,0,0.6); }
        .date-picker-box { display: flex; gap: 15px; align-items: center; margin-bottom: 20px; background: #202020; padding: 12px 18px; border-radius: 8px; }
        input[type="date"], input[type="text"] { padding: 9px 12px; background: #181818; border: 1px solid #383838; border-radius: 6px; color: #fff; font-size: 13px; }
        input[type="date"]:focus, input[type="text"]:focus { border-color: #d4af37; outline: none; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #262626; text-align: left; font-size: 13px; }
        th { background: #d4af37; color: #121212; font-weight: 800; }
        .status-radio { display: flex; gap: 12px; }
        .status-radio label { cursor: pointer; font-size: 12px; display: flex; align-items: center; gap: 4px; }
        .btn { background: linear-gradient(135deg, #d4af37 0%, #aa8c2c 100%); color: #121212; padding: 12px 24px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 14px; margin-top: 20px; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4); }
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
            <li><a href="absensi.php" class="active">📅 Rekap Kehadiran</a></li>
            <li><a href="prestasi.php">🏆 Poin & Prestasi</a></li>
            <li><a href="spp.php">💰 Pembayaran SPP</a></li>
            <li><a href="laporan.php">🖨️ Cetak Laporan</a></li>
            <li><a href="logout.php" style="color:#ff6b6b; margin-top:10px;">🚪 Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h2>📅 Rekapitulasi Presensi & Kehadiran Siswa</h2>

        <?php if($pesan): ?><div class="alert">✅ <?= $pesan; ?></div><?php endif; ?>

        <!-- Summary Status Cards -->
        <div class="stats-grid">
            <div class="stat-card hadir"><div style="color:#28a745; font-size:12px; font-weight:bold;">HADIR</div><div class="num" style="color:#28a745;"><?= $jml_h; ?> Siswa</div></div>
            <div class="stat-card izin"><div style="color:#ffc107; font-size:12px; font-weight:bold;">IZIN</div><div class="num" style="color:#ffc107;"><?= $jml_i; ?> Siswa</div></div>
            <div class="stat-card sakit"><div style="color:#17a2b8; font-size:12px; font-weight:bold;">SAKIT</div><div class="num" style="color:#17a2b8;"><?= $jml_s; ?> Siswa</div></div>
            <div class="stat-card alpa"><div style="color:#dc3545; font-size:12px; font-weight:bold;">ALPA</div><div class="num" style="color:#dc3545;"><?= $jml_a; ?> Siswa</div></div>
        </div>

        <div class="form-card">
            <form action="" method="POST">
                <div class="date-picker-box">
                    <label style="color:#d4af37; font-weight:bold; font-size:13px;">PILIH TANGGAL PRESENSI:</label>
                    <input type="date" name="tanggal" value="<?= htmlspecialchars($tgl_input); ?>" required onchange="this.form.submit()">
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>NISN</th>
                            <th>Nama Lengkap Siswa</th>
                            <th style="width: 90px; text-align:center;">Kelas</th>
                            <th style="width: 320px;">Status Kehadiran</th>
                            <th>Catatan / Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while($s = mysqli_fetch_array($siswa_list, MYSQLI_BOTH)): 
                            $s_id    = $s['ID'] ?? $s['id'] ?? $s[0];
                            $s_nis   = $s['NISN'] ?? $s['nis'] ?? $s[1];
                            $s_nama  = $s['Nama_Lengkap'] ?? $s['nama'] ?? $s[2];
                            $s_kelas = $s['Kelas'] ?? $s['kelas'] ?? $s[3];

                            // Ambil catatan presensi jika ada
                            $st_val  = 'Hadir';
                            $ket_val = '';
                            $q_p = mysqli_query($koneksi, "SELECT status, keterangan FROM presensi_siswa WHERE tanggal='$tgl_input' AND id_siswa=$s_id");
                            if ($q_p && mysqli_num_rows($q_p) > 0) {
                                $data_p  = mysqli_fetch_assoc($q_p);
                                $st_val  = $data_p['status'];
                                $ket_val = $data_p['keterangan'];
                            }
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($s_nis); ?></td>
                            <td style="font-weight:600; color:#fff;"><?= htmlspecialchars($s_nama); ?></td>
                            <td style="text-align:center;"><?= htmlspecialchars($s_kelas); ?></td>
                            <td>
                                <div class="status-radio">
                                    <label style="color:#28a745;"><input type="radio" name="status[<?= $s_id; ?>]" value="Hadir" <?= $st_val=='Hadir'?'checked':''; ?>> Hadir</label>
                                    <label style="color:#ffc107;"><input type="radio" name="status[<?= $s_id; ?>]" value="Izin" <?= $st_val=='Izin'?'checked':''; ?>> Izin</label>
                                    <label style="color:#17a2b8;"><input type="radio" name="status[<?= $s_id; ?>]" value="Sakit" <?= $st_val=='Sakit'?'checked':''; ?>> Sakit</label>
                                    <label style="color:#f87171;"><input type="radio" name="status[<?= $s_id; ?>]" value="Alpa" <?= $st_val=='Alpa'?'checked':''; ?>> Alpa</label>
                                </div>
                            </td>
                            <td>
                                <input type="text" name="keterangan[<?= $s_id; ?>]" value="<?= htmlspecialchars($ket_val); ?>" placeholder="Alasan / no surat..." style="width:100%;">
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <button type="submit" name="simpan_presensi" class="btn">💾 SIMPAN REKAP PRESENSI HARIAN</button>
            </form>
        </div>
    </div>
</body>
</html>