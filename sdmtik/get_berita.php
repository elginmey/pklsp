<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Start output buffering to prevent any unexpected output
ob_start();

if (isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM berita WHERE id = :id");
        $stmt->bindParam(':id', $_GET['id']);
        $stmt->execute();
        $berita = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$berita) {
            http_response_code(404);
            echo json_encode(["error" => true, "message" => "Berita tidak ditemukan"]);
            exit;
        }

        echo json_encode($berita, JSON_UNESCAPED_UNICODE);
        exit;
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode([
            "error" => true,
            "message" => $e->getMessage(),
            "file" => $e->getFile(),
            "line" => $e->getLine()
        ]);
        exit;
    }
}

try {
    $stmt = $conn->prepare("SELECT * FROM berita ORDER BY tanggal DESC");
    $stmt->execute();
    $berita = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($berita)) {
        echo json_encode(["error" => true, "message" => "No news found"]);
        exit;
    }

    // Format data for response
    $formattedBerita = array_map(function($item) {
        // Detect encoding and convert to UTF-8 if necessary
        $encoding = mb_detect_encoding($item['konten'], 'UTF-8, ISO-8859-1, ISO-8859-15', true);
        if ($encoding !== 'UTF-8') {
            $item['konten'] = mb_convert_encoding($item['konten'], 'UTF-8', $encoding);
        }

        // Remove any invalid UTF-8 characters (optional)
        $item['konten'] = iconv('UTF-8', 'UTF-8//IGNORE', $item['konten']);
        
        return [
            'image' => $item['gambar'],
            'date' => date('d/m/Y H:i', strtotime($item['tanggal'])) . ' WIB',
            'title' => $item['judul'],
            'content' => $item['konten'],  // Keep HTML content intact
            'link' => $item['link']
        ];
    }, $berita);



    // JSON encode the formatted data with robust encoding options
    echo json_encode($formattedBerita, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);

    // Check for JSON encoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "JSON encoding error: " . json_last_error_msg();
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

// End output buffering
ob_end_flush();
?>
