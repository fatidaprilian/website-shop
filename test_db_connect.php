<?php
include 'db_connect.php'; // Memastikan file db_connect.php sudah ada di direktori yang sama

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
echo "Koneksi berhasil!";
