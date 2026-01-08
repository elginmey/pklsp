<?php
include 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemegang Sertifikat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            padding: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background: #f2f2f2;
        }
    </style>
</head>
<body>

<h2>Data Pemegang Sertifikat</h2>

<table>
    <tr>
        <th>No</th>
        <th>Nama Peserta</th>
        <th>No Sertifikat</th>
        <th>Skema Kompetensi</th>
        <th>Tanggal Lahir</th>
        <th>Hasil</th>
    </tr>

    <?php
    $no = 1;
    $query = mysqli_query($koneksi, "SELECT * FROM pemegang_sertifikat");

    if (mysqli_num_rows($query) > 0) {
        while ($data = mysqli_fetch_assoc($query)) {
    ?>
        <tr>
            <td><?= $no++; ?></td>
            <td>
    <a href="biodata.php?id=<?= $data['id']; ?>">
        <?= $data['nama_peserta']; ?>
    </a>
</td>

            <td><?= $data['no_sertifikat']; ?></td>
            <td><?= $data['skema_kompetensi']; ?></td>
            <td><?= $data['tanggal_lahir']; ?></td>
            <td><?= $data['hasil']; ?></td>
        </tr>
    <?php
        }
    } else {
        echo "<tr><td colspan='6'>Data belum tersedia</td></tr>";
    }
    ?>
</table>

</body>
</html>

