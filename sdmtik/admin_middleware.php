<?php
session_start();
require_once 'koneksi.php';

class AdminMiddleware {
    private $conn;
    private $session_timeout = 1800; // 30 menit dalam detik
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function checkAuth() {
        // Cek apakah user sudah login
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_activity'])) {
            $this->redirectToLogin();
        }

        // Cek session timeout
        if (time() - $_SESSION['last_activity'] > $this->session_timeout) {
            $this->logout();
            $this->redirectToLogin("Sesi Anda telah berakhir. Silakan login kembali.");
        }

        // Update last activity time
        $_SESSION['last_activity'] = time();

        // Verifikasi token CSRF
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Verifikasi IP dan User Agent
        if (!isset($_SESSION['client_fingerprint']) || 
            $_SESSION['client_fingerprint'] !== $this->generateFingerprint()) {
            $this->logout();
            $this->redirectToLogin("Deteksi aktivitas mencurigakan. Silakan login kembali.");
        }
    }

    private function generateFingerprint() {
        return hash('sha256', $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
    }

    private function redirectToLogin($message = null) {
        if ($message) {
            $_SESSION['login_message'] = $message;
        }
        header("Location: login.php");
        exit();
    }

    public function logout() {
        session_unset();
        session_destroy();
        session_start();
    }
}

// Inisialisasi middleware
$adminMiddleware = new AdminMiddleware($conn);
$adminMiddleware->checkAuth(); 