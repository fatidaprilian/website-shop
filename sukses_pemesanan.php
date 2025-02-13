<?php
session_start();

// Cek apakah ada id_pesanan
if (!isset($_GET['id_pesanan'])) {
    echo "ID Pesanan tidak ada!";
    exit();
}

$id_pesanan = $_GET['id_pesanan'];

// Koneksi ke database
require 'db_connect.php';

// Ambil data pesanan
$stmt = $koneksi->prepare("SELECT * FROM pesanan WHERE id_pesanan = ?");
$stmt->bind_param("i", $id_pesanan);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Pesanan tidak ditemukan.";
    exit();
}

$pesanan = $result->fetch_assoc();

// Ambil detail pesanan
$stmt_detail = $koneksi->prepare("SELECT dp.id_produk, p.nama_produk, dp.quantity, dp.harga_total 
                                  FROM detail_pesanan dp 
                                  JOIN produk p ON dp.id_produk = p.id_produk 
                                  WHERE dp.id_pesanan = ?");
$stmt_detail->bind_param("i", $id_pesanan);
$stmt_detail->execute();
$result_detail = $stmt_detail->get_result();

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sukses Pemesanan - Nyendok Nyruput</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Reset margin dan padding */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Styling untuk body */
        body {
            font-family: Arial, sans-serif;
            background-color: #fafafa;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Navbar */
        .navbar {
            background-color: #f8bbd0;
            /* Pink lembut */
            padding: 15px 20px;
            color: white;
            text-align: center;
            z-index: 100;
            /* Menjaga navbar tetap di atas */
            position: relative;
        }

        /* Page header */
        .page-header {
            background-color: #f8bbd0;
            /* Warna pink lembut */
            color: white;
            text-align: center;
            padding: 30px 0;
            /* Memberikan ruang atas */
            margin-top: 0px;
            /* Memberikan ruang agar tidak tertutup oleh navbar */
        }

        /* Kontainer utama */
        .order-container {
            width: 80%;
            max-width: 900px;
            margin: 40px auto;
            /* Memberikan margin atas untuk memberi ruang */
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            font-size: 16px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #ff4081;
            /* Warna pink pada judul */
            margin-top: 40px;
            /* Memberikan ruang di atas judul */
        }

        /* Tabel pesanan */
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .order-table th,
        .order-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .order-table th {
            background-color: #fce4ec;
            /* Warna pink muda untuk header tabel */
        }

        /* Tombol */
        .btn {
            display: inline-block;
            padding: 12px 25px;
            margin-top: 20px;
            text-align: center;
            background-color: #ff4081;
            /* Warna tombol pink */
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
        }

        .btn:hover {
            background-color: #c2185b;
            /* Efek hover dengan warna pink lebih gelap */
        }

        /* Footer */
        footer {
            background-color: #f8bbd0;
            /* Warna pink lembut */
            color: white;
            text-align: center;
            padding: 15px;
            margin-top: 40px;
        }

        /* Styling responsif */
        @media (max-width: 768px) {
            .order-container {
                width: 95%;
                padding: 15px;
            }

            .navbar a {
                margin: 0 5px;
            }
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
            <a href="keranjang.php">Keranjang</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="page-header">
        <h1>Pesanan Berhasil</h1>
    </div>

    <div class="order-container">
        <h2>Rincian Pesanan</h2>

        <p>Terima kasih telah memesan di Nyendok Nyruput! Berikut adalah rincian pesanan Anda:</p>

        <p><strong>Nama Pemesan:</strong> <?php echo htmlspecialchars($pesanan['nama_pemesan']); ?></p>
        <p><strong>Total Pembayaran:</strong> Rp <?php echo number_format($pesanan['total_pembayaran'], 0, ',', '.'); ?></p>
        <p><strong>Alamat Pengiriman:</strong> <?php echo htmlspecialchars($pesanan['alamat_pengiriman']); ?></p>
        <p><strong>Nomor Telepon:</strong> <?php echo htmlspecialchars($pesanan['nomor_telepon']); ?></p>
        <p><strong>Metode Pembayaran:</strong> <?php echo htmlspecialchars($pesanan['metode_pembayaran']); ?></p>
        <p><strong>Status Pembayaran:</strong> <?php echo htmlspecialchars($pesanan['status_pembayaran']); ?></p>
        <p><strong>Status Pesanan:</strong> <?php echo htmlspecialchars($pesanan['status_pesanan']); ?></p>

        <h3>Detail Produk</h3>
        <table class="order-table">
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Kuantitas</th>
                    <th>Total Harga</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Cek apakah detail pesanan ada
                if ($result_detail->num_rows > 0) {
                    while ($detail = $result_detail->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($detail['nama_produk']) . "</td>";
                        echo "<td>" . $detail['quantity'] . "</td>";
                        echo "<td>Rp " . number_format($detail['harga_total'], 0, ',', '.') . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>Tidak ada detail produk untuk pesanan ini.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <a href="index.php" class="btn">Kembali ke Home</a>
    </div>

    <footer>
        <p>&copy; 2024 Nyendok Nyruput. Semua hak cipta dilindungi.</p>
    </footer>

</body>

</html>

<?php
// Menutup koneksi
$conn->close();
?>