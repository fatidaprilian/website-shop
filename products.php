<?php
// products.php

// Mulai session
session_start();

// Panggil file db_connect.php untuk menghubungkan ke database
require 'db_connect.php';  // Pastikan path-nya benar

// Query untuk mengambil produk
$sql = "SELECT id_produk, nama_produk, deskripsi, harga, url_gambar, kategori FROM produk";

// Jalankan query
$result = mysqli_query($koneksi, $sql);

// Cek apakah query berhasil
if (!$result) {
    die("Query gagal: " . mysqli_error($koneksi));  // Tampilkan error jika query gagal
}
// Tutup koneksi (opsional, karena koneksi akan otomatis tertutup saat script selesai)
mysqli_close($koneksi);
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nyendok Nyruput</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            <a href="products.php" class="active">Produk</a>
            <a href="keranjang.php">Keranjang (<?php echo count($_SESSION['keranjang'] ?? []); ?>)</a>
        </div>
    </nav>

    <div class="page-header">
        <h1>Menu Kami</h1>
        <p>Pilihan makanan dan minuman premium untuk berbagai momen istimewa</p>
    </div>

    <div class="container">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
        ?>
                <div class="card">
                    <div class="card-inner">
                        <div class="card-front">
                            <img src="<?php echo $row['url_gambar']; ?>" alt="<?php echo $row['nama_produk']; ?>">
                            <h3><?php echo $row['nama_produk']; ?></h3>
                        </div>
                        <div class="card-back">
                            <h3><?php echo $row['nama_produk']; ?></h3>
                            <p><?php echo $row['deskripsi']; ?></p>
                            <div class="price">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></div>

                            <!-- Tombol Pesan Sekarang -->
                            <a href="javascript:void(0);" class="order-link pesan-sekarang"
                                data-id="<?php echo $row['id_produk']; ?>"
                                data-nama="<?php echo $row['nama_produk']; ?>"
                                data-harga="<?php echo $row['harga']; ?>"
                                data-gambar="<?php echo $row['url_gambar']; ?>">
                                Pesan Sekarang
                            </a>


                            <!-- Tombol Masukkan Keranjang -->
                            <form action="javascript:void(0);" class="add-to-cart" data-id="<?php echo $row['id_produk']; ?>" data-nama="<?php echo $row['nama_produk']; ?>" data-harga="<?php echo $row['harga']; ?>" data-gambar="<?php echo $row['url_gambar']; ?>">
                                <button type="submit" class="order-link">Masukkan Keranjang</button>
                            </form>
                        </div>
                    </div>
                </div>
        <?php
            }
        } else {
            echo "<p>Tidak ada produk yang tersedia.</p>";
        }

        ?>
    </div>

    <script>
        $(document).ready(function() {
            // Fungsi untuk menangani klik pada tombol "Pesan Sekarang"
            $(".pesan-sekarang").on('click', function(e) {
                e.preventDefault();

                var id_produk = $(this).data("id");
                var nama_produk = $(this).data("nama");
                var harga = $(this).data("harga");
                var gambar = $(this).data("gambar");

                // Redirect ke halaman order.php dengan data produk
                window.location.href = "order.php?product_id=" + id_produk + "&product_name=" + encodeURIComponent(nama_produk) + "&product_price=" + harga + "&product_image=" + encodeURIComponent(gambar);
            });

            // Fungsi tetap untuk menambahkan produk ke keranjang
            $(".add-to-cart").on('submit', function(e) {
                e.preventDefault();

                var id_produk = $(this).data("id");
                var nama_produk = $(this).data("nama");
                var harga = $(this).data("harga");
                var gambar = $(this).data("gambar");

                $.ajax({
                    url: "proses_keranjang.php",
                    type: "POST",
                    data: {
                        id_produk: id_produk,
                        nama_produk: nama_produk,
                        harga: harga,
                        gambar: gambar,
                        add_to_cart: true
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.keranjangCount !== undefined) {
                            $(".nav-links a").last().text("Keranjang (" + response.keranjangCount + ")");
                        }
                        alert(response.message || "Produk berhasil ditambahkan ke keranjang.");
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", error);
                        console.error("Response Text:", xhr.responseText);
                        alert("Terjadi kesalahan saat menambahkan produk ke keranjang. Silakan coba lagi.");
                    }
                });
            });
        });
    </script>


</body>

</html>