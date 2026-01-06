<?php
// Koneksi ke database
$host = 'localhost';
$dbname = 'lspketapang_sdmtik';
$username = 'lspketapang_adminlsp';
$password = 'superadmin123!';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(["error" => "Koneksi gagal: " . $e->getMessage()]));
}

$stmt = $conn->prepare("SELECT * FROM gallery ORDER BY date_added DESC");
$stmt->execute();
$gallery_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($gallery_items);