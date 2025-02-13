<?php
session_start(); // Memulai sesi untuk menyimpan data sesi

require_once('db_connect.php'); // Koneksi ke database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Menangani input dari form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validasi input untuk memastikan username dan password tidak kosong
    if (empty($username) || empty($password)) {
        echo "<script>alert('Username atau password tidak boleh kosong!'); window.location.href = 'login.html';</script>";
        exit();
    }

    // Query menggunakan prepared statements untuk menghindari SQL Injection
    $query = "SELECT * FROM pengguna WHERE username = ?";
    if ($stmt = mysqli_prepare($koneksi, $query)) {
        mysqli_stmt_bind_param($stmt, 's', $username); // Bind parameter username
        mysqli_stmt_execute($stmt); // Eksekusi query
        $result = mysqli_stmt_get_result($stmt); // Ambil hasil query

        // Cek apakah pengguna ditemukan
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Set session data untuk pengguna
                $_SESSION['id_pengguna'] = $user['id_pengguna'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role']; // Menyimpan role pengguna di session

                // Menyimpan jumlah barang di keranjang ke sesi setelah login berhasil
                if ($user['role'] != 'admin') { // Hanya untuk pengguna non-admin
                    $cart_query = $koneksi->prepare("SELECT SUM(id_produk) AS total_items FROM keranjang_belanja WHERE id_pengguna = ?");
                    $cart_query->bind_param("i", $user['id_pengguna']);
                    $cart_query->execute();
                    $cart_result = $cart_query->get_result();
                    $cart_row = $cart_result->fetch_assoc();

                    // Simpan jumlah item di keranjang ke sesi
                    $_SESSION['cart_count'] = $cart_row['total_items'] ?? 0; // Default 0 jika tidak ada barang
                    $cart_query->close();
                }

                // Menampilkan popup sukses dan redirect ke halaman yang sesuai berdasarkan role
                echo "<script>alert('Login berhasil!');";
                if ($user['role'] == 'admin') {
                    echo "window.location.href = 'dashboard_admin.php';"; // Redirect ke dashboard admin
                } else {
                    echo "window.location.href = 'index.php';"; // Redirect ke homepage untuk customer
                }
                echo "</script>";
                exit(); // Pastikan script berhenti setelah redirect
            } else {
                // Menampilkan popup error jika password salah
                echo "<script>alert('Password salah!'); window.location.href = 'login.html';</script>";
            }
        } else {
            // Menampilkan popup error jika username tidak ditemukan
            echo "<script>alert('Username tidak ditemukan!'); window.location.href = 'login.html';</script>";
        }
        mysqli_stmt_close($stmt); // Tutup prepared statement
    } else {
        // Menampilkan popup error jika koneksi database gagal
        echo "<script>alert('Terjadi kesalahan dalam koneksi database.'); window.location.href = 'login.html';</script>";
    }
} else {
    // Menampilkan popup error jika metode request tidak valid
    echo "<script>alert('Metode request tidak valid.'); window.location.href = 'login.html';</script>";
}
