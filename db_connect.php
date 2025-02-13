<?php
// Tentukan konfigurasi koneksi database
$host = 'localhost';  // Atau IP server jika menggunakan remote
$username = 'root';   // Username untuk koneksi database
$password = '';       // Password untuk koneksi database
$database = 'jurnal';  // Ganti dengan nama database Anda

// Membuat koneksi ke database
$koneksi = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
