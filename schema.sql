SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `shop` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `shop`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `role` enum('member','factory','admin') NOT NULL DEFAULT 'member',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image` varchar(255) NOT NULL DEFAULT 'default.png',
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` int(11) NOT NULL,
  `stock` int(11) NOT NULL,
  `sales_total` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('0','1','2','3','4','5') NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivery_date` datetime DEFAULT NULL,
  `slip_image` varchar(255) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `transfer_date` date DEFAULT NULL,
  `transfer_time` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`username`, `password`, `first_name`, `last_name`, `email`, `phone`, `address`, `role`) VALUES
('admin', '$2y$10$169Yb2sOc/wEXxFd3HdT6.GdFxgbelPud9f2t7mkwQJOuIEtQjOnK', 'Admin', 'User', 'admin@shop.test', '0800000001', 'Head office', 'admin'),
('factory', '$2y$10$169Yb2sOc/wEXxFd3HdT6.GdFxgbelPud9f2t7mkwQJOuIEtQjOnK', 'Factory', 'Staff', 'factory@shop.test', '0800000002', 'Warehouse', 'factory'),
('member', '$2y$10$169Yb2sOc/wEXxFd3HdT6.GdFxgbelPud9f2t7mkwQJOuIEtQjOnK', 'Demo', 'Member', 'member@shop.test', '0800000003', 'Customer address', 'member');

INSERT INTO `products` (`image`, `name`, `description`, `price`, `stock`, `sales_total`) VALUES
('default.png', 'Gundam RX-78-2', 'Classic 1/144 scale model kit.', 890, 25, 12),
('default.png', 'Tank Model T-34', 'Detailed 1/35 scale tank model.', 1290, 15, 8),
('default.png', 'Fighter Jet F-16', '1/72 scale aircraft model.', 750, 30, 5),
('default.png', 'Ship Model Yamato', '1/700 scale battleship kit.', 1490, 10, 3),
('default.png', 'Car Model GT-R', '1/24 scale sports car model.', 990, 20, 6),
('default.png', 'Robot Diorama Base', 'Display base for model kits.', 350, 50, 15);

COMMIT;
