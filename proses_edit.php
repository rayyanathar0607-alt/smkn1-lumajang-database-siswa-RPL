<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id      = $_POST['id'];
    $nis     = $_POST['nis'];
    $nama    = $_POST['nama'];
    $kelas   = $_POST['kelas'];
    $jurusan = $_POST['jurusan'];

    $query = "UPDATE siswa SET nis='$nis', nama='$nama', kelas='$kelas', jurusan='$jurusan' WHERE id='$id'";

    if (mysqli_query($koneksi, $query)) {
        header("Location: index.php?status=sukses_update");
    } else {
        echo "Gagal mengupdate data: " . mysqli_error($koneksi);
    }
}
?>