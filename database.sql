-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 11, 2026 at 11:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `basta_masarap`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `label` varchar(50) DEFAULT 'Home',
  `full_address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `name_en` varchar(80) NOT NULL,
  `name_fil` varchar(80) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `slug`, `name_en`, `name_fil`, `sort_order`) VALUES
(1, 'ulam', 'Ulam (Main Dishes)', 'Ulam', 1),
(2, 'rice-meals', 'Rice Meals', 'Rice Meals', 2),
(3, 'silog', 'Silog Meals', 'Silog', 3),
(4, 'noodles', 'Noodles', 'Pansit', 4),
(5, 'appetizers', 'Appetizers', 'Pampagana', 5),
(6, 'desserts', 'Desserts', 'Panghimagas', 6),
(7, 'drinks', 'Drinks', 'Inumin', 7),
(8, 'specials', 'Chef Specials', 'Specialty ng Bahay', 8);

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `delivery_id` int(11) NOT NULL,
  `delivery_code` varchar(30) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` enum('pending','confirmed','preparing','out_for_delivery','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dishes`
--

CREATE TABLE `dishes` (
  `dish_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `desc_en` text NOT NULL,
  `desc_fil` text NOT NULL,
  `ingredients` text NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_popular` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dishes`
--

INSERT INTO `dishes` (`dish_id`, `category_id`, `name`, `desc_en`, `desc_fil`, `ingredients`, `price`, `image_path`, `is_available`, `is_featured`, `is_popular`, `created_at`) VALUES
(1, 1, 'Chicken Adobo', 'Chicken braised slowly in soy sauce, vinegar, garlic, and bay leaf — the Philippines\' most iconic home-cooked dish.', 'Manok na niluto sa toyo, suka, bawang, at laurel hanggang lumambot at sumarap.', 'Chicken, soy sauce, vinegar, garlic, bay leaf, peppercorns', 149.00, 'assets/images/dishes/c98979045897136049f9f5f6.jpg', 1, 1, 1, '2026-08-11 20:51:33'),
(2, 1, 'Sinigang na Baboy', 'Pork simmered in a sour tamarind broth with vegetables — comfort food on a rainy day.', 'Baboy na niluto sa maasim na sabaw ng sampalok kasama ang mga gulay.', 'Pork, tamarind, kangkong, radish, string beans, tomato', 189.00, 'assets/images/dishes/cfa05ef10f3838d1ce2b7ec8.jpg', 1, 1, 1, '2026-08-11 20:51:33'),
(3, 1, 'Kare Kare', 'Oxtail and vegetables in a rich, roasted peanut sauce, served with shrimp paste on the side.', 'Buntot ng baka at gulay sa malapot na sarsa ng mani, may bagoong sa tabi.', 'Oxtail, peanut sauce, eggplant, string beans, bagoong', 259.00, 'assets/images/dishes/64171c507703ee3b83b5ada3.jpg', 1, 1, 0, '2026-08-11 20:51:33'),
(4, 1, 'Crispy Pata', 'Deep-fried whole pork leg, crackling skin outside and tender meat inside.', 'Buong binti ng baboy na pinirito hanggang malutong sa labas at malambot sa loob.', 'Pork leg, garlic, bay leaf, peppercorns, soy-vinegar dip', 349.00, 'assets/images/dishes/b88ca020d60e3ef2efafb5b6.jpg', 1, 0, 1, '2026-08-11 20:51:33'),
(5, 1, 'Sisig', 'Sizzling chopped pork face and liver seasoned with calamansi, onions, and chili — the ultimate beer pulutan.', 'Giniling na mukha at atay ng baboy, maasim-maanghang, sizzling sa kawali.', 'Pork face, pork liver, onion, calamansi, chili, egg', 179.00, 'assets/images/dishes/58a254650169c751bdd62679.jpg', 1, 1, 1, '2026-08-11 20:51:33'),
(6, 1, 'Lechon Kawali', 'Deep-fried pork belly, shatteringly crisp skin, served with liver sauce.', 'Pork belly na pinirito hanggang malutong ang balat, may sarsa.', 'Pork belly, salt, pepper, bay leaf, liver sauce', 219.00, 'assets/images/dishes/4b1fa011098d720b48340a53.jpg', 1, 0, 1, '2026-08-11 20:51:33'),
(7, 1, 'Tinolang Manok', 'Chicken soup with green papaya and chili leaves in a light ginger broth.', 'Sabaw ng manok na may papaya at dahon ng sili, may lasa ng luya.', 'Chicken, green papaya, chili leaves, ginger, fish sauce', 169.00, 'assets/images/dishes/09f5de6e4db6814ef012a085.jpg', 1, 0, 0, '2026-08-11 20:51:33'),
(8, 1, 'Bulalo', 'Beef shank and marrow bones simmered for hours into a deep, savory broth with vegetables.', 'Buto-buto ng baka na niluto ng matagal hanggang lumabas ang linamnam ng utak.', 'Beef shank, bone marrow, corn, cabbage, potato', 289.00, 'assets/images/dishes/11d96387b090267a0665e043.jpg', 1, 0, 0, '2026-08-11 20:51:33'),
(9, 1, 'Chicken Inasal', 'Grilled chicken marinated in calamansi, lemongrass, and annatto oil, Bacolod-style.', 'Inihaw na manok na naka-marinate sa calamansi, tanglad, at atsuete.', 'Chicken, calamansi, lemongrass, annatto oil, garlic', 159.00, 'assets/images/dishes/3035702ad8db4fc3a5c8fbfe.jpg', 1, 1, 0, '2026-08-11 20:51:33'),
(10, 1, 'Calamansi Chicken', 'Pan-seared chicken glazed in a tangy calamansi-soy sauce.', 'Manok na niluto sa maasim-maalat na sarsa ng calamansi.', 'Chicken thigh, calamansi, soy sauce, garlic, brown sugar', 165.00, 'assets/images/dishes/d0d1f9eb0cc89fde7e6dc10e.jpg', 1, 0, 0, '2026-08-11 20:51:33'),
(11, 4, 'Pancit Canton', 'Stir-fried wheat noodles with vegetables, pork, and shrimp in a savory sauce.', 'Pinirito na canton noodles na may gulay, baboy, at hipon.', 'Canton noodles, pork, shrimp, cabbage, carrot, soy sauce', 139.00, 'assets/images/dishes/eceef36f3fae9e11d89fcbc0.jpg', 1, 0, 1, '2026-08-11 20:51:33'),
(12, 4, 'Pancit Bihon', 'Rice noodles stir-fried with vegetables and chicken in a light soy-based sauce.', 'Pinirito na bihon na may gulay at manok sa magaan na sarsa.', 'Rice noodles, chicken, carrot, cabbage, soy sauce', 129.00, 'assets/images/dishes/6459867c4f262e3afea5ed49.jpg', 1, 0, 0, '2026-08-11 20:51:33'),
(13, 3, 'Tapsilog', 'Marinated beef tapa, garlic fried rice, and a fried egg — the classic silog combo.', 'Tapang baka, sinangag na bawang, at itlog na pinirito.', 'Beef tapa, garlic rice, fried egg', 159.00, 'assets/images/dishes/66ef33f58bf95e72476ac468.jpg', 1, 1, 1, '2026-08-11 20:51:33'),
(14, 3, 'Bangsilog', 'Crispy fried milkfish (bangus), garlic rice, and a fried egg.', 'Pritong bangus, sinangag, at itlog.', 'Bangus, garlic rice, fried egg', 149.00, 'assets/images/dishes/82f44faca9247e94587a0347.jpg', 1, 0, 0, '2026-08-11 20:51:33'),
(15, 3, 'Chicksilog', 'Fried chicken, garlic fried rice, and a fried egg.', 'Pritong manok, sinangag, at itlog.', 'Fried chicken, garlic rice, fried egg', 149.00, 'assets/images/dishes/f020c34fc1535a2e638482f0.jpg', 1, 0, 0, '2026-08-11 20:51:33'),
(16, 3, 'Tocilog', 'Sweet-cured pork tocino, garlic fried rice, and a fried egg.', 'Matamis na tocino, sinangag, at itlog.', 'Tocino, garlic rice, fried egg', 139.00, 'assets/images/dishes/f8019549cb7ad0421c10c53f.jpg', 1, 0, 1, '2026-08-11 20:51:33'),
(17, 3, 'Longsilog', 'Filipino sweet garlic sausage, garlic fried rice, and a fried egg.', 'Longganisa, sinangag, at itlog.', 'Longganisa, garlic rice, fried egg', 135.00, 'assets/images/dishes/3ac78b3113be4c4cfa7d7621.jpg', 1, 0, 0, '2026-08-11 20:51:33'),
(18, 5, 'Lumpiang Shanghai', 'Crispy fried spring rolls filled with seasoned ground pork.', 'Pinirito na lumpia na may giniling na baboy sa loob.', 'Ground pork, carrot, spring roll wrapper', 129.00, 'assets/images/dishes/eb3368486f339b687725b8b8.jpg', 1, 0, 1, '2026-08-11 20:51:33'),
(19, 2, 'Garlic Rice', 'Steamed rice pan-fried with garlic until fragrant and golden.', 'Kanin na pinirito sa bawang hanggang bumango.', 'Rice, garlic, oil, salt', 45.00, 'assets/images/dishes/7584c47128ac8395de2d343e.jpg', 1, 0, 0, '2026-08-11 20:51:33'),
(20, 6, 'Halo Halo', 'Shaved ice mixed with sweet beans, fruits, jellies, leche flan, and ube ice cream.', 'Dinurog na yelo na may minatamis na beans, prutas, at sahog, may ube ice cream sa ibabaw.', 'Shaved ice, ube, leche flan, sweet beans, jellies, evaporated milk', 99.00, 'assets/images/dishes/259bfc961632db17e04086f4.jpg', 1, 1, 1, '2026-08-11 20:51:33'),
(21, 6, 'Leche Flan', 'Silky steamed custard topped with caramel syrup.', 'Malambot na custard na may caramel sa ibabaw.', 'Egg yolk, condensed milk, evaporated milk, sugar', 69.00, 'assets/images/dishes/58e1f940b97580aa84daa6b6.jpg', 1, 0, 1, '2026-08-11 20:51:33'),
(22, 6, 'Turon', 'Crispy fried banana rolls glazed in caramelized sugar.', 'Saging na binalot sa lumpia wrapper, pinirito na may caramel.', 'Saba banana, jackfruit, spring roll wrapper, brown sugar', 59.00, 'assets/images/dishes/357188cb5f33fccc0616ea1d.jpg', 1, 0, 0, '2026-08-11 20:51:33'),
(23, 6, 'Bibingka', 'Baked rice cake with a soft, slightly charred top, topped with salted egg and cheese.', 'Niluto sa banga na rice cake, may salted egg at keso sa ibabaw.', 'Rice flour, coconut milk, salted egg, cheese, banana leaf', 65.00, 'assets/images/dishes/0818654672d7fce9b41652fb.jpg', 1, 0, 0, '2026-08-11 20:51:33'),
(24, 6, 'Puto', 'Soft steamed rice cakes, a classic pasalubong and merienda favorite.', 'Malambot na steamed rice cake, paboritong meryenda.', 'Rice flour, sugar, baking powder, coconut', 55.00, 'assets/images/dishes/894f27da6ef78a1b75c15c85.jpg', 1, 0, 0, '2026-08-11 20:51:33'),
(25, 7, 'Sago\'t Gulaman', 'Sweet iced drink with tapioca pearls and gelatin cubes in brown sugar syrup.', 'Matamis na inumin na may sago at gulaman sa brown sugar syrup.', 'Sago pearls, gulaman, brown sugar, water', 45.00, 'assets/images/dishes/8592266631859da42a20ce35.jpg', 1, 0, 0, '2026-08-11 20:51:33'),
(26, 7, 'Calamansi Juice', 'Freshly squeezed calamansi juice, sweet and tangy.', 'Sariwang katas ng calamansi, may lasang matamis-maasim.', 'Calamansi, sugar, water, ice', 39.00, 'assets/images/dishes/b5bc714eaf530bc26d5392cf.jpg', 1, 0, 0, '2026-08-11 20:51:33'),
(27, 8, 'Bulalo Special Platter', 'Our best-selling bulalo, extra bone marrow, served for sharing.', 'Best-seller na bulalo na may dagdag na utak, para sa sharing.', 'Beef shank, bone marrow, corn, vegetables', 399.00, 'assets/images/dishes/e3d6f4d982842ed3f99c2994.jpg', 1, 1, 1, '2026-08-11 20:51:33');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `favorite_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dish_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_code` varchar(30) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_id` int(11) DEFAULT NULL,
  `delivery_address` varchar(255) NOT NULL,
  `contact_number` varchar(30) NOT NULL,
  `subtotal` decimal(9,2) NOT NULL,
  `delivery_fee` decimal(8,2) NOT NULL DEFAULT 49.00,
  `total` decimal(9,2) NOT NULL,
  `status` enum('pending','confirmed','preparing','out_for_delivery','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `dish_id` int(11) NOT NULL,
  `dish_name` varchar(120) NOT NULL,
  `unit_price` decimal(8,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `line_total` decimal(9,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reactions`
--

CREATE TABLE `reactions` (
  `reaction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dish_id` int(11) NOT NULL,
  `type` enum('like','dislike') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_settings`
--

CREATE TABLE `restaurant_settings` (
  `setting_key` varchar(60) NOT NULL,
  `setting_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restaurant_settings`
--

INSERT INTO `restaurant_settings` (`setting_key`, `setting_value`) VALUES
('delivery_fee', '49.00'),
('restaurant_name', 'Basta Masarap Restaurant'),
('tagline_en', 'When it is delicious, it does not need much explanation.'),
('tagline_fil', 'Kapag masarap, hindi na kailangan ng maraming explanation.');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dish_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `comment` text NOT NULL,
  `is_edited` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('visible','hidden') NOT NULL DEFAULT 'visible',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `review_reports`
--

CREATE TABLE `review_reports` (
  `report_id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'admin'),
(2, 'customer');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT 2,
  `username` varchar(50) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `contact_number` varchar(30) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `role_id`, `username`, `email`, `password_hash`, `full_name`, `contact_number`, `profile_picture`, `status`, `created_at`) VALUES
(1, 1, 'admin', 'admin@bastamasarap.local', '$2y$10$E.izqnhYa6sGlBty5kj5vuLyEaIxee.MRbmIg3RT1y520K8xzHBP2', 'System Administrator', '09170000000', NULL, 'active', '2026-08-11 20:51:33');

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `wishlist_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dish_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`delivery_id`),
  ADD UNIQUE KEY `delivery_code` (`delivery_code`),
  ADD UNIQUE KEY `order_id` (`order_id`);

--
-- Indexes for table `dishes`
--
ALTER TABLE `dishes`
  ADD PRIMARY KEY (`dish_id`),
  ADD KEY `category_id` (`category_id`);
ALTER TABLE `dishes` ADD FULLTEXT KEY `ft_search` (`name`,`desc_en`,`desc_fil`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`favorite_id`),
  ADD UNIQUE KEY `uq_fav` (`user_id`,`dish_id`),
  ADD KEY `dish_id` (`dish_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `address_id` (`address_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `dish_id` (`dish_id`);

--
-- Indexes for table `reactions`
--
ALTER TABLE `reactions`
  ADD PRIMARY KEY (`reaction_id`),
  ADD UNIQUE KEY `uq_reaction` (`user_id`,`dish_id`),
  ADD KEY `dish_id` (`dish_id`);

--
-- Indexes for table `restaurant_settings`
--
ALTER TABLE `restaurant_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `dish_id` (`dish_id`);

--
-- Indexes for table `review_reports`
--
ALTER TABLE `review_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD UNIQUE KEY `uq_report` (`review_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD UNIQUE KEY `uq_wish` (`user_id`,`dish_id`),
  ADD KEY `dish_id` (`dish_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `delivery_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dishes`
--
ALTER TABLE `dishes`
  MODIFY `dish_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reactions`
--
ALTER TABLE `reactions`
  MODIFY `reaction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `review_reports`
--
ALTER TABLE `review_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `dishes`
--
ALTER TABLE `dishes`
  ADD CONSTRAINT `dishes_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`dish_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`address_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`dish_id`);

--
-- Constraints for table `reactions`
--
ALTER TABLE `reactions`
  ADD CONSTRAINT `reactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reactions_ibfk_2` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`dish_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`dish_id`) ON DELETE CASCADE;

--
-- Constraints for table `review_reports`
--
ALTER TABLE `review_reports`
  ADD CONSTRAINT `review_reports_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`review_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_reports_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_ibfk_2` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`dish_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
