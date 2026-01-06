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
    $stmt = $conn->prepare("SELECT * FROM gallery WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $gallery_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($gallery_item) {
        echo json_encode($gallery_item);
    } else {
        echo json_encode(['error' => 'Data galeri tidak ditemukan']);
    }
} else {
    echo json_encode(['error' => 'ID tidak diberikan']);
}
?>
