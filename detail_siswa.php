<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'koneksi.php';
if (!isset($_SESSION['admin_nama'])) { header("Location: login.php"); exit(); }

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$query = mysqli_query($koneksi, "SELECT * FROM data_siswa WHERE ID = $id");
$siswa = mysqli_fetch_assoc($query);

if (!$siswa) {
    echo "<script>alert('Data siswa tidak ditemukan!'); window.location='data_siswa.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Pelajar & Detail Resmi - SMK Negeri 1 Lumajang</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --bg-dark: #09090b; --card-bg: #141418; --border-color: #27272a; --gold-primary: #d4af37; --gold-gradient: linear-gradient(135deg, #fce080 0%, #d4af37 50%, #aa8c2c 100%); --text-main: #f4f4f5; --text-muted: #a1a1aa; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-dark); color: var(--text-main); display: flex; min-height: 100vh; }
        .main-content { flex: 1; padding: 32px; background: radial-gradient(circle at top right, rgba(212, 175, 55, 0.05), transparent 40%); }
        
        .card-preview-wrapper { max-width: 480px; margin: 0 auto; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 24px; padding: 24px; box-shadow: 0 15px 35px rgba(0,0,0,0.5); }
        
        /* Kartu Pelajar Desain Standar Nasional 3x4 Black & Gold */
        .student-id-card {
            width: 100%;
            height: 250px;
            background: linear-gradient(135deg, #18181b 0%, #09090b 100%);
            border: 2px solid var(--gold-primary);
            border-radius: 18px;
            padding: 16px;
            position: relative;
            box-shadow: 0 10px 30px rgba(212,175,55,0.25);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card-top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #27272a;
            padding-bottom: 8px;
        }
        .school-name { font-size: 11px; font-weight: 800; color: var(--gold-primary); letter-spacing: 0.5px; }
        .badge-pro { background: var(--gold-gradient); color: #000; font-size: 8.5px; font-weight: 800; padding: 2px 8px; border-radius: 10px; }

        .card-body-section {
            display: flex;
            gap: 16px;
            align-items: center;
            margin-top: 6px;
        }

        /* Kotak Foto Ukuran 3 x 4 */
        .photo-box-3x4 {
            width: 75px;
            height: 100px;
            background: #1f1f23;
            border: 1.5px dashed var(--gold-primary);
            border-radius: 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            flex-shrink: 0;
        }
        .photo-placeholder { font-size: 32px; }
        .photo-label-size {
            position: absolute;
            bottom: 2px;
            font-size: 8px;
            color: var(--gold-primary);
            font-weight: 700;
            background: rgba(0,0,0,0.6);
            padding: 1px 4px;
            border-radius: 4px;
        }

        .student-details h4 { font-size: 13.5px; font-weight: 700; color: #fff; margin-bottom: 4px; line-height: 1.2; }
        .student-details p { font-size: 11px; color: var(--text-muted); margin-bottom: 3px; }
        .student-details strong { color: var(--gold-primary); }

        .card-bottom-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #27272a;
            padding-top: 6px;
            font-size: 9.5px;
            color: var(--text-muted);
        }

        .btn-gold { background: var(--gold-gradient); color: #000; font-weight: 700; padding: 12px 20px; border-radius: 12px; border: none; cursor: pointer; font-size: 14px; width: 100%; text-align: center; display: block; text-decoration: none; margin-top: 16px; }
        
        @media (max-width: 480px) {
            .main-content { padding: 16px; }
            .student-id-card { height: 240px; }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <div class="card-preview-wrapper">
            <div style="text-align: center; margin-bottom: 16px;">
                <h2 style="font-size: 18px; font-weight: 800; background: var(--gold-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">🪪 Kartu Pelajar Digital Resmi</h2>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">Identitas Resmi Peserta Didik SMK Negeri 1 Lumajang</p>
            </div>

            <!-- Desain Kartu Pelajar -->
            <div class="student-id-card">
                <div class="card-top-header">
                    <span class="school-name">SMK NEGERI 1 LUMAJANG</span>
                    <span class="badge-pro">PRO MAX</span>
                </div>

                <div class="card-body-section">
                    <!-- Kotak Foto 3 x 4 -->
                    <div class="photo-box-3x4">
                        <div class="photo-placeholder"></div>
                        <span class="photo-label-size">3 x 4</span>
                    </div>

                    <div class="student-details">
                        <h4><?= htmlspecialchars($siswa['Nama_Lengkap']); ?></h4>
                        <p>NISN: <strong><?= htmlspecialchars($siswa['NISN']); ?></strong></p>
                        <p>Kelas: <strong><?= htmlspecialchars($siswa['Kelas']); ?></strong></p>
                        <p>Jurusan: <strong><?= htmlspecialchars($siswa['Jurusan']); ?></strong></p>
                        <p>Status: <span style="color: #4ade80; font-weight: 700;">Aktif / Resmi</span></p>
                    </div>
                </div>

                <div class="card-bottom-footer">
                    <span>Berlaku Selama Menjadi Siswa</span>
                    <span>TA 2026/2027</span>
                </div>
            </div>

            <button onclick="window.print();" class="btn-gold">🖨️ Cetak / Download Kartu Pelajar</button>
            <a href="data_siswa.php" class="btn-gold" style="background: #27272a; color: #fff; margin-top: 8px;">&larr; Kembali ke Data Siswa</a>
        </div>
    </main>
</body>
</html>