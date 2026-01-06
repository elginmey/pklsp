<?php
$host = 'localhost';
$dbname = 'lspketapang_sdmtik';
$username = 'lspketapang_adminlsp';
$password = 'superadmin123!';

// Fungsi untuk upload gambar
function upload_image($file, $target_dir) {
    // Cek apakah ada file yang diupload
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return null; // Return null jika tidak ada file
    }
    
    // Buat direktori jika belum ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    
    // Cek apakah file adalah gambar
    if(!getimagesize($file["tmp_name"])) {
        throw new Exception("File bukan gambar.");
    }
    
    // Cek ukuran file
    if ($file["size"] > 5000000) {
        throw new Exception("File terlalu besar.");
    }
    
    // Izinkan format tertentu
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        throw new Exception("Hanya file JPG, JPEG & PNG yang diizinkan.");
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    }
    
    throw new Exception("Gagal mengupload file.");
}

// Fungsi untuk upload file
function upload_file($file, $target_dir) {
    // Cek apakah ada file yang diupload
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return null; // Return null jika tidak ada file
    }
    
    // Buat direktori jika belum ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . basename($file["name"]);
    $fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    
    // Cek ukuran file
    if ($file["size"] > 10000000) {
        throw new Exception("File terlalu besar.");
    }
    
    // Izinkan format PDF
    if($fileType != "pdf") {
        throw new Exception("Hanya file PDF yang diizinkan.");
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    }
    
    throw new Exception("Gagal mengupload file.");
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Koneksi gagal: " . $e->getMessage();
    die();
}

// Proses tambah/edit skema
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $class = $_POST['class'];
        $jenis = $_POST['jenis'];
        $skkni = $_POST['skkni'];
        $level = $_POST['level'];
        $harga = $_POST['harga'];
        
        // Upload gambar jika ada
        $image = null;
        if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
            $image = upload_image($_FILES['image'], 'Galery/SDM TIK/Skema/');
        }
        
        // Upload file skema jika ada
        $download_link = null;
        if (isset($_FILES['skema_file']) && !empty($_FILES['skema_file']['name'])) {
            $download_link = upload_file($_FILES['skema_file'], 'files/skema/');
        }
        
        if ($_POST['action'] == 'add') {
            $stmt = $conn->prepare("INSERT INTO skema (id, title, description, class, jenis, skkni, level, harga, image, download_link) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $title, $description, $class, $jenis, $skkni, $level, $harga, $image, $download_link]);
            
            // Simpan detail unit kompetensi
            foreach ($_POST['unit'] as $key => $unit) {
                $stmt = $conn->prepare("INSERT INTO skema_detail (skema_id, unit_code, kompetensi) VALUES (?, ?, ?)");
                $stmt->execute([$id, $unit, $_POST['kompetensi'][$key]]);
            }
        } else {
            // Update data dasar
            $stmt = $conn->prepare("UPDATE skema SET title=?, description=?, class=?, jenis=?, skkni=?, level=?, harga=? WHERE id=?");
            $stmt->execute([$title, $description, $class, $jenis, $skkni, $level, $harga, $id]);
            
            // Update image jika ada
            if ($image !== null) {
                $stmt = $conn->prepare("UPDATE skema SET image=? WHERE id=?");
                $stmt->execute([$image, $id]);
            }
            
            // Update download_link jika ada
            if ($download_link !== null) {
                $stmt = $conn->prepare("UPDATE skema SET download_link=? WHERE id=?");
                $stmt->execute([$download_link, $id]);
            }
            
            // Update detail unit kompetensi
            $stmt = $conn->prepare("DELETE FROM skema_detail WHERE skema_id=?");
            $stmt->execute([$id]);
            
            foreach ($_POST['unit'] as $key => $unit) {
                $stmt = $conn->prepare("INSERT INTO skema_detail (skema_id, unit_code, kompetensi) VALUES (?, ?, ?)");
                $stmt->execute([$id, $unit, $_POST['kompetensi'][$key]]);
            }
        }
        
        header('Location: admin_skema.php');
        exit;
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
    }
}

function deleteSkema($id) {
    // Pastikan ID valid
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
        return;
    }

    // Hapus skema dari database
    $stmt = $conn->prepare("DELETE FROM skema WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(['status' => 'success', 'message' => 'Skema berhasil dihapus']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus skema']);
    }
    exit; // Pastikan untuk keluar setelah mengirim respons
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Kelola Skema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            transition: all 0.3s;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
        }
        .sidebar a:hover {
            color: #f8f9fa;
        }
        .content {
            padding: 20px;
        }
        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 10px;
        }
        
        #sidebar-toggle {
            display: none;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                z-index: 1000;
                left: -100%;
                width: 250px;
            }
            
            .sidebar.active {
                left: 0;
            }
            
            #sidebar-toggle {
                display: block;
                position: fixed;
                left: 10px;
                top: 10px;
                z-index: 999;
            }
            
            .content {
                width: 100%;
                margin-left: 0;
                padding-top: 60px;
            }
            
            .col-md-9 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
        
        .pagination {
            margin-bottom: 20px;
        }
        
        .pagination .page-link {
            color: #333;
            border-radius: 5px;
            margin: 0 3px;
            transition: all 0.3s;
        }
        
        .pagination .page-link:hover {
            background-color: #e9ecef;
            color: #0d6efd;
        }
        
        .pagination .active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }
        
        .pagination .disabled .page-link {
            color: #6c757d;
            pointer-events: none;
        }
        
        .input-group {
            position: relative;
        }
        
        #searchInput {
            padding-right: 40px;
            border-radius: 20px;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }
        
        #searchInput:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        #clearSearch {
            position: absolute;
            right: 0;
            z-index: 4;
            border: none;
            background: transparent;
            padding: 8px 12px;
            cursor: pointer;
            display: none;
        }
        
        #clearSearch:hover {
            color: #dc3545;
        }
        
        .highlight {
            background-color: #fff3cd;
            padding: 2px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <!-- Tombol Toggle Sidebar -->
    <button id="sidebar-toggle" class="btn btn-dark">
        <i class="fas fa-bars"></i>
    </button>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="text-center mb-4">
                    <img src="logo 3.png" alt="SDM TIK" class="profile-image">
                    <h5>SDM TIK</h5>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link" href="tuk.php">TUK</a></li>
                    <li class="nav-item"><a class="nav-link" href="asesor.php">Asesor</a></li>
                    <li class="nav-item"><a class="nav-link" href="mitra.php">Mitra</a></li>
                    <li class="nav-item"><a class="nav-link active" href="admin_skema.php">Skema</a></li>
                    <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="berita.php">Berita</a></li>
                    <li class="nav-item"><a class="nav-link" href="nambah.php">Tambah</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.html">Keluar</a></li>
                </ul>
            </div>

            <!-- Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content">
                <h2>Kelola Skema Sertifikasi</h2>
                
                <!-- Search Box -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form method="GET" action="">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Cari skema..." value="<?php echo htmlspecialchars($searchTerm ?? '', ENT_QUOTES); ?>">
                                <button class="btn btn-outline-secondary" type="submit">Cari</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Tombol Tambah Skema -->
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#skemaModal">
                    Tambah Skema Baru
                </button>
                
                <!-- Tabel Skema -->
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Gambar</th>
                                <th>Judul</th>
                                <th>Jenis</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Ambil parameter pencarian
                            $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

                            // Konfigurasi pagination
                            $items_per_page = 5;
                            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                            $start_from = ($page - 1) * $items_per_page;

                            // Hitung total skema dengan pencarian
                            $total_query = $conn->prepare("SELECT COUNT(*) FROM skema WHERE title LIKE :searchTerm");
                            $total_query->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
                            $total_query->execute();
                            $total_items = $total_query->fetchColumn();
                            $total_pages = ceil($total_items / $items_per_page);

                            // Query dengan limit untuk pagination dan pencarian
                            $stmt = $conn->prepare("SELECT * FROM skema WHERE title LIKE :searchTerm ORDER BY id LIMIT :start, :items");
                            $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
                            $stmt->bindValue(':start', $start_from, PDO::PARAM_INT);
                            $stmt->bindValue(':items', $items_per_page, PDO::PARAM_INT);
                            $stmt->execute();

                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>
                                        <td>{$row['id']}</td>
                                        <td>";
                                // Tampilkan gambar jika ada
                                if (!empty($row['image'])) {
                                    echo "<img src='{$row['image']}' alt='Skema Image' class='img-thumbnail' style='max-width: 100px;'>";
                                } else {
                                    echo "<span class='text-muted'>No image</span>";
                                }
                                echo "</td>
                                        <td>{$row['title']}</td>
                                        <td>{$row['jenis']}</td>
                                        <td>Rp " . number_format((int)$row['harga'], 0, ',', '.') . "</td>
                                        <td>
                                            <button class='btn btn-sm btn-warning' onclick='editSkema(\"{$row['id']}\")'>Edit</button>
                                            <button class='btn btn-sm btn-danger' onclick='deleteSkema(\"{$row['id']}\")'>Hapus</button>
                                        </td>
                                      </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($searchTerm); ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo; Sebelumnya</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($searchTerm); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($searchTerm); ?>" aria-label="Next">
                                    <span aria-hidden="true">Selanjutnya &raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Modal Form Skema -->
    <div class="modal fade" id="skemaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah/Edit Skema</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="skemaForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label>ID Skema</label>
                            <input type="text" name="id" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label>Judul</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label>Deskripsi</label>
                            <textarea name="description" class="form-control" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label>Kategori</label>
                            <select name="class" class="form-control" required>
                                <option value="">Pilih Kategori</option>
                                <option value="programming">PROGRAMMING AND SOFTWARE DEVELOPMENT</option>
                                <option value="network">NETWORK AND INFRASTRUCTURE</option>
                                <option value="operation">OPERATION AND SYSTEM TOOLS</option>
                                <option value="multimedia">IT MULTEMEDIA / DESAIN KOMUNIKASI VISUAL</option>
                                <option value="security">IT SECURITY AND COMPLIANCE</option>
                                <option value="iot">IT MOBILITY AND INTERNET OF THINGS</option>
                                <option value="other">TIK LAINNYA</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label>Jenis</label>
                            <select name="jenis" class="form-control" required>
                                <option value="OKUPASI">Okupasi</option>
                                <option value="KLASTER">Klaster</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label>SKKNI</label>
                            <textarea name="skkni" class="form-control" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label>Level</label>
                            <input type="text" name="level" class="form-control">
                        </div>
                        
                        <div class="mb-3">
                            <label>Harga</label>
                            <input type="number" name="harga" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label>Gambar Skema</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        
                        <div class="mb-3">
                            <label>File Skema (PDF)</label>
                            <input type="file" name="skema_file" class="form-control" accept=".pdf">
                        </div>
                        
                        <div id="unitContainer">
                            <h5>Unit Kompetensi</h5>
                            <div class="unit-row mb-3">
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="text" name="unit[]" class="form-control" placeholder="Kode Unit">
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="kompetensi[]" class="form-control" placeholder="Kompetensi">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-secondary" onclick="addUnitRow()">Tambah Unit</button>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tambahkan script untuk toggle sidebar
        document.getElementById('sidebar-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        function addUnitRow() {
            const container = document.getElementById('unitContainer');
            const newRow = document.createElement('div');
            newRow.className = 'unit-row mb-3';
            newRow.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" name="unit[]" class="form-control" placeholder="Kode Unit">
                    </div>
                    <div class="col-md-8">
                        <input type="text" name="kompetensi[]" class="form-control" placeholder="Kompetensi">
                    </div>
                </div>
            `;
            container.appendChild(newRow);
        }

        function editSkema(id) {
            fetch('get_skema.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    
                    // Reset form
                    document.getElementById('skemaForm').reset();
                    document.getElementById('unitContainer').innerHTML = '<h5>Unit Kompetensi</h5>';
                    
                    // Isi form dengan data
                    document.querySelector('[name="id"]').value = data.id;
                    document.querySelector('[name="title"]').value = data.title;
                    document.querySelector('[name="description"]').value = data.description;
                    document.querySelector('[name="class"]').value = data.class;
                    document.querySelector('[name="jenis"]').value = data.jenis;
                    document.querySelector('[name="skkni"]').value = data.skkni;
                    document.querySelector('[name="level"]').value = data.level;
                    document.querySelector('[name="harga"]').value = data.harga;
                    
                    // Set mode edit
                    document.querySelector('[name="action"]').value = 'edit';
                    document.querySelector('[name="id"]').readOnly = true;
                    
                    // Tampilkan modal
                    new bootstrap.Modal(document.getElementById('skemaModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengambil data: ' + error.message);
                });
        }

        function deleteSkema(id) {
            if (confirm('Yakin ingin menghapus skema ini?')) {
                const formData = new FormData();
                formData.append('id', id);

                fetch('delete_skema.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Skema berhasil dihapus');
                        location.reload();
                    } else {
                        alert('Gagal menghapus skema: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghapus skema');
                });
            }
        }

        // Tambahkan event listener untuk reset form saat modal dibuka untuk tambah baru
        document.querySelector('[data-bs-target="#skemaModal"]').addEventListener('click', function() {
            document.getElementById('skemaForm').reset();
            document.querySelector('[name="action"]').value = 'add';
            document.querySelector('[name="id"]').readOnly = false;
            document.getElementById('unitContainer').innerHTML = '<h5>Unit Kompetensi</h5>';
        });

        // Fungsi pencarian
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');
            const clearButton = document.getElementById('clearSearch');
            
            // Tampilkan/sembunyikan tombol clear
            clearButton.style.display = searchTerm.length > 0 ? 'block' : 'none';
            
            let found = false;
            tableRows.forEach(row => {
                const title = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const id = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                const jenis = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || id.includes(searchTerm) || jenis.includes(searchTerm)) {
                    row.style.display = '';
                    found = true;
                    
                    // Highlight teks yang cocok
                    highlightText(row, searchTerm);
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Tampilkan pesan jika tidak ada hasil
            const noResultsMsg = document.getElementById('noResultsMsg');
            if (!found && searchTerm.length > 0) {
                if (!noResultsMsg) {
                    const msg = document.createElement('tr');
                    msg.id = 'noResultsMsg';
                    msg.innerHTML = `
                        <td colspan="6" class="text-center py-3">
                            <div class="alert alert-info mb-0">
                                Tidak ada skema yang cocok dengan pencarian "${searchTerm}"
                            </div>
                        </td>
                    `;
                    document.querySelector('tbody').appendChild(msg);
                }
            } else if (noResultsMsg) {
                noResultsMsg.remove();
            }
        });

        // Fungsi untuk highlight teks
        function highlightText(row, searchTerm) {
            const cells = row.getElementsByTagName('td');
            for (let cell of cells) {
                const text = cell.textContent;
                if (!cell.querySelector('img') && !cell.querySelector('button')) { // Skip cells with images or buttons
                    cell.innerHTML = text.replace(new RegExp(searchTerm, 'gi'), match => 
                        `<span class="highlight">${match}</span>`
                    );
                }
            }
        }

        // Clear search
        document.getElementById('clearSearch').addEventListener('click', function() {
            document.getElementById('searchInput').value = '';
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.style.display = '';
                // Remove highlights
                const highlights = row.querySelectorAll('.highlight');
                highlights.forEach(h => {
                    const text = h.textContent;
                    h.outerHTML = text;
                });
            });
            this.style.display = 'none';
            
            const noResultsMsg = document.getElementById('noResultsMsg');
            if (noResultsMsg) {
                noResultsMsg.remove();
            }
        });
    </script>
</body>
</html>