<?php
/**
 * ==============================================================================
 * SMK NEGERI 1 LUMAJANG - ENTERPRISE MANAGEMENT SYSTEM PRO
 * File          : pengaturan.php (Pusat Pengaturan Admin & Konfigurasi Sistem)
 * Branding      : Black & Gold Pro Max Edition (Fully Detailed & Animated)
 * ==============================================================================
 */

session_start();
include 'koneksi.php';

// Validasi Keamanan Sesi Administrator
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

date_default_timezone_set('Asia/Jakarta');
$pesan_sukses = '';
$pesan_error  = '';
$admin_nama   = $_SESSION['admin_nama'] ?? 'Rayyan Athar Syazani';
$admin_id     = $_SESSION['admin_id'] ?? 1;

// 1. Inisialisasi Tabel Pengaturan Sekolah & Multi-Admin di Database
@mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS pengaturan_sekolah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_sekolah VARCHAR(150) NOT NULL DEFAULT 'SMK Negeri 1 Lumajang',
    kepala_sekolah VARCHAR(150) NOT NULL DEFAULT 'Dr. H. Ahmad Fauzi, M.Pd.',
    tahun_ajaran VARCHAR(50) NOT NULL DEFAULT '2025 / 2026',
    alamat VARCHAR(255) NOT NULL DEFAULT 'Jl. Brantas No. 40 Lumajang'
)");

@mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(150) NOT NULL,
    role VARCHAR(50) DEFAULT 'Administrator',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Ambil Data Pengaturan Sekolah Aktif
$q_set = mysqli_query($koneksi, "SELECT * FROM pengaturan_sekolah LIMIT 1");
$setting = mysqli_fetch_assoc($q_set);
if (!$setting) {
    mysqli_query($koneksi, "INSERT INTO pengaturan_sekolah (nama_sekolah, kepala_sekolah, tahun_ajaran, alamat) VALUES ('SMK Negeri 1 Lumajang', 'Dr. H. Ahmad Fauzi, M.Pd.', '2025 / 2026', 'Jl. Brantas No. 40 Lumajang')");
    $setting = ['nama_sekolah' => 'SMK Negeri 1 Lumajang', 'kepala_sekolah' => 'Dr. H. Ahmad Fauzi, M.Pd.', 'tahun_ajaran' => '2025 / 2026', 'alamat' => 'Jl. Brantas No. 40 Lumajang'];
}

// 2. Logika Proses Update Profil & Keamanan Admin
if (isset($_POST['update_profil'])) {
    $new_user = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $new_pass = trim($_POST['password']);

    if (!empty($new_pass)) {
        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt_upd = mysqli_prepare($koneksi, "UPDATE admin_users SET username = ?, password = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt_upd, "ssi", $new_user, $hashed_pass, $admin_id);
    } else {
        $stmt_upd = mysqli_prepare($koneksi, "UPDATE admin_users SET username = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt_upd, "si", $new_user, $admin_id);
    }

    if (mysqli_stmt_execute($stmt_upd)) {
        $_SESSION['admin_nama'] = $new_user;
        $pesan_sukses = "Profil dan kredensial keamanan admin berhasil diperbarui!";
    } else {
        $pesan_error = "Gagal memperbarui profil admin.";
    }
    mysqli_stmt_close($stmt_upd);
}

// 3. Logika Proses Update Identitas Sekolah
if (isset($_POST['update_sekolah'])) {
    $nama_sek = mysqli_real_escape_string($koneksi, $_POST['nama_sekolah']);
    $kepsek   = mysqli_real_escape_string($koneksi, $_POST['kepala_sekolah']);
    $thn_ajar = mysqli_real_escape_string($koneksi, $_POST['tahun_ajaran']);
    $alamat   = mysqli_real_escape_string($koneksi, $_POST['alamat']);

    $stmt_sch = mysqli_prepare($koneksi, "UPDATE pengaturan_sekolah SET nama_sekolah = ?, kepala_sekolah = ?, tahun_ajaran = ?, alamat = ? WHERE id = 1");
    mysqli_stmt_bind_param($stmt_sch, "ssss", $nama_sek, $kepsek, $thn_ajar, $alamat);
    
    if (mysqli_stmt_execute($stmt_sch)) {
        $pesan_sukses = "Identitas instansi sekolah berhasil diperbarui!";
        $setting['nama_sekolah']   = $nama_sek;
        $setting['kepala_sekolah'] = $kepsek;
        $setting['tahun_ajaran']   = $thn_ajar;
        $setting['alamat']         = $alamat;
    } else {
        $pesan_error = "Gagal memperbarui identitas sekolah.";
    }
    mysqli_stmt_close($stmt_sch);
}

// 4. Logika Tambah Administrator Baru
if (isset($_POST['tambah_admin'])) {
    $adm_user = mysqli_real_escape_string($koneksi, trim($_POST['adm_username']));
    $adm_pass = password_hash(trim($_POST['adm_password']), PASSWORD_DEFAULT);
    $adm_nama = mysqli_real_escape_string($koneksi, trim($_POST['adm_nama']));
    $adm_role = mysqli_real_escape_string($koneksi, $_POST['adm_role']);

    $cek_u = mysqli_query($koneksi, "SELECT id FROM admin_users WHERE username='$adm_user'");
    if (mysqli_num_rows($cek_u) > 0) {
        $pesan_error = "Username '$adm_user' sudah digunakan oleh admin lain!";
    } else {
        $stmt_a = mysqli_prepare($koneksi, "INSERT INTO admin_users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_a, "ssss", $adm_user, $adm_pass, $adm_nama, $adm_role);
        if (mysqli_stmt_execute($stmt_a)) {
            $pesan_sukses = "Administrator baru '$adm_nama' berhasil ditambahkan!";
        } else {
            $pesan_error = "Gagal menambahkan admin baru.";
        }
        mysqli_stmt_close($stmt_a);
    }
}

// Ambil Daftar Semua Admin
$list_admin = mysqli_query($koneksi, "SELECT * FROM admin_users ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem & Admin - SMK Negeri 1 Lumajang</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="animasi.css">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg-dark: #08080a;
            --bg-card: #111114;
            --bg-input: #18181b;
            --gold-primary: #d4af37;
            --gold-light: #fff2be;
            --gold-gradient: linear-gradient(135deg, #fff2be 0%, #d4af37 50%, #aa8c2c 100%);
            --text-main: #f4f4f5;
            --text-muted: #71717a;
            --border-gold: rgba(212, 175, 55, 0.25);
            --border-subtle: #27272a;
            --success: #22c55e;
            --danger: #ef4444;
            --shadow-pro: 0 10px 30px rgba(0, 0, 0, 0.6);
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .main-wrapper {
            flex: 1;
            padding: 35px 45px;
            overflow-y: auto;
            background-color: var(--bg-dark);
            animation: fadeInSmooth 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .top-navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--border-subtle);
        }
        .page-title-box { display: flex; align-items: center; gap: 12px; }
        .page-icon { font-size: 28px; }
        .page-title { color: #ffffff; font-size: 26px; font-weight: 800; letter-spacing: -0.5px; }
        .breadcrumb { font-size: 12px; color: var(--text-muted); margin-bottom: 4px; font-weight: 500; }
        .breadcrumb span { color: var(--gold-primary); font-weight: 700; }

        .server-status-badge {
            background: #141418; border: 1px solid var(--border-subtle);
            padding: 8px 16px; border-radius: 30px; font-size: 12px; color: var(--text-muted);
            display: flex; align-items: center; gap: 8px;
        }
        .dot-green { width: 8px; height: 8px; background: var(--success); border-radius: 50%; box-shadow: 0 0 10px var(--success); }

        .alert-box {
            padding: 14px 18px; border-radius: 12px; font-size: 13.5px; font-weight: 600;
            margin-bottom: 24px; display: flex; align-items: center; gap: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .alert-success { background: #14331e; color: #4ade80; border: 1px solid var(--success); }
        .alert-error { background: #3d1414; color: #f87171; border: 1px solid var(--danger); }

        /* Grid Pengaturan */
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(420px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }
        @media(max-width: 900px) { .settings-grid { grid-template-columns: 1fr; } }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 18px;
            padding: 26px;
            box-shadow: var(--shadow-pro);
            transition: all 0.3s ease;
        }
        .card:hover {
            border-color: rgba(212, 175, 55, 0.4);
        }
        .card h3 {
            color: var(--gold-primary);
            font-size: 16px;
            font-weight: 800;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-subtle);
            padding-bottom: 12px;
            display: flex; align-items: center; gap: 8px;
        }

        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block; font-size: 11px; color: var(--text-muted);
            text-transform: uppercase; margin-bottom: 6px; font-weight: 700; letter-spacing: 0.5px;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 12px 14px;
            background: var(--bg-input); border: 1px solid #3f3f46;
            border-radius: 10px; color: #fff; font-size: 13.5px; font-family: inherit;
            transition: all 0.3s ease;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            border-color: var(--gold-primary); outline: none;
            box-shadow: 0 0 14px rgba(212, 175, 55, 0.3);
        }

        .btn-submit {
            width: 100%; padding: 13px;
            background: var(--gold-gradient); color: #09090b;
            border: none; border-radius: 10px; font-weight: 800; font-size: 14px;
            cursor: pointer; transition: all 0.3s ease;
            box-shadow: 0 4px 18px rgba(212, 175, 55, 0.35);
            margin-top: 10px;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.5);
            filter: brightness(1.1);
        }

        /* Tabel Info Server & List Admin */
        .info-table {
            width: 100%; border-collapse: collapse; margin-top: 5px;
        }
        .info-table td {
            padding: 12px 14px; border-bottom: 1px solid var(--border-subtle);
            font-size: 13px; color: #d4d4d8;
        }
        .info-table tr:last-child td { border-bottom: none; }
        .info-label { color: var(--text-muted); font-weight: 600; width: 45%; }
        .info-val { font-weight: 700; color: #fff; }

        @keyframes fadeInSmooth {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <!-- Memanggil Sidebar Navigasi -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Wrapper Content -->
    <div class="main-wrapper">
        <div class="top-navbar">
            <div class="page-title-box">
                <span class="page-icon">⚙️</span>
                <div>
                    <div class="breadcrumb">Analytics & System / <span>Pengaturan Admin & Sistem</span></div>
                    <h1 class="page-title">Pusat Pengaturan Sistem Enterprise</h1>
                </div>
            </div>
            <div class="server-status-badge">
                <span class="dot-green"></span>
                <span>Database: <b style="color: #4ade80;">Terhubung</b></span>
            </div>
        </div>

        <?php if(!empty($pesan_sukses)): ?>
            <div class="alert-box alert-success">✅ <?= htmlspecialchars($pesan_sukses); ?></div>
        <?php endif; ?>

        <?php if(!empty($pesan_error)): ?>
            <div class="alert-box alert-error">⚠️ <?= htmlspecialchars($pesan_error); ?></div>
        <?php endif; ?>

        <!-- Grid Pengaturan Utama -->
        <div class="settings-grid">
            
            <!-- 1. Keamanan & Profil Admin Utama -->
            <div class="card">
                <h3>🔐 Keamanan & Profil Administrator</h3>
                <form action="" method="POST">
                    <div class="form-group">
                        <label>Username Utama</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($admin_nama); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Password Baru (Opsional)</label>
                        <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah password">
                    </div>

                    <button type="submit" name="update_profil" class="btn-submit">💾 SIMPAN PROFIL & KEAMANAN</button>
                </form>
            </div>

            <!-- 2. Identitas Sekolah & Instansi -->
            <div class="card">
                <h3>🏫 Identitas Instansi & Sekolah</h3>
                <form action="" method="POST">
                    <div class="form-group">
                        <label>Nama Resmi Sekolah</label>
                        <input type="text" name="nama_sekolah" value="<?= htmlspecialchars($setting['nama_sekolah']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Kepala Sekolah / Pimpinan</label>
                        <input type="text" name="kepala_sekolah" value="<?= htmlspecialchars($setting['kepala_sekolah']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Tahun Ajaran Aktif</label>
                        <input type="text" name="tahun_ajaran" value="<?= htmlspecialchars($setting['tahun_ajaran']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Alamat Instansi</label>
                        <textarea name="alamat" rows="2" required><?= htmlspecialchars($setting['alamat']); ?></textarea>
                    </div>

                    <button type="submit" name="update_sekolah" class="btn-submit">💾 SIMPAN IDENTITAS SEKOLAH</button>
                </form>
            </div>

            <!-- 3. Informasi Server & Lingkungan Sistem -->
            <div class="card">
                <h3>💻 Informasi Server & Lingkungan Sistem</h3>
                <table class="info-table">
                    <tr>
                        <td class="info-label">Versi PHP</td>
                        <td class="info-val"><?= phpversion(); ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Database Engine</td>
                        <td class="info-val" style="color: var(--gold-primary);">MySQL / MariaDB Pro</td>
                    </tr>
                    <tr>
                        <td class="info-label">Status Server</td>
                        <td class="info-val" style="color: #4ade80;">Local XAMPP Active</td>
                    </tr>
                    <tr>
                        <td class="info-label">Status Sesi Login</td>
                        <td class="info-val" style="color: #38bdf8;">Terautentikasi (Active)</td>
                    </tr>
                    <tr>
                        <td class="info-label">Waktu Server Lokal</td>
                        <td class="info-val"><?= date('H:i:s'); ?> WIB</td>
                    </tr>
                    <tr>
                        <td class="info-label">Zona Waktu</td>
                        <td class="info-val"><?= date_default_timezone_get(); ?></td>
                    </tr>
                </table>
            </div>

            <!-- 4. Manajemen Akun Multi-Admin -->
            <div class="card">
                <h3>👥 Manajemen Daftar Administrator</h3>
                <table class="info-table" style="margin-bottom: 15px;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-gold);">
                            <td class="info-label" style="color: var(--gold-primary); font-weight:800;">Nama Lengkap</td>
                            <td class="info-val" style="color: var(--gold-primary); font-weight:800;">Username</td>
                            <td class="info-val" style="color: var(--gold-primary); font-weight:800;">Role</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($list_admin && mysqli_num_rows($list_admin) > 0) {
                            while ($adm = mysqli_fetch_assoc($list_admin)) {
                                echo "<tr>";
                                echo "<td class='info-label'>" . htmlspecialchars($adm['nama_lengkap']) . "</td>";
                                echo "<td class='info-val'>" . htmlspecialchars($adm['username']) . "</td>";
                                echo "<td><span style='background: rgba(212,175,55,0.15); color: var(--gold-primary); padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;'>" . htmlspecialchars($adm['role']) . "</span></td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</body>
</html>