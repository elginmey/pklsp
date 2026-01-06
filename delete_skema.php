<?php
include 'koneksi.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    try {
        $conn->beginTransaction();
        
        // Hapus detail skema terlebih dahulu
        $stmt = $conn->prepare("DELETE FROM skema_detail WHERE skema_id = ?");
        $stmt->execute([$_POST['id']]);
        
        // Kemudian hapus skema
        $stmt = $conn->prepare("DELETE FROM skema WHERE id = ?");
        $result = $stmt->execute([$_POST['id']]);
        
        if ($result) {
            $conn->commit();
            echo json_encode(['status' => 'success']);
        } else {
            throw new Exception("Gagal menghapus skema");
        }
    } catch(PDOException $e) {
        $conn->rollBack();
        error_log("Database Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan database']);
    } catch(Exception $e) {
        $conn->rollBack();
        error_log("Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method or missing ID']);
}