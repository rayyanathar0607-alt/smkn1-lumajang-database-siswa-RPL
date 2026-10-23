<?php
session_start();
include 'koneksi.php';

// Proteksi Halaman Login
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$pesan_sukses = '';$pesan_error  = '';

// Proses Simpan Data Siswa Baru
if (isset($_POST['simpan'])) {
    $nisn         = trim($_POST['nisn']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $gender       = trim($_POST['gender']);
    $kelas        = trim($_POST['kelas']);
    $jurusan      = trim($_POST['jurusan']);
    $telp         = trim($_POST['telp']);
    $alamat       = trim($_POST['alamat']);

    if (!empty($nisn) && !empty($nama_lengkap) && !empty($kelas) && !empty($jurusan)) {
        // Deteksi nama tabel aktif
        $tabel_aktif = 'siswa';
        $cek_data = mysqli_query($koneksi, "SELECT COUNT(*) FROM data_siswa");
        if ($cek_data && mysqli_fetch_row($cek_data)[0] > 0) {$tabel_aktif = 'data_siswa';
        }

        // Query Insert (Menyesuaikan struktur kolom)
        $query = "INSERT INTO `$tabel_aktif` (NISN, Nama_Lengkap, Kelas, Jurusan) VALUES ('$nisn', '$nama_lengkap', '$kelas', '$jurusan')";
        $result = mysqli_query($koneksi,$query);

        if ($result) {$pesan_sukses = "Data siswa baru berhasil ditambahkan ke sistem!";
        } else {
            $pesan_error = "Gagal menyimpan ke database: " . mysqli_error($koneksi);
        }
    } else {
        $pesan_error = "NISN, Nama Lengkap, Kelas, dan Jurusan wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Siswa Baru - Black & Gold Modern</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #121212; 
            color: #e0e0e0; 
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            background-color: #1a1a1a;
            border-right: 1px solid #d4af37;
            padding: 25px 15px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            flex-shrink: 0;
        }
        .sidebar-brand {
            color: #d4af37;
            font-size: 18px;
            font-weight: 700;
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 1px solid #333;
            letter-spacing: 1px;
        }
        .sidebar-section-title {
            color: #777;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
            padding-left: 10px;
        }
        .sidebar-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .sidebar-menu a {
            color: #ccc;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: #d4af37;
            color: #121212;
            font-weight: 700;
            transform: translateX(4px);
        }
        .sidebar-menu a.logout {
            color: #ff6b6b;
            border: 1px solid #a71d2a;
            margin-top: 10px;
        }
        .sidebar-menu a.logout:hover {
            background-color: #a71d2a;
            color: #fff;
        }

        /* Main Content with Fade Animation */
        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            animation: fadeIn 0.6s ease-in-out;
        }
        h2 { 
            color: #d4af37; 
            margin-bottom: 20px; 
            border-bottom: 2px solid #d4af37;
            padding-bottom: 10px;
        }

        /* Alert Notifikasi */
        .alert {
            padding: 12px 18px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
            animation: fadeIn 0.4s ease;
        }
        .alert-success { background-color: #1e4620; color: #a3e635; border: 1px solid #2e7d32; }
        .alert-error { background-color: #4a1212; color: #f87171; border: 1px solid #991b1b; }

        /* Form Card Container */
        .form-card {
            background: linear-gradient(135deg, #1e1e1e 0%, #161616 100%);
            border: 1px solid #d4af37;
            border-radius: 12px;
            padding: 30px;
            max-width: 750px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.6);
            transition: box-shadow 0.3s ease;
        }
        .form-card:hover {
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.2);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        label {
            display: block;
            color: #bbb;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        input[type="text"], select, textarea {
            width: 100%;
            padding: 12px 14px;
            background-color: #252525;
            border: 1px solid #444;
            border-radius: 6px;
            color: #fff;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        input[type="text"]:focus, select:focus, textarea:focus {
            border-color: #d4af37;
            outline: none;
            background-color: #2a2a2a;
            box-shadow: 0 0 8px rgba(212, 175, 55, 0.3);
        }
        textarea {
            resize: vertical;
            height: 90px;
        }

        /* Button Group */
        .btn-container {
            display: flex;
            gap: 12px;
            margin-top: 10px;
        }
        .btn {
            padding: 12px 22px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-submit {
            background-color: #d4af37;
            color: #121212;
            flex: 1;
        }
        .btn-submit:hover {
            background-color: #b3922f;
            transform: translateY(-2px);
        }
        .btn-back {
            background-color: #252525;
            color: #d4af37;
            border: 1px solid #d4af37;
            text-align: center;
        }
        .btn-back:hover {
            background-color: #d4af37;
            color: #121212;
        }

        /* Fade-in Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-brand">SCHOOL ADMIN</div>
        
        <div class="sidebar-section-title">Menu Utama</div>
        <ul class="sidebar-menu">
            <li><a href="index.php">📊 Dashboard</a></li>
            <li><a href="index.php">📚 Data Siswa</a></li>
            <li><a href="tambah.php" class="active">➕ Tambah Siswa</a></li>
        </ul>

        <div class="sidebar-section-title">Laporan & Sistem</div>
        <ul class="sidebar-menu">
            <li><a href="laporan.php">🖨️ Cetak Laporan</a></li>
            <li><a href="statistik.php">📈 Statistik Sekolah</a></li>
            <li><a href="pengaturan.php">⚙️ Pengaturan Admin</a></li>
            <li><a href="logout.php" class="logout" onclick="return confirm('Apakah Anda yakin ingin keluar?')">🚪 Logout</a></li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <h2>➕ Pendaftaran Data Siswa Baru</h2>

        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert alert-success">✅ <?= $pesan_sukses; ?></div>
        <?php endif; ?>

        <?php if (!empty($pesan_error)): ?>
            <div class="alert alert-error">⚠️ <?= $pesan_error; ?></div>
        <?php endif; ?>

        <div class="form-card">
            <form action="" method="POST">
                <div class="form-grid">
                    
                    <div class="form-group">
                        <label>NISN (Nomor Induk Siswa Nasional)</label>
                        <input type="text" name="nisn" placeholder="Contoh: 2162214541" required autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label>Nama Lengkap Siswa</label>
                        <input type="text" name="nama_lengkap" placeholder="Contoh: Achmad Nur Hidayat" required autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label>Jenis Kelamin</label>
                        <select name="gender">
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tingkat Kelas</label>
                        <select name="kelas" required>
                            <option value="">-- Pilih Kelas --</option>
                            <option value="X">Kelas X</option>
                            <option value="XI">Kelas XI</option>
                            <option value="XII">Kelas XII</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Program Keahlian / Jurusan</label>
                        <select name="jurusan" required>
                            <option value="">-- Pilih Jurusan --</option>
                            <option value="RPL">Rekayasa Perangkat Lunak (RPL)</option>
                            <option value="TKJ">Teknik Komputer & Jaringan (TKJ)</option>
                            <option value="MM">Multimedia / DKV (MM)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Nomor Telepon / WhatsApp</label>
                        <input type="text" name="telp" placeholder="Contoh: 081234567890">
                    </div>

                    <div class="form-group full-width">
                        <label>Alamat Domisili</label>
                        <textarea name="alamat" placeholder="Masukkan alamat lengkap siswa..."></textarea>
                    </div>

                </div>

                <div class="btn-container">
                    <button type="submit" name="simpan" class="btn btn-submit">💾 Simpan Data Siswa</button>
                    <a href="index.php" class="btn btn-back">← Batal / Kembali</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>