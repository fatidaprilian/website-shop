<?php
session_start();

// Koneksi ke database
require_once 'db_connect.php'; // Menggunakan file db_connect.php untuk koneksi database

// Fungsi untuk menangani form tambah produk
if (isset($_POST['tambah_produk'])) {
    $nama_produk = $_POST['nama_produk'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $kategori = $_POST['kategori'];
    $url_gambar = "";

    // Menentukan apakah gambar diupload sebagai file atau URL
    if (isset($_POST['url_gambar_input']) && !empty($_POST['url_gambar_input'])) {
        // Menggunakan URL yang diberikan
        $url_gambar = $_POST['url_gambar_input'];
    } elseif (isset($_FILES['url_gambar']) && $_FILES['url_gambar']['error'] == 0) {
        // Mengupload file gambar
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Buat direktori jika belum ada
        }
        $url_gambar = $upload_dir . basename($_FILES['url_gambar']['name']);
        if (move_uploaded_file($_FILES['url_gambar']['tmp_name'], $url_gambar)) {
            // File berhasil diupload
        } else {
            echo "<script>alert('Gagal mengupload gambar.');</script>";
            $url_gambar = "";
        }
    }

    if ($url_gambar) {
        $query = "INSERT INTO produk (nama_produk, deskripsi, harga, kategori, url_gambar) VALUES ('$nama_produk', '$deskripsi', '$harga', '$kategori', '$url_gambar')";
        if (mysqli_query($koneksi, $query)) {
            header("Location: dashboard_admin.php?page=produk");
            exit();
        } else {
            echo "<script>alert('Gagal menambahkan produk');</script>";
        }
    } else {
        echo "<script>alert('URL atau File Gambar tidak valid');</script>";
    }
}

mysqli_close($koneksi);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk</title>
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

        .form-control {
            margin-bottom: 15px;
        }

        .btn-primary {
            width: 100%;
        }

        .custom-file-label {
            overflow: hidden;
        }
    </style>
</head>

<body>

    <!-- Navbar untuk navigasi -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <a class="navbar-brand" href="#">Tambah Produk</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard_admin.php?page=produk">Kembali ke Manajemen Produk</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2>Tambah Produk Baru</h2>
        <form method="POST" action="tambah_produk.php" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nama_produk">Nama Produk:</label>
                <input type="text" class="form-control" id="nama_produk" name="nama_produk" required>
            </div>
            <div class="form-group">
                <label for="deskripsi">Deskripsi:</label>
                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label for="harga">Harga:</label>
                <input type="number" class="form-control" id="harga" name="harga" required>
            </div>
            <div class="form-group">
                <label for="kategori">Kategori:</label>
                <select class="form-control" id="kategori" name="kategori" required>
                    <option value="Kue">Kue</option>
                    <option value="Snack">Snack</option>
                    <option value="Minuman">Minuman</option>
                </select>
            </div>
            <div class="form-group">
                <label for="url_gambar">Pilih Gambar Produk:</label>
                <!-- Pilihan untuk mengupload file atau menggunakan URL -->
                <input type="radio" id="upload_file" name="image_type" value="file" checked>
                <label for="upload_file">Upload File</label>
                <input type="radio" id="url_gambar_radio" name="image_type" value="url">
                <label for="url_gambar_radio">URL Gambar</label>
                <br>

                <!-- Input untuk file gambar -->
                <div id="file_input">
                    <input type="file" class="form-control-file" id="url_gambar" name="url_gambar">
                </div>

                <!-- Input untuk URL gambar -->
                <div id="url_input" style="display:none;">
                    <input type="text" class="form-control" id="url_gambar_input" name="url_gambar_input" placeholder="Masukkan URL Gambar">
                </div>
            </div>

            <button type="submit" name="tambah_produk" class="btn btn-primary">Tambah Produk</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

    <script>
        // Menangani perubahan tampilan input gambar
        document.querySelectorAll('input[name="image_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'url') {
                    document.getElementById('file_input').style.display = 'none';
                    document.getElementById('url_input').style.display = 'block';
                    document.getElementById('url_gambar').required = false;
                    document.getElementById('url_gambar_input').required = true;
                } else {
                    document.getElementById('file_input').style.display = 'block';
                    document.getElementById('url_input').style.display = 'none';
                    document.getElementById('url_gambar').required = true;
                    document.getElementById('url_gambar_input').required = false;
                }
            });
        });
    </script>
</body>

</html>