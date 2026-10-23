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

// Buat tabel pengumuman secara otomatis jika belum ada
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS pengumuman_sekolah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    isi TEXT NOT NULL,
    tanggal DATE NOT NULL,
    penulis VARCHAR(100) NOT NULL
)");

// Tambah Pengumuman Baru
if (isset($_POST['tambah_pengumuman'])) {
    $judul    = trim($_POST['judul']);
    $kategori = trim($_POST['kategori']);
    $isi      = trim($_POST['isi']);
    $tanggal  = date('Y-m-d');
    $penulis  = $_SESSION['admin_nama'] ?? 'Administrator';

    if (!empty($judul) && !empty($isi)) {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO pengumuman_sekolah (judul, kategori, isi, tanggal, penulis) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssss", $judul, $kategori, $isi, $tanggal, $penulis);
        if (mysqli_stmt_execute($stmt)) {
            $pesan = "Pengumuman berhasil dipublikasikan ke sistem!";
        } else {
            $error = "Gagal mempublikasikan pengumuman.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = "Judul dan isi pengumuman tidak boleh kosong!";
    }
}

// Hapus Pengumuman
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM pengumuman_sekolah WHERE id = $id_hapus");
    header("Location: pengumuman.php");
    exit;
}

$admin_nama = $_SESSION['admin_nama'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Papan Pengumuman Sekolah - SMK Negeri 1 Lumajang</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; 
            background-color: #08080a; 
            color: #e2e8f0; 
            display: flex; 
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 285px; background: #111114; border-right: 1px solid rgba(212, 175, 55, 0.25);
            padding: 24px 18px; display: flex; flex-direction: column; gap: 14px; flex-shrink: 0;
            box-shadow: 5px 0 25px rgba(0,0,0,0.6); z-index: 100;
        }
        .sidebar-brand { text-align: center; padding-bottom: 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); }
        .sidebar-brand h2 {
            color: #d4af37; font-size: 20px; font-weight: 800; letter-spacing: 1.5px;
            background: linear-gradient(135deg, #fff2be 0%, #d4af37 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .sidebar-brand .brand-sub { font-size: 10px; color: #71717a; letter-spacing: 1.2px; text-transform: uppercase; }

        .user-profile-card {
            background: linear-gradient(135deg, #1d1d21 0%, #121215 100%);
            border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 12px; padding: 12px 14px; display: flex; align-items: center; gap: 12px;
        }
        .user-avatar { width: 38px; height: 38px; background: #0d0d0d; border: 1.5px solid #d4af37; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #d4af37; }
        .user-info .user-name { font-size: 13px; font-weight: 700; color: #d4af37; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px; }
        .user-info .user-status { font-size: 11px; color: #a1a1aa; display: flex; align-items: center; gap: 6px; }
        .status-dot { width: 8px; height: 8px; background-color: #22c55e; border-radius: 50%; box-shadow: 0 0 8px #22c55e; }

        .sidebar-section-title { color: #52525b; font-size: 10px; text-transform: uppercase; letter-spacing: 1.2px; margin-top: 10px; padding-left: 8px; font-weight: 800; }
        .sidebar-menu { list-style: none; display: flex; flex-direction: column; gap: 5px; }
        .sidebar-menu a {
            color: #a1a1aa; text-decoration: none; padding: 10px 14px; border-radius: 10px; font-size: 13px; font-weight: 600;
            display: flex; align-items: center; gap: 12px; transition: all 0.3s ease;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: linear-gradient(135deg, #d4af37 0%, #aa8c2c 100%); color: #09090b; font-weight: 800; transform: translateX(4px); box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
        }

        .clock-widget { background: #18181b; border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 10px; padding: 12px; text-align: center; margin-top: auto; }
        .clock-time { color: #d4af37; font-size: 16px; font-weight: 800; font-family: monospace; }
        .clock-date { color: #71717a; font-size: 10px; margin-top: 2px; }

        /* Main Content */
        .main-content { flex: 1; padding: 30px 40px; overflow-y: auto; animation: fadeInUp 0.6s ease-out; }
        .top-navbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 18px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); }
        .page-title { color: #fff; font-size: 26px; font-weight: 800; }

        .layout-grid { display: grid; grid-template-columns: 1fr 1.6fr; gap: 24px; }
        @media(max-width: 1024px) { .layout-grid { grid-template-columns: 1fr; } }

        .card {
            background: #121215; border: 1px solid #27272a; border-radius: 18px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .card h3 { color: #d4af37; font-size: 16px; margin-bottom: 20px; font-weight: 800; border-bottom: 1px solid #27272a; padding-bottom: 12px; }

        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 11px; color: #a1a1aa; text-transform: uppercase; margin-bottom: 6px; font-weight: 700; letter-spacing: 0.5px; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 12px 14px; background: #18181b; border: 1px solid #3f3f46; border-radius: 10px; color: #fff; font-size: 13.5px; font-family: inherit; transition: all 0.3s;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #d4af37; outline: none; box-shadow: 0 0 12px rgba(212, 175, 55, 0.25); }
        .form-group textarea { resize: vertical; height: 110px; }

        .btn-submit {
            width: 100%; padding: 13px; background: linear-gradient(135deg, #d4af37 0%, #aa8c2c 100%); color: #09090b; border: none; border-radius: 10px; font-weight: 800; font-size: 14px; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 18px rgba(212, 175, 55, 0.35);
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(212, 175, 55, 0.5); }

        /* Announcement Item List */
        .announcement-item {
            background: #18181b; border: 1px solid #27272a; border-left: 4px solid #d4af37; border-radius: 12px; padding: 18px; margin-bottom: 16px; transition: all 0.3s;
        }
        .announcement-item:hover { border-color: #d4af37; box-shadow: 0 5px 20px rgba(0,0,0,0.4); }
        .ann-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .ann-title { font-size: 16px; font-weight: 700; color: #fff; }
        .ann-tag { background: rgba(212, 175, 55, 0.15); color: #d4af37; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; border: 1px solid rgba(212, 175, 55, 0.3); }
        .ann-desc { font-size: 13.5px; color: #a1a1aa; line-height: 1.6; margin-bottom: 12px; white-space: pre-line; }
        .ann-footer { display: flex; justify-content: space-between; align-items: center; font-size: 11.5px; color: #71717a; border-top: 1px solid #27272a; padding-top: 10px; }
        .btn-delete { color: #ef4444; text-decoration: none; font-weight: 700; border: 1px solid rgba(239, 68, 68, 0.3); padding: 3px 10px; border-radius: 6px; transition: all 0.2s; }
        .btn-delete:hover { background: #ef4444; color: #fff; }

        .alert { padding: 12px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; margin-bottom: 20px; }
        .alert-success { background: #1e4620; color: #4ade80; border: 1px solid #22c55e; }
        .alert-error { background: #4a1212; color: #f87171; border: 1px solid #ef4444; }

        @keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2>SCHOOL ADMIN</h2>
            <div class="brand-sub">Management System Pro</div>
        </div>

        <div class="user-profile-card">
            <div class="user-avatar">👨‍💼</div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($admin_nama); ?></div>
                <div class="user-status"><span class="status-dot"></span> Online (Admin)</div>
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
            <li><a href="pengumuman.php" class="active">📢 Papan Pengumuman</a></li>
            <li><a href="pengaturan.php">⚙️ Pengaturan Admin</a></li>
            <li><a href="logout.php" style="color:#ef4444; border:1px solid rgba(239,68,68,0.3); margin-top:10px;">🚪 Logout</a></li>
        </ul>

        <div class="clock-widget">
            <div id="sidebar-time" class="clock-time">00:00:00</div>
            <div id="sidebar-date" class="clock-date">Sabtu, 17 Oktober 2026</div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-navbar">
            <div>
                <div style="font-size: 12px; color: #71717a;">Informasi Instansi / <span style="color:#d4af37; font-weight:700;">Papan Pengumuman</span></div>
                <h1 class="page-title">📢 Papan Pengumuman & Informasi Sekolah</h1>
            </div>
        </div>

        <?php if($pesan): ?><div class="alert alert-success">✅ <?= $pesan; ?></div><?php endif; ?>
        <?php if($error): ?><div class="alert alert-error">⚠️ <?= $error; ?></div><?php endif; ?>

        <div class="layout-grid">
            
            <!-- Form Tambah Pengumuman -->
            <div class="card">
                <h3>✍️ Buat Pengumuman Baru</h3>
                <form action="" method="POST">
                    <div class="form-group">
                        <label>Judul Pengumuman</label>
                        <input type="text" name="judul" placeholder="Contoh: Jadwal Ujian Semester..." required autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategori" required>
                            <option value="Akademik">Akademik</option>
                            <option value="Penting">Penting / Urgen</option>
                            <option value="Kegiatan">Kegiatan Sekolah</option>
                            <option value="Libur">Libur & Cuti</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Isi Pengumuman</label>
                        <textarea name="isi" placeholder="Tuliskan detail informasi pengumuman di sini..." required></textarea>
                    </div>

                    <button type="submit" name="tambah_pengumuman" class="btn-submit">🚀 PUBLIKASIKAN PENGUMUMAN</button>
                </form>
            </div>

            <!-- Daftar Pengumuman Aktif -->
            <div class="card">
                <h3>📋 Daftar Pengumuman Aktif SMK Negeri 1 Lumajang</h3>
                <div style="max-height: 550px; overflow-y: auto; padding-right: 5px;">
                    <?php
                    $q_ann = mysqli_query($koneksi, "SELECT * FROM pengumuman_sekolah ORDER BY id DESC");
                    if ($q_ann && mysqli_num_rows($q_ann) > 0) {
                        while ($row = mysqli_fetch_assoc($q_ann)) {
                    ?>
                        <div class="announcement-item">
                            <div class="ann-header">
                                <div class="ann-title"><?= htmlspecialchars($row['judul']); ?></div>
                                <span class="ann-tag"><?= htmlspecialchars($row['kategori']); ?></span>
                            </div>
                            <div class="ann-desc"><?= nl2br(htmlspecialchars($row['isi'])); ?></div>
                            <div class="ann-footer">
                                <span>📅 <?= $row['tanggal']; ?> &nbsp;|&nbsp; ✍️ <?= htmlspecialchars($row['penulis']); ?></span>
                                <a href="pengumuman.php?hapus=<?= $row['id']; ?>" class="btn-delete" onclick="return confirm('Yakin ingin menghapus pengumuman ini?')">Hapus</a>
                            </div>
                        </div>
                    <?php
                        }
                    } else {
                        echo "<div style='text-align:center; color:#71717a; padding:40px;'>Belum ada pengumuman yang dipublikasikan. Silakan buat pengumuman melalui form di samping.</div>";
                    }
                    ?>
                </div>
            </div>

        </div>
    </div>

    <script>
        function updateSidebarClock() {
            const now = new Date();
            document.getElementById('sidebar-time').textContent = now.toLocaleTimeString('id-ID', { hour12: false });
            document.getElementById('sidebar-date').textContent = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
        setInterval(updateSidebarClock, 1000); updateSidebarClock();
    </script>
</body>
</html>