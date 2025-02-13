<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id_pengguna = $_SESSION['user_id'];
$nama_pemesan = $_POST['nama_pemesan'];
$alamat_pengiriman = $_POST['alamat_pengiriman'];
$catatan_khusus = $_POST['catatan_khusus'];
$metode_pembayaran = $_POST['metode_pembayaran'];

// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "toko_kue";

$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Hitung total pembayaran
$sql = "SELECT k.subtotal FROM keranjang_belanja k WHERE k.id_pengguna = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_pengguna);
$stmt->execute();
$result = $stmt->get_result();
$total_pembayaran = 0;
while ($row = $result->fetch_assoc()) {
    $total_pembayaran += $row['subtotal'];
}

// Set payment status based on payment method
$status_pembayaran = ($metode_pembayaran === 'Transfer Bank') ? 'Lunas' : 'Belum Lunas';

$sql_pesanan = "INSERT INTO pesanan (id_pengguna, nama_pemesan, total_pembayaran, metode_pembayaran, status_pembayaran, status_pesanan, tanggal_pesanan, alamat_pengiriman, catatan_khusus)  
                VALUES (?, ?, ?, ?, ?, 'Diproses', NOW(), ?, ?)";
$stmt_pesanan = $conn->prepare($sql_pesanan);
$stmt_pesanan->bind_param("isdsiss", $id_pengguna, $nama_pemesan, $total_pembayaran, $metode_pembayaran, $status_pembayaran, $alamat_pengiriman, $catatan_khusus);
$stmt_pesanan->execute();

// Reset keranjang belanja
$sql_reset = "DELETE FROM keranjang_belanja WHERE id_pengguna = ?";
$stmt_reset = $conn->prepare($sql_reset);
$stmt_reset->bind_param("i", $id_pengguna);
$stmt_reset->execute();

// Redirect ke halaman selesai
header("Location: selesai.php");
exit();

$conn->close();
