<?php
#include 'admin_middleware.php';#

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
   if (isset($_POST['add_multiple'])) {
       // Proses penambahan data multiple
       $kode_tuk = $_POST['kode_tuk'];
       $nama_tuk = $_POST['nama_tuk'];
       $alamat = $_POST['alamat'];
       $jenis_tuk = $_POST['jenis_tuk'];
       
       $stmt = $conn->prepare("INSERT INTO data_tuk (kode_tuk, nama_tuk, alamat, jenis_tuk) VALUES (:kode_tuk, :nama_tuk, :alamat, :jenis_tuk)");
       
       for ($i = 0; $i < count($kode_tuk); $i++) {
           $stmt->bindParam(':kode_tuk', $kode_tuk[$i]);
           $stmt->bindParam(':nama_tuk', $nama_tuk[$i]);
           $stmt->bindParam(':alamat', $alamat[$i]);
           $stmt->bindParam(':jenis_tuk', $jenis_tuk[$i]);
           $stmt->execute();
       }
        header('Location: tuk.php?status=success&message=Data TUK berhasil ditambahkan');
       exit();
   } elseif (isset($_POST['update'])) {
       $id = $_POST['id'];
       $kode_tuk = $_POST['kode_tuk'];
       $nama_tuk = $_POST['nama_tuk'];
       $alamat = $_POST['alamat'];
       $jenis_tuk = $_POST['jenis_tuk'];
        $stmt = $conn->prepare("UPDATE data_tuk SET kode_tuk = :kode_tuk, nama_tuk = :nama_tuk, alamat = :alamat, jenis_tuk = :jenis_tuk WHERE id = :id");
       $stmt->bindParam(':id', $id);
       $stmt->bindParam(':kode_tuk', $kode_tuk);
       $stmt->bindParam(':nama_tuk', $nama_tuk);
       $stmt->bindParam(':alamat', $alamat);
       $stmt->bindParam(':jenis_tuk', $jenis_tuk);
       
       if ($stmt->execute()) {
           header('Location: tuk.php?status=success&message=Data berhasil diupdate');
           exit();
       }
   }

// Proses DELETE
if (isset($_GET['delete'])) {
   $id = $_GET['delete'];
   $stmt = $conn->prepare("DELETE FROM data_tuk WHERE id = :id");
   $stmt->bindParam(':id', $id);
   $stmt->execute();
   header('Location: tuk.php?status=success&message=Data TUK berhasil dihapus');
   exit();
}

// Ambil semua TUK
$stmt = $conn->prepare("SELECT * FROM data_tuk");
$stmt->execute();
$data_tuk = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Manajemen Data TUK</title>
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
               </div>
               <ul class="nav flex-column">
                   <li class="nav-item"><a class="nav-link active" href="tuk.php">TUK</a></li>
                   <li class="nav-item"><a class="nav-link" href="asesor.php">Asesor</a></li>
                   <li class="nav-item"><a class="nav-link" href="mitra.php">Mitra</a></li>
                   <li class="nav-item"><a class="nav-link" href="admin_skema.php">Skema</a></li>
                   <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
                   <li class="nav-item"><a class="nav-link" href="berita.php">Berita</a></li>
                   <li class="nav-item"><a class="nav-link" href="nambah.php">Tambah</a></li>
                   <li class="nav-item"><a class="nav-link" href="index.html">Keluar</a></li>
               </ul>
           </div>
            <!-- Main Content -->
           <div class="col-md-9 col-lg-10 content">
               <h2 class="mb-4">Manajemen Data TUK</h2>
               <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">Tambah</button>
               <a href="lihat_tuk.php" class="btn btn-info mb-3 ml-2">Lihat Data TUK</a>
               
               <div class="table-responsive">
                   <table id="tukTable" class="table table-striped table-bordered">
                       <thead>
                           <tr>
                               <th>No.</th>
                               <th>Kode TUK</th>
                               <th>Nama TUK</th>
                               <th>Alamat</th>
                               <th>Jenis TUK</th>
                               <th>Aksi</th>
                           </tr>
                       </thead>
                       <tbody>
                           <?php foreach ($data_tuk as $index => $tuk): ?>
                           <tr>
                               <td><?= $index + 1 ?></td>
                               <td><?= $tuk['kode_tuk'] ?></td>
                               <td><?= $tuk['nama_tuk'] ?></td>
                               <td><?= $tuk['alamat'] ?></td>
                               <td><?= $tuk['jenis_tuk'] ?></td>
                               <td>
                                   <button class="btn btn-sm btn-info edit-btn" data-id="<?= $tuk['id'] ?>">Edit</button>
                                   <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $tuk['id'] ?>">Hapus</button>
                               </td>
                           </tr>
                           <?php endforeach; ?>
                       </tbody>
                   </table>
               </div>
           </div>
       </div>
   </div>
    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
       <div class="alert alert-success"><?= htmlspecialchars($_GET['message']) ?></div>
   <?php endif; ?>
    <!-- Modal untuk menambah data -->
   <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
       <div class="modal-dialog modal-lg" role="document">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title">Tambah TUK Baru</h5>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">&times;</span>
                   </button>
               </div>
               <form method="POST" action="">
                   <div class="modal-body">
                       <div id="form-container">
                           <div class="form-row">
                               <div class="form-group col-md-3">
                                   <label>Kode TUK</label>
                                   <input type="text" class="form-control" name="kode_tuk[]" required>
                               </div>
                               <div class="form-group col-md-3">
                                   <label>Nama TUK</label>
                                   <input type="text" class="form-control" name="nama_tuk[]" required>
                               </div>
                               <div class="form-group col-md-3">
                                   <label>Alamat</label>
                                   <input type="text" class="form-control" name="alamat[]" required>
                               </div>
                               <div class="form-group col-md-3">
                                   <label>Jenis TUK</label>
                                   <select class="form-control" name="jenis_tuk[]" required>
                                       <option value="">Pilih Jenis TUK</option>
                                       <option value="Mandiri">Mandiri</option>
                                       <option value="Sewaktu">Sewaktu</option>
                                       <option value="Tempat Kerja">Tempat Kerja</option>
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
    <!-- Modal Edit -->
   <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
       <div class="modal-dialog" role="document">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title">Edit TUK</h5>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">&times;</span>
                   </button>
               </div>
               <form method="POST" action="">
                   <div class="modal-body">
                       <input type="hidden" name="id" id="edit_id">
                       <div class="form-group">
                           <label>Kode TUK</label>
                           <input type="text" class="form-control" name="kode_tuk" id="edit_kode_tuk" required>
                       </div>
                       <div class="form-group">
                           <label>Nama TUK</label>
                           <input type="text" class="form-control" name="nama_tuk" id="edit_nama_tuk" required>
                       </div>
                       <div class="form-group">
                           <label>Alamat</label>
                           <input type="text" class="form-control" name="alamat" id="edit_alamat" required>
                       </div>
                       <div class="form-group">
                           <label>Jenis TUK</label>
                           <select class="form-control" name="jenis_tuk" id="edit_jenis_tuk" required>
                               <option value="">Pilih Jenis TUK</option>
                               <option value="Mandiri">Mandiri</option>
                               <option value="Sewaktu">Sewaktu</option>
                               <option value="Tempat Kerja">Tempat Kerja</option>
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
           // Inisialisasi DataTable
           var table = $('#tukTable').DataTable({
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
                   $('.edit-btn').off('click').on('click', function() {
                       var id = $(this).data('id');
                       $.ajax({
                           url: 'get_tuk.php',
                           method: 'GET',
                           data: {id: id},
                           dataType: 'json',
                           success: function(data) {
                               $('#edit_id').val(data.id);
                               $('#edit_kode_tuk').val(data.kode_tuk);
                               $('#edit_nama_tuk').val(data.nama_tuk);
                               $('#edit_alamat').val(data.alamat);
                               $('#edit_jenis_tuk').val(data.jenis_tuk);
                               $('#editModal').modal('show');
                           },
                           error: function(xhr, status, error) {
                               console.error('Error:', error);
                               alert('Terjadi kesalahan saat mengambil data');
                           }
                       });
                   });
                    $('.delete-btn').off('click').on('click', function() {
                       var id = $(this).data('id');
                       if (confirm('Apakah Anda yakin ingin menghapus TUK ini?')) {
                           window.location.href = '?delete=' + id;
                       }
                   });
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
            // Tambah form
           $('#add-form').click(function() {
               var newForm = `
                   <div class="form-row">
                       <div class="form-group col-md-3">
                           <input type="text" class="form-control" name="kode_tuk[]" required>
                       </div>
                       <div class="form-group col-md-3">
                           <input type="text" class="form-control" name="nama_tuk[]" required>
                       </div>
                       <div class="form-group col-md-3">
                           <input type="text" class="form-control" name="alamat[]" required>
                       </div>
                       <div class="form-group col-md-3">
                           <select class="form-control" name="jenis_tuk[]" required>
                               <option value="">Pilih Jenis TUK</option>
                               <option value="Mandiri">Mandiri</option>
                               <option value="Sewaktu">Sewaktu</option>
                               <option value="Tempat Kerja">Tempat Kerja</option>
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