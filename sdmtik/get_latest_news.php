<?php
require_once 'config.php';

try {
    $stmt = $conn->prepare("SELECT * FROM berita ORDER BY tanggal DESC LIMIT 3");
    $stmt->execute();
    $latest_news = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($latest_news);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>