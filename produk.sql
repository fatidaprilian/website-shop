-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql107.infinityfree.com
-- Generation Time: Jan 14, 2025 at 05:06 AM
-- Server version: 10.6.19-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_37879118_toko_kue`
--

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` decimal(10,2) NOT NULL,
  `url_gambar` varchar(255) DEFAULT NULL,
  `kategori` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `deskripsi`, `harga`, `url_gambar`, `kategori`) VALUES
(6, 'Mango Yakult', 'Manisnya buah mangga segar dipadu dengan kesegaran yogurt Yakult yang menyegarkan, menciptakan rasa tropis yang lezat dan menyehatkan. Nikmati kombinasi manis dan asam yang sempurna dalam setiap tegukan.', '10000.00', 'https://i.ibb.co.com/XxRxW3v/2692d655-111f-406d-af53-5492ff60aae1.webp', 'Minuman'),
(7, 'Chocolate Milk', 'Nikmati kesegaran Es Coklat kami, terbuat dari coklat pilihan, es serut dingin, dan susu segar. Rasa manis yang sempurna untuk menghilangkan dahaga atau sebagai teman santai di cuaca panas.', '10000.00', 'https://i.ibb.co.com/Wxpb5y3/d590cbf3-0ea1-4eb5-a93e-8d3e541d48d8.webp', 'Minuman'),
(9, 'Korean Strawberry Milk', 'Nikmati kelezatan Korean Strawberry Milk yang lembut dan manis. Perpaduan susu segar dengan stroberi alami menciptakan rasa creamy yang menyegarkan. Sempurna untuk Anda yang menyukai minuman manis dan segar, cocok dinikmati kapan saja.', '15000.00', 'https://i.ibb.co.com/0XjGgmd/06c18f39-7f39-456a-9c54-ebb1f4ec6d51.webp', 'Minuman'),
(10, 'Chocolate Mousse Cups', 'Cangkir-cangkir berisi mousse cokelat yang creamy dan kaya rasa. Topping krim kocok dan chip cokelat menambah sentuhan istimewa pada hidangan penutup yang terlihat lezat ini.', '20000.00', 'https://i.ibb.co.com/yshJ5dn/Whats-App-Image-2024-12-07-at-9-33-33-PM.webp', 'Kue'),
(11, 'Strawberry Pudding', 'Strawberry Pudding berwarna merah muda yang manis dan lembut. Irisan stroberi di permukaannya menambah daya tarik visual pada pudding ini', '20000.00', 'https://i.ibb.co.com/jLZDY4P/Whats-App-Image-2024-12-07-at-9-32-42-PM.webp', 'Kue'),
(12, 'Blueberry Pudding', 'Hidangan penutup berbahan dasar blueberry yang kental dan creamy. Warna ungu yang kaya dan tekstur yang lembut membuat dessert ini tampak lezat dan menggiurkan.', '20000.00', 'https://i.ibb.co.com/wYG7Cxr/Whats-App-Image-2024-12-07-at-9-32-42-PM-1.webp', 'Kue');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
