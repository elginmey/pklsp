<?php
$servername = "localhost";
$database = "lspketapang_userlsp";
$username = "lspketapang_adminlsp";
$password = "superadmin123!";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Koneksi Gagal : " . mysqli_connect_error());
} else {
    echo "";
}


