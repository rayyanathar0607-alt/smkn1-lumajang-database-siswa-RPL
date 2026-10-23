<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? $_GET['id'] : '';
$query = "SELECT * FROM data_siswa WHERE ID = '$id' OR id = '$id' OR NISN = '$id' LIMIT 1";
$result = mysqli_query($koneksi, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    $result = mysqli_query($koneksi, "SELECT * FROM siswa LIMIT 1");
}

$data = mysqli_fetch_array($result, MYSQLI_BOTH);
$nis   = isset($data['NISN']) ? $data['NISN'] : (isset($data['nis']) ? $data['nis'] : '-');
$nama  = isset($data['Nama_Lengkap']) ? $data['Nama_Lengkap'] : (isset($data['nama']) ? $data['nama'] : '-');
$kelas = isset($data['Kelas']) ? $data['Kelas'] : (isset($data['kelas']) ? $data['kelas'] : '-');
$jur   = isset($data['Jurusan']) ? $data['Jurusan'] : (isset($data['jurusan']) ? $data['jurusan'] : '-');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Pelajar - Black & Gold</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            padding: 30px; 
            background-color: #121212; 
            color: #e0e0e0; 
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 90vh;
        }
        .card-container {
            width: 100%;
            max-width: 520px;
            background: linear-gradient(135deg, #1e1e1e 0%, #121212 100%);
            border: 2px solid #d4af37;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.25);
            overflow: hidden;
        }
        .card-header {
            background-color: #d4af37;
            color: #121212;
            padding: 15px 20px;
            text-align: center;
        }
        .card-header h3 {
            margin: 0;
            font-size: 14px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            font-weight: 800;
        }
        .card-header h2 {
            margin: 3px 0 0 0;
            font-size: 18px;
            letter-spacing: 2px;
            font-weight: 800;
        }
        .card-body {
            padding: 25px;
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .photo-box {
            width: 120px;
            height: 160px;
            background-color: #252525;
            border: 2px solid #d4af37;
            border-radius: 6px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
        }
        .photo-box .icon {
            font-size: 45px;
            color: #d4af37;
            margin-bottom: 5px;
        }
        .photo-box .label-photo {
            font-size: 11px;
            color: #888;
            font-weight: 600;
        }
        .info-container {
            flex: 1;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 8px 4px;
            font-size: 14px;
            vertical-align: top;
        }
        .info-table td.label {
            color: #d4af37;
            font-weight: 600;
            width: 80px;
        }
        .info-table td.colon {
            color: #d4af37;
            width: 10px;
        }
        .info-table td.value {
            color: #ffffff;
            font-weight: 500;
        }
        .btn-group {
            margin-top: 25px;
            display: flex;
            gap: 12px;
            width: 100%;
            max-width: 520px;
        }
        .btn {
            flex: 1;
            padding: 12px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
            text-align: center;
            border: none;
            transition: 0.3s;
        }
        .btn-cetak {
            background-color: #d4af37;
            color: #121212;
        }
        .btn-cetak:hover {
            background-color: #b3922f;
        }
        .btn-kembali {
            background-color: #222;
            color: #d4af37;
            border: 1px solid #d4af37;
        }
        .btn-kembali:hover {
            background-color: #d4af37;
            color: #121212;
        }
        @media print {
            body {
                background-color: #fff;
            }
            .btn-group {
                display: none;
            }
            .card-container {
                border: 2px solid #000;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

    <div class="card-container">
        <div class="card-header">
            <h2>SMK NEGERI 1 LUMAJANG</h2>
            <h3>KARTU PELAJAR</h3>
        </div>
        <div class="card-body">
            <div class="photo-box">
                <div class="icon"></div>
                <div class="label-photo">FOTO 3x4</div>
            </div>

            <div class="info-container">
                <table class="info-table">
                    <tr>
                        <td class="label">Nama</td>
                        <td class="colon">:</td>
                        <td class="value"><?php echo htmlspecialchars($nama); ?></td>
                    </tr>
                    <tr>
                        <td class="label">NISN</td>
                        <td class="colon">:</td>
                        <td class="value"><?php echo htmlspecialchars($nis); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Kelas</td>
                        <td class="colon">:</td>
                        <td class="value"><?php echo htmlspecialchars($kelas); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Jurusan</td>
                        <td class="colon">:</td>
                        <td class="value"><?php echo htmlspecialchars($jur); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="btn-group">
        <button onclick="window.print()" class="btn btn-cetak">🖨️ Cetak Kartu</button>
        <a href="index.php" class="btn btn-kembali">← Kembali</a>
    </div>

</body>
</html>