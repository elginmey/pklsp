<?php
#include 'admin_middleware.php';#
require_once 'config.php';
require_once 'admin_middleware.php';
$host = 'localhost';
$dbname = '';
$username = 'adminpers_persmin';
$password = 'superadmin123!';




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_berita'])) {
        $judul = clean_input($_POST['judul']);
        $tanggal = clean_input($_POST['tanggal']);
        $konten = clean_input($_POST['konten']);
        $link = clean_input($_POST['link']);

        // Cek apakah menggunakan URL gambar atau file upload
        if (!empty($_POST['gambar_url'])) {
            $gambar = clean_input($_POST['gambar_url']);
        } elseif ($_FILES['gambar']['error'] == 0) {
            $target_dir = __DIR__ . "/uploads/";
            $file_extension = pathinfo($_FILES["gambar"]["name"], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (!is_dir($target_dir) || !is_writable($target_dir)) {
                die("Folder upload tidak ada atau tidak dapat ditulis. Path: " . $target_dir);
            }

            if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                $gambar = "uploads/" . $new_filename;
            } else {
                $error = error_get_last();
                die("Gagal mengunggah file. Error: " . $error['message']);
            }
        } else {
            die("Harap pilih file gambar atau masukkan URL gambar.");
        }

        try {
            $stmt = $conn->prepare("INSERT INTO berita (judul, tanggal, konten, gambar, link) VALUES (:judul, :tanggal, :konten, :gambar, :link)");
            $stmt->bindParam(':judul', $judul);
            $stmt->bindParam(':tanggal', $tanggal);
            $stmt->bindParam(':konten', $konten);
            $stmt->bindParam(':gambar', $gambar);
            $stmt->bindParam(':link', $link);
            
            if ($stmt->execute()) {
                redirect('berita.php?status=success&message=' . urlencode('Berita berhasil ditambahkan'));
            } else {
                echo display_error("Gagal menambahkan berita.");
            }
        } catch (PDOException $e) {
            echo display_error("Database error: " . $e->getMessage());
        }
    } elseif (isset($_POST['update'])) {
        // Proses update berita
        $id = $_POST['id'];
        $judul = clean_input($_POST['judul']);
        $tanggal = clean_input($_POST['tanggal']);
        $konten = clean_input($_POST['konten']);
        $link = clean_input($_POST['link']);

        // Cek apakah ada perubahan gambar
        if (!empty($_POST['gambar_url'])) {
            $gambar = clean_input($_POST['gambar_url']);
        } elseif ($_FILES['gambar']['error'] == 0) {
            // Proses upload file baru
            $target_dir = __DIR__ . "/uploads/";
            $file_extension = pathinfo($_FILES["gambar"]["name"], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                $gambar = "uploads/" . $new_filename;
            } else {
                $error = error_get_last();
                die("Gagal mengunggah file. Error: " . $error['message']);
            }
        } else {
            // Gunakan gambar yang sudah ada
            $stmt = $conn->prepare("SELECT gambar FROM berita WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $gambar = $result['gambar'];
        }

        try {
            $stmt = $conn->prepare("UPDATE berita SET judul = :judul, tanggal = :tanggal, konten = :konten, gambar = :gambar, link = :link WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':judul', $judul);
            $stmt->bindParam(':tanggal', $tanggal);
            $stmt->bindParam(':konten', $konten);
            $stmt->bindParam(':gambar', $gambar);
            $stmt->bindParam(':link', $link);
            
            if ($stmt->execute()) {
                redirect('berita.php?status=success&message=' . urlencode('Berita berhasil diperbarui'));
            } else {
                echo display_error("Gagal memperbarui berita.");
            }
        } catch (PDOException $e) {
            echo display_error("Database error: " . $e->getMessage());
        }
    }
}

function getBerita() {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM berita ORDER BY tanggal DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Jika ada request AJAX
if(isset($_GET['action']) && $_GET['action'] == 'getBerita') {
    $berita = getBerita();
    echo json_encode($berita);
    exit;
}

// Proses DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $conn->prepare("DELETE FROM berita WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        redirect('berita.php?status=success&message=' . urlencode('Berita berhasil dihapus'));
    } catch (PDOException $e) {
        echo display_error("Database error: " . $e->getMessage());
    }
}

// Ambil semua berita
try {
    $stmt = $conn->prepare("SELECT * FROM berita ORDER BY tanggal DESC");
    $stmt->execute();
    $berita = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo display_error("Database error: " . $e->getMessage());
    $berita = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Berita</title>
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

            <ul class="list-unstyled components">
                <li><a href="tuk.php">TUK</a></li>
                <li><a href="asesor.php">Asesor</a></li>
                <li><a href="mitra.php">Mitra</a></li>
                  <li class="nav-item"><a class="nav-link" href="admin_skema.php">Skema</a></li>
                <li><a href="berita.php">Berita</a></li>
                <li><a href="gallery.php">Galeri</a></li>
                 <li><a href="nambah.php">Tambah</a></li>
                 <li class="nav-item"><a class="nav-link" href="?logout=1">Keluar</a></li>
            </ul>
        </nav>
            <!-- Page Content -->
        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-info">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <span class="navbar-brand mb-0 h1 ml-2">Manajemen Data Berita</span>
                </div>
            </nav>
                <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">Tambah Berita</button>
                
                <div class="table-responsive">
                    <table id="beritaTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Judul</th>
                                <th>Tanggal</th>
                                <th>Gambar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($berita as $index => $item): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= $item['judul'] ?></td>
                                <td><?= $item['tanggal'] ?></td>
                                <td><img src="<?= $item['gambar'] ?>" alt="<?= $item['judul'] ?>" width="100"></td>
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

    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Berita Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Judul</label>
                            <input type="text" class="form-control" name="judul" required>
                        </div>
                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="date" class="form-control" name="tanggal" required>
                        </div>
                        <div class="form-group">
                            <label>Konten</label>
                            <textarea class="form-control" name="konten" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Gambar</label>
                            <input type="file" class="form-control-file" name="gambar">
                        </div>
                        <div class="form-group">
                            <label>Atau URL Gambar</label>
                            <input type="url" class="form-control" name="gambar_url">
                        </div>
                        <div class="form-group">
                            <label>Link</label>
                            <input type="url" class="form-control" name="link" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" name="add_berita" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

     <!-- Modal untuk mengedit berita -->
     <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Berita</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="form-group">
                            <label>Judul</label>
                            <input type="text" class="form-control" name="judul" id="edit_judul" required>
                        </div>
                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="date" class="form-control" name="tanggal" id="edit_tanggal" required>
                        </div>
                        <div class="form-group">
                            <label>Konten</label>
                            <textarea class="form-control" name="konten" id="edit_konten" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Gambar Saat Ini</label>
                            <img id="edit_current_image" src="" alt="Current Image" style="max-width: 100%; height: auto;">
                        </div>
                        <div class="form-group">
                            <label>Ganti Gambar (Opsional)</label>
                            <input type="file" class="form-control-file" name="gambar">
                        </div>
                        <div class="form-group">
                            <label>Atau URL Gambar Baru</label>
                            <input type="url" class="form-control" name="gambar_url" id="edit_gambar_url">
                        </div>
                        <div class="form-group">
                            <label>Link</label>
                            <input type="url" class="form-control" name="link" id="edit_link" required>
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
            var table = $('#beritaTable').DataTable({
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

            // Menggunakan event delegation untuk tombol edit
            $('#beritaTable').on('click', '.edit-btn', function() {
    var id = $(this).data('id');
    $.ajax({
        url: 'get_berita.php',
        method: 'GET',
        data: {id: id},
        dataType: 'json',
        success: function(data) {
            if (data.error) {
                alert(data.error);
            } else {
                // Mengisi form edit dengan data yang ada
                $('#edit_id').val(data.id);
                $('#edit_judul').val(data.judul);
                $('#edit_tanggal').val(data.tanggal);
                $('#edit_konten').val(data.konten);
                $('#edit_link').val(data.link);
                $('#edit_current_image').attr('src', data.gambar);
                $('#edit_gambar_url').val(data.gambar);
                
                // Menampilkan modal edit
                $('#editModal').modal('show');
            }
        },
        error: function(xhr, status, error) {
            console.error("Error: " + error);
            alert("Terjadi kesalahan saat mengambil data berita.");
        }
    });
});

            // Menggunakan event delegation untuk tombol hapus
            $('#beritaTable').on('click', '.delete-btn', function() {
                var id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menghapus berita ini?')) {
                    window.location.href = '?delete=' + id;
                }
            });

            $('#sidebarCollapse').on('click', function() {
                $('#sidebar, #content').toggleClass('active');
            });
        });
    </script>
</body>
</html>
