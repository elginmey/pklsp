<?php
include 'koneksi.php';

// ============================
// KONFIGURASI
// ============================
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 15;
$per_page = ($per_page < 15) ? 15 : (($per_page > 50) ? 50 : $per_page);

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = ($page < 1) ? 1 : $page;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

$offset = ($page - 1) * $per_page;

// ============================
// KONDISI SQL
// ============================
$where = "WHERE 1=1";

if ($search != '') {
    $where .= " AND (nama_peserta LIKE '%$search%' OR no_sertifikat LIKE '%$search%')";
}

if ($filter == 'aktif') {
    $where .= " AND DATE_ADD(tanggal_keputusan, INTERVAL 3 YEAR) >= CURDATE()";
} elseif ($filter == 'expired') {
    $where .= " AND DATE_ADD(tanggal_keputusan, INTERVAL 3 YEAR) < CURDATE()";
}

// ============================
// TOTAL DATA
// ============================
$total_query = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pemegang_sertifikat $where");
$total_data = mysqli_fetch_assoc($total_query)['total'];
$total_page = ceil($total_data / $per_page);

// ============================
// DATA
// ============================
$query = mysqli_query(
    $koneksi,
    "SELECT * FROM pemegang_sertifikat $where LIMIT $per_page OFFSET $offset"
);

// ============================
// CARD COUNT
// ============================
$jumlah_data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) total FROM pemegang_sertifikat"))['total'];
$jumlah_aktif = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) total FROM pemegang_sertifikat WHERE DATE_ADD(tanggal_keputusan, INTERVAL 3 YEAR) >= CURDATE()"))['total'];
$jumlah_expired = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) total FROM pemegang_sertifikat WHERE DATE_ADD(tanggal_keputusan, INTERVAL 3 YEAR) < CURDATE()"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Pemegang Sertifikat</title>
<style>
body {font-family:Arial;background:#f4f6f8;padding:20px}
h2{text-align:center}
.cards{display:flex;gap:15px;margin-bottom:20px}
.card{flex:1;padding:15px;border-radius:8px;color:#fff;text-align:center;cursor:pointer}
.card.total{background:#007bff}
.card.aktif{background:#28a745}
.card.expired{background:#dc3545}
.card:hover{opacity:.9}
.search-box{display:flex;gap:10px;margin-bottom:15px}
table{width:100%;border-collapse:collapse;background:#fff}
th,td{padding:10px;border:1px solid #ddd;text-align:center}
th{background:#343a40;color:#fff}
.pagination{display:flex;justify-content:space-between;align-items:center;margin-top:15px}
select, input{padding:6px}
button{padding:6px 12px}
.status-aktif{color:green;font-weight:bold}
.status-expired{color:red;font-weight:bold}
</style>
</head>
<body>

<h2>Data Pemegang Sertifikat</h2>

<!-- ============================
     FILTER CARD
============================ -->
<div class="cards">
    <div class="card total" onclick="location.href='?'">
        Jumlah Data<br><b><?= $jumlah_data ?></b>
    </div>
    <div class="card aktif" onclick="location.href='?filter=aktif'">
        Aktif<br><b><?= $jumlah_aktif ?></b>
    </div>
    <div class="card expired" onclick="location.href='?filter=expired'">
        Expired<br><b><?= $jumlah_expired ?></b>
    </div>
</div>

<!-- ============================
     SEARCH & PER PAGE
============================ -->
<form method="GET" class="search-box">
    <input type="text" name="search" placeholder="Cari nama / no sertifikat" value="<?= $search ?>">
    <select name="per_page">
        <?php for($i=15;$i<=50;$i+=5): ?>
            <option value="<?= $i ?>" <?= ($per_page==$i)?'selected':'' ?>><?= $i ?> / page</option>
        <?php endfor; ?>
    </select>
    <button type="submit">Terapkan</button>
</form>

<table>
<tr>
    <th>No</th>
    <th>Nama</th>
    <th>No Sertifikat</th>
    <th>Skema</th>
    <th>Status</th>
</tr>

<?php
if(mysqli_num_rows($query)>0){
    $no=$offset+1;
    while($data=mysqli_fetch_assoc($query)){
        $expired = date('Y-m-d', strtotime('+3 years', strtotime($data['tanggal_keputusan'])));
        $status = (date('Y-m-d') <= $expired)
            ? "<span class='status-aktif'>AKTIF</span>"
            : "<span class='status-expired'>EXPIRED</span>";
?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= $data['nama_peserta'] ?></td>
    <td><?= $data['no_sertifikat'] ?></td>
    <td><?= $data['skema_kompetensi'] ?></td>
    <td><?= $status ?></td>
</tr>
<?php }} else { ?>
<tr><td colspan="5">Data tidak ditemukan</td></tr>
<?php } ?>
</table>

<!-- ============================
     PAGINATION
============================ -->
<div class="pagination">
    <div>
        <?php if($page>1): ?>
            <a href="?page=<?= $page-1 ?>&search=<?= $search ?>&filter=<?= $filter ?>&per_page=<?= $per_page ?>">
                <button>Prev</button>
            </a>
        <?php endif; ?>
    </div>

    <div>
        Halaman <?= $page ?> dari <?= $total_page ?>
    </div>

    <div>
        <?php if($page<$total_page): ?>
            <a href="?page=<?= $page+1 ?>&search=<?= $search ?>&filter=<?= $filter ?>&per_page=<?= $per_page ?>">
                <button>Next</button>
            </a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
  