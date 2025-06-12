-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 12, 2025 at 05:15 PM
-- Server version: 10.6.21-MariaDB-cll-lve
-- PHP Version: 8.3.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `solucionesbtp_CarWash`
--

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_movements`
--

CREATE TABLE `inventory_movements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `movement_type` enum('entrada','salida') NOT NULL,
  `quantity` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2025_04_12_210017_create_vehicle_types_table', 2),
(6, '2025_04_12_210238_create_services_table', 2),
(7, '2025_04_12_210632_create_service_prices_table', 2),
(8, '2025_04_12_210858_create_products_table', 2),
(9, '2025_04_12_210959_create_washers_table', 2),
(10, '2025_04_12_211026_create_tickets_table', 2),
(11, '2025_04_12_211055_create_ticket_details_table', 2),
(12, '2025_04_12_211130_create_petty_cash_expenses_table', 2),
(13, '2025_04_12_211158_create_inventory_movements_table', 2),
(14, '2025_04_12_211225_create_washer_payments_table', 2),
(15, '2025_04_12_211536_add_role_to_users_table', 3);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_expenses`
--

CREATE TABLE `petty_cash_expenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `created_at`, `updated_at`) VALUES
(1, 'Brugal Añejo', NULL, 350.00, 99, '2025-04-13 01:41:23', '2025-04-14 08:07:12'),
(2, 'Ron Barceló', NULL, 360.00, 100, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(3, 'Whisky Johnnie Walker', NULL, 1200.00, 100, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(4, 'Whisky Chivas Regal', NULL, 1350.00, 100, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(5, 'Papas Lay’s', NULL, 50.00, 100, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(6, 'Doritos', NULL, 60.00, 97, '2025-04-13 01:41:23', '2025-04-14 08:07:12'),
(7, 'Agua Botella', NULL, 25.00, 98, '2025-04-13 01:41:23', '2025-04-14 08:14:02'),
(8, 'Cerveza Presidente', NULL, 100.00, 100, '2025-04-13 01:41:23', '2025-04-13 01:41:23');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Lavado Básico', 'Lavado Básico para todo tipo de vehículo.', '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(2, 'Lavado Premium', 'Lavado Premium para todo tipo de vehículo.', '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(3, 'Aspirado Interior', 'Aspirado Interior para todo tipo de vehículo.', '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(4, 'Lavado de Motor', 'Lavado de Motor para todo tipo de vehículo.', '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(5, 'Pulido de Carrocería', 'Pulido de Carrocería para todo tipo de vehículo.', '2025-04-13 01:41:23', '2025-04-13 01:41:23');

-- --------------------------------------------------------

--
-- Table structure for table `service_prices`
--

CREATE TABLE `service_prices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `service_id` bigint(20) UNSIGNED NOT NULL,
  `vehicle_type_id` bigint(20) UNSIGNED NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_prices`
--

INSERT INTO `service_prices` (`id`, `service_id`, `vehicle_type_id`, `price`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 519.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(2, 1, 2, 468.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(3, 1, 3, 357.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(4, 1, 4, 583.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(5, 1, 5, 442.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(6, 1, 6, 436.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(7, 1, 7, 431.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(8, 1, 8, 366.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(9, 1, 9, 571.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(10, 2, 1, 520.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(11, 2, 2, 485.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(12, 2, 3, 448.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(13, 2, 4, 376.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(14, 2, 5, 534.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(15, 2, 6, 415.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(16, 2, 7, 584.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(17, 2, 8, 497.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(18, 2, 9, 399.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(19, 3, 1, 523.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(20, 3, 2, 550.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(21, 3, 3, 424.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(22, 3, 4, 479.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(23, 3, 5, 577.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(24, 3, 6, 408.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(25, 3, 7, 543.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(26, 3, 8, 393.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(27, 3, 9, 580.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(28, 4, 1, 511.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(29, 4, 2, 389.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(30, 4, 3, 402.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(31, 4, 4, 474.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(32, 4, 5, 565.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(33, 4, 6, 581.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(34, 4, 7, 543.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(35, 4, 8, 489.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(36, 4, 9, 509.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(37, 5, 1, 558.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(38, 5, 2, 356.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(39, 5, 3, 552.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(40, 5, 4, 506.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(41, 5, 5, 466.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(42, 5, 6, 471.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(43, 5, 7, 597.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(44, 5, 8, 520.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(45, 5, 9, 392.00, '2025-04-13 01:41:23', '2025-04-13 01:41:23');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `washer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `vehicle_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL,
  `change` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('efectivo','tarjeta','transferencia','mixto') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `user_id`, `washer_id`, `vehicle_type_id`, `total_amount`, `paid_amount`, `change`, `payment_method`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 4, 1995.00, 5000.00, 3005.00, 'efectivo', '2025-04-14 08:07:12', '2025-04-14 08:07:12'),
(2, 1, 3, 5, 584.00, 200.00, -384.00, 'efectivo', '2025-04-14 08:14:02', '2025-04-14 08:14:02');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_details`
--

CREATE TABLE `ticket_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('service','product') NOT NULL,
  `service_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ticket_details`
--

INSERT INTO `ticket_details` (`id`, `ticket_id`, `type`, `service_id`, `product_id`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 'service', 1, NULL, 1, 583.00, 583.00, '2025-04-14 08:07:12', '2025-04-14 08:07:12'),
(2, 1, 'service', 2, NULL, 1, 376.00, 376.00, '2025-04-14 08:07:12', '2025-04-14 08:07:12'),
(3, 1, 'service', 5, NULL, 1, 506.00, 506.00, '2025-04-14 08:07:12', '2025-04-14 08:07:12'),
(4, 1, 'product', NULL, 1, 1, 350.00, 350.00, '2025-04-14 08:07:12', '2025-04-14 08:07:12'),
(5, 1, 'product', NULL, 6, 3, 60.00, 180.00, '2025-04-14 08:07:12', '2025-04-14 08:07:12'),
(6, 2, 'service', 2, NULL, 1, 534.00, 534.00, '2025-04-14 08:14:02', '2025-04-14 08:14:02'),
(7, 2, 'product', NULL, 7, 2, 25.00, 50.00, '2025-04-14 08:14:02', '2025-04-14 08:14:02');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` enum('admin','cajero') NOT NULL DEFAULT 'cajero'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`) VALUES
(1, 'Jorge Tonos', 'jtonos@infotep.gob.do', NULL, '$2y$12$NA1eqqzhKuKFHeBfN9E93O3MWxe3ZearTwsx8Hr1BVQnk0w.StfZy', NULL, '2025-04-14 06:47:29', '2025-04-14 06:47:29', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_types`
--

CREATE TABLE `vehicle_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicle_types`
--

INSERT INTO `vehicle_types` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Bicicleta', '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(2, 'Motor', '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(3, 'Pasola', '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(4, 'Carro', '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(5, 'Jeepeta', '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(6, 'Camioneta', '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(7, 'Minibús', '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(8, 'Guagua', '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(9, 'Camión', '2025-04-13 01:41:23', '2025-04-13 01:41:23');

-- --------------------------------------------------------

--
-- Table structure for table `washers`
--

CREATE TABLE `washers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `washers`
--

INSERT INTO `washers` (`id`, `name`, `phone`, `created_at`, `updated_at`) VALUES
(1, 'Carlos Martínez', '8091234567', '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(2, 'Luis Gómez', '8092345678', '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(3, 'Pedro Rodríguez', '8093456789', '2025-04-13 01:41:23', '2025-04-13 01:41:23'),
(4, 'Jonathan Ramírez', '8094567890', '2025-04-13 01:41:23', '2025-04-13 01:41:23');

-- --------------------------------------------------------

--
-- Table structure for table `washer_payments`
--

CREATE TABLE `washer_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `washer_id` bigint(20) UNSIGNED NOT NULL,
  `payment_date` date NOT NULL,
  `total_washes` int(11) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `observations` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_movements_product_id_foreign` (`product_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `petty_cash_expenses`
--
ALTER TABLE `petty_cash_expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `petty_cash_expenses_user_id_foreign` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_name_unique` (`name`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `services_name_unique` (`name`);

--
-- Indexes for table `service_prices`
--
ALTER TABLE `service_prices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_prices_service_id_vehicle_type_id_unique` (`service_id`,`vehicle_type_id`),
  ADD KEY `service_prices_vehicle_type_id_foreign` (`vehicle_type_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tickets_user_id_foreign` (`user_id`),
  ADD KEY `tickets_washer_id_foreign` (`washer_id`),
  ADD KEY `tickets_vehicle_type_id_foreign` (`vehicle_type_id`);

--
-- Indexes for table `ticket_details`
--
ALTER TABLE `ticket_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_details_ticket_id_foreign` (`ticket_id`),
  ADD KEY `ticket_details_service_id_foreign` (`service_id`),
  ADD KEY `ticket_details_product_id_foreign` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `vehicle_types`
--
ALTER TABLE `vehicle_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vehicle_types_name_unique` (`name`);

--
-- Indexes for table `washers`
--
ALTER TABLE `washers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `washer_payments`
--
ALTER TABLE `washer_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `washer_payments_washer_id_foreign` (`washer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `petty_cash_expenses`
--
ALTER TABLE `petty_cash_expenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `service_prices`
--
ALTER TABLE `service_prices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ticket_details`
--
ALTER TABLE `ticket_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vehicle_types`
--
ALTER TABLE `vehicle_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `washers`
--
ALTER TABLE `washers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `washer_payments`
--
ALTER TABLE `washer_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD CONSTRAINT `inventory_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `petty_cash_expenses`
--
ALTER TABLE `petty_cash_expenses`
  ADD CONSTRAINT `petty_cash_expenses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_prices`
--
ALTER TABLE `service_prices`
  ADD CONSTRAINT `service_prices_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_prices_vehicle_type_id_foreign` FOREIGN KEY (`vehicle_type_id`) REFERENCES `vehicle_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_vehicle_type_id_foreign` FOREIGN KEY (`vehicle_type_id`) REFERENCES `vehicle_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tickets_washer_id_foreign` FOREIGN KEY (`washer_id`) REFERENCES `washers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ticket_details`
--
ALTER TABLE `ticket_details`
  ADD CONSTRAINT `ticket_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ticket_details_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ticket_details_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `washer_payments`
--
ALTER TABLE `washer_payments`
  ADD CONSTRAINT `washer_payments_washer_id_foreign` FOREIGN KEY (`washer_id`) REFERENCES `washers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
