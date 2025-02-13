<?php
session_start();
include 'db_connect.php';

$user_id = $_SESSION['id_pengguna'];

// Ambil role pengguna dari tabel pengguna
$sql_user = "SELECT role FROM pengguna WHERE id_pengguna = ?";
$stmt_user = $koneksi->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_role = $result_user->fetch_assoc()['role'];
$stmt_user->close();

// Ambil riwayat chat
$sql = "SELECT f.*, p.role 
        FROM feedback f 
        JOIN pengguna p ON f.id_pengguna = p.id_pengguna 
        WHERE f.id_pengguna = ? OR f.parent_id IN (SELECT id_ulasan FROM feedback WHERE id_pengguna = ?) 
        ORDER BY f.tanggal_dibuat ASC";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $is_reply = $row['parent_id'] !== null;
    $role = $row['role'];
    echo "<div class='chat-box " . ($is_reply ? 'reply' : '') . "'>";
    echo "<div class='message'><strong>" . ($is_reply ? ($role === 'admin' ? 'Admin' : 'Anda') : 'Anda') . ":</strong> " . $row['pesan'] . "</div>";
    echo "<small>" . $row['tanggal_dibuat'] . "</small>";
    echo "</div>";
}

$stmt->close();
$koneksi->close();
