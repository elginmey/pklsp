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

if ($filter == 'aktif') {
    $where .= " AND DATE_ADD(tanggal_keputusan, INTERVAL 3 YEAR) >= CURDATE()";
} elseif ($filter == 'expired') {
    $where .= " AND DATE_ADD(tanggal_keputusan, INTERVAL 3 YEAR) < CURDATE()";
}

// ============================
// TOTAL DATA
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
// DATA
// ============================
$data_sql = "SELECT * FROM pemegang_sertifikat $where LIMIT ? OFFSET ?";
$stmt_data = $koneksi->prepare($data_sql);

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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, )#7e8ba3 100%;
    min-height: 100vh;
    padding: 30px 20px;
    position: relative;
    overflow-x: hidden;
}

body::before {
    content: '';
    position: fixed;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
    background-size: 50px 50px;
    animation: moveGrid 20s linear infinite;
    pointer-events: none;
}

@keyframes moveGrid {
    0% { transform: translate(0, 0); }
    100% { transform: translate(50px, 50px); }
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

/* Header */
.header {
    text-align: center;
    margin-bottom: 40px;
    animation: fadeInDown 0.6s ease;
}

.header h1 {
    color: black;
    font-size: 2.5em;
    font-weight: 700;
    text-shadow: 0 4px 20px rgba(0,0,0,0.3);
    margin-bottom: 10px;
}

.header p {
    color: black;
    font-size: 1.1em;
    font-weight: 300;
}

/* Cards */
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 35px;
    animation: fadeInUp 0.6s ease 0.2s both;
}

.stat-card {
    background: rgba(255,255,255,0.95);
    padding: 30px;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 5px;
    background: linear-gradient(90deg, #1e88e5, #42a5f5);
    transform: scaleX(0);
    transition: transform 0.4s ease;
}

.stat-card:hover::before {
    transform: scaleX(1);
}

.stat-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.stat-card.total {
    background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
    color: white;
}

.stat-card.aktif {
    background: linear-gradient(135deg, #43a047 0%, #2e7d32 100%);
    color: white;
}

.stat-card.expired {
    background: linear-gradient(135deg, #e53935 0%, #c62828 100%);
    color: white;
}

.stat-card .icon {
    font-size: 3em;
    opacity: 0.3;
    position: absolute;
    right: 20px;
    top: 20px;
}

.stat-card .label {
    font-size: 0.95em;
    font-weight: 500;
    margin-bottom: 10px;
    opacity: 0.9;
}

.stat-card .value {
    font-size: 2.8em;
    font-weight: 700;
    line-height: 1;
}

/* Control Panel */
.control-panel {
    background: rgba(255,255,255,0.95);
    padding: 25px;
    border-radius: 20px;
    margin-bottom: 30px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    animation: fadeInUp 0.6s ease 0.4s both;
}

.search-container {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
}

.search-wrapper {
    flex: 1;
    min-width: 300px;
    position: relative;
}

.search-wrapper i {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: #1e88e5;
    font-size: 1.1em;
}

.search-wrapper input {
    width: 100%;
    padding: 15px 20px 15px 50px;
    border: 2px solid #e3f2fd;
    border-radius: 50px;
    font-size: 15px;
    transition: all 0.3s ease;
    font-family: 'Poppins', sans-serif;
}

.search-wrapper input:focus {
    outline: none;
    border-color: #1e88e5;
    box-shadow: 0 0 0 4px rgba(30,136,229,0.1);
}

.select-wrapper {
    position: relative;
}

.select-wrapper select {
    padding: 15px 45px 15px 20px;
    border: 2px solid #e3f2fd;
    border-radius: 50px;
    font-size: 15px;
    cursor: pointer;
    background: white;
    transition: all 0.3s ease;
    font-family: 'Poppins', sans-serif;
    appearance: none;
}

.select-wrapper::after {
    content: '\f107';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #1e88e5;
}

.select-wrapper select:focus {
    outline: none;
    border-color: #1e88e5;
    box-shadow: 0 0 0 4px rgba(30,136,229,0.1);
}

/* Table */
.table-wrapper {
    background: rgba(255,255,255,0.95);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    animation: fadeInUp 0.6s ease 0.6s both;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
}

th {
    padding: 20px 15px;
    text-align: left;
    color: white;
    font-weight: 600;
    font-size: 0.95em;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

tbody tr {
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}

tbody tr:hover {
    background: #e3f2fd;
    transform: scale(1.01);
}

td {
    padding: 18px 15px;
    color: #333;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 0.85em;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.aktif {
    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    color: #2e7d32;
}

.status-badge.expired {
    background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
    color: #c62828;
}

.no-data {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.no-data i {
    font-size: 4em;
    margin-bottom: 20px;
    opacity: 0.3;
}

/* Pagination */
.pagination-wrapper {
    background: rgba(255,255,255,0.95);
    padding: 25px;
    border-radius: 20px;
    margin-top: 30px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    animation: fadeInUp 0.6s ease 0.8s both;
}

.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.pagination .page-btn,
.pagination .page-num {
    padding: 12px 20px;
    border: 2px solid #e3f2fd;
    background: white;
    color: #1565c0;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-family: 'Poppins', sans-serif;
}

.pagination .page-btn:hover:not(:disabled),
.pagination .page-num:hover:not(.active) {
    background: #e3f2fd;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(30,136,229,0.2);
}

.pagination .page-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.pagination .page-num.active {
    background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
    color: white;
    border-color: #1565c0;
    transform: scale(1.1);
    box-shadow: 0 5px 20px rgba(30,136,229,0.4);
}

.pagination .dots {
    padding: 12px 8px;
    color: #1565c0;
    font-weight: 600;
}

/* Animations */
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .header h1 {
        font-size: 1.8em;
    }
    
    .stats-cards {
        grid-template-columns: 1fr;
    }
    
    .search-container {
        flex-direction: column;
    }
    
    .search-wrapper {
        min-width: 100%;
    }
    
    table {
        font-size: 13px;
    }
    
    th, td {
        padding: 12px 8px;
    }
    
    .pagination {
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .pagination .page-btn,
    .pagination .page-num {
        padding: 10px 15px;
        font-size: 13px;
    }
}

/* Loading Animation */
.loading {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header">
        <h1> Data Pemegang Sertifikat</h1>
        <p>Sistem Manajemen Sertifikat Kompetensi</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-cards">
        <div class="stat-card total" onclick="location.href='?'">
            <i class="fas fa-database icon"></i>
            <div class="label">Total Data</div>
            <div class="value"><?= number_format($jumlah_data) ?></div>
        </div>
        <div class="stat-card aktif" onclick="location.href='?filter=aktif'">
            <i class="fas fa-check-circle icon"></i>
            <div class="label">Sertifikat Aktif</div>
            <div class="value"><?= number_format($jumlah_aktif) ?></div>
        </div>
        <div class="stat-card expired" onclick="location.href='?filter=expired'">
            <i class="fas fa-times-circle icon"></i>
            <div class="label">Sertifikat Expired</div>
            <div class="value"><?= number_format($jumlah_expired) ?></div>
        </div>
    </div>

    <!-- Control Panel -->
    <div class="control-panel">
        <form method="GET" class="search-container" id="filterForm">
            <div class="search-wrapper">
                <i class="fas fa-search"></i>
                <input
                    type="text"
                    name="search"
                    placeholder="Cari berdasarkan ID, Nama, atau No Sertifikat..."
                    value="<?= htmlspecialchars($search) ?>"
                    onkeyup="autoSubmit()"
                >
            </div>

            <div class="select-wrapper">
                <select name="per_page" onchange="autoSubmit()">
                    <?php for($i=15; $i<=50; $i+=5): ?>
                        <option value="<?= $i ?>" <?= ($per_page==$i)?'selected':'' ?>>
                            <?= $i ?> Data per halaman
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
        </form>
    </div>

    <!-- Table -->
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th style="width: 80px; text-align: center;">No</th>
                    <th>Nama Peserta</th>
                    <th>No Sertifikat</th>
                    <th>Skema Kompetensi</th>
                    <th style="width: 150px; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if($query->num_rows > 0) {
                $no = $offset + 1;
                while($data = $query->fetch_assoc()) {
                    $expired = date('Y-m-d', strtotime('+3 years', strtotime($data['tanggal_keputusan'])));
                    $is_aktif = (date('Y-m-d') <= $expired);
                    $status_class = $is_aktif ? 'aktif' : 'expired';
                    $status_icon = $is_aktif ? 'fa-check-circle' : 'fa-times-circle';
                    $status_text = $is_aktif ? 'Aktif' : 'Expired';
            ?>
                <tr>
                    <td style="text-align: center; font-weight: 600; color: #1565c0;"><?= $no++ ?></td>
                    <td><strong><?= htmlspecialchars($data['nama_peserta']) ?></strong></td>
                    <td><?= htmlspecialchars($data['no_sertifikat']) ?></td>
                    <td><?= htmlspecialchars($data['skema_kompetensi']) ?></td>
                    <td style="text-align: center;">
                        <span class="status-badge <?= $status_class ?>">
                            <i class="fas <?= $status_icon ?>"></i>
                            <?= $status_text ?>
                        </span>
                    </td>
                </tr>
            <?php 
                }
            } else { 
            ?>
                <tr>
                    <td colspan="5" class="no-data">
                        <i class="fas fa-inbox"></i>
                        <p>Data tidak ditemukan</p>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if($total_page > 1): ?>
    <div class="pagination-wrapper">
        <div class="pagination">
            <!-- Previous Button -->
            <?php if($page > 1): ?>
                <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>&per_page=<?= $per_page ?>" class="page-btn">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php else: ?>
                <button class="page-btn" disabled>
                    <i class="fas fa-chevron-left"></i> Previous
                </button>
            <?php endif; ?>

            <?php
            // Hitung range halaman yang akan ditampilkan (maksimal 5)
            $start_page = max(1, $page - 2);
            $end_page = min($total_page, $start_page + 4);
            
            // Adjust start jika end_page menyentuh batas
            if($end_page - $start_page < 4) {
                $start_page = max(1, $end_page - 4);
            }

            // Tampilkan halaman pertama jika tidak termasuk range
            if($start_page > 1): ?>
                <a href="?page=1&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>&per_page=<?= $per_page ?>" class="page-num">1</a>
                <?php if($start_page > 2): ?>
                    <span class="dots">...</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php
            // Tampilkan range halaman
            for($i = $start_page; $i <= $end_page; $i++):
                $active_class = ($i == $page) ? 'active' : '';
            ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>&per_page=<?= $per_page ?>" 
                   class="page-num <?= $active_class ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php
            // Tampilkan halaman terakhir jika tidak termasuk range
            if($end_page < $total_page): ?>
                <?php if($end_page < $total_page - 1): ?>
                    <span class="dots">...</span>
                <?php endif; ?>
                <a href="?page=<?= $total_page ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>&per_page=<?= $per_page ?>" class="page-num"><?= $total_page ?></a>
            <?php endif; ?>

            <!-- Next Button -->
            <?php if($page < $total_page): ?>
                <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>&per_page=<?= $per_page ?>" class="page-btn">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php else: ?>
                <button class="page-btn" disabled>
                    Next <i class="fas fa-chevron-right"></i>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
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

// Smooth scroll untuk mobile
document.querySelectorAll('a[href^="?"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});
</script>

</body>
</html>
<?php
// Clean up
$stmt_data->close();
$koneksi->close();
?>