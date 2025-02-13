<?php
// Include koneksi database
include('db_connect.php');  // Sesuaikan path jika perlu

// Ambil ID pesanan dari URL
$id_pesanan = $_GET['id_pesanan'];

// Cek apakah tombol untuk mengubah status ditekan
if (isset($_POST['update_status'])) {
    // Ambil status baru dari formulir
    $new_status = $_POST['new_status'];

    // Query untuk mengupdate status pesanan
    $update_query = "UPDATE Pesanan SET status_pesanan = '$new_status' WHERE id_pesanan = $id_pesanan";
    mysqli_query($koneksi, $update_query);

    // Refresh halaman setelah update
    header("Location: detail_pesanan.php?id_pesanan=$id_pesanan");
    exit;
}

// Query untuk mengambil data pesanan berdasarkan ID pesanan
$query = "SELECT p.id_pesanan, p.nama_pemesan, p.nomor_telepon, p.total_pembayaran, p.metode_pembayaran, p.status_pembayaran, p.status_pesanan, p.tanggal_pesanan, p.alamat_pengiriman, p.catatan_khusus, dp.id_produk, dp.quantity, dp.harga_total, pr.nama_produk 
          FROM Pesanan p
          JOIN Detail_pesanan dp ON p.id_pesanan = dp.id_pesanan
          JOIN Produk pr ON dp.id_produk = pr.id_produk
          WHERE p.id_pesanan = $id_pesanan";

// Eksekusi query
$result = mysqli_query($koneksi, $query);
$pesanan = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan</title>
    <!-- Bootstrap CSS untuk styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>Detail Pesanan</h2>

        <!-- Menampilkan informasi pesanan -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama Pemesan</th>
                    <th>Nomor Telepon</th>
                    <th>Total Pembayaran</th>
                    <th>Metode Pembayaran</th>
                    <th>Status Pembayaran</th>
                    <th>Status Pesanan</th>
                    <th>Tanggal Pesanan</th>
                    <th>Alamat Pengiriman</th>
                    <th>Catatan Khusus</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo $pesanan['nama_pemesan']; ?></td>
                    <td><?php echo $pesanan['nomor_telepon']; ?></td>
                    <td><?php echo "Rp. " . number_format($pesanan['total_pembayaran'], 0, ',', '.'); ?></td>
                    <td><?php echo $pesanan['metode_pembayaran']; ?></td>
                    <td><?php echo $pesanan['status_pembayaran']; ?></td>
                    <td><?php echo $pesanan['status_pesanan']; ?></td>
                    <td><?php echo date("d-m-Y H:i:s", strtotime($pesanan['tanggal_pesanan'])); ?></td>
                    <td><?php echo $pesanan['alamat_pengiriman']; ?></td>
                    <td><?php echo $pesanan['catatan_khusus']; ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Form untuk mengubah status pesanan -->
        <h3 class="mt-4">Ubah Status Pesanan</h3>
        <form action="detail_pesanan.php?id_pesanan=<?php echo $id_pesanan; ?>" method="POST">
            <div class="mb-3">
                <label for="new_status" class="form-label">Status Pesanan</label>
                <select name="new_status" id="new_status" class="form-select" required>
                    <option value="Diproses" <?php echo ($pesanan['status_pesanan'] == 'Diproses') ? 'selected' : ''; ?>>Diproses</option>
                    <option value="Dikirim" <?php echo ($pesanan['status_pesanan'] == 'Dikirim') ? 'selected' : ''; ?>>Dikirim</option>
                    <option value="Selesai" <?php echo ($pesanan['status_pesanan'] == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                    <option value="Dibatalkan" <?php echo ($pesanan['status_pesanan'] == 'Dibatalkan') ? 'selected' : ''; ?>>Dibatalkan</option>
                </select>
            </div>
            <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
        </form>

        <h3 class="mt-4">Detail Produk yang Dipesan</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Jumlah</th>
                    <th>Harga Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query untuk mengambil detail produk yang dipesan
                $query_detail = "SELECT dp.quantity, dp.harga_total, pr.nama_produk 
                                 FROM Detail_pesanan dp
                                 JOIN Produk pr ON dp.id_produk = pr.id_produk
                                 WHERE dp.id_pesanan = $id_pesanan";
                $result_detail = mysqli_query($koneksi, $query_detail);
                while ($detail = mysqli_fetch_assoc($result_detail)) {
                    echo "<tr>";
                    echo "<td>" . $detail['nama_produk'] . "</td>";
                    echo "<td>" . $detail['quantity'] . "</td>";
                    echo "<td>" . "Rp. " . number_format($detail['harga_total'], 0, ',', '.') . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        <a href="dashboard_admin.php" class="btn btn-primary">Kembali ke Home</a>
    </div>

    <!-- Bootstrap JS (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>