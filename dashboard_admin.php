<?php
// Koneksi ke database
require_once 'db_connect.php';

// Menangani routing berdasarkan parameter URL 'page'
$allowed_pages = ['admin', 'produk', 'pesanan', 'feedback'];
$page = isset($_GET['page']) && in_array($_GET['page'], $allowed_pages) ? $_GET['page'] : 'pesanan'; // Default adalah 'pesanan'

// Query untuk menampilkan data pesanan yang baru
$query_pesanan = "SELECT * FROM pesanan ORDER BY tanggal_pesanan DESC";
$result_pesanan = mysqli_query($koneksi, $query_pesanan);
if (!$result_pesanan) {
    die('Query error: ' . mysqli_error($koneksi));
}
$pesanan_data = mysqli_fetch_all($result_pesanan, MYSQLI_ASSOC);

// Query untuk menampilkan data admin
$query_admin = "SELECT * FROM pengguna WHERE role = 'admin'";
$result_admin = mysqli_query($koneksi, $query_admin);
if (!$result_admin) {
    die('Query error: ' . mysqli_error($koneksi));
}
$admin_data = mysqli_fetch_all($result_admin, MYSQLI_ASSOC);

// Query untuk menampilkan data produk
$query_produk = "SELECT * FROM produk";
$result_produk = mysqli_query($koneksi, $query_produk);
if (!$result_produk) {
    die('Query error: ' . mysqli_error($koneksi));
}
$produk_data = mysqli_fetch_all($result_produk, MYSQLI_ASSOC);

// Query untuk mengambil data feedback dengan JOIN ke tabel pengguna
$query_feedback = "
    SELECT f.*, p.role 
    FROM feedback f
    JOIN pengguna p ON f.id_pengguna = p.id_pengguna
    ORDER BY f.tanggal_dibuat DESC
";
$result_feedback = mysqli_query($koneksi, $query_feedback);
if (!$result_feedback) {
    die('Query error: ' . mysqli_error($koneksi));
}
$feedback_data = mysqli_fetch_all($result_feedback, MYSQLI_ASSOC);

// Kelompokkan feedback berdasarkan thread (parent_id atau id_ulasan)
$grouped_feedback = [];
foreach ($feedback_data as $feedback) {
    $thread_id = $feedback['parent_id'] ? $feedback['parent_id'] : $feedback['id_ulasan'];
    $grouped_feedback[$thread_id][] = $feedback;
}

// Fungsi untuk menangani logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

// Fungsi tambah produk
if (isset($_POST['tambah_produk'])) {
    $nama_produk = htmlspecialchars($_POST['nama_produk']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    $harga = floatval($_POST['harga']);
    $kategori = htmlspecialchars($_POST['kategori']);
    $url_gambar = '';

    // Validasi file upload
    if ($_FILES['url_gambar']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if (in_array($_FILES['url_gambar']['type'], $allowed_types) && $_FILES['url_gambar']['size'] <= $max_size) {
            $url_gambar = 'uploads/' . basename($_FILES['url_gambar']['name']);
            move_uploaded_file($_FILES['url_gambar']['tmp_name'], $url_gambar);
        } else {
            echo "<script>alert('File harus berupa gambar (JPEG, PNG, GIF) dan maksimal 2MB.');</script>";
            exit;
        }
    }

    // Query tambah produk
    $query = "INSERT INTO produk (nama_produk, deskripsi, harga, kategori, url_gambar) VALUES (?, ?, ?, ?, ?)";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("ssdss", $nama_produk, $deskripsi, $harga, $kategori, $url_gambar);

    if ($stmt->execute()) {
        header("Location: dashboard_admin.php?page=produk");
    } else {
        echo "<script>alert('Gagal menambahkan produk.');</script>";
    }
    $stmt->close();
}

// Fungsi hapus produk
if (isset($_GET['delete'])) {
    $id_produk = intval($_GET['delete']);

    $query = "DELETE FROM produk WHERE id_produk = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $id_produk);

    if ($stmt->execute()) {
        echo "<script>alert('Produk berhasil dihapus.'); window.location='dashboard_admin.php?page=produk';</script>";
    } else {
        echo "<script>alert('Gagal menghapus produk.');</script>";
    }
    $stmt->close();
}

// Mengecek apakah user memilih untuk mengexport laporan
if (isset($_GET['export'])) {
    $export_type = $_GET['export'];
    $date_format = '';

    if ($export_type == 'harian') {
        $date_format = 'Y-m-d';
        $date_filter = date('Y-m-d');
    } elseif ($export_type == 'bulanan') {
        $date_format = 'Y-m';
        $date_filter = date('Y-m');
    }

    $query_export = "SELECT * FROM pesanan WHERE DATE(tanggal_pesanan) LIKE ? ORDER BY tanggal_pesanan DESC";
    $stmt = $koneksi->prepare($query_export);
    $stmt->bind_param("s", $date_filter);
    $stmt->execute();
    $result_export = $stmt->get_result();

    if (mysqli_num_rows($result_export) > 0) {
        $filename = ($export_type == 'harian') ? "pesanan_harian_" . date('Ymd') . ".csv" : "pesanan_bulanan_" . date('Ym') . ".csv";

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID Pesanan', 'Nama Pemesan', 'Total Pembayaran', 'Metode Pembayaran', 'Status Pembayaran', 'Status Pesanan', 'Alamat Pengiriman', 'Tanggal Pesanan']);

        while ($pesanan = mysqli_fetch_assoc($result_export)) {
            fputcsv($output, [
                $pesanan['id_pesanan'],
                $pesanan['nama_pemesan'],
                number_format($pesanan['total_pembayaran'], 0, ',', '.'),
                $pesanan['metode_pembayaran'],
                $pesanan['status_pembayaran'],
                $pesanan['status_pesanan'],
                $pesanan['alamat_pengiriman'],
                $pesanan['tanggal_pesanan']
            ]);
        }

        fclose($output);
        exit();
    } else {
        echo "<script>alert('Tidak ada data pesanan untuk export.'); window.location.href = 'dashboard_admin.php?page=pesanan';</script>";
    }
    $stmt->close();
}

mysqli_close($koneksi);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .container {
            margin-top: 20px;
        }

        h2 {
            color: #007bff;
            margin-bottom: 20px;
        }

        table {
            margin-bottom: 40px;
        }

        .table thead {
            background-color: #007bff;
            color: white;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .card {
            margin-bottom: 30px;
        }

        .card-header {
            background-color: #007bff;
            color: white;
        }

        .navbar {
            margin-bottom: 20px;
        }

        .navbar a {
            color: white !important;
        }

        .collapse {
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <!-- Navbar untuk navigasi -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <a class="navbar-brand" href="#">Dashboard Admin</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="?page=admin">Manajemen Admin</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?page=produk">Manajemen Produk</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?page=pesanan">Pesanan Masuk</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?page=feedback">Feedback</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?logout=true">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <?php if ($page == 'admin') : ?>
            <!-- Halaman Manajemen Admin -->
            <h2>Daftar Admin</h2>
            <a href="tambah_admin.php" class="btn btn-primary">Tambah Admin</a>
            <?php if (empty($admin_data)) : ?>
                <p>Tidak ada admin.</p>
            <?php else : ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID Pengguna</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Tanggal Daftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admin_data as $admin) : ?>
                            <tr>
                                <td><?= htmlspecialchars($admin['id_pengguna']) ?></td>
                                <td><?= htmlspecialchars($admin['username']) ?></td>
                                <td><?= htmlspecialchars($admin['email']) ?></td>
                                <td><?= htmlspecialchars($admin['tanggal_daftar']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        <?php elseif ($page == 'produk') : ?>
            <!-- Halaman Manajemen Produk -->
            <h2>Daftar Produk</h2>
            <a href="tambah_produk.php" class="btn btn-primary">Tambah Produk</a>
            <?php if (empty($produk_data)) : ?>
                <p>Tidak ada produk.</p>
            <?php else : ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID Produk</th>
                            <th>Nama Produk</th>
                            <th>Harga</th>
                            <th>Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produk_data as $produk) : ?>
                            <tr>
                                <td><?= htmlspecialchars($produk['id_produk']) ?></td>
                                <td><?= htmlspecialchars($produk['nama_produk']) ?></td>
                                <td><?= number_format($produk['harga'], 2, ',', '.') ?></td>
                                <td><?= htmlspecialchars($produk['kategori']) ?></td>
                                <td>
                                    <a href="?page=produk&delete=<?= $produk['id_produk'] ?>" class="btn btn-danger">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        <?php elseif ($page == 'pesanan') : ?>
            <!-- Halaman Pesanan Masuk -->
            <h2>Pesanan yang Masuk</h2>
            <a href="?page=pesanan&export=harian" class="btn btn-success">Export Harian</a>
            <a href="?page=pesanan&export=bulanan" class="btn btn-success">Export Bulanan</a>
            <?php if (empty($pesanan_data)) : ?>
                <p>Tidak ada pesanan yang masuk.</p>
            <?php else : ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Nama Pemesan</th>
                            <th>Total Pembayaran</th>
                            <th>Metode Pembayaran</th>
                            <th>Status Pembayaran</th>
                            <th>Status Pesanan</th>
                            <th>Alamat Pengiriman</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pesanan_data as $pesanan) : ?>
                            <tr>
                                <td><?= htmlspecialchars($pesanan['id_pesanan']) ?></td>
                                <td><?= htmlspecialchars($pesanan['nama_pemesan']) ?></td>
                                <td>Rp <?= number_format($pesanan['total_pembayaran'], 0, ',', '.') ?></td>
                                <td><?= htmlspecialchars($pesanan['metode_pembayaran']) ?></td>
                                <td><?= htmlspecialchars($pesanan['status_pembayaran']) ?></td>
                                <td><?= htmlspecialchars($pesanan['status_pesanan']) ?></td>
                                <td><?= htmlspecialchars($pesanan['alamat_pengiriman']) ?></td>
                                <td><a href="detail_pesanan.php?id_pesanan=<?= $pesanan['id_pesanan'] ?>" class="btn btn-info">Detail</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        <?php elseif ($page == 'feedback') : ?>
            <!-- Halaman Feedback -->
            <h2>Feedback Pengguna</h2>
            <?php if (empty($grouped_feedback)) : ?>
                <p>Tidak ada feedback yang diterima.</p>
            <?php else : ?>
                <?php foreach ($grouped_feedback as $thread_id => $feedbacks) : ?>
                    <div class="card">
                        <div class="card-header">
                            Thread Feedback ID: <?= htmlspecialchars($thread_id) ?>
                            <button class="btn btn-primary float-right" type="button" data-toggle="collapse" data-target="#chat-<?= $thread_id ?>">
                                Buka Chat
                            </button>
                        </div>
                        <div id="chat-<?= $thread_id ?>" class="collapse">
                            <div class="card-body">
                                <?php foreach ($feedbacks as $feedback) : ?>
                                    <div class="chat-box <?= $feedback['parent_id'] ? 'reply' : '' ?>">
                                        <p><strong><?= $feedback['role'] == 'admin' ? 'Admin' : 'Pengguna' ?>:</strong> <?= htmlspecialchars($feedback['pesan']) ?></p>
                                        <p><small><?= htmlspecialchars($feedback['tanggal_dibuat']) ?></small></p>
                                    </div>
                                <?php endforeach; ?>

                                <!-- Form balas pesan -->
                                <form class="reply-form" data-thread-id="<?= $thread_id ?>">
                                    <input type="hidden" name="parent_id" value="<?= $feedbacks[0]['id_ulasan'] ?>">
                                    <input type="hidden" name="subject" value="Balasan: <?= htmlspecialchars($feedbacks[0]['subjek']) ?>">
                                    <div class="form-group">
                                        <label for="message">Balas Pesan:</label>
                                        <textarea name="message" rows="3" class="form-control" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Kirim Balasan</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.reply-form').off('submit').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const userId = form.data('user-id');
                const formData = form.serialize();

                $.ajax({
                    url: 'process_contact.php',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        const res = JSON.parse(response);
                        if (res.success) {
                            alert('Balasan berhasil dikirim.');
                            location.reload();
                        } else {
                            alert('Gagal mengirim balasan: ' + res.message);
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan saat mengirim balasan.');
                    }
                });
            });
        });
    </script>
</body>

</html>