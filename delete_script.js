document.querySelectorAll('.delete-btn').forEach(function(button) {
    button.addEventListener('click', function() {
        const productId = this.getAttribute('data-id');
        if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
            // Kirim permintaan GET ke delete_product.php
            fetch(`/delete_product.php?id_produk=${productId}`, {
                method: 'GET'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Produk berhasil dihapus');
                    // Hapus elemen produk dari halaman tanpa reload
                    this.closest('tr').remove();
                } else {
                    alert('Gagal menghapus produk');
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });
});