<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_email']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Cek jika ada request logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Jalankan pengecekan login
requireLogin();
?> 