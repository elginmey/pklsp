<?php
include 'koneksi.php';

// ============================
// KONFIGURASI
// ============================
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 15;
$per_page = ($per_page < 15) ? 15 : (($per_page > 50) ? 50 : $per_page);

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = ($page < 1) ? 1 : $page;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

$offset = ($page - 1) * $per_page;

// ============================
// PREPARED STATEMENT SETUP
// ============================
$params = [];
$types = '';
$where = "WHERE 1=1";

// Search condition dengan prepared statement
if ($search != '') {
    $where .= " AND (
        id LIKE ? OR
        nama_peserta LIKE ? OR
        no_sertifikat LIKE ?
    )";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

// Filter condition
if ($filter == 'aktif') {
    $where .= " AND DATE_ADD(tanggal_keputusan, INTERVAL 3 YEAR) >= CURDATE()";
} elseif ($filter == 'expired') {
    $where .= " AND DATE_ADD(tanggal_keputusan, INTERVAL 3 YEAR) < CURDATE()";
}

// ============================
// TOTAL DATA dengan Prepared Statement
// ============================
$total_sql = "SELECT COUNT(*) as total FROM pemegang_sertifikat $where";
$stmt_total = $koneksi->prepare($total_sql);

if (!empty($params)) {
    $stmt_total->bind_param($types, ...$params);
}

$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_data = $total_result->fetch_assoc()['total'];
$total_page = ceil($total_data / $per_page);
$stmt_total->close();

// ============================
// DATA dengan Prepared Statement
// ============================
$data_sql = "SELECT * FROM pemegang_sertifikat $where LIMIT ? OFFSET ?";
$stmt_data = $koneksi->prepare($data_sql);

// Gabungkan params
$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt_data->bind_param($types, ...$params);
$stmt_data->execute();
$query = $stmt_data->get_result();

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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Pemegang Sertifikat</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 20px;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
}

h2 {
    text-align: center;
    margin-bottom: 30px;
    color: white;
    font-size: 2em;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}

/* Cards */
.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.card {
    padding: 25px;
    border-radius: 15px;
    color: #fff;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.4);
}

.card.total {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card.aktif {
    background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
}

.card.expired {
    background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
}

.card b {
    display: block;
    font-size: 2.5em;
    margin-top: 10px;
}

/* Search Box */
.search-box {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

input[type="text"],
select {
    flex: 1;
    min-width: 200px;
    padding: 12px 15px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

input[type="text"]:focus,
select:focus {
    outline: none;
    box-shadow: 0 2px 15px rgba(102, 126, 234, 0.4);
}

/* Table */
.table-container {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
}

th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 13px;
    letter-spacing: 0.5px;
}

tr:hover {
    background: #f8f9ff;
}

tr:last-child td {
    border-bottom: none;
}

.status-aktif {
    display: inline-block;
    padding: 5px 15px;
    border-radius: 20px;
    background: #d4edda;
    color: #155724;
    font-weight: bold;
    font-size: 12px;
}

.status-expired {
    display: inline-block;
    padding: 5px 15px;
    border-radius: 20px;
    background: #f8d7da;
    color: #721c24;
    font-weight: bold;
    font-size: 12px;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.pagination button {
    padding: 10px 25px;
    border: none;
    border-radius: 8px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.pagination button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.pagination button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.page-info {
    color: #333;
    font-weight: 500;
}

/* Responsive */
@media (max-width: 768px) {
    .cards {
        grid-template-columns: 1fr;
    }
    
    .search-box {
        flex-direction: column;
    }
    
    table {
        font-size: 12px;
    }
    
    th, td {
        padding: 10px 5px;
    }
    
    .pagination {
        flex-direction: column;
        gap: 15px;
    }
}

.no-data {
    text-align: center;
    padding: 40px;
    color: #999;
    font-size: 16px;
}
</style>
</head>
<body>

<div class="container">
    <h2>📋 Data Pemegang Sertifikat</h2>

    <!-- Filter Cards -->
    <div class="cards">
        <div class="card total" onclick="location.href='?'">
            <div>Jumlah Data</div>
            <b><?= number_format($jumlah_data) ?></b>
        </div>
        <div class="card aktif" onclick="location.href='?filter=aktif'">
            <div>Sertifikat Aktif</div>
            <b><?= number_format($jumlah_aktif) ?></b>
        </div>
        <div class="card expired" onclick="location.href='?filter=expired'">
            <div>Sertifikat Expired</div>
            <b><?= number_format($jumlah_expired) ?></b>
        </div>
    </div>

    <!-- Search & Filter -->
    <form method="GET" class="search-box" id="filterForm">
        <input
            type="text"
            name="search"
            placeholder="🔍 Cari ID / Nama / No Sertifikat"
            value="<?= htmlspecialchars($search) ?>"
            onkeyup="autoSubmit()"
        >

        <select name="per_page" onchange="autoSubmit()">
            <?php for($i=15; $i<=50; $i+=5): ?>
                <option value="<?= $i ?>" <?= ($per_page==$i)?'selected':'' ?>>
                    <?= $i ?> data / halaman
                </option>
            <?php endfor; ?>
        </select>

        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
    </form>

    <!-- Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Nama Peserta</th>
                    <th>No Sertifikat</th>
                    <th>Skema Kompetensi</th>
                    <th style="width: 120px;">Status</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if($query->num_rows > 0) {
                $no = $offset + 1;
                while($data = $query->fetch_assoc()) {
                    $expired = date('Y-m-d', strtotime('+3 years', strtotime($data['tanggal_keputusan'])));
                    $status = (date('Y-m-d') <= $expired)
                        ? "<span class='status-aktif'>✓ AKTIF</span>"
                        : "<span class='status-expired'>✗ EXPIRED</span>";
            ?>
                <tr>
                    <td style="text-align: center;"><?= $no++ ?></td>
                    <td><?= htmlspecialchars($data['nama_peserta']) ?></td>
                    <td><?= htmlspecialchars($data['no_sertifikat']) ?></td>
                    <td><?= htmlspecialchars($data['skema_kompetensi']) ?></td>
                    <td style="text-align: center;"><?= $status ?></td>
                </tr>
            <?php 
                }
            } else { 
            ?>
                <tr>
                    <td colspan="5" class="no-data">
                        📭 Data tidak ditemukan
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        <div>
            <?php if($page > 1): ?>
                <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>&per_page=<?= $per_page ?>">
                    <button>← Prev</button>
                </a>
            <?php else: ?>
                <button disabled>← Prev</button>
            <?php endif; ?>
        </div>

        <div class="page-info">
            Halaman <strong><?= $page ?></strong> dari <strong><?= $total_page ?: 1 ?></strong>
        </div>

        <div>
            <?php if($page < $total_page): ?>
                <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>&per_page=<?= $per_page ?>">
                    <button>Next →</button>
                </a>
            <?php else: ?>
                <button disabled>Next →</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Auto Submit Script -->
<script>
let timer = null;
function autoSubmit() {
    clearTimeout(timer);
    timer = setTimeout(() => {
        document.getElementById('filterForm').submit();
    }, 500);
}
</script>

</body>
</html>
<?php
// Clean up
$stmt_data->close();
$koneksi->close();
?>