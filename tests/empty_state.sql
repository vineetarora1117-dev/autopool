-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: SAPG
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
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
  `upline_node_id` int(11) DEFAULT NULL,
  `position_slot` tinyint(1) NOT NULL COMMENT '1-4 positions per level',
  `matrix_level` tinyint(1) NOT NULL COMMENT '1=Level 1 (4 slots), 2=Level 2 (16 slots)',
  `board_id` int(11) DEFAULT NULL COMMENT 'Groups nodes into individual 20-person boards',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_upline` (`upline_id`,`booster_type`),
  KEY `idx_booster` (`booster_type`),
  KEY `idx_board` (`board_id`),
  KEY `fk_bm_upline_node` (`upline_node_id`),
  CONSTRAINT `fk_bm_upline_node` FOREIGN KEY (`upline_node_id`) REFERENCES `booster_matrices` (`id`) ON DELETE SET NULL
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
-- Table structure for table `booster_transactions`
--

DROP TABLE IF EXISTS `booster_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booster_transactions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `booster_id` bigint(20) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `from_booster_id` bigint(20) DEFAULT NULL,
  `from_user_id` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('user_earning','company_revenue','auto_reentry') NOT NULL,
  `narration` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `booster_id` (`booster_id`),
  KEY `user_id` (`user_id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booster_transactions`
--

LOCK TABLES `booster_transactions` WRITE;
/*!40000 ALTER TABLE `booster_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `booster_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_ledger`
--

DROP TABLE IF EXISTS `company_ledger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_ledger` (
  `id` int(11) NOT NULL DEFAULT 1,
  `total_funds_received` decimal(15,4) DEFAULT 0.0000,
  `unutilized_funds` decimal(15,4) DEFAULT 0.0000,
  `invested_funds` decimal(15,4) DEFAULT 0.0000,
  `total_usdt_paid_out` decimal(15,4) DEFAULT 0.0000,
  `company_wallet_balance` decimal(15,4) DEFAULT 0.0000,
  `total_payout_liability_main` decimal(15,4) DEFAULT 0.0000,
  `total_payout_liability_booster` decimal(15,4) DEFAULT 0.0000,
  `total_held_sponsor_income` decimal(15,4) DEFAULT 0.0000,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_ledger`
--

LOCK TABLES `company_ledger` WRITE;
/*!40000 ALTER TABLE `company_ledger` DISABLE KEYS */;
INSERT INTO `company_ledger` VALUES (1,0.0000,0.0000,0.0000,0.0000,0.0000,0.0000,0.0000,0.0000,'2026-09-16 08:19:02');
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
  `upline_node_id` int(11) DEFAULT NULL,
  `position_slot` tinyint(1) NOT NULL COMMENT '1=Left, 2=Right',
  `matrix_level` int(11) DEFAULT 1 COMMENT 'Depth level in the matrix',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_upline` (`upline_id`,`package_type`),
  KEY `idx_package` (`package_type`),
  KEY `fk_pm_upline_node` (`upline_node_id`),
  CONSTRAINT `fk_pm_upline_node` FOREIGN KEY (`upline_node_id`) REFERENCES `package_matrices` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `package_matrices`
--

LOCK TABLES `package_matrices` WRITE;
/*!40000 ALTER TABLE `package_matrices` DISABLE KEYS */;
INSERT INTO `package_matrices` VALUES (1,'SA000001','main_11',NULL,NULL,1,1,'2026-09-16 08:19:02'),(2,'SA000001','main_30',NULL,NULL,1,1,'2026-09-16 08:19:02'),(3,'SA000001','main_60',NULL,NULL,1,1,'2026-09-16 08:19:02'),(4,'SA000001','main_120',NULL,NULL,1,1,'2026-09-16 08:19:02'),(5,'SA000001','main_240',NULL,NULL,1,1,'2026-09-16 08:19:02'),(6,'SA000001','main_480',NULL,NULL,1,1,'2026-09-16 08:19:02');
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'company_usdt_address','0xb6dfCd1815423600BD8e969ADC44D59f93F57b6e','Company USDT Wallet Address (BEP20)','2026-08-04 14:40:55'),(2,'company_qr_code_path','assets/company_qr.png','Company QR Code Image Path','2026-08-06 15:56:22'),(3,'min_withdrawal_amount','10','Minimum Withdrawal Amount ($)','2026-08-06 15:57:39'),(4,'max_withdrawal_amount','500','Maximum Withdrawal Amount ($)','2026-07-20 18:24:08'),(5,'withdrawal_enabled','1','Enable/Disable Withdrawals','2026-07-19 11:26:03'),(6,'registration_enabled','1','Enable/Disable New Registrations','2026-08-09 01:13:32'),(7,'fund_transfer_fee_percent','10','Fund Transfer Fee (%)','2026-08-04 09:54:03');
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
  `amount` decimal(15,4) NOT NULL,
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
-- Table structure for table `user_boosters`
--

DROP TABLE IF EXISTS `user_boosters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_boosters` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) NOT NULL,
  `upline_booster_id` bigint(20) DEFAULT NULL,
  `downline_count` int(11) DEFAULT 0,
  `purchase_type` enum('manual','reentry') NOT NULL DEFAULT 'manual',
  `status` enum('active','completed') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `upline_booster_id` (`upline_booster_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_boosters`
--

LOCK TABLES `user_boosters` WRITE;
/*!40000 ALTER TABLE `user_boosters` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_boosters` ENABLE KEYS */;
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
  `main_deposit_balance` decimal(15,4) DEFAULT 0.0000,
  `earnings_11_wallet` decimal(15,4) DEFAULT 0.0000,
  `earnings_30_wallet` decimal(15,4) DEFAULT 0.0000,
  `earnings_60_wallet` decimal(15,4) DEFAULT 0.0000,
  `earnings_120_wallet` decimal(15,4) DEFAULT 0.0000,
  `earnings_240_wallet` decimal(15,4) DEFAULT 0.0000,
  `earnings_480_wallet` decimal(15,4) DEFAULT 0.0000,
  `booster_10_wallet` decimal(15,4) DEFAULT 0.0000,
  `booster_20_wallet` decimal(15,4) DEFAULT 0.0000,
  `booster_40_wallet` decimal(15,4) DEFAULT 0.0000,
  `booster_80_wallet` decimal(15,4) DEFAULT 0.0000,
  `booster_160_wallet` decimal(15,4) DEFAULT 0.0000,
  `booster_320_wallet` decimal(15,4) DEFAULT 0.0000,
  `total_direct_referral_income` decimal(15,4) DEFAULT 0.0000,
  `total_team_level_income` decimal(15,4) DEFAULT 0.0000,
  `total_global_autopool_income` decimal(15,4) DEFAULT 0.0000,
  `total_booster_income` decimal(15,4) DEFAULT 0.0000,
  `total_reward_income` decimal(15,4) DEFAULT 0.0000,
  `total_withdrawal_amount` decimal(15,4) DEFAULT 0.0000,
  `net_income` decimal(15,4) DEFAULT 0.0000,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `booster_wallet` decimal(15,4) DEFAULT 0.0000,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `user_financial_summary_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_financial_summary`
--

LOCK TABLES `user_financial_summary` WRITE;
/*!40000 ALTER TABLE `user_financial_summary` DISABLE KEYS */;
INSERT INTO `user_financial_summary` VALUES ('SA000001',480.00,0,0,0,0,0,0.0000,0.0000,0.0000,0.0000,0.0000,0.0000,0.0000,0.0000,0.0000,0.0000,0.0000,0.0000,0.0000,0.0000,0.0000,0.0000,0.0000,0.0000,0.0000,0.0000,'2026-09-16 08:19:02',0.0000);
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
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_packages`
--

LOCK TABLES `user_packages` WRITE;
/*!40000 ALTER TABLE `user_packages` DISABLE KEYS */;
INSERT INTO `user_packages` VALUES (1,'SA000001','main_11',1,'2026-08-07 10:15:28','SA000001'),(2,'SA000001','main_30',1,'2026-08-07 10:15:32','SA000001'),(3,'SA000001','main_60',1,'2026-08-07 10:15:37','SA000001'),(4,'SA000001','main_120',1,'2026-08-07 10:16:50','SA000001'),(5,'SA000001','main_240',1,'2026-08-07 10:16:55','SA000001'),(6,'SA000001','main_480',1,'2026-08-07 10:17:51','SA000001'),(8,'SA000001','booster_10',1,'2026-08-07 10:21:04','SA000001'),(9,'SA000001','booster_20',1,'2026-08-07 10:21:08','SA000001'),(10,'SA000001','booster_40',1,'2026-08-07 10:21:11','SA000001'),(11,'SA000001','booster_80',1,'2026-08-07 10:21:15','SA000001'),(12,'SA000001','booster_160',1,'2026-08-07 10:22:16','SA000001'),(13,'SA000001','booster_320',1,'2026-08-07 10:22:20','SA000001');
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
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'SA000001',NULL,'SAPG','admin@asmultisoftware.in','0000000000','$2y$10$UUJymPzhECKsU98iwAHt..IbBqbM1a3nhrueH3Qp5vOLG5I9WiHBi','Active','2026-07-19 00:26:03','2026-08-05 13:08:07',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wallet_configurations`
--

LOCK TABLES `wallet_configurations` WRITE;
/*!40000 ALTER TABLE `wallet_configurations` DISABLE KEYS */;
INSERT INTO `wallet_configurations` VALUES (1,'earnings_11','$11 Package Wallet',10.00,10.00,'2026-08-04 16:03:07'),(2,'earnings_30','$30 Package Wallet',10.00,10.00,'2026-08-04 16:03:14'),(3,'earnings_60','$60 Package Wallet',5.00,5.00,'2026-07-19 11:26:03'),(4,'earnings_120','$120 Package Wallet',5.00,5.00,'2026-07-19 11:26:03'),(5,'earnings_240','$240 Package Wallet',5.00,5.00,'2026-07-19 11:26:03'),(6,'earnings_480','$480 Package Wallet',5.00,5.00,'2026-07-19 11:26:03'),(7,'booster_10','$10 Booster Wallet',5.00,5.00,'2026-07-19 11:26:03'),(8,'booster_20','$20 Booster Wallet',5.00,5.00,'2026-07-19 11:26:03'),(9,'booster_40','$40 Booster Wallet',5.00,5.00,'2026-07-19 11:26:03'),(10,'booster_80','$80 Booster Wallet',5.00,5.00,'2026-07-19 11:26:03'),(11,'booster_160','$160 Booster Wallet',5.00,5.00,'2026-07-19 11:26:03'),(12,'booster_320','$320 Booster Wallet',5.00,5.00,'2026-07-19 11:26:03'),(13,'booster_wallet','',5.00,10.00,'2026-09-15 07:31:11');
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

-- Dump completed on 2026-09-16 13:49:29
