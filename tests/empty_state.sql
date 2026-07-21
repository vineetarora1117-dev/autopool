-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: SAPG
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Bcrypt hashed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'admin','$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy','2026-07-19 11:26:03');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booster_matrices`
--

DROP TABLE IF EXISTS `booster_matrices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booster_matrices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(8) NOT NULL,
  `booster_type` enum('booster_10','booster_20','booster_40','booster_80','booster_160','booster_320') NOT NULL,
  `upline_id` varchar(8) DEFAULT NULL COMMENT 'Direct upline in this booster matrix',
  `position_slot` tinyint(1) NOT NULL COMMENT '1-4 positions per level',
  `matrix_level` tinyint(1) NOT NULL COMMENT '1=Level 1 (4 slots), 2=Level 2 (16 slots)',
  `board_id` int(11) DEFAULT NULL COMMENT 'Groups nodes into individual 20-person boards',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_upline` (`upline_id`,`booster_type`),
  KEY `idx_booster` (`booster_type`),
  KEY `idx_board` (`board_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booster_matrices`
--

LOCK TABLES `booster_matrices` WRITE;
/*!40000 ALTER TABLE `booster_matrices` DISABLE KEYS */;
/*!40000 ALTER TABLE `booster_matrices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_ledger`
--

DROP TABLE IF EXISTS `company_ledger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_ledger` (
  `id` int(11) NOT NULL DEFAULT 1,
  `total_funds_received` decimal(15,2) DEFAULT 0.00 COMMENT 'Cumulative USDT deposited into system',
  `unutilized_funds` decimal(15,2) DEFAULT 0.00 COMMENT 'Total in all users main wallets (not spent)',
  `invested_funds` decimal(15,2) DEFAULT 0.00 COMMENT 'Total spent on packages/boosters',
  `total_usdt_paid_out` decimal(15,2) DEFAULT 0.00 COMMENT 'Total USDT that left system via withdrawals',
  `company_wallet_balance` decimal(15,2) DEFAULT 0.00 COMMENT 'Company earnings (fees + revenue + SA000001 sweep)',
  `total_payout_liability_main` decimal(15,2) DEFAULT 0.00 COMMENT 'Total sitting in all users package earning wallets',
  `total_payout_liability_booster` decimal(15,2) DEFAULT 0.00 COMMENT 'Total sitting in all users booster earning wallets',
  `total_held_sponsor_income` decimal(15,2) DEFAULT 0.00 COMMENT 'Total sponsor income held pending sponsor upgrade',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_ledger`
--

LOCK TABLES `company_ledger` WRITE;
/*!40000 ALTER TABLE `company_ledger` DISABLE KEYS */;
INSERT INTO `company_ledger` VALUES (1,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'2026-07-21 07:40:22');
/*!40000 ALTER TABLE `company_ledger` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deposit_requests`
--

DROP TABLE IF EXISTS `deposit_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deposit_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(8) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `tx_hash` varchar(255) DEFAULT NULL COMMENT 'Blockchain transaction hash',
  `proof_image` varchar(255) DEFAULT NULL COMMENT 'Path to uploaded proof screenshot',
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `admin_remarks` text DEFAULT NULL COMMENT 'Reason for rejection if applicable',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deposit_requests`
--

LOCK TABLES `deposit_requests` WRITE;
/*!40000 ALTER TABLE `deposit_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `deposit_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `package_matrices`
--

DROP TABLE IF EXISTS `package_matrices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `package_matrices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(8) NOT NULL,
  `package_type` enum('main_11','main_30','main_60','main_120','main_240','main_480') NOT NULL,
  `upline_id` varchar(8) DEFAULT NULL COMMENT 'Direct upline in this specific matrix',
  `position_slot` tinyint(1) NOT NULL COMMENT '1=Left, 2=Right',
  `matrix_level` int(11) DEFAULT 1 COMMENT 'Depth level in the matrix',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_upline` (`upline_id`,`package_type`),
  KEY `idx_package` (`package_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `package_matrices`
--

LOCK TABLES `package_matrices` WRITE;
/*!40000 ALTER TABLE `package_matrices` DISABLE KEYS */;
/*!40000 ALTER TABLE `package_matrices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_label` varchar(100) DEFAULT NULL COMMENT 'Human-readable label for admin UI',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'company_usdt_address','sdaasdasdasdassaddsadsa','Company USDT Wallet Address (TRC20)','2026-07-20 18:22:52'),(2,'company_qr_code_path','assets/company_qr.jpg','Company QR Code Image Path','2026-07-20 18:28:18'),(3,'min_withdrawal_amount','10','Minimum Withdrawal Amount ($)','2026-07-19 11:26:03'),(4,'max_withdrawal_amount','500','Maximum Withdrawal Amount ($)','2026-07-20 18:24:08'),(5,'withdrawal_enabled','1','Enable/Disable Withdrawals','2026-07-19 11:26:03'),(6,'registration_enabled','1','Enable/Disable New Registrations','2026-07-19 11:26:03');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_tickets`
--

DROP TABLE IF EXISTS `support_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(8) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('Open','In Progress','Closed') DEFAULT 'Open',
  `admin_reply` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_tickets`
--

LOCK TABLES `support_tickets` WRITE;
/*!40000 ALTER TABLE `support_tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(8) NOT NULL COMMENT 'The user this transaction belongs to',
  `transaction_type` enum('deposit','withdrawal','package_purchase','booster_purchase','autopool_income','sponsor_income','level_income','booster_income','reward_income','sponsor_income_held','sponsor_income_released','internal_transfer','admin_charge','company_revenue','company_sweep') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `wallet_type` varchar(20) DEFAULT NULL COMMENT 'e.g. main_deposit, earnings_11, booster_10',
  `status` enum('Pending','Approved','Rejected','Held','Released','Completed') DEFAULT 'Completed',
  `narration` text NOT NULL COMMENT 'Human-readable description of the transaction',
  `related_user_id` varchar(8) DEFAULT NULL COMMENT 'The other party involved (sponsor, buyer, etc.)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `blocked_by_user_id` varchar(8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_type` (`transaction_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`),
  KEY `idx_wallet` (`wallet_type`),
  KEY `idx_user_type` (`user_id`,`transaction_type`),
  KEY `idx_blocked_by` (`blocked_by_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_financial_summary`
--

DROP TABLE IF EXISTS `user_financial_summary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_financial_summary` (
  `user_id` varchar(8) NOT NULL,
  `my_package` decimal(10,2) DEFAULT 0.00 COMMENT 'Highest active package amount',
  `direct_team_count` int(11) DEFAULT 0 COMMENT 'Number of direct referrals',
  `total_active_team_count` int(11) DEFAULT 0 COMMENT 'All downlines with at least $11 package',
  `total_inactive_team_count` int(11) DEFAULT 0 COMMENT 'All downlines with no package',
  `strong_leg_count` int(11) DEFAULT 0 COMMENT 'Size of largest single leg',
  `other_legs_count` int(11) DEFAULT 0 COMMENT 'Sum of all other legs',
  `main_deposit_balance` decimal(15,2) DEFAULT 0.00,
  `earnings_11_wallet` decimal(15,2) DEFAULT 0.00,
  `earnings_30_wallet` decimal(15,2) DEFAULT 0.00,
  `earnings_60_wallet` decimal(15,2) DEFAULT 0.00,
  `earnings_120_wallet` decimal(15,2) DEFAULT 0.00,
  `earnings_240_wallet` decimal(15,2) DEFAULT 0.00,
  `earnings_480_wallet` decimal(15,2) DEFAULT 0.00,
  `booster_10_wallet` decimal(15,2) DEFAULT 0.00,
  `booster_20_wallet` decimal(15,2) DEFAULT 0.00,
  `booster_40_wallet` decimal(15,2) DEFAULT 0.00,
  `booster_80_wallet` decimal(15,2) DEFAULT 0.00,
  `booster_160_wallet` decimal(15,2) DEFAULT 0.00,
  `booster_320_wallet` decimal(15,2) DEFAULT 0.00,
  `total_direct_referral_income` decimal(15,2) DEFAULT 0.00,
  `total_team_level_income` decimal(15,2) DEFAULT 0.00,
  `total_global_autopool_income` decimal(15,2) DEFAULT 0.00,
  `total_booster_income` decimal(15,2) DEFAULT 0.00,
  `total_reward_income` decimal(15,2) DEFAULT 0.00,
  `total_withdrawal_amount` decimal(15,2) DEFAULT 0.00,
  `net_income` decimal(15,2) DEFAULT 0.00 COMMENT 'Total earned minus total withdrawn',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  CONSTRAINT `user_financial_summary_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_financial_summary`
--

LOCK TABLES `user_financial_summary` WRITE;
/*!40000 ALTER TABLE `user_financial_summary` DISABLE KEYS */;
INSERT INTO `user_financial_summary` VALUES ('SA000001',0.00,0,0,0,0,0,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'2026-07-21 07:40:22');
/*!40000 ALTER TABLE `user_financial_summary` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_packages`
--

DROP TABLE IF EXISTS `user_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(8) NOT NULL,
  `package_type` enum('main_11','main_30','main_60','main_120','main_240','main_480','booster_10','booster_20','booster_40','booster_80','booster_160','booster_320') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `activated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `funded_by` varchar(8) DEFAULT NULL COMMENT 'user_id of the person who paid for this package',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_package` (`user_id`,`package_type`),
  KEY `idx_user` (`user_id`),
  KEY `idx_type` (`package_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_packages`
--

LOCK TABLES `user_packages` WRITE;
/*!40000 ALTER TABLE `user_packages` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(8) NOT NULL COMMENT 'Alphanumeric ID: SA followed by 6 digits',
  `sponsor_id` varchar(8) DEFAULT NULL COMMENT 'References parent user_id in sponsor tree',
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Bcrypt hashed',
  `status` enum('Active','Inactive','Blocked') DEFAULT 'Inactive' COMMENT 'Inactive = registered but no package',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `wallet_address` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `idx_sponsor` (`sponsor_id`),
  KEY `idx_status` (`status`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'SA000001',NULL,'SAPG','admin@sapg.com','0000000000','$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy','Active','2026-07-19 11:26:03','2026-07-21 07:40:22','ggjhghjgjg');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wallet_configurations`
--

DROP TABLE IF EXISTS `wallet_configurations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallet_configurations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wallet_type` varchar(20) NOT NULL,
  `wallet_label` varchar(50) NOT NULL COMMENT 'Human-readable label',
  `internal_transfer_fee_percent` decimal(5,2) DEFAULT 5.00,
  `external_withdrawal_fee_percent` decimal(5,2) DEFAULT 5.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `wallet_type` (`wallet_type`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wallet_configurations`
--

LOCK TABLES `wallet_configurations` WRITE;
/*!40000 ALTER TABLE `wallet_configurations` DISABLE KEYS */;
INSERT INTO `wallet_configurations` VALUES (1,'earnings_11','$11 Package Wallet',5.00,5.00,'2026-07-19 11:26:03'),(2,'earnings_30','$30 Package Wallet',5.00,5.00,'2026-07-19 11:26:03'),(3,'earnings_60','$60 Package Wallet',5.00,5.00,'2026-07-19 11:26:03'),(4,'earnings_120','$120 Package Wallet',5.00,5.00,'2026-07-19 11:26:03'),(5,'earnings_240','$240 Package Wallet',5.00,5.00,'2026-07-19 11:26:03'),(6,'earnings_480','$480 Package Wallet',5.00,5.00,'2026-07-19 11:26:03'),(7,'booster_10','$10 Booster Wallet',5.00,5.00,'2026-07-19 11:26:03'),(8,'booster_20','$20 Booster Wallet',5.00,5.00,'2026-07-19 11:26:03'),(9,'booster_40','$40 Booster Wallet',5.00,5.00,'2026-07-19 11:26:03'),(10,'booster_80','$80 Booster Wallet',5.00,5.00,'2026-07-19 11:26:03'),(11,'booster_160','$160 Booster Wallet',5.00,5.00,'2026-07-19 11:26:03'),(12,'booster_320','$320 Booster Wallet',5.00,5.00,'2026-07-19 11:26:03');
/*!40000 ALTER TABLE `wallet_configurations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `withdrawal_requests`
--

DROP TABLE IF EXISTS `withdrawal_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `withdrawal_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(8) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `wallet_type` varchar(20) NOT NULL COMMENT 'Which earning wallet this is from',
  `fee_amount` decimal(15,2) DEFAULT 0.00,
  `net_amount` decimal(15,2) DEFAULT 0.00 COMMENT 'Amount after fee deduction',
  `destination_address` varchar(255) NOT NULL COMMENT 'External USDT wallet address',
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `admin_remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `withdrawal_requests`
--

LOCK TABLES `withdrawal_requests` WRITE;
/*!40000 ALTER TABLE `withdrawal_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `withdrawal_requests` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-21 13:12:45
