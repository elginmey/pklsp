<?php
#include 'admin_middleware.php';#
require_once 'admin_middleware.php';
// Koneksi ke database
$host = 'localhost';
$dbname = 'lspketapang_sdmtik';
$username = 'lspketapang_adminlsp';
$password = 'superadmin123!';


try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_multiple'])) {
        // Proses penambahan data multiple
        $nama = $_POST['nama'];
        $alamat = $_POST['alamat'];
        $kerja_sama = $_POST['kerja_sama'];
        
        // Mengambil nomor terakhir
        $stmt = $conn->query("SELECT MAX(no) as max_no FROM mitra_kerja");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $last_no = $result['max_no'] ?? 0;
        
        // Menyiapkan statement dengan field 'no'
        $stmt = $conn->prepare("INSERT INTO mitra_kerja (no, nama, alamat, kerja_sama) VALUES (:no, :nama, :alamat, :kerja_sama)");
        
        for ($i = 0; $i < count($nama); $i++) {
            $current_no = $last_no + $i + 1;
            $stmt->bindParam(':no', $current_no);
            $stmt->bindParam(':nama', $nama[$i]);
            $stmt->bindParam(':alamat', $alamat[$i]);
            $stmt->bindParam(':kerja_sama', $kerja_sama[$i]);
            $stmt->execute();
        }

        header('Location: mitra.php?status=success&message=Data Mitra berhasil ditambahkan');
        exit();
    } elseif (isset($_POST['update'])) {
        $id = $_POST['id'];
        $nama = $_POST['nama'];
        $alamat = $_POST['alamat'];
        $kerja_sama = $_POST['kerja_sama'];
        
        // Update query tidak perlu mengubah field 'no'
        $stmt = $conn->prepare("UPDATE mitra_kerja SET nama = :nama, alamat = :alamat, kerja_sama = :kerja_sama WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':alamat', $alamat);
        $stmt->bindParam(':kerja_sama', $kerja_sama);
        
        if ($stmt->execute()) {
            header('Location: mitra.php?status=success&message=Data berhasil diupdate');
            exit();
        }
    }
}

// Proses DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM mitra_kerja WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    header('Location: mitra.php?status=success&message=Data Mitra berhasil dihapus');
    exit();
}

// Ambil semua data mitra
$stmt = $conn->prepare("SELECT * FROM mitra_kerja");
$stmt->execute();
$mitra_kerja = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Data Mitra Kerja</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
                    <img src="logo 3.png" alt="Moch. Shohibul Asyrof" class="profile-image">
                    <h5>SDM TIK</h5>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link" href="tuk.php">TUK</a></li>
                    <li class="nav-item"><a class="nav-link" href="asesor.php">Asesor</a></li>
                    <li class="nav-item"><a class="nav-link active" href="mitra.php">Mitra</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_skema.php">Skema</a></li>
                    <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="berita.php">Berita</a></li>
                    <li class="nav-item"><a class="nav-link" href="nambah.php">Tambah</a></li>
                    <li class="nav-item"><a class="nav-link" href="?logout=1">Keluar</a></li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 content">
                <h2 class="mb-4">Manajemen Data Mitra Kerja</h2>
                <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">Tambah</button>
                <a href="lihatmitra.php" class="btn btn-info mb-3 ml-2">Lihat Data Mitra</a>

                <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($_GET['message']) ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table id="mitraTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nama</th>
                                <th>Alamat</th>
                                <th>Kerja Sama</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mitra_kerja as $index => $mitra): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($mitra['nama']) ?></td>
                                <td><?= htmlspecialchars($mitra['alamat']) ?></td>
                                <td><?= htmlspecialchars($mitra['kerja_sama']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info edit-btn" data-id="<?= $mitra['id'] ?>">Edit</button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $mitra['id'] ?>">Hapus</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Mitra Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div id="form-container">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Nama</label>
                                    <input type="text" class="form-control" name="nama[]" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Alamat</label>
                                    <input type="text" class="form-control" name="alamat[]" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Kerja Sama</label>
                                    <input type="text" class="form-control" name="kerja_sama[]" required>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary" id="add-form">Tambah Form</button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" name="add_multiple" class="btn btn-primary">Simpan Semua</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Mitra</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="form-group">
                            <label>Nama</label>
                            <input type="text" class="form-control" name="nama" id="edit_nama" required>
                        </div>
                        <div class="form-group">
                            <label>Alamat</label>
                            <input type="text" class="form-control" name="alamat" id="edit_alamat" required>
                        </div>
                        <div class="form-group">
                            <label>Kerja Sama</label>
                            <input type="text" class="form-control" name="kerja_sama" id="edit_kerja_sama" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" name="update" class="btn btn-primary">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi DataTable
            var table = $('#mitraTable').DataTable({
                "language": {
                    "lengthMenu": "Tampilkan _MENU_ entri",
                    "search": "Cari:",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                },
                "drawCallback": function(settings) {
                    bindEventHandlers();
                }
            });

            // Fungsi untuk mengikat event handlers
            function bindEventHandlers() {
                // Edit button handler
                $('.edit-btn').off('click').on('click', function() {
                    var id = $(this).data('id');
                    $.ajax({
                        url: 'get_mitra.php',
                        method: 'GET',
                        data: {id: id},
                        dataType: 'json',
                        success: function(data) {
                            $('#edit_id').val(data.id);
                            $('#edit_nama').val(data.nama);
                            $('#edit_alamat').val(data.alamat);
                            $('#edit_kerja_sama').val(data.kerja_sama);
                            $('#editModal').modal('show');
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan saat mengambil data');
                        }
                    });
                });

                // Delete button handler
                $('.delete-btn').off('click').on('click', function() {
                    var id = $(this).data('id');
                    if (confirm('Apakah Anda yakin ingin menghapus mitra ini?')) {
                        window.location.href = '?delete=' + id;
                    }
                });
            }

            // Toggle Sidebar
            $('#sidebar-toggle').click(function(e) {
                e.stopPropagation();
                $('.sidebar').toggleClass('active');
            });

            // Tutup sidebar saat mengklik di luar
            $(document).click(function(e) {
                if (!$(e.target).closest('.sidebar').length && 
                    !$(e.target).closest('#sidebar-toggle').length && 
                    $('.sidebar').hasClass('active')) {
                    $('.sidebar').removeClass('active');
                }
            });

            // Tambah form
            $('#add-form').click(function() {
                var newForm = `
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <input type="text" class="form-control" name="nama[]" required placeholder="Nama">
                        </div>
                        <div class="form-group col-md-4">
                            <input type="text" class="form-control" name="alamat[]" required placeholder="Alamat">
                        </div>
                        <div class="form-group col-md-4">
                            <input type="text" class="form-control" name="kerja_sama[]" required placeholder="Kerja Sama">
                        </div>
                    </div>
                `;
                $('#form-container').append(newForm);
            });

            // Inisialisasi awal event handlers
            bindEventHandlers();
        });
    </script>
</body>
</html>