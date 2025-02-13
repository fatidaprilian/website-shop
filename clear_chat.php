<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['id_pengguna'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Anda harus login terlebih dahulu.'
    ]);
    exit;
}

$user_id = $_SESSION['id_pengguna'];

// Ambil semua id_ulasan yang terkait dengan pengguna (termasuk parent_id)
$sql_select = "SELECT id_ulasan FROM feedback WHERE id_pengguna = ?";
$stmt_select = $koneksi->prepare($sql_select);
$stmt_select->bind_param("i", $user_id);
$stmt_select->execute();
$result_select = $stmt_select->get_result();

$feedback_ids = [];
while ($row = $result_select->fetch_assoc()) {
    $feedback_ids[] = $row['id_ulasan'];
}
$stmt_select->close();

if (empty($feedback_ids)) {
    echo json_encode([
        'success' => true,
        'message' => 'Tidak ada chat yang perlu dihapus.'
    ]);
    exit;
}

// Hapus semua feedback yang terkait dengan id_ulasan atau parent_id yang ditemukan
$sql_delete = "DELETE FROM feedback WHERE id_pengguna = ? OR parent_id IN (" . implode(',', $feedback_ids) . ")";
$stmt_delete = $koneksi->prepare($sql_delete);
$stmt_delete->bind_param("i", $user_id);

if ($stmt_delete->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Chat sebelumnya telah dihapus.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menghapus chat.'
    ]);
}

$stmt_delete->close();
$koneksi->close();
