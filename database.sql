-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 01, 2025 at 08:08 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `store`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'suraj', '$2y$10$2DUm33LvEXXTP/dOf9Xzluvbt5ksto40Vmk/etpcgOyaFsmwbR4Ke', '2025-02-28 11:08:57'),
(2, 'nishan', '$2y$10$rjzuzWE9OtuT6/R67v03WeDA8ydi09hU1OyQLdnZZ5aCDygVKBfYS', '2025-03-01 03:33:35');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_image` varchar(255) DEFAULT NULL,
  `category_slug` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `category_image`, `category_slug`) VALUES
(1, 'Mountain Bikes', 'uploads/mountain_bike.jpg', 'mountain-bikes'),
(2, 'Road Bikes', 'uploads/road_bike.jpg', 'road-bikes'),
(3, 'Hybrid Bikes', 'uploads/hybrid_bike.jpg', 'hybrid-bikes'),
(4, 'Electric Bikes', 'uploads/electric_bike.jpg', 'electric-bikes'),
(5, 'Kids Bikes', 'uploads/kids_bike.jpg', 'kids-bikes'),
(6, 'BMX Bikes', 'uploads/bmx_bike.jpg', 'bmx-bikes'),
(7, 'Folding Bikes', 'uploads/folding_bike.jpg', 'folding-bikes'),
(8, 'Cruiser Bikes', 'uploads/cruiser_bike.jpg', 'cruiser-bikes'),
(9, 'Gravel Bikes', 'uploads/gravel_bike.jpg', 'gravel-bikes'),
(10, 'Touring Bikes', 'uploads/touring_bike.jpg', 'touring-bikes'),
(11, 'Cyclocross Bikes', 'uploads/cyclocross_bike.jpg', 'cyclocross-bikes'),
(12, 'Tandem Bikes', 'uploads/tandem_bike.jpg', 'tandem-bikes');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(50) NOT NULL,
  `zip_code` varchar(20) NOT NULL,
  `notes` text DEFAULT NULL,
  `payment_method` varchar(20) NOT NULL,
  `order_total` decimal(10,2) NOT NULL,
  `order_status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `first_name`, `last_name`, `email`, `phone`, `address`, `city`, `state`, `zip_code`, `notes`, `payment_method`, `order_total`, `order_status`, `created_at`, `updated_at`) VALUES
(5, 'ORD-20250301-2749', 'sdafjkj', 'kljsadklfj', 'kaldsjk@gmail.com', '343234', 'asdfasdf', 'asdfadsf', 'asdfasdf', '21800', 'asdflkjaksdf', 'cod', 444.00, 'pending', '2025-03-01 09:35:06', NULL),
(6, 'ORD-20250301-4793', 'dkasjdhf', 'jkhjkasdhfjkas', 'asdfljkadsf@gmail.com', '90830498', 'asdfkjasdfkl', 'jlaksjdlf', 'jljlaksdjf', '2334', 'asdfasdf', 'cod', 7188.00, 'pending', '2025-03-01 12:27:58', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `created_at`) VALUES
(8, 5, 29, 2, '2025-03-01 09:35:06'),
(9, 6, 43, 4, '2025-03-01 12:27:58'),
(10, 6, 40, 4, '2025-03-01 12:27:58'),
(11, 6, 42, 4, '2025-03-01 12:27:58');

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `title_slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `image_url_1` varchar(255) DEFAULT NULL,
  `image_url_2` varchar(255) DEFAULT NULL,
  `image_url_3` varchar(255) DEFAULT NULL,
  `image_url_4` varchar(255) DEFAULT NULL,
  `resized_image_url` varchar(255) DEFAULT NULL,
  `age_range` varchar(50) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `title_slug`, `description`, `price`, `keywords`, `image_url_1`, `image_url_2`, `image_url_3`, `image_url_4`, `resized_image_url`, `age_range`, `featured`) VALUES
(30, 'Raven Trickster 18', 'raven-trickster-18', 'The Raven Trickster 18\" BMX Bike is crafted for young riders seeking to hone their freestyle skills. Its hi-tensile steel frame with an 18\" top tube ensures a durable yet responsive ride. Featuring a 3-piece Cro-Mo crankset and sealed bearing hubs, it offers smooth pedaling and longevity. The rear alloy U-brake provides reliable stopping power, and the front caliper remains optional for a customizable setup. Built to handle tricks and jumps, the Raven Trickster 18\" is the perfect companion for BMX beginners and enthusiasts.', 399.00, 'Raven, BMX, TRICKSTER 18', 'uploads/67c2856f7bd40-42765+AT+Raven+16inch+Trickster+BMX+Bike+Gloss+Black+White+45D.jpg', 'uploads/67c2865bc66a9-42152+AT+Raven+18inch+Trickster+BMX+Bike+Gloss+Pink+White+45D.jpg', NULL, NULL, 'resized/67c2856f7bd40-42765+AT+Raven+16inch+Trickster+BMX+Bike+Gloss+Black+White+45D.jpg', '13+', 1),
(31, 'Raven Trickster 20', 'raven-trickster-20', 'The Raven Trickster 20\" BMX Bike is built to handle the demands of both beginners and experienced riders. Featuring a tough hi-tensile steel frame with a 19.5\" top tube, it ensures durability and responsiveness. With a 3-piece Cro-Mo crankset and sealed bearings in both the front and rear hubs, this BMX provides smooth performance while the rear alloy U-brake ensures reliable stopping power.', 300.00, 'Bmx Bikes, Bmx, Teen Bikes, Raven Trickster', 'uploads/67c2860bd65b8-42150+AT+Raven+18inch+Trickster+BMX+Bike+Gloss+Black+White+45D.jpg', 'uploads/67c2860c0cc4f-42151+AT+Raven+18inch+Trickster+BMX+Bike+Gloss+Blue+White+45D.jpg', 'uploads/67c2860c32430-42152+AT+Raven+18inch+Trickster+BMX+Bike+Gloss+Pink+White+45D.jpg', NULL, 'resized/67c2860bd65b8-42150+AT+Raven+18inch+Trickster+BMX+Bike+Gloss+Black+White+45D.jpg', '13+', 1),
(32, 'Merida eOne-Sixty 675 Electric', 'merida-eone-sixty-675-electric', 'The Merida eOne-Sixty 675 Electric Dual Suspension Mountain Bike is designed for adventure seekers who crave versatility and performance. With its durable aluminium frame and user-removable 750Wh battery, this bike combines award-winning handling and suspension with high-quality components, making it perfect for both challenging trails and casual rides. The robust Marzocchi Z1 fork and Shimano Deore LinkGlide drivetrain ensure a smooth and powerful ride, whether you\'re tackling steep climbs or enjoying exhilarating descents.', 5999.00, 'Electric Bike, Merdia, Dual Suspension', 'uploads/67c28709b6ae1-WhatsApp Image 2025-03-01 at 9.48.02 AM (1).jpeg', 'uploads/67c28709c61c3-WhatsApp Image 2025-03-01 at 9.48.02 AM.jpeg', NULL, NULL, 'resized/67c28709b6ae1-WhatsApp Image 2025-03-01 at 9.48.02 AM (1).jpeg', '13+', 1),
(33, ' VelectriX Newtown Electric Commuter Bike', 'velectrix-newtown-electric-commuter-bike', 'The VelectriX Newtown Electric Commuter Bike is a sleek and sporty solution for urban commuting and leisurely rides. Equipped with a powerful Bafang rear hub motor and a robust 417Wh battery, this bike offers an impressive range and quick recharging, making it perfect for daily use. Its lightweight aluminum alloy frame and durable double-walled rims ensure a comfortable and reliable ride, while the single-speed belt drive system minimizes maintenance, allowing you to focus on enjoying your journey.', 1999.00, 'Velectix Newtown, Electric Bike, Hybrid', 'uploads/67c287df3eb26-WhatsApp Image 2025-03-01 at 9.49.39 AM.jpeg', 'uploads/67c287df4de26-WhatsApp Image 2025-03-01 at 9.49.53 AM.jpeg', 'uploads/67c287df56750-WhatsApp Image 2025-03-01 at 9.50.06 AM.jpeg', NULL, 'resized/67c287df3eb26-WhatsApp Image 2025-03-01 at 9.49.39 AM.jpeg', '13+', 1),
(34, 'Jet BMX Block BMX Bike', 'jet-bmx-block-bmx-bike', 'This Jet BMX Bike is the most affordable full size 20\" BMX we currently offer, and with everything it has on it it\'s a perfect entry level bike.\r\n\r\nIt\'s packed full of features like the Hi-ten 20.25\" top tube frame, 8\" high steel bars, 3-piece forged cranks with sealed mid BB, 9t sealed cassette hub, padded seat and post combo and so much more.\r\n\r\nIt\'s unheard of to get 3-piece cranks, sealed hubs and bearings at this price point - all of these things mean the bike is lighter, stronger and harder wearing than parts normally found at this price.', 299.00, 'JET BMX, BLOCK BMX BIKE, ', 'uploads/67c28891bcbfd-WhatsApp Image 2025-03-01 at 9.53.22 AM.jpeg', 'uploads/67c28891cc8cb-WhatsApp Image 2025-03-01 at 9.53.14 AM.jpeg', NULL, NULL, 'resized/67c28891bcbfd-WhatsApp Image 2025-03-01 at 9.53.22 AM.jpeg', '13+', 0),
(35, 'BMC Unrestricted TWO AL Gravel Bike ', 'bmc-unrestricted-two-al-gravel-bike', 'URS AL has chameleon-like capabilities to adapt and thrive in diverse surroundings. Maintaining the URS family\'s handling characteristics, whilst offering even greater practicality through additional cargo mounts and dropper post compatibility, this aluminium gravel bike is ready for adventure. Specced with the SRAM Apex 1x11 drivetrain with an 11-42 cassette, hydraulic disc brakes and 45 mm WTB tires, this build isn\'t afraid to tackle diverse terrain. ', 2999.00, 'BMC Unrestricted TWO AL Gravel Bike, Touring Bike, Gravel Bikes, Cyclocross Bikes', 'uploads/67c28943029b3-WhatsApp Image 2025-03-01 at 9.56.05 AM.jpeg', NULL, NULL, NULL, 'resized/67c28943029b3-WhatsApp Image 2025-03-01 at 9.56.05 AM.jpeg', '13+', 1),
(36, ' Norco Search XR S1 Gravel Bike', 'norco-search-xr-s1-gravel-bike', 'The Norco Search XR S1 brings road bike efficiency and the versatility of a cyclo-cross bike to create an incredibly capable gravel bike. The Search XR S1 is built with an incredibly tough Search Reynolds 725 Chromoly Steel frame for the perfect blend of lightweight comfort and durability when you take this bike off road.  While the Mid-Modulus Carbon Fork minimises weight yet offers excellent control and comfort on rough surfaces.', 3999.00, 'NORCO, GRAVEL BIKE, CYCLOCROSS BIKE, XR S1', 'uploads/67c289ec0a06f-WhatsApp Image 2025-03-01 at 9.58.45 AM.jpeg', 'uploads/67c289ec1b254-WhatsApp Image 2025-03-01 at 9.59.05 AM.jpeg', NULL, NULL, 'resized/67c289ec0a06f-WhatsApp Image 2025-03-01 at 9.58.45 AM.jpeg', '13+', 0),
(37, 'Cube Stereo ONE44 C:62', 'cube-stereo-one44-c-62', 'A great all-round full suspension bike must be several things. It needs to be quick-handling. It should be light. And, above all, it must be capable of dealing with rowdy, rock and root-filled trails. The Cube Stereo ONE44 C:62 Race Dual Suspension Mountain Bike is all of these, and more. With a lightweight carbon chassis and clever design features like a storage compartment big enough to fit a jacket, gloves or most trail tools (or just a multitude of snacks!) and Angle Set headset, it\'s all you need for Alpine adventures.', 3599.00, 'CUBE STEREO, GEAR BIKE, MOUNTAIN BIKE', 'uploads/67c28abfaf725-WhatsApp Image 2025-03-01 at 10.02.06 AM.jpeg', 'uploads/67c28abfc1289-WhatsApp Image 2025-03-01 at 10.02.23 AM.jpeg', NULL, NULL, 'resized/67c28abfaf725-WhatsApp Image 2025-03-01 at 10.02.06 AM.jpeg', '13+', 1),
(38, 'Cube Stereo ONE55 C:62 Race 29 Dual ', 'cube-stereo-one55-c-62-race-29-dual', 'It takes a special breed to match rock-solid descending ability with uphill agility and the Cube Stereo ONE55 C:62 Race 29 Dual Suspension Mountain Bike is one of the best. Combining the low weight and exceptional efficiency of its Advanced Twin Mould C:62 carbon frame, the Stereo ONE55 delivers on its promises and then some. There\'s no better way to take on tough mountain trails and come out on top. With the new Cube Stereo ONE55 C:62 Race 29 Dual Suspension Mountain Bike you don\'t have to choose between elegant form and rugged function. ', 3456.00, 'CUBE STEREO, GEAR BIKE, MOUNTAIN BIKE, ELECTRIC BIKE', 'uploads/67c28b7d07485-WhatsApp Image 2025-03-01 at 10.05.14 AM.jpeg', 'uploads/67c28b7d15f6c-WhatsApp Image 2025-03-01 at 10.05.35 AM.jpeg', NULL, NULL, 'resized/67c28b7d07485-WhatsApp Image 2025-03-01 at 10.05.14 AM.jpeg', '13+', 0),
(39, 'Pedal Brewer Electric Cruiser Bike ', 'pedal-brewer-electric-cruiser-bike', 'With the Pedal Brewer Electric Cruiser Bike 540Wh you can move about town with ease and in style. Featuring a 540Wh battery and a bump-start button to make getting moving a breeze, Brewer has sufficient range for up to 50km!¬†The Brewer weighs approx 26kg.', 2199.00, 'Pedal Brewer Electric Cruiser Bike , ELECTRIC BIKES, FOLDING BIKE', 'uploads/67c28c26eabaa-WhatsApp Image 2025-03-01 at 10.08.34 AM.jpeg', 'uploads/67c28c27086c2-WhatsApp Image 2025-03-01 at 10.08.53 AM.jpeg', NULL, NULL, 'resized/67c28c26eabaa-WhatsApp Image 2025-03-01 at 10.08.34 AM.jpeg', '13+', 1),
(40, 'Pedal Dynamo 2 Electric Folding Bike', 'pedal-dynamo-2-electric-folding-bike', 'The Pedal Dynamo 2 Electric Folding Bike is a conveniently sized electric bike that folds into a compact space-saving size! Pedal have used a centre-folding frame and handle-post stem, and also added adjustable everything: The stem, handlebar and seatpost are all adjustable! Coupled with the compact 20\" wheels, you\'ll find Dynamo is both easy to ride and convenient to store away, get on the train with. or pop in the boot of your car, folding down to just 85cm x 65cm x 50cm.', 999.00, 'Pedal Dynamo, Electric Folding Bike', 'uploads/67c28ca314c7f-WhatsApp Image 2025-03-01 at 10.10.53 AM.jpeg', 'uploads/67c28ca321539-WhatsApp Image 2025-03-01 at 10.11.11 AM.jpeg', NULL, NULL, 'resized/67c28ca314c7f-WhatsApp Image 2025-03-01 at 10.10.53 AM.jpeg', '13+', 0),
(41, 'Cruzee UltraLite 12\" Balance Bike', 'cruzee-ultralite-12-balance-bike', 'Cruzee have gone to great lengths to provide your child with the best balance bike possible. The Cruzee UltraLite Balance Bike is a lightweight\" durable\" safe\" adjustable and fun bike designed for children aged 18 months to 5 years. The aluminium frame means that the Cruzee weighs in at just 1.9kg (4.4lbs). You and your child can easily carry a Cruzee around when it is not being ridden. The lightweight design also makes the Cruzee easy to ride and manoeuvre\" while the aluminium frame will not rust\" which means your Cruzee will survive no matter where your child leaves it for the night! With the added bonus of puncture-less tyres\" the Cruzee UltraLite Balance Bike is incredibly durable and can ride on all terrains ensuring hassle free riding. The adjustable handle bar and seat height means that the Cruzee will grow with your child and is suitable for children aged 18 months to 5 years. Cruzee‚s also ship with an extra long seat post that can be switched over if necessary.', 196.00, 'Cruzee UltraLite, BALANCE BIKE', 'uploads/67c28d10da558-WhatsApp Image 2025-03-01 at 10.13.01 AM.jpeg', NULL, NULL, NULL, 'resized/67c28d10da558-WhatsApp Image 2025-03-01 at 10.13.01 AM.jpeg', '4-7', 1),
(42, 'Pedal Bam 20\" Kids', 'pedal-bam-20-kids', 'The Pedal Bam 20\" Kids Bike is designed to offer young riders (approx. 5-8 yrs old) a fun and confident cycling experience. With its lightweight 20\" alloy frame, it provides a balanced ride that’s both durable and easy to handle. This bike features kid-friendly coaster and front brakes, along with wide 2.35\" tyres for stability on different surfaces. Built for simplicity and ease, the single-speed drivetrain allows for smooth, hassle-free rides, making the Pedal Bam perfect for any child ready to hit the road or trail.', 499.00, 'Pedal Bam, Kids Bikes, Children Bike', 'uploads/67c28da59c8d3-WhatsApp Image 2025-03-01 at 10.15.02 AM.jpeg', 'uploads/67c28da5ad004-WhatsApp Image 2025-03-01 at 10.15.21 AM.jpeg', NULL, NULL, 'resized/67c28da59c8d3-WhatsApp Image 2025-03-01 at 10.15.02 AM.jpeg', '7-12', 1),
(43, 'Pedal Uptown Classic Vintage Cruiser', 'pedal-uptown-classic-vintage-cruiser', 'Pedal Uptown is cruiser bike that combines vintage styling with practical features. Built with the casual rider in mind the Uptown is ideal for shorter rides around town or down to the park. Swept back and slightly rising handlebars give a relaxed upright riding position while the steel frame provides classical retro-style tubing optimised for comfort and ease of handling as the material absorbs bumps in the road. A fully customised chain cover and integrated double-sided chain guard protect from clothing from snags and the chain from dropping out. Colour-matched mudguards are stylish yet practical shielding you from any potential splashes. Pedal Uptown features high-quality dual-pivot brakes the same style of brakes used on road bikes that offer smooth reliable stopping power in any condition. Shimano 7-speed twist shifting also gives a good range of gears to help you cruise as well as tackle any hill.', 299.00, 'Pedal Uptown Classic Vintage Cruiser', 'uploads/67c28e9003c69-WhatsApp Image 2025-03-01 at 10.18.21 AM.jpeg', 'uploads/67c28e9017ba1-WhatsApp Image 2025-03-01 at 10.18.32 AM.jpeg', NULL, NULL, 'resized/67c28e9003c69-WhatsApp Image 2025-03-01 at 10.18.21 AM.jpeg', '13+', 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`product_id`, `category_id`) VALUES
(30, 2),
(30, 6),
(31, 2),
(31, 6),
(31, 9),
(32, 2),
(32, 4),
(33, 2),
(33, 3),
(33, 4),
(33, 10),
(34, 6),
(35, 2),
(35, 3),
(35, 9),
(35, 10),
(35, 11),
(36, 2),
(36, 3),
(36, 9),
(36, 10),
(36, 11),
(37, 1),
(37, 2),
(37, 3),
(37, 9),
(37, 10),
(37, 12),
(38, 1),
(38, 2),
(38, 3),
(38, 4),
(38, 9),
(38, 10),
(38, 12),
(39, 2),
(39, 3),
(39, 4),
(39, 7),
(40, 2),
(40, 3),
(40, 4),
(40, 7),
(40, 10),
(41, 5),
(42, 2),
(42, 5),
(43, 2),
(43, 3),
(43, 8);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_slug` (`category_slug`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `title_slug` (`title_slug`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`product_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD CONSTRAINT `product_categories_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `product_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
