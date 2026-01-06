<?php
session_start();
require 'koneksi.php';

$login_error = false; // Inisialisasi variabel di sini

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Gunakan prepared statement untuk mencegah SQL injection
    $query_sql = "SELECT * FROM tbl_users WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($query_sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Login berhasil
        $_SESSION['user_email'] = $email;
        header("Location: tuk.php");
        exit();
    } else {
        // Login gagal
        $login_error = true;
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="stylea.css" media="screen" title="no title">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css">
    <title>Login Page</title>
    <style>
        /* Tambahkan gaya responsif di sini */
        @media screen and (max-width: 600px) {
            body {
                padding: 20px;
            }
            .input {
                width: 100%;
                max-width: 300px;
                margin: 0 auto;
            }
            .box-input {
                margin-bottom: 15px;
            }
            .btn-input {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <?php if ($login_error): ?>
        <center>
            <h1>Email atau Password Anda Salah. Silahkan Coba Login Kembali.</h1>
            <button><strong><a href='login.php'>Login</a></strong></button>
        </center>
    <?php else: ?>
        <div class="input">
            <h1>LOGIN</h1>
            <form id="loginForm" action="login.php" method="POST">
                <div class="box-input">
                    <i class="fas fa-envelope-open-text"></i>
                    <input type="text" name="email" id="email" placeholder="Email">
                </div>
                <div class="box-input">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" placeholder="Password">
                </div>
                <button type="submit" name="login" class="btn-input">Login</button>
                <div class="bottom">
                    <p>Silahkan Login</p>
                </div>
            </form>
            <p id="errorMessage" style="color: red; display: none; margin-top: 50px;"></p>
        </div>

        <script>
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                var email = document.getElementById('email').value;
                var password = document.getElementById('password').value;
                var errorMessage = document.getElementById('errorMessage');
                
                if (email.trim() === '' || password.trim() === '') {
                    e.preventDefault();
                    errorMessage.textContent = 'Silakan masukkan email dan password Anda.';
                    errorMessage.style.display = 'block';
                }
            });
        </script>
    <?php endif; ?>
</body>
</html>