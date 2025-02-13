<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: login.html");
    exit();
}

// Koneksi ke database
include 'db_connect.php';

// Ambil ID pengguna dari session
$id_pengguna = $_SESSION['id_pengguna'];

// Inisialisasi keranjang dari database
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];

    // Ambil data keranjang dari database
    $query = $koneksi->prepare("SELECT k.id_produk, p.nama_produk, p.harga AS product_price, k.jumlah, p.url_gambar AS product_image 
                                FROM keranjang_belanja k 
                                INNER JOIN produk p ON k.id_produk = p.id_produk 
                                WHERE k.id_pengguna = ?");
    $query->bind_param("i", $id_pengguna);
    $query->execute();
    $result = $query->get_result();

    while ($row = $result->fetch_assoc()) {
        $_SESSION['keranjang'][] = [
            'product_id' => $row['id_produk'],
            'product_name' => $row['nama_produk'],
            'product_price' => $row['product_price'],
            'quantity' => $row['jumlah'],
            'product_image' => $row['product_image']
        ];
    }
}

// Proses update kuantitas
if (isset($_POST['update_quantity'])) {
    $id_produk = intval($_POST['id_produk']);
    $new_quantity = intval($_POST['quantity']);

    foreach ($_SESSION['keranjang'] as &$item) {
        if ($item['product_id'] == $id_produk) {
            $item['quantity'] = max(1, $new_quantity);

            // Update jumlah di database
            $query = $koneksi->prepare("UPDATE keranjang_belanja SET jumlah = ? WHERE id_pengguna = ? AND id_produk = ?");
            $query->bind_param("iii", $new_quantity, $id_pengguna, $id_produk);
            $query->execute();
            break;
        }
    }

    header("Location: keranjang.php");
    exit();
}

// Menghapus produk dari keranjang
if (isset($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);

    $_SESSION['keranjang'] = array_values(
        array_filter($_SESSION['keranjang'], function ($item) use ($remove_id) {
            return $item['product_id'] != $remove_id;
        })
    );

    $query = $koneksi->prepare("DELETE FROM keranjang_belanja WHERE id_pengguna = ? AND id_produk = ?");
    $query->bind_param("ii", $id_pengguna, $remove_id);
    $query->execute();

    header("Location: keranjang.php");
    exit();
}

// Menghitung total harga keranjang
$total_harga = 0;
$valid_cart_items = array_filter($_SESSION['keranjang'], function ($item) {
    return is_array($item) &&
        isset($item['product_id']) &&
        isset($item['product_name']) &&
        isset($item['product_price']) &&
        isset($item['quantity']);
});

foreach ($valid_cart_items as $item) {
    $total_harga += $item['product_price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Nyendok Nyruput</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .cart-table th,
        .cart-table td {
            padding: 15px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .cart-table th {
            background-color: #f4f4f4;
            font-size: 16px;
            font-weight: bold;
        }

        .cart-table td img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }

        .cart-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            gap: 20px;
        }

        .cart-footer .total {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            flex-grow: 1;
            text-align: left;
            margin-right: 20px;
        }

        .order-btn {
            padding: 12px 25px;
            background-color: #28a745;
            color: white;
            border-radius: 5px;
            font-size: 18px;
            text-align: center;
            display: inline-block;
            margin-left: 20px;
        }

        .order-btn a {
            text-decoration: none;
            color: white;
        }

        .order-btn a:hover {
            background-color: #218838;
        }

        .remove-link {
            color: #e74c3c;
            text-decoration: none;
        }

        .remove-link:hover {
            text-decoration: underline;
        }

        .update-btn {
            background-color: #3498db;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
        }

        .update-btn:hover {
            background-color: #2980b9;
        }

        .cart-empty {
            text-align: center;
            margin-top: 50px;
        }

        .cart-empty img {
            width: 150px;
        }

        .cart-empty p {
            font-size: 18px;
            color: #7f8c8d;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="nav-brand">
            <a href="index.php" class="brand-logo">
                <img src="https://i.ibb.co.com/7G0b1Pt/Cokelat-Hijau-Ilustrasi-Logo-Kedai-Kopi-1-removebg-preview.png" alt="Nyendok Nyruput Logo" border="0" class="nav-logo">
            </a>
        </div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="products.php">Produk</a>
            <a href="keranjang.php" class="active">Keranjang</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="page-header">
        <h1>Keranjang Belanja Anda</h1>
    </div>

    <div class="container">
        <?php if (!empty($valid_cart_items)): ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Kuantitas</th>
                        <th>Total Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($valid_cart_items as $item): ?>
                        <tr>
                            <td><img src="<?php echo $item['product_image']; ?>" alt="<?php echo $item['product_name']; ?>"></td>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td>Rp <?php echo number_format($item['product_price'], 0, ',', '.'); ?></td>
                            <td>
                                <form action="keranjang.php" method="POST">
                                    <input type="hidden" name="id_produk" value="<?php echo $item['product_id']; ?>">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" style="width: 70px;">
                                    <button type="submit" name="update_quantity" class="update-btn">Update</button>
                                </form>
                            </td>
                            <td>Rp <?php echo number_format($item['product_price'] * $item['quantity'], 0, ',', '.'); ?></td>
                            <td>
                                <a href="keranjang.php?remove=<?php echo $item['product_id']; ?>" class="remove-link">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-footer">
                <div class="total">Total Harga: Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></div>
                <div class="order-btn">
                    <a href="order.php">Pesan Sekarang</a>
                </div>
            </div>
        <?php else: ?>
            <div class="cart-empty">
                <img src="empty-cart.png" alt="Keranjang Kosong">
                <p>Keranjang Belanja Anda Kosong. Silakan lanjutkan belanja!</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="script.js"></script>
</body>

</html>