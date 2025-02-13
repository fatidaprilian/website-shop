<?php
session_start();

// Cek apakah pengguna sudah login dan memiliki role 'customer'
if (!isset($_SESSION['id_pengguna']) || $_SESSION['role'] !== 'customer') {
    // Redirect ke halaman login jika belum login atau bukan customer
    header('Location: login.html');
    exit;
}

// Koneksi ke database
include('db_connect.php');

// Ambil id_pengguna dari session
$id_pengguna = $_SESSION['id_pengguna'];

// Proses update status pesanan menjadi "Selesai"
if (isset($_POST['update_status']) && isset($_POST['id_pesanan'])) {
    $id_pesanan = $_POST['id_pesanan'];

    // Update status pesanan menjadi 'Selesai'
    $query_update = "UPDATE Pesanan SET status_pesanan = 'Selesai' WHERE id_pesanan = $id_pesanan";
    $result_update = mysqli_query($koneksi, $query_update);

    if (!$result_update) {
        echo "Error: " . mysqli_error($koneksi);
    }
}

// Query untuk mengambil data pesanan yang dimiliki oleh pengguna
$query_pesanan = "
    SELECT p.id_pesanan, p.tanggal_pesanan, p.status_pesanan, p.total_pembayaran, 
           GROUP_CONCAT(pr.nama_produk SEPARATOR ', ') AS produk, 
           GROUP_CONCAT(dp.quantity SEPARATOR ', ') AS jumlah, 
           SUM(dp.harga_total) AS total_harga
    FROM Pesanan p
    JOIN Detail_pesanan dp ON p.id_pesanan = dp.id_pesanan
    JOIN Produk pr ON dp.id_produk = pr.id_produk
    WHERE p.id_pengguna = $id_pengguna
    GROUP BY p.id_pesanan
    ORDER BY p.tanggal_pesanan DESC
";

$result_pesanan = mysqli_query($koneksi, $query_pesanan);

// Cek apakah ada pesanan
if (mysqli_num_rows($result_pesanan) == 0) {
    $pesanan_empty = true;
} else {
    $pesanan_empty = false;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya</title>
    <link rel="stylesheet" href="style.css">
    <!-- Menambahkan Bootstrap untuk styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Menyesuaikan warna tombol dengan tema */
        .btn-success-custom {
            background-color: #28a745;
            border-color: #28a745;
            padding: 6px 12px;
            font-size: 14px;
        }

        .btn-success-custom:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        /* Menyesuaikan jarak tabel dan elemen-elemen lainnya */
        table {
            margin-top: 20px;
            font-size: 14px;
        }

        th,
        td {
            padding: 10px;
            text-align: center;
        }

        /* Header */
        .page-header {
            background-color: #f1f1f1;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }

        .page-header h1 {
            font-size: 2rem;
            color: #333;
        }

        .page-header p {
            color: #777;
            font-size: 1rem;
        }

        /* Mengatur padding footer */
        footer {
            padding: 20px 0;
            background-color: #f8f9fa;
        }

        .hero-content h1 {
            font-size: 2rem;
            margin-bottom: 20px;
        }

        /* Membuat tampilan lebih rapi pada navbar */
        .navbar {
            background-color: #f8f9fa;
        }

        /* Menambahkan sedikit margin pada tabel */
        .table {
            margin-top: 30px;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        /* Menyesuaikan layout kolom */
        .col-md-4 {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card {
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        /* Tambahan CSS untuk memisahkan produk dan jumlah */
        .produk-jumlah {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .produk-jumlah span {
            margin: 2px 0;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <div class="nav-brand">
                <a href="index.php" class="brand-logo">
                    <img src="https://i.ibb.co.com/7G0b1Pt/Cokelat-Hijau-Ilustrasi-Logo-Kedai-Kopi-1-removebg-preview.png" alt="Nyendok Nyruput Logo" border="0" class="nav-logo">
                </a>
            </div>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.html">Kontak</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="container">
        <div class="page-header">
            <h1>Daftar Pesanan Anda</h1>
            <p>Kelola pesanan Anda yang telah diproses dan selesaikan pesanan yang telah anda Terima.</p>
        </div>

        <?php if ($pesanan_empty): ?>
            <div class="alert alert-info">Anda belum memiliki pesanan.</div>
        <?php else: ?>
            <!-- Tabel Pesanan -->
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID Pesanan</th>
                        <th>Tanggal Pesanan</th>
                        <th>Status Pesanan</th>
                        <th>Total Pembayaran</th>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Harga Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($pesanan = mysqli_fetch_assoc($result_pesanan)): ?>
                        <?php
                        // Memisahkan produk dan jumlah
                        $produk = explode(', ', $pesanan['produk']);
                        $jumlah = explode(', ', $pesanan['jumlah']);
                        ?>
                        <tr>
                            <td><?php echo $pesanan['id_pesanan']; ?></td>
                            <td><?php echo date("d-m-Y H:i:s", strtotime($pesanan['tanggal_pesanan'])); ?></td>
                            <td><?php echo $pesanan['status_pesanan']; ?></td>
                            <td><?php echo "Rp. " . number_format($pesanan['total_pembayaran'], 0, ',', '.'); ?></td>
                            <td>
                                <div class="produk-jumlah">
                                    <?php foreach ($produk as $p): ?>
                                        <span><?php echo $p; ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td>
                                <div class="produk-jumlah">
                                    <?php foreach ($jumlah as $j): ?>
                                        <span><?php echo $j; ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td><?php echo "Rp. " . number_format($pesanan['total_harga'], 0, ',', '.'); ?></td>
                            <td>
                                <?php if ($pesanan['status_pesanan'] == 'Dikirim'): ?>
                                    <form action="pesanan.php" method="POST">
                                        <input type="hidden" name="id_pesanan" value="<?php echo $pesanan['id_pesanan']; ?>">
                                        <button type="submit" name="update_status" class="btn btn-success btn-success-custom">Selesai</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</body>

</html>