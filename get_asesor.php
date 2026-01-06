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

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM data_asesor WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT); // Pastikan tipe data integer
    $stmt->execute();
    $asesor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($asesor) {
        echo json_encode($asesor);
    } else {
        echo json_encode(['error' => 'Data tidak ditemukan']);
    }
} else {
    echo json_encode(['error' => 'ID tidak disediakan']);
}
?>