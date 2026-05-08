-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 13 Mar 2025 pada 16.27
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ewalet`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengguna`
--

CREATE TABLE `pengguna` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `nomorHP` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengguna`
--

INSERT INTO `pengguna` (`id`, `username`, `nomorHP`, `password`, `role_id`) VALUES
(1, 'administrator', '1', '$2a$12$sUY.ObwDx5WgEaZNYMp.8.tWLvdP1NEF7QcPCOB5jP2i//ABhHMZ6', 1),
(2, 'bankmini', '2', '$2a$12$RfiGAN.JTgWfsNjwm8zDieFz956AA.r0Y1CLbN1cAKLgas2raumsW', 2),
(19, 'Rifki', '08138589201', '$2y$10$MnIOCfyglktO/4LC/sOp/eh5oezBQKfVPOzTOxdP4FuPBWHPKtZme', 3),
(20, 'mudinn', '081384800915', '$2y$10$XUIowbvmo7YA.oEpctR3AeAzs9t5LtTGn8Gz8wM7n4uZNeAgSkk8u', 3);

-- --------------------------------------------------------

--
-- Struktur dari tabel `role`
--

CREATE TABLE `role` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `role`
--

INSERT INTO `role` (`id`, `name`) VALUES
(1, 'admin'),
(2, 'bankmini'),
(3, 'pengguna');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nomorHP` varchar(20) NOT NULL,
  `type` enum('topup','withdraw','transfer') NOT NULL,
  `jumlah` double NOT NULL,
  `reason` text NOT NULL,
  `status` enum('waiting_approval','approved','rejected') NOT NULL DEFAULT 'waiting_approval',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id`, `user_id`, `nomorHP`, `type`, `jumlah`, `reason`, `status`, `created_at`) VALUES
(76, 19, '08138589201', 'topup', 100000, '', 'approved', '2025-03-13 19:02:56'),
(77, 19, '08138589201', 'withdraw', 10000, '', 'approved', '2025-03-13 19:03:52'),
(78, 19, '08138589201', 'topup', 1000000000, '', 'approved', '2025-03-13 19:11:23'),
(79, 19, '08138589201', 'topup', 10000, 'erorr', 'rejected', '2025-03-13 19:41:10'),
(80, 19, '08138589201', 'withdraw', 100000, '', 'approved', '2025-03-13 21:32:02'),
(81, 19, '08138589201', 'withdraw', 20000, '', 'approved', '2025-03-13 21:32:09'),
(82, 19, '08138589201', 'withdraw', 10000, '', 'approved', '2025-03-13 21:34:40'),
(83, 19, '08138589201', 'withdraw', 10000, '', 'approved', '2025-03-13 21:34:48'),
(84, 19, '08138589201', 'withdraw', 10000, '', 'approved', '2025-03-13 21:37:32'),
(85, 19, '08138589201', 'withdraw', 10000, '', 'approved', '2025-03-13 21:38:19'),
(86, 19, '08138589201', 'withdraw', 10000, '', 'approved', '2025-03-13 21:44:37'),
(87, 19, '08138589201', 'withdraw', 10000, '', 'approved', '2025-03-13 21:45:37');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi_pengguna`
--

CREATE TABLE `transaksi_pengguna` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `type` enum('transfer','') NOT NULL,
  `nomorHP` varchar(80) NOT NULL,
  `jumlah` double NOT NULL,
  `status` enum('pending','sukses','gagal') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi_pengguna`
--

INSERT INTO `transaksi_pengguna` (`id`, `sender_id`, `receiver_id`, `type`, `nomorHP`, `jumlah`, `status`, `created_at`) VALUES
(35, 19, 18, 'transfer', '081384800915', 10000, 'sukses', '2025-03-13 12:04:20'),
(36, 19, 20, 'transfer', '081384800915', 10000, 'sukses', '2025-03-13 12:11:36'),
(37, 19, 20, 'transfer', '081384800915', 10000, 'sukses', '2025-03-13 12:43:20'),
(38, 19, 19, 'transfer', '08138589201', 10000, 'sukses', '2025-03-13 14:48:57'),
(39, 19, 19, 'transfer', '08138589201', 100000, 'sukses', '2025-03-13 14:49:15'),
(40, 19, 20, 'transfer', '081384800915', 10000, 'sukses', '2025-03-13 14:49:25'),
(41, 19, 20, 'transfer', '081384800915', 10000, 'sukses', '2025-03-13 14:51:22'),
(42, 19, 20, 'transfer', '081384800915', 10000, 'sukses', '2025-03-13 14:54:13'),
(43, 19, 20, 'transfer', '081384800915', 10000, 'sukses', '2025-03-13 14:55:46'),
(44, 19, 20, 'transfer', '081384800915', 10000, 'sukses', '2025-03-13 14:57:32');

-- --------------------------------------------------------

--
-- Struktur dari tabel `wallets`
--

CREATE TABLE `wallets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `credit` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `credit`) VALUES
(32, 19, 999830000),
(33, 20, 70000);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `users_ibfk_1` (`role_id`);

--
-- Indeks untuk tabel `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `transaksi_pengguna`
--
ALTER TABLE `transaksi_pengguna`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indeks untuk tabel `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `role`
--
ALTER TABLE `role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT untuk tabel `transaksi_pengguna`
--
ALTER TABLE `transaksi_pengguna`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT untuk tabel `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  ADD CONSTRAINT `pengguna_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transaksi_pengguna`
--
ALTER TABLE `transaksi_pengguna`
  ADD CONSTRAINT `transaksi_pengguna_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `pengguna` (`id`);

--
-- Ketidakleluasaan untuk tabel `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `wallets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
