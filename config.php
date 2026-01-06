<?php
// Konfigurasi database untuk cPanel
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'lspketapang_adminlsp');
define('DB_PASSWORD', 'superadmin123!');
define('DB_NAME', 'lspketapang_sdmtik');

$host = 'localhost';
$dbname = 'lspketapang_sdmtik';
$username = 'lspketapang_adminlsp';
$password = 'superadmin123!';

// Mencoba membuat koneksi ke database menggunakan PDO
try {
    $conn = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("ERROR: Tidak bisa terhubung ke database. " . $e->getMessage());
}

// Fungsi untuk membersihkan input
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}



// Fungsi untuk redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Fungsi untuk menampilkan pesan error
function display_error($message) {
    return "<div class='alert alert-danger'>$message</div>";
}

// Fungsi untuk menampilkan pesan sukses
function display_success($message) {
    return "<div class='alert alert-success'>$message</div>";
}

// Anda bisa menambahkan fungsi-fungsi lain yang sering digunakan di sini
?>