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
    die("Koneksi gagal: " . $e->getMessage());
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $conn->prepare("SELECT * FROM data_tuk WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode($data);
}
?>