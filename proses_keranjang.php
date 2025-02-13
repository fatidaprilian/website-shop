<?php
session_start();
include 'db_connect.php'; // Koneksi ke database

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (empty($_POST['id_produk']) || empty($_POST['nama_produk']) || empty($_POST['harga']) || empty($_POST['gambar'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Data produk tidak lengkap.']);
        exit;
    }

    $id_produk = intval($_POST['id_produk']);
    $nama_produk = $_POST['nama_produk'];
    $harga = floatval($_POST['harga']);
    $gambar = $_POST['gambar'];

    if (!isset($_SESSION['id_pengguna'])) {
        http_response_code(401); // Unauthorized
        echo json_encode(['error' => 'Pengguna tidak login.']);
        exit;
    }
    $id_pengguna = $_SESSION['id_pengguna'];

    // Periksa apakah produk sudah ada di keranjang database
    $query = $koneksi->prepare("SELECT * FROM keranjang_belanja WHERE id_pengguna = ? AND id_produk = ?");
    $query->bind_param("ii", $id_pengguna, $id_produk);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        // Jika produk sudah ada, tambahkan kuantitasnya
        $row = $result->fetch_assoc();
        $jumlah_baru = $row['jumlah'] + 1;
        $subtotal_baru = $jumlah_baru * $harga;

        $update_query = $koneksi->prepare("UPDATE keranjang_belanja SET jumlah = ?, subtotal = ? WHERE id_pengguna = ? AND id_produk = ?");
        $update_query->bind_param("idii", $jumlah_baru, $subtotal_baru, $id_pengguna, $id_produk);
        $update_query->execute();
    } else {
        // Tambahkan produk baru ke keranjang
        $quantity = 1;
        $subtotal = $harga;

        $insert_query = $koneksi->prepare("INSERT INTO keranjang_belanja (id_pengguna, id_produk, jumlah, subtotal) VALUES (?, ?, ?, ?)");
        $insert_query->bind_param("iiid", $id_pengguna, $id_produk, $quantity, $subtotal);
        $insert_query->execute();
    }

    // Perbarui session keranjang
    $_SESSION['keranjang'] = [];
    $fetch_query = $koneksi->prepare("SELECT k.id_produk, p.nama_produk, p.harga AS product_price, k.jumlah, p.url_gambar AS product_image 
                                      FROM keranjang_belanja k 
                                      INNER JOIN produk p ON k.id_produk = p.id_produk 
                                      WHERE k.id_pengguna = ?");
    $fetch_query->bind_param("i", $id_pengguna);
    $fetch_query->execute();
    $fetch_result = $fetch_query->get_result();

    while ($row = $fetch_result->fetch_assoc()) {
        $_SESSION['keranjang'][] = [
            'product_id' => $row['id_produk'],
            'product_name' => $row['nama_produk'],
            'product_price' => $row['product_price'],
            'quantity' => $row['jumlah'],
            'product_image' => $row['product_image']
        ];
    }

    // Hitung jumlah total produk unik di keranjang
    $keranjang_count = count($_SESSION['keranjang']);

    echo json_encode([
        'keranjangCount' => $keranjang_count,
        'message' => 'Produk berhasil ditambahkan ke keranjang.'
    ]);
    exit;
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Metode request tidak diizinkan.']);
    exit;
}
