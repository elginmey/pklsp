<?php
include 'koneksi.php';

if (!isset($_GET['id'])) {
    echo "Data tidak ditemukan";
    exit;
}

$id = $_GET['id'];

$query = mysqli_query($koneksi, "SELECT * FROM pemegang_sertifikat WHERE id='$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Data tidak ditemukan";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Biodata Peserta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            padding: 20px;
        }
        .card {
            background: #fff;
            padding: 20px;
            max-width: 600px;
            margin: auto;
            border-radius: 8px;
        }
        h2 {
            text-align: center;
        }
        table {
            width: 100%;
        }
        td {
            padding: 8px;
        }
        .back {
            margin-top: 15px;
            display: inline-block;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Biodata Pemegang Sertifikat</h2>
    <table>
        <tr>
            <td>Nama Peserta</td>
            <td>: <?= $data['nama_peserta']; ?></td>
        </tr>
        <tr>
            <td>No Sertifikat</td>
            <td>: <?= $data['no_sertifikat']; ?></td>
        </tr>
        <tr>
            <td>Tanggal Lahir</td>
            <td>: <?= $data['tanggal_lahir']; ?></td>
        </tr>
        <tr>
            <td>Organisasi</td>
            <td>: <?= $data['organisasi']; ?></td>
        </tr>
        <tr>
            <td>Skema Kompetensi</td>
            <td>: <?= $data['skema_kompetensi']; ?></td>
        </tr>
        <tr>
            <td>Hasil</td>
            <td>: <?= $data['hasil']; ?></td>
        </tr>
    </table>

    <a href="pemegang_sertifikat.php" class="back">⬅ Kembali</a>
</div>

</body>
</html>
