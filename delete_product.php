<?php
    // Delete product functionality
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id_produk = $_POST['id_produk'];

        // Database connection and deletion logic
        include 'db_connect.php';
        $sql = "DELETE FROM produk WHERE id_produk = $id_produk";
        if (mysqli_query($conn, $sql)) {
            echo "Produk berhasil dihapus.";
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
        mysqli_close($conn);
    }
    ?>
    <form method="POST">
        <label for="id_produk">ID Produk:</label>
        <input type="number" id="id_produk" name="id_produk" required><br><br>
        <input type="submit" value="Hapus Produk">
    </form>