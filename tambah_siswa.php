<?php
/**
 * ==============================================================================
 * SMK NEGERI 1 LUMAJANG - ENTERPRISE MANAGEMENT SYSTEM PRO MAX
 * File          : tambah_siswa.php (Form Input Siswa Baru)
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

// Proses Simpan Data Baru
if (isset($_POST['simpan'])) {
    $nisn    = mysqli_real_escape_string($koneksi, $_POST['NISN']);
    $nama    = mysqli_real_escape_string($koneksi, $_POST['Nama_Lengkap']);
    $kelas   = mysqli_real_escape_string($koneksi, $_POST['Kelas']);
    $jurusan = mysqli_real_escape_string($koneksi, $_POST['Jurusan']);

    $insert = mysqli_query($koneksi, "INSERT INTO data_siswa (NISN, Nama_Lengkap, Kelas, Jurusan) VALUES ('$nisn', '$nama', '$kelas', '$jurusan')");

    if ($insert) {
        echo "<script>alert('Data siswa baru berhasil ditambahkan!'); window.location='data_siswa.php';</script>";
        exit();
    } else {
        $error = "Gagal menyimpan data: " . mysqli_error($koneksi);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Siswa Baru - SMK Negeri 1 Lumajang Pro Max</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #09090b; --card-bg: #141418; --border-color: #27272a;
            --gold-primary: #d4af37; --gold-gradient: linear-gradient(135deg, #fce080 0%, #d4af37 50%, #aa8c2c 100%);
            --text-main: #f4f4f5; --text-muted: #a1a1aa;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-dark); color: var(--text-main); display: flex; min-height: 100vh; overflow-x: hidden; }
        
        .main-content { flex: 1; padding: 32px; background: radial-gradient(circle at top right, rgba(212, 175, 55, 0.05), transparent 40%); }
        
        .page-header {
            background: linear-gradient(135deg, #18181b 0%, #09090b 100%);
            border: 1px solid var(--border-color); border-radius: 20px; padding: 24px 28px; margin-bottom: 24px;
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;
        }
        .header-title h1 { font-size: 22px; font-weight: 800; background: var(--gold-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .header-title p { color: var(--text-muted); font-size: 13px; margin-top: 4px; }
        
        .card-box {
            background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 20px; padding: 28px;
            max-width: 650px; margin: 0 auto; box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 13px; font-weight: 700; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-input {
            width: 100%; padding: 14px 18px; background: #09090b; border: 1px solid var(--border-color);
            border-radius: 12px; color: #fff; font-size: 14px; transition: all 0.3s ease;
        }
        .form-input:focus { outline: none; border-color: var(--gold-primary); box-shadow: 0 0 10px rgba(212, 175, 55, 0.15); }
        
        .button-group { display: flex; gap: 12px; margin-top: 24px; }
        .btn-gold {
            background: var(--gold-gradient); color: #000; font-weight: 800; padding: 14px 20px;
            border-radius: 12px; border: none; cursor: pointer; font-size: 14px; flex: 1; text-align: center;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none;
        }
        .btn-gold:hover { opacity: 0.9; }
        .btn-secondary {
            background: #27272a; color: #fff; font-weight: 700; padding: 14px 20px; border-radius: 12px;
            border: 1px solid var(--border-color); text-decoration: none; text-align: center; font-size: 14px; flex: 1; display: inline-block;
        }
        .btn-secondary:hover { background: #3f3f46; }

        @media (max-width: 768px) { body { flex-direction: column; } .main-content { padding: 16px; } .button-group { flex-direction: column; } }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <div class="header-title">
                <h1>➕ Tambah Peserta Didik Baru</h1>
                <p>Daftarkan siswa baru ke dalam database master sekolah</p>
            </div>
            <a href="data_siswa.php" class="btn-secondary" style="flex: unset; padding: 8px 16px; font-size: 12px;">&larr; Kembali</a>
        </div>

        <div class="card-box">
            <?php if (isset($error)) echo "<div style='background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #f87171; padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 13px;'>$error</div>"; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Nomor Induk Siswa Nasional (NISN)</label>
                    <input type="text" name="NISN" class="form-input" placeholder="Contoh: 2162214542" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Lengkap Siswa</label>
                    <input type="text" name="Nama_Lengkap" class="form-input" placeholder="Masukkan nama lengkap siswa..." required>
                </div>
                <div class="form-group">
                    <label class="form-label">Kelas Akademik</label>
                    <input type="text" name="Kelas" class="form-input" placeholder="Contoh: X, XI, XII" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Program Keahlian (Jurusan)</label>
                    <input type="text" name="Jurusan" class="form-input" placeholder="Contoh: RPL, TKJ, DKV" required>
                </div>

                <div class="button-group">
                    <button type="submit" name="simpan" class="btn-gold">💾 Simpan Data Siswa</button>
                    <a href="data_siswa.php" class="btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>