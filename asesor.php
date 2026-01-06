<?php
#include 'admin_middleware.php';#
require_once 'admin_middleware.php';
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
 
error_reporting(E_ALL);
ini_set('display_errors', 1);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_multiple'])) {
        // Proses penambahan data multiple
        $nama_asesor = $_POST['nama_asesor'];
        $no_registrasi = $_POST['no_registrasi'];
        $alamat = $_POST['alamat'];
        $status = $_POST['status'];
        
        $stmt = $conn->prepare("INSERT INTO data_asesor (nama_asesor, no_registrasi, alamat, status) VALUES (:nama_asesor, :no_registrasi, :alamat, :status)");
        
        for ($i = 0; $i < count($nama_asesor); $i++) {
            $stmt->bindParam(':nama_asesor', $nama_asesor[$i]);
            $stmt->bindParam(':no_registrasi', $no_registrasi[$i]);
            $stmt->bindParam(':alamat', $alamat[$i]);
            $stmt->bindParam(':status', $status[$i]);
            $stmt->execute();
        }
        header('Location: asesor.php?message=Data berhasil ditambahkan');
        exit();
    } elseif (isset($_POST['update'])) {
        $id = $_POST['id'];
        $nama_asesor = $_POST['nama_asesor'];
        $no_registrasi = $_POST['no_registrasi'];
        $alamat = $_POST['alamat'];
        $status = $_POST['status'];
        
        $stmt = $conn->prepare("UPDATE data_asesor SET nama_asesor = :nama_asesor, no_registrasi = :no_registrasi, alamat = :alamat, status = :status WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nama_asesor', $nama_asesor);
        $stmt->bindParam(':no_registrasi', $no_registrasi);
        $stmt->bindParam(':alamat', $alamat);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        header('Location: asesor.php?message=Data berhasil diperbarui');
        exit();
    }
}

// Proses DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM data_asesor WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    header('Location: asesor.php?message=Data berhasil dihapus');
    exit();
}

// Ambil semua asesor
$stmt = $conn->prepare("SELECT * FROM data_asesor");
$stmt->execute();
$data_asesor = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Manajemen Data Asesor</title>
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
                   <img src="logo 3.png" alt="SDM TIK" class="profile-image">
                   <h5>SDM TIK</h5>
                   <?php if(isset($_SESSION['user_email'])): ?>
                       <small class="text-white"><?php echo htmlspecialchars($_SESSION['user_email']); ?></small>
                   <?php endif; ?>
               </div>
               <ul class="nav flex-column">
                   <li class="nav-item"><a class="nav-link" href="tuk.php">TUK</a></li>
                   <li class="nav-item"><a class="nav-link active" href="asesor.php">Asesor</a></li>
                   <li class="nav-item"><a class="nav-link" href="mitra.php">Mitra</a></li>
                   <li class="nav-item"><a class="nav-link" href="admin_skema.php">Skema</a></li>
                   <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
                   <li class="nav-item"><a class="nav-link" href="berita.php">Berita</a></li>
                   <li class="nav-item"><a class="nav-link" href="nambah.php">Tambah</a></li>
                   <li class="nav-item"><a class="nav-link" href="?logout=1">Keluar</a></li>
               </ul>
           </div>
            <!-- Main Content -->
           <div class="col-md-9 col-lg-10 content">
               <h2 class="mb-4">Manajemen Data Asesor</h2>
               <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">Tambah</button>
               <a href="lihatasesor.php" class="btn btn-info mb-3 ml-2">Lihat Data Asesor</a>
               
               <div class="table-responsive">
                   <table id="asesorTable" class="table table-striped table-bordered">
                       <thead>
                           <tr>
                               <th>No.</th>
                               <th>Nama Asesor</th>
                               <th>No Registrasi</th>
                               <th>Alamat</th>
                               <th>Status</th>
                               <th>Aksi</th>
                           </tr>
                       </thead>
                       <tbody>
                           <?php foreach ($data_asesor as $index => $asesor): ?>
                           <tr>
                               <td><?= $index + 1 ?></td>
                               <td><?= $asesor['nama_asesor'] ?></td>
                               <td><?= $asesor['no_registrasi'] ?></td>
                               <td><?= $asesor['alamat'] ?></td>
                               <td><?= $asesor['status'] ?></td>
                               <td>
                                   <button class="btn btn-sm btn-info edit-btn" data-id="<?= $asesor['id'] ?>">Edit</button>
                                   <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $asesor['id'] ?>">Hapus</button>
                               </td>
                           </tr>
                           <?php endforeach; ?>
                       </tbody>
                   </table>
               </div>
           </div>
       </div>
   </div>
    <!-- Modal untuk menambah data -->
   <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
       <div class="modal-dialog modal-lg" role="document">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title">Tambah Asesor Baru</h5>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">&times;</span>
                   </button>
               </div>
               <form method="POST" action="">
                   <div class="modal-body">
                       <div id="form-container">
                           <div class="form-row">
                               <div class="form-group col-md-3">
                                   <label>Nama Asesor</label>
                                   <input type="text" class="form-control" name="nama_asesor[]" required>
                               </div>
                               <div class="form-group col-md-3">
                                   <label>No Registrasi</label>
                                   <input type="text" class="form-control" name="no_registrasi[]" required>
                               </div>
                               <div class="form-group col-md-3">
                                   <label>Alamat</label>
                                   <input type="text" class="form-control" name="alamat[]" required>
                               </div>
                               <div class="form-group col-md-3">
                                   <label>Status</label>
                                   <select class="form-control" name="status[]" required>
                                       <option value="Aktif">Aktif</option>
                                       <option value="Nonaktif">Nonaktif</option>
                                   </select>
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
    <!-- Modal untuk edit data -->
   <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
       <div class="modal-dialog" role="document">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title">Edit Asesor</h5>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">&times;</span>
                   </button>
               </div>
               <form method="POST" action="">
                   <div class="modal-body">
                       <input type="hidden" name="id" id="edit_id">
                       <div class="form-group">
                           <label>Nama Asesor</label>
                           <input type="text" class="form-control" name="nama_asesor" id="edit_nama_asesor" required>
                       </div>
                       <div class="form-group">
                           <label>No Registrasi</label>
                           <input type="text" class="form-control" name="no_registrasi" id="edit_no_registrasi" required>
                       </div>
                       <div class="form-group">
                           <label>Alamat</label>
                           <input type="text" class="form-control" name="alamat" id="edit_alamat" required>
                       </div>
                       <div class="form-group">
                           <label>Status</label>
                           <select class="form-control" name="status" id="edit_status" required>
                               <option value="Aktif">Aktif</option>
                               <option value="Nonaktif">Nonaktif</option>
                           </select>
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
    // Inisialisasi DataTable dengan event delegation
    var table = $('#asesorTable').DataTable({
        "language": {
            "lengthMenu": "Tampilkan _MENU_ entri",
            "search": "Cari:",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            }
        }
    });
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
       
       $('#asesorTable').on('click', '.edit-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var id = $(this).data('id');
        $.ajax({
            url: 'get_asesor.php',
            method: 'GET',
            data: {id: id},
            dataType: 'json',
            success: function(data) {
                $('#edit_id').val(data.id);
                $('#edit_nama_asesor').val(data.nama_asesor);
                $('#edit_no_registrasi').val(data.no_registrasi);
                $('#edit_alamat').val(data.alamat);
                $('#edit_status').val(data.status);
                $('#editModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error("Error:", error);
                alert("Terjadi kesalahan saat mengambil data asesor.");
            }
        });
    });
        // Delete button handler
        $('#asesorTable').on('click', '.delete-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var id = $(this).data('id');
        if (confirm('Apakah Anda yakin ingin menghapus asesor ini?')) {
            window.location.href = '?delete=' + id;
        }
    });
        // Tambah form handler
       $('#add-form').click(function() {
           var newForm = `
               <div class="form-row">
                   <div class="form-group col-md-3">
                       <input type="text" class="form-control" name="nama_asesor[]" required placeholder="Nama Asesor">
                   </div>
                   <div class="form-group col-md-3">
                       <input type="text" class="form-control" name="no_registrasi[]" required placeholder="No Registrasi">
                   </div>
                   <div class="form-group col-md-3">
                       <input type="text" class="form-control" name="alamat[]" required placeholder="Alamat">
                   </div>
                   <div class="form-group col-md-3">
                       <select class="form-control" name="status[]" required>
                           <option value="Aktif">Aktif</option>
                           <option value="Nonaktif">Nonaktif</option>
                       </select>
                   </div>
               </div>
           `;
           $('#form-container').append(newForm);
       });
   });
   </script>
</body>
</html>