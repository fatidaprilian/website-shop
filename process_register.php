<?php
session_start();
require_once('db_connect.php'); // Koneksi ke database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari formulir pendaftaran
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    // Hash password
    $password_hashed = password_hash($password, PASSWORD_BCRYPT);

    // Set role sebagai 'customer'
    $role = 'customer';

    // Query untuk menyimpan data pengguna baru
    $query = "INSERT INTO pengguna (username, password, email, role, tanggal_daftar) 
              VALUES ('$username', '$password_hashed', '$email', '$role', NOW())";

    // Eksekusi query
    if (mysqli_query($koneksi, $query)) {
        // Jika berhasil, tampilkan popup dan arahkan ke login.html
        echo "<script>alert('Pendaftaran berhasil, silakan login!'); window.location.href = 'login.html';</script>";
    } else {
        // Jika gagal, tampilkan popup dan arahkan kembali ke register.html
        echo "<script>alert('Terjadi kesalahan: " . mysqli_error($conn) . "'); window.location.href = 'register.html';</script>";
    }
}
