-- phpMyAdmin SQL Dump
-- version 4.2.7
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Feb 08, 2016 at 03:50 PM
-- Server version: 5.5.41-log
-- PHP Version: 5.6.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `vsdashboard`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE IF NOT EXISTS `addresses` (
`address_id` int(11) NOT NULL,
  `address_user` int(11) NOT NULL,
  `address_first_name` varchar(128) NOT NULL,
  `address_last_name` varchar(128) NOT NULL,
  `address_company_name` varchar(128) NOT NULL,
  `address_street` varchar(128) NOT NULL,
  `address_apartment` varchar(128) NOT NULL,
  `address_city` varchar(128) NOT NULL,
  `address_county` varchar(128) NOT NULL COMMENT 'or state',
  `address_postcode` varchar(128) NOT NULL COMMENT 'or zipcode',
  `address_email` varchar(128) NOT NULL,
  `address_phone` varchar(128) NOT NULL,
  `address_notes` varchar(128) NOT NULL,
  `address_type` enum('Billing','Shipping','Billing & Shipping') NOT NULL COMMENT 'What the address is used for',
  `address_date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `address_country` varchar(128) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE IF NOT EXISTS `coupons` (
`coupon_id` int(11) NOT NULL,
  `coupon_admin_id` int(32) NOT NULL COMMENT 'the admin account that created the coupon',
  `coupon_date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'date added to database',
  `coupon_date_start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'date the coupon will work',
  `coupon_date_end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'date the coupon will no longer work',
  `coupon_uses_allowed` int(32) NOT NULL COMMENT 'max amount of uses allowed',
  `coupon_code` varchar(32) NOT NULL COMMENT 'the code',
  `coupon_amount` float NOT NULL COMMENT 'amount off',
  `coupon_is_percent` tinyint(1) DEFAULT NULL COMMENT 'If true, coupon_amount is % off and not currency off'
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `login_history`
--

CREATE TABLE IF NOT EXISTS `login_history` (
`history_id` int(11) NOT NULL COMMENT 'The unique ID for the record',
  `history_user` int(11) NOT NULL COMMENT 'What user this login attempt belongs to',
  `history_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `history_http_user_agent` varchar(128) NOT NULL,
  `history_http_referer` varchar(128) DEFAULT NULL COMMENT 'Where the user came from',
  `history_ip` varchar(32) NOT NULL COMMENT 'The IP that requested the page'
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE IF NOT EXISTS `orders` (
`order_id` int(11) NOT NULL,
  `order_braintree_id` varchar(11) NOT NULL,
  `order_user` int(11) DEFAULT NULL,
  `order_non_user_email` varchar(128) DEFAULT NULL,
  `order_non_user_phone_number` varchar(18) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `order_status` enum('Reviewing','In Progress','Dispatched','Delivered','Declined') NOT NULL,
  `order_shipping_address` int(11) NOT NULL,
  `order_billing_address` int(11) NOT NULL,
  `order_sub_total` float NOT NULL,
  `order_shipping_total` float NOT NULL,
  `order_grand_total` float NOT NULL,
  `order_payment_type` varchar(64) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=237 ;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE IF NOT EXISTS `order_items` (
`items_id` int(11) NOT NULL,
  `items_order_id` varchar(11) NOT NULL,
  `items_product_id` int(11) NOT NULL,
  `items_name` varchar(128) NOT NULL,
  `items_price` float NOT NULL,
  `items_quantity` int(11) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=226 ;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE IF NOT EXISTS `products` (
`product_id` int(11) NOT NULL,
  `product_name` varchar(128) NOT NULL,
  `product_price` float NOT NULL,
  `product_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `product_image` varchar(128) NOT NULL,
  `product_description` text NOT NULL,
  `product_visibility` tinyint(1) NOT NULL,
  `product_keywords` varchar(258) NOT NULL,
  `product_options` varchar(256) NOT NULL,
  `product_image_one` varchar(256) NOT NULL,
  `product_image_two` varchar(256) NOT NULL,
  `product_image_three` varchar(256) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
`user_id` int(11) NOT NULL COMMENT 'auto incrementing user_id of each user, unique index',
  `user_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'user''s name, unique',
  `user_last_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `user_first_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `user_avatar` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_display_avatar` enum('Site Avatar','Gravatar','','') COLLATE utf8_unicode_ci NOT NULL,
  `user_password_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'user''s password in salted and hashed format',
  `user_email` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'user''s email, unique',
  `user_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'user''s activation status',
  `user_account_type` enum('normal','admin') COLLATE utf8_unicode_ci NOT NULL COMMENT 'If the user is an admin or not',
  `user_activation_hash` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'user''s email verification hash string',
  `user_password_reset_hash` char(40) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'user''s password reset code',
  `user_password_reset_timestamp` bigint(20) DEFAULT NULL COMMENT 'timestamp of the password reset request',
  `user_rememberme_token` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'user''s remember-me cookie token',
  `user_failed_logins` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'user''s failed login attemps',
  `user_last_failed_login` int(10) DEFAULT NULL COMMENT 'unix timestamp of last failed login attempt',
  `user_registration_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_registration_ip` varchar(39) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0.0.0.0'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='user data' AUTO_INCREMENT=27 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
 ADD PRIMARY KEY (`address_id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
 ADD PRIMARY KEY (`coupon_id`);

--
-- Indexes for table `login_history`
--
ALTER TABLE `login_history`
 ADD PRIMARY KEY (`history_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
 ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
 ADD PRIMARY KEY (`items_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
 ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
 ADD PRIMARY KEY (`user_id`), ADD UNIQUE KEY `user_name` (`user_name`), ADD UNIQUE KEY `user_email` (`user_email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
MODIFY `coupon_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'The unique ID for the record',AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=237;
--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
MODIFY `items_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=226;
--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'auto incrementing user_id of each user, unique index',AUTO_INCREMENT=27;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
