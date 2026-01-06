<?php
// Koneksi ke database
$host = 'localhost';
$dbname = 'lspketapang_sdmtik';
$username = 'lspketapang_adminlsp';
$password = 'superadmin123!';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => "Koneksi gagal: " . $e->getMessage()]));
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $conn->prepare("SELECT * FROM mitra_kerja WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $mitra = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($mitra) {
        echo json_encode($mitra);
    } else {
        echo json_encode(['error' => 'Data mitra tidak ditemukan']);
    }
} else {
    echo json_encode(['error' => 'ID tidak diberikan']);
}