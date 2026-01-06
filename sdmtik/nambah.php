<?php

require_once 'admin_middleware.php';
// Koneksi ke database
$host = 'localhost';
$dbname = 'lspketapang_userlsp'; // Ubah nama database menjadi db_users
$username = 'lspketapang_adminlsp';
$password = 'superadmin123!';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        // Proses penambahan data
        $fullname = $_POST['fullname'];
        $username = $_POST['username'];
        $institution = $_POST['institution'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        $stmt = $conn->prepare("INSERT INTO tbl_users (fullname, username, institution, email, password) VALUES (:fullname, :username, :institution, :email, :password)");
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':institution', $institution);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();

        // Setelah pemrosesan selesai, lakukan redirect
        header('Location: nambah.php?status=success&message=Data pengguna berhasil ditambahkan');
        exit();
    } elseif (isset($_POST['update'])) {
        // Proses update data
        $id = $_POST['id'];
        $fullname = $_POST['fullname'];
        $username = $_POST['username'];
        $institution = $_POST['institution'];
        $email = $_POST['email'];
        
        $stmt = $conn->prepare("UPDATE tbl_users SET fullname = :fullname, username = :username, institution = :institution, email = :email WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':institution', $institution);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Setelah pemrosesan selesai, lakukan redirect
        header('Location: nambah.php?status=success&message=Data pengguna berhasil diperbarui');
        exit();
    }
}

// Proses DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tbl_users WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    // Redirect setelah menghapus
    header('Location: nambah.php?status=success&message=Data pengguna berhasil dihapus');
    exit();
}

// Ambil semua pengguna
$stmt = $conn->prepare("SELECT * FROM tbl_users");
$stmt->execute();
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Data Pengguna</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
    <style>
        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="text-center mb-4">
                    <img src="logo 3.png" alt="Moch. Shohibul Asyrof" class="profile-image">
                    <h5>SDM TIK</h5>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link active" href="tuk.php">TUK</a></li>
                    <li class="nav-item"><a class="nav-link" href="asesor.php">Asesor</a></li>
                    <li class="nav-item"><a class="nav-link" href="mitra.php">Mitra</a></li>
                    <li class="nav-item"><a class="nav-link" href="berita.php">Berita</a></li>
                    <li class="nav-item"><a class="nav-link" href="gallery.php">gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="nambah.php">Tambah</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.html">Keluar</a></li>
                </ul>
            </div>
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 content">
                <h2 class="mb-4">Manajemen Data Pengguna</h2>
                <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">Tambah</button>
                
                <div class="table-responsive">
                    <table id="usersTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nama Lengkap</th>
                                <th>Username</th>
                                <th>Institusi</th>
                                <th>Email</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $index => $user): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($user['fullname'] ?? '') ?></td>
                                <td><?= htmlspecialchars($user['username'] ?? '') ?></td>
                                <td><?= htmlspecialchars($user['institution'] ?? '') ?></td>
                                <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                                <td>
    <button class="btn btn-sm btn-info edit-btn" data-id="<?= htmlspecialchars($user['id'] ?? '') ?>">Edit</button>
    <button class="btn btn-sm btn-danger delete-btn" data-id="<?= htmlspecialchars($user['id'] ?? '') ?>">Hapus</button>
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

    <!-- Modal untuk menambah data -->
    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengguna Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" class="form-control" name="fullname" required>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="form-group">
                            <label>Institusi</label>
                            <input type="text" class="form-control" name="institution" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" name="add" class="btn btn-primary">Simpan</button>
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
                    <h5 class="modal-title">Edit Pengguna</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" class="form-control" name="fullname" id="edit_fullname" required>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" class="form-control" name="username" id="edit_username" required>
                        </div>
                        <div class="form-group">
                            <label>Institusi</label>
                            <input type="text" class="form-control" name="institution" id="edit_institution" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" id="edit_email" required>
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
            $('#usersTable').DataTable({
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

            // Fungsi untuk mengisi modal edit
            $('.edit-btn').click(function() {
                var id = $(this).data('id');
                // Di sini Anda perlu mengambil data dari server berdasarkan ID
                // dan mengisi form edit. Contoh sederhana:
                $('#edit_id').val(id);
                $('#edit_fullname').val('Nama Lengkap ' + id);
                $('#edit_username').val('username' + id);
                $('#edit_institution').val('Institusi ' + id);
                $('#edit_email').val('email' + id + '@example.com');
                $('#editModal').modal('show');
            });

            // Fungsi untuk konfirmasi hapus
            $('.delete-btn').click(function() {
                var id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menghapus pengguna ini?')) {
                    window.location.href = 'nambah.php?delete=' + id;
                }
            });
        });
    </script>
</body>
</html>