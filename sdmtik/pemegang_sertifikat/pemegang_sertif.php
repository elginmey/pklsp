<?php
include 'koneksi.php';

// ============================
// KONFIGURASI PAGINATION
// ============================
$limit = 15; // jumlah data per halaman

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = ($page < 1) ? 1 : $page;

$offset = ($page - 1) * $limit;

// ============================
// HITUNG TOTAL DATA
// ============================
$total_query = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pemegang_sertifikat");
$total_data = mysqli_fetch_assoc($total_query)['total'];
$total_page = ceil($total_data / $limit);

// ============================
// AMBIL DATA SESUAI HALAMAN
// ============================
$query = mysqli_query(
    $koneksi,
    "SELECT * FROM pemegang_sertifikat LIMIT $limit OFFSET $offset"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pemegang Sertifikat</title>
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
            color: #fff;
        }
        tr:nth-child(even) {
            background: #f2f2f2;
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination select {
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
        .info {
            text-align: center;
            margin-top: 10px;
            color: #555;
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
        <th>Status</th>
    </tr>

    <?php
    if (mysqli_num_rows($query) > 0) {
        $no = $offset + 1;
        while ($data = mysqli_fetch_assoc($query)) {
    ?>
        <tr>
            <td><?= $no++; ?></td>
            <td><?= $data['nama_peserta']; ?></td>
            <td><?= $data['no_sertifikat']; ?></td>
            <td><?= $data['skema_kompetensi']; ?></td>
            <td><?= $data['tanggal_keputusan']; ?></td>
        </tr>
    <?php
        }
    } else {
        echo "<tr><td colspan='6'>Data belum tersedia</td></tr>";
    }
    ?>
</table>

<!-- ============================
     PAGINATION DROPDOWN
     ============================ -->
<div class="pagination">
    <form method="GET">
        <label for="page">Pilih Halaman:</label>
        <select name="page" id="page" onchange="this.form.submit()">
            <?php for ($i = 1; $i <= $total_page; $i++): ?>
                <option value="<?= $i; ?>" <?= ($i == $page) ? 'selected' : ''; ?>>
                    Halaman <?= $i; ?>
                </option>
            <?php endfor; ?>
        </select>
    </form>
</div>

<!-- ============================
     INFO DATA
     ============================ -->
<div class="info">
    Menampilkan 
    <?= ($offset + 1); ?> 
    – 
    <?= min($offset + $limit, $total_data); ?> 
    dari <?= $total_data; ?> data
</div>

</body>
</html>
