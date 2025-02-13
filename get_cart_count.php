<?php
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['id_pengguna'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Pengguna tidak login.']);
    exit;
}

// Periksa session cart_count
if (!isset($_SESSION['cart_count'])) {
    // Jika session cart_count belum ada, hitung dari database
    require 'db_connect.php'; // Pastikan koneksi database tersedia
    $id_pengguna = $_SESSION['id_pengguna'];

    // Hitung jumlah produk unik di keranjang
    $query = $koneksi->prepare("SELECT COUNT(id_produk) AS unique_items FROM keranjang_belanja WHERE id_pengguna = ?");
    $query->bind_param("i", $id_pengguna);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();

    $_SESSION['cart_count'] = $row['unique_items'] ?? 0; // Simpan ke session
}

// Kirimkan jumlah produk unik di keranjang
echo json_encode([
    'cartCount' => $_SESSION['cart_count']
]);
