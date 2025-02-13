<?php
session_start();

// Memanggil file koneksi database
require 'db_connect.php';

// Fungsi untuk menangani form tambah admin
if (isset($_POST['tambah_admin'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Sesuaikan query dengan tabel dan kolom yang ada di database `jurnal`
    $query = "INSERT INTO pengguna (username, email, password, role, tanggal_daftar) VALUES ('$username', '$email', '$password', 'admin', NOW())";
    if (mysqli_query($koneksi, $query)) {
        header("Location: dashboard_admin.php?page=admin");
        exit();
    } else {
        echo "<script>alert('Gagal menambahkan admin');</script>";
    }
}

mysqli_close($koneksi);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Admin</title>
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
    </style>
</head>

<body>

    <!-- Navbar untuk navigasi -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <a class="navbar-brand" href="#">Tambah Admin</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard_admin.php?page=admin">Kembali ke Manajemen Admin</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2>Tambah Admin Baru</h2>
        <form method="POST" action="tambah_admin.php">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" name="tambah_admin" class="btn btn-primary">Tambah Admin</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>

</html>