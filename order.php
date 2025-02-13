<?php
session_start();

// Pastikan pengguna sudah login
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: login.html");
    exit();
}

// Panggil file koneksi database
require 'db_connect.php';

// Pastikan keranjang adalah array
if (!isset($_SESSION['keranjang']) || !is_array($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Jika ada data produk dari URL, tambahkan ke keranjang
if (isset($_GET['product_id']) && isset($_GET['product_name']) && isset($_GET['product_price'])) {
    $product_id = $_GET['product_id'];
    $product_name = $_GET['product_name'];
    $product_price = $_GET['product_price'];
    $product_image = $_GET['product_image'];

    // Tambahkan produk ke keranjang
    $_SESSION['keranjang'][] = [
        'product_id' => $product_id,
        'product_name' => $product_name,
        'product_price' => $product_price,
        'product_image' => $product_image,
        'quantity' => 1
    ];
}

// Menghitung total harga keranjang
$total_price = 0;
if (!empty($_SESSION['keranjang'])) {
    foreach ($_SESSION['keranjang'] as $item) {
        if (is_array($item) && isset($item['product_price']) && isset($item['quantity'])) {
            $total_price += $item['product_price'] * $item['quantity'];
        }
    }
}

// Proses pemesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_penerima = $_POST['nama_penerima'];
    $alamat = $_POST['alamat'];
    $nomor_telepon = $_POST['nomor_telepon'];
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $catatan_khusus = $_POST['catatan_khusus'];

    // Set status pembayaran berdasarkan metode pembayaran
    $status_pembayaran = ($metode_pembayaran === 'Transfer Bank') ? 'Lunas' : 'Belum Lunas';

    // Masukkan pemesanan ke tabel pesanan
    $stmt = $koneksi->prepare("INSERT INTO pesanan (id_pengguna, total_pembayaran, nama_pemesan, metode_pembayaran, alamat_pengiriman, nomor_telepon, catatan_khusus, status_pembayaran) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssss", $_SESSION['id_pengguna'], $total_price, $nama_penerima, $metode_pembayaran, $alamat, $nomor_telepon, $catatan_khusus, $status_pembayaran);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // Masukkan detail pemesanan ke tabel detail_pesanan
    foreach ($_SESSION['keranjang'] as $item) {
        if (is_array($item) && isset($item['product_id']) && isset($item['quantity'])) {
            $stmt_detail = $koneksi->prepare("INSERT INTO detail_pesanan (id_pesanan, id_produk, quantity, harga_total) VALUES (?, ?, ?, ?)");
            $harga_total = $item['product_price'] * $item['quantity'];
            $stmt_detail->bind_param("iiii", $order_id, $item['product_id'], $item['quantity'], $harga_total);
            $stmt_detail->execute();
        }
    }

    // Hapus data keranjang belanja dari database untuk pengguna yang sedang login
    $stmt_clear_cart = $koneksi->prepare("DELETE FROM keranjang_belanja WHERE id_pengguna = ?");
    $stmt_clear_cart->bind_param("i", $_SESSION['id_pengguna']);
    $stmt_clear_cart->execute();

    // Kosongkan keranjang di sesi
    unset($_SESSION['keranjang']);

    // Redirect ke halaman sukses pemesanan
    header("Location: sukses_pemesanan.php?id_pesanan=$order_id");
    exit();
}

$koneksi->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order - Nyendok Nyruput</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .order-container {
            width: 80%;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .order-table th,
        .order-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .order-table th {
            background-color: #f4f4f4;
        }

        .order-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .order-form input,
        .order-form select {
            padding: 12px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
        }

        .order-form textarea {
            padding: 12px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
            resize: vertical;
        }

        .order-btn {
            padding: 12px 25px;
            background-color: #28a745;
            color: white;
            border-radius: 5px;
            font-size: 18px;
            text-align: center;
            cursor: pointer;
            width: 100%;
            border: none;
        }

        .order-btn:hover {
            background-color: #218838;
        }

        .total-price {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <a href="index.php" class="brand-logo">
            <img src="https://i.ibb.co.com/7G0b1Pt/Cokelat-Hijau-Ilustrasi-Logo-Kedai-Kopi-1-removebg-preview.png" alt="Nyendok Nyruput Logo" border="0" class="nav-logo">
        </a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="products.php">Produk</a>
            <a href="keranjang.php">Keranjang</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="page-header">
        <h1>Formulir Pemesanan</h1>
    </div>

    <div class="order-container">
        <h2>Rincian Pemesanan</h2>

        <!-- Tabel Keranjang -->
        <table class="order-table">
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Harga</th>
                    <th>Kuantitas</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($_SESSION['keranjang'] as $item) {
                    if (is_array($item) && isset($item['product_name'], $item['product_price'], $item['quantity'])) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($item['product_name']) . "</td>";
                        echo "<td>Rp " . number_format($item['product_price'], 0, ',', '.') . "</td>";
                        echo "<td>" . $item['quantity'] . "</td>";
                        echo "<td>Rp " . number_format($item['product_price'] * $item['quantity'], 0, ',', '.') . "</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>

        <!-- Total Harga -->
        <div class="total-price">
            <p>Total Harga: Rp <?php echo number_format($total_price, 0, ',', '.'); ?></p>
        </div>

        <!-- Formulir Data Pengiriman -->
        <h3>Data Pengiriman</h3>
        <form class="order-form" method="POST" action="order.php">
            <input type="text" name="nama_penerima" placeholder="Nama Penerima" required>
            <input type="text" name="alamat" placeholder="Alamat Pengiriman" required>
            <input type="text" name="nomor_telepon" placeholder="Nomor Telepon" required>
            <select name="metode_pembayaran" required>
                <option value="Transfer Bank">Transfer Bank</option>
                <option value="COD">Cash On Delivery (COD)</option>
            </select>
            <textarea name="catatan_khusus" placeholder="Catatan Khusus (Opsional)"></textarea>
            <button type="submit" class="order-btn">Pesan Sekarang</button>
        </form>
    </div>

    <footer>
        <p>&copy; 2024 Nyendok Nyruput</p>
    </footer>
</body>

</html>