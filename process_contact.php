<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['id_pengguna'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Anda harus login terlebih dahulu untuk mengirim pesan.'
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['id_pengguna'];
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    $parent_id = isset($_POST['parent_id']) ? $_POST['parent_id'] : null;

    if (empty($message)) {
        echo json_encode([
            'success' => false,
            'message' => 'Pesan harus diisi.'
        ]);
        exit;
    }

    $sql = "INSERT INTO feedback (id_pengguna, subjek, pesan, parent_id) VALUES (?, ?, ?, ?)";
    try {
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param("issi", $user_id, $subject, $message, $parent_id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Pesan Anda telah terkirim. Terima kasih!'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal mengirim pesan. Silakan coba lagi.'
            ]);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }

    $koneksi->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Metode request tidak valid.'
    ]);
}
