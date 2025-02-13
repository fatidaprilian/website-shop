<?php
session_start();
require_once('db_connect.php');

// Pastikan admin sudah login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.html');
    exit();
}

// Proses untuk menambah admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_admin'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $email = $_POST['email'];
    $role = 'admin'; // Pastikan role-nya adalah admin

    // Query untuk menambah admin
    $query = "INSERT INTO pengguna (username, password, email, role, tanggal_daftar) 
              VALUES ('$username', '$password', '$email', '$role', NOW())";

    if (mysqli_query($conn, $query)) {
        $message = "Admin berhasil ditambahkan!";
    } else {
        $message = "Terjadi kesalahan: " . mysqli_error($conn);
    }
}

// Proses untuk menghapus admin
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Pastikan admin tidak menghapus dirinya sendiri
    if ($delete_id != $_SESSION['id_pengguna']) {
        $query = "DELETE FROM pengguna WHERE id_pengguna = $delete_id";

        if (mysqli_query($conn, $query)) {
            $message = "Admin berhasil dihapus!";
        } else {
            $message = "Terjadi kesalahan: " . mysqli_error($conn);
        }
    } else {
        $message = "Anda tidak bisa menghapus diri sendiri!";
    }
}

// Ambil semua pengguna untuk ditampilkan
$users_query = "SELECT * FROM pengguna";
$users_result = mysqli_query($conn, $users_query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admin - Sweet Delight</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <nav class="navbar">
        <div class="nav-brand">Sweet Delight - Admin</div>
        <div class="nav-links">
            <a href="dashboard_admin.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <h2>Manage Admin</h2>

        <?php if (isset($message)): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>

        <!-- Form untuk menambah admin -->
        <div class="add-admin">
            <h3>Tambah Admin</h3>
            <form action="manage_admin.php" method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="email" name="email" placeholder="Email" required>
                <button type="submit" name="add_admin">Tambah Admin</button>
            </form>
        </div>

        <!-- List pengguna/admin -->
        <div class="user-list">
            <h3>Manajemen Pengguna</h3>
            <table>
                <tr>
                    <th>ID Pengguna</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
                <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                    <tr>
                        <td><?= $user['id_pengguna'] ?></td>
                        <td><?= $user['username'] ?></td>
                        <td><?= $user['email'] ?></td>
                        <td><?= $user['role'] ?></td>
                        <td>
                            <?php if ($user['role'] != 'admin'): ?>
                                <a href="manage_admin.php?delete_id=<?= $user['id_pengguna'] ?>">Hapus</a>
                            <?php else: ?>
                                <span>Admin Tidak Bisa Dihapus</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- Tombol Cetak Laporan -->
        <button onclick="window.print()">Cetak Laporan</button>
    </div>

</body>

</html>