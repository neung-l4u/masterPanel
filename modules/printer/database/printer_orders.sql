-- Database table for printer orders
CREATE TABLE IF NOT EXISTS `printer_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(30) NOT NULL,
  `shop_name` varchar(200) NOT NULL,
  `address` text NOT NULL,
  `country` varchar(2) NOT NULL COMMENT 'AU or NZ',
  `printer_model` varchar(50) NOT NULL COMMENT 'TM-T82IIIL or TM-M30',
  `printer_full_name` varchar(255) NOT NULL,
  `price` varchar(50) NOT NULL,
  `supplier_email` varchar(100) NOT NULL,
  `order_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_order_date` (`order_date`),
  INDEX `idx_country` (`country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
