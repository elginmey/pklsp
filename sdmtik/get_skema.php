<?php
$host = 'localhost';
$dbname = 'lspketapang_sdmtik';
$username = 'lspketapang_adminlsp';
$password = 'superadmin123!';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => "Koneksi gagal: " . $e->getMessage()]);
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM skema WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($data);
    } else {
        echo json_encode(['error' => 'Data tidak ditemukan']);
    }
} else {
    echo json_encode(['error' => 'ID tidak valid']);
}
?>