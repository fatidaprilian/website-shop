<?php
session_start();
$is_logged_in = isset($_SESSION['id_pengguna']); // Cek apakah sudah login
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toy Toy</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-brand">
            <a href="index.php" class="brand-logo">
                <img src="https://i.ibb.co.com/7G0b1Pt/Cokelat-Hijau-Ilustrasi-Logo-Kedai-Kopi-1-removebg-preview.png" alt="Nyendok Nyruput Logo" border="0" class="nav-logo">
            </a>
        </div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="products.php">Produk</a>
            <a href="contact.php">Kontak</a>

            <?php if ($is_logged_in): ?>
                <!-- Tampilkan shortcut pesanan setelah login -->
                <a href="pesanan.php">Pesanan Saya</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.html">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero">
        <div class="hero-content">
            <h1>Selamat Datang di Nyendok Nyruput</h1>
            <p>Segar disedot lembut dimulut</p> <br>
            <a href="#preview" class="cta-button">Jelajahi Menu</a>
        </div>
    </header>


    <!-- Features Section -->
    <section id="features" class="features">
        <div class="feature-card">
            <div class="feature-icon">🎂</div>
            <h3>Premium Quality</h3>
            <p>Bahan berkualitas tinggi untuk hasil terbaik</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🏆</div>
            <h3>Best Seller</h3>
            <p>Ribuan pelanggan puas dengan produk kami</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🚚</div>
            <h3>Free Delivery</h3>
            <p>Gratis pengiriman untuk area tertentu</p>
        </div>
    </section>

    <!-- Preview Section -->
    <section id="preview" class="preview">
        <h2>Best Seller Kami</h2>
        <div class="preview-grid">
            <div class="preview-item">
                <img src="https://i.ibb.co.com/0XjGgmd/06c18f39-7f39-456a-9c54-ebb1f4ec6d51.webp" alt="Korean Strawberry Milk">
                <h3>Korean Strawberry Milk</h3>
                <a href="products.php" class="preview-link">Lihat Detail</a>
            </div>
            <div class="preview-item">
                <img src="https://i.ibb.co.com/yshJ5dn/Whats-App-Image-2024-12-07-at-9-33-33-PM.webp" alt="Chocolate Mousse Cups">
                <h3>Chocolate Mousse Cups</h3>
                <a href="products.php" class="preview-link">Lihat Detail</a>
            </div>
            <div class="preview-item">
                <img src="https://i.ibb.co.com/jLZDY4P/Whats-App-Image-2024-12-07-at-9-32-42-PM.webp" alt="Strawberry Smoothie">
                <h3>Strawberry Smoothie</h3>
                <a href="products.php" class="preview-link">Lihat Detail</a>
            </div>
        </div>
        <br>
        <a href="contact.html" class="cta-button">Beri Kami Masukan</a>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; Nyendok Nyruput</p>
    </footer>
</body>

</html>