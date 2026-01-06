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



// Tambahkan kode berikut di bagian awal file, setelah koneksi database
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM gallery WHERE id = :id");
    $stmt->execute([':id' => $id]);
    header('Location: gallery.php?status=success&message=Data galeri berhasil dihapus');
    exit();
}
// Fungsi untuk mengunggah gambar
function uploadImage($file) {
    if (!isset($file) || $file['error'] != UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File tidak valid atau terjadi kesalahan saat upload.'];
    }

    $target_dir = "uploads/gallery/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    
    // Cek apakah file benar-benar gambar
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return ['success' => false, 'message' => 'File bukan gambar yang valid.'];
    }
    
    // Cek ukuran file (batas maksimum 5MB)
    if ($file["size"] > 5000000) {
        return ['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimum 5MB.'];
    }
    
    // Izinkan hanya format tertentu
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        return ['success' => false, 'message' => 'Hanya format gambar JPG, PNG, JPEG, dan GIF yang diizinkan.'];
    }
    
    // Jika lolos semua pengecekan, coba unggah file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'message' => $target_file];
    } else {
        return ['success' => false, 'message' => 'Gagal mengunggah gambar. Error: ' . $file['error']];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_multiple'])) {
        // Proses penambahan data multiple
        $title = $_POST['title'];
        $description = $_POST['description'];
        $category = $_POST['category'];
        
        $stmt = $conn->prepare("INSERT INTO gallery (image_url, title, description, category, date_added) VALUES (:image_url, :title, :description, :category, NOW())");
        
        $error_message = '';
        for ($i = 0; $i < count($title); $i++) {
            if (isset($_FILES['image']['name'][$i]) && $_FILES['image']['error'][$i] == UPLOAD_ERR_OK) {
                $image_file = [
                    'name' => $_FILES['image']['name'][$i],
                    'type' => $_FILES['image']['type'][$i],
                    'tmp_name' => $_FILES['image']['tmp_name'][$i],
                    'error' => $_FILES['image']['error'][$i],
                    'size' => $_FILES['image']['size'][$i]
                ];
                $upload_result = uploadImage($image_file);
                if ($upload_result['success']) {
                    $image_url = $upload_result['message'];
                    $stmt->bindParam(':image_url', $image_url);
                    $stmt->bindParam(':title', $title[$i]);
                    $stmt->bindParam(':description', $description[$i]);
                    $stmt->bindParam(':category', $category[$i]);
                    if (!$stmt->execute()) {
                        $error_message = 'Gagal menambahkan data galeri. Error: ' . $stmt->errorInfo()[2];
                        break;
                    }
                } else {
                    $error_message = $upload_result['message'];
                    break;
                }
            }
        }

        // Setelah pemrosesan selesai, lakukan redirect
        if (empty($error_message)) {
            header('Location: gallery.php?status=success&message=Data galeri berhasil ditambahkan');
        } else {
            header('Location: gallery.php?status=error&message=' . urlencode($error_message));
        }
        exit();
    } elseif (isset($_POST['update'])) {
        // Proses update data
        $id = $_POST['id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $category = $_POST['category'];

        // Perbaikan untuk handling gambar
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $upload_result = uploadImage($_FILES['image']);
            if ($upload_result['success']) {
                $image_url = $upload_result['message'];
                
                // Hapus gambar lama jika ada
                $stmt = $conn->prepare("SELECT image_url FROM gallery WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $old_image = $stmt->fetchColumn();
                if ($old_image && file_exists($old_image)) {
                    unlink($old_image);
                }
            } else {
                header('Location: gallery.php?status=error&message=' . urlencode($upload_result['message']));
                exit();
            }
        } else {
            // Jika tidak ada file baru diunggah, gunakan gambar yang ada
            $stmt = $conn->prepare("SELECT image_url FROM gallery WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $image_url = $stmt->fetchColumn();
        }

        // Update data ke database
        $stmt = $conn->prepare("UPDATE gallery SET image_url = :image_url, title = :title, description = :description, category = :category WHERE id = :id");
        if ($stmt->execute([
            ':id' => $id,
            ':image_url' => $image_url,
            ':title' => $title,
            ':description' => $description,
            ':category' => $category
        ])) {
            header('Location: gallery.php?status=success&message=Data galeri berhasil diperbarui');
        } else {
            header('Location: gallery.php?status=error&message=Gagal memperbarui data galeri');
        }
        exit();
    }
}

// Ambil semua data galeri
$stmt = $conn->prepare("SELECT * FROM gallery ORDER BY date_added DESC");
$stmt->execute();
$gallery_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Galeri</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    <style>
        .wrapper {
            display: flex;
            width: 100%;
        }
        #sidebar {
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 999;
            background: #343a40;
            color: #fff;
            transition: all 0.3s;
            overflow-y: auto; /* Tambahkan ini untuk memungkinkan scrolling */
        }
        #sidebar.active {
            margin-left: -250px;
        }
        #sidebar .sidebar-header {
            padding: 20px;
            background: #343a40;
        }
        #sidebar ul.components {
            padding: 20px 0;
        }
        #sidebar ul li a {
            padding: 10px;
            font-size: 1.1em;
            display: block;
            color: #fff;
            text-decoration: none;
        }
        #sidebar ul li a:hover {
            color: #343a40;
            background: #fff;
        }
        #content {
            width: calc(100% - 250px);
            padding: 20px;
            min-height: 100vh;
            transition: all 0.3s;
            position: absolute;
            top: 0;
            right: 0;
        }
        #content.active {
            width: 100%;
        }
        @media (max-width: 768px) {
            #sidebar {
                margin-left: -250px;
            }
            #sidebar.active {
                margin-left: 0;
            }
            #content {
                width: 100%;
            }
            #content.active {
                width: calc(100% - 250px);
            }
        }
        .profile-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header text-center">
                  <img src="logo 3.png" alt="Moch. Shohibul Asyrof" class="profile-image">
                    <h5>SDM TIK</h5>
            </div>

                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link active" href="tuk.php">TUK</a></li>
                    <li class="nav-item"><a class="nav-link" href="asesor.php">Asesor</a></li>
                    <li class="nav-item"><a class="nav-link" href="mitra.php">Mitra</a></li>
                                <li class="nav-item"><a class="nav-link" href="admin_skema.php">Skema</a></li>
                    <li class="nav-item"><a class="nav-link" href="berita.php">Berita</a></li>
                    <li class="nav-item"><a class="nav-link" href="gallery.php">gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="nambah.php">Tambah</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.html">Keluar</a></li>
                </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-info">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <span class="navbar-brand mb-0 h1 ml-2">Manajemen Data Gallery</span>
                </div>
            </nav>
                <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">Tambah</button>
                
                <div class="table-responsive">
                    <table id="galleryTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Gambar</th>
                                <th>Judul</th>
                                <th>Deskripsi</th>
                                <th>Kategori</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gallery_items as $index => $item): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><img src="<?= $item['image_url'] ?>" alt="<?= $item['title'] ?>" style="width: 100px; height: auto;"></td>
                                <td><?= $item['title'] ?></td>
                                <td><?= $item['description'] ?></td>
                                <td><?= $item['category'] ?></td>
                                <td><?= $item['date_added'] ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info edit-btn" data-id="<?= $item['id'] ?>">Edit</button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $item['id'] ?>">Hapus</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Tampilkan pesan jika ada
    if (isset($_GET['status']) && $_GET['status'] === 'success') {
        echo '<div class="alert alert-success">' . htmlspecialchars($_GET['message']) . '</div>';
    }
    ?>

<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Galeri Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div id="form-container">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Gambar</label>
                                    <input type="file" class="form-control-file" name="image[]" required accept="image/*">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Judul</label>
                                    <input type="text" class="form-control" name="title[]" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Deskripsi</label>
                                    <textarea class="form-control" name="description[]" required></textarea>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Kategori</label>
                                    <select class="form-control" name="category[]" required>
                                        <option value="ujikom">Ujikom</option>
                                        <option value="ujikomindustri">Ujikom industri</option>
                                        <option value="Penyerahan_Sertifikat">Penyerahan Sertifikat</option>
                                        <option value="Kerja_Sama">Kerja Sama</option>
                                        <option value="Event">Event</option>
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

    <!-- Modal untuk mengedit data -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Galeri</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="form-group">
                            <label>Gambar Saat Ini</label>
                            <img id="edit_current_image" src="" alt="Gambar Saat Ini" style="max-width: 100%; height: auto;">
                        </div>
                        <div class="form-group">
                            <label>Gambar Baru (Opsional)</label>
                            <input type="file" class="form-control-file" name="image" accept="image/*">
                            <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah gambar.</small>
                        </div>
                        <div class="form-group">
                            <label>Judul</label>
                            <input type="text" class="form-control" name="title" id="edit_title" required>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea class="form-control" name="description" id="edit_description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Kategori</label>
                            <select class="form-control" name="category" id="edit_category" required>
                                <option value="ujikom">Ujikom</option>
                                <option value="ujikomindustri">Ujikom industri</option>
                                <option value="Penyerahan_Sertifikat">Penyerahan Sertifikat</option>
                                <option value="Kerja_Sama">Kerja Sama</option>
                                <option value="Event">Event</option>
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
    console.log("jQuery version:", $.fn.jquery);

    var table = $('#galleryTable').DataTable({
        "language": {
            "lengthMenu": "Tampilkan MENU entri",
            "search": "Cari:",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            }
        }
    });

    $('#sidebarCollapse').on('click', function() {
                $('#sidebar, #content').toggleClass('active');
            });

    $('#galleryTable').on('click', '.edit-btn', function(e) {
        e.preventDefault();
        console.log("Tombol edit diklik");
        var id = $(this).data('id');
        console.log("ID item yang diedit:", id);

        $.ajax({
            url: 'get_gallery.php',
            method: 'GET',
            data: {id: id},
            dataType: 'json',
            success: function(data) {
                console.log("Data diterima:", data);
                if (data.error) {
                    alert(data.error);
                } else {
                    $('#edit_id').val(data.id);
                    $('#edit_current_image').attr('src', data.image_url);
                    $('#edit_title').val(data.title);
                    $('#edit_description').val(data.description);
                    $('#edit_category').val(data.category);
                    $('#editModal').modal('show');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX:", status, error);
                alert("Terjadi kesalahan saat mengambil data. Silakan coba lagi.");
            }
        });
    });

    // Menggunakan event delegation untuk tombol hapus
    $('#galleryTable').on('click', '.delete-btn', function() {
    var id = $(this).data('id');
    if (confirm('Apakah Anda yakin ingin menghapus item galeri ini?')) {
        $.ajax({
            url: 'gallery.php',
            method: 'GET',
            data: { delete: id },
            success: function(response) {
                location.reload();
            },
            error: function() {
                alert('Terjadi kesalahan saat menghapus data.');
            }
        });
    }
});

        // Fungsi untuk menambah form
        $('#add-form').click(function() {
        var newForm = `
            <div class="form-row">
                <div class="form-group col-md-6">
                    <input type="file" class="form-control-file" name="image[]" required accept="image/*">
                </div>
                <div class="form-group col-md-6">
                    <input type="text" class="form-control" name="title[]" placeholder="Judul" required>
                </div>
                <div class="form-group col-md-6">
                    <textarea class="form-control" name="description[]" placeholder="Deskripsi" required></textarea>
                </div>
                <div class="form-group col-md-6">
                    <select class="form-control" name="category[]" required>
                        <option value="ujikom">Ujikom</option>
                        <option value="ujikomindustri">Ujikom industri</option>
                        <option value="Penyerahan_Sertifikat">Penyerahan Sertifikat</option>
                        <option value="Kerja_Sama">Kerja Sama</option>
                        <option value="Event">Event</option>
                    </select>
                </div>
            </div>
        `;
        $('#form-container').append(newForm);
    });
}); // Akhir dari $(document).ready

</script>
</body>
</html>
</html>