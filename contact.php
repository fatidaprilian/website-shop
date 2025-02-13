<?php
// Include file koneksi database
include 'db_connect.php';

session_start();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak Kami - Toy Toy</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        .chat-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .chat-box {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .chat-box .message {
            margin-bottom: 10px;
        }

        .chat-box .reply {
            margin-left: 20px;
            border-left: 2px solid #007bff;
            padding-left: 10px;
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="nav-brand">
            <a href="index.php" class="brand-logo">
                <img src="https://i.ibb.co.com/7G0b1Pt/Cokelat-Hijau-Ilustrasi-Logo-Kedai-Kopi-1-removebg-preview.png" alt="Nyendok Nyruput Logo" border="0" class="nav-logo">
            </a>
        </div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="products.php">Produk</a>
            <a href="contact.html" class="active">Kontak</a>
        </div>
    </nav>

    <section class="chat-container">
        <h2>Kontak Kami</h2>
        <div id="chat-history">
            <!-- Riwayat pesan akan dimuat di sini -->
        </div>
        <form id="chat-form" action="process_contact.php" method="POST">
            <input type="hidden" id="parent_id" name="parent_id">
            <?php
            // Tampilkan kolom subjek hanya jika ini adalah chat baru (tidak ada pesan sebelumnya)
            $user_id = $_SESSION['id_pengguna'] ?? null;
            if ($user_id) {
                $sql = "SELECT COUNT(*) as total FROM feedback WHERE id_pengguna = ?";
                $stmt = $koneksi->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                if ($row['total'] == 0) {
                    echo '<div class="form-group" id="subject-group">
                            <label for="subject">Subjek:</label>
                            <input type="text" id="subject" name="subject" class="form-control" required>
                          </div>';
                }
            }
            ?>
            <div class="form-group">
                <label for="message">Pesan:</label>
                <textarea id="message" name="message" rows="5" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Kirim Pesan</button>
        </form>
        <?php
        if (isset($_SESSION['id_pengguna'])) {
            echo '<button id="new-chat" class="btn btn-secondary">Mulai Chat Baru</button>';
        } else {
            echo '<a href="login.php" class="btn btn-secondary">Mulai Sesi Baru</a>';
        }
        ?>
    </section>

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script>
        $(document).ready(function() {
            function loadChatHistory() {
                $.ajax({
                    url: 'get_chat_history.php',
                    method: 'GET',
                    success: function(response) {
                        $('#chat-history').html(response);
                    }
                });
            }

            loadChatHistory();

            $('#chat-form').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'process_contact.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        var res = JSON.parse(response);
                        if (res.success) {
                            loadChatHistory();
                            $('#chat-form')[0].reset();
                            // Hapus kolom subjek setelah pesan pertama dikirim
                            $('#subject-group').remove();
                        } else {
                            alert(res.message);
                        }
                    }
                });
            });

            $('#new-chat').on('click', function() {
                $.ajax({
                    url: 'clear_chat.php',
                    method: 'POST',
                    success: function(response) {
                        var res = JSON.parse(response);
                        if (res.success) {
                            loadChatHistory();
                            $('#chat-form')[0].reset();
                            // Hapus kolom subjek jika sudah ada
                            $('#subject-group').remove();
                            // Tambahkan kolom subjek baru
                            $('#chat-form').prepend('<div class="form-group" id="subject-group"><label for="subject">Subjek:</label><input type="text" id="subject" name="subject" class="form-control" required></div>');
                            // Reset parent_id
                            $('#parent_id').val('');
                        } else {
                            alert(res.message);
                        }
                    }
                });
            });
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Fungsi untuk memuat riwayat chat
            function loadChatHistory() {
                $.ajax({
                    url: 'get_chat_history.php',
                    method: 'GET',
                    success: function(response) {
                        $('#chat-history').html(response);
                    }
                });
            }

            // Memuat riwayat chat setiap 5 detik
            setInterval(loadChatHistory, 5000);

            // Memuat riwayat chat saat halaman pertama kali dibuka
            loadChatHistory();
        });
    </script>
</body>

</html>