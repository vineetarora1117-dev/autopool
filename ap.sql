-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 07, 2026 at 08:48 AM
-- Server version: 11.8.8-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u983618620_autopool`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Bcrypt hashed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', '2026-07-19 11:26:03');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booster_matrices`
--

CREATE TABLE `booster_matrices` (
  `id` int(11) NOT NULL,
  `user_id` varchar(8) NOT NULL,
  `booster_type` enum('booster_10','booster_20','booster_40','booster_80','booster_160','booster_320') NOT NULL,
  `upline_id` varchar(8) DEFAULT NULL COMMENT 'Direct upline in this booster matrix',
  `position_slot` tinyint(1) NOT NULL COMMENT '1-4 positions per level',
  `matrix_level` tinyint(1) NOT NULL COMMENT '1=Level 1 (4 slots), 2=Level 2 (16 slots)',
  `board_id` int(11) DEFAULT NULL COMMENT 'Groups nodes into individual 20-person boards',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booster_matrices`
--

INSERT INTO `booster_matrices` (`id`, `user_id`, `booster_type`, `upline_id`, `position_slot`, `matrix_level`, `board_id`, `created_at`) VALUES
(1, 'SA000001', 'booster_10', NULL, 1, 1, NULL, '2026-08-04 16:24:52'),
(2, 'SA176865', 'booster_10', 'SA000001', 1, 2, NULL, '2026-08-04 16:24:52'),
(3, 'SA000001', 'booster_10', 'SA000001', 2, 2, NULL, '2026-08-04 16:27:06'),
(4, 'SA559719', 'booster_10', 'SA176865', 1, 3, NULL, '2026-08-04 16:38:49');

-- --------------------------------------------------------

--
-- Table structure for table `company_ledger`
--

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `company_ledger`
--

INSERT INTO `company_ledger` (`id`, `total_funds_received`, `unutilized_funds`, `invested_funds`, `total_usdt_paid_out`, `company_wallet_balance`, `total_payout_liability_main`, `total_payout_liability_booster`, `total_held_sponsor_income`, `updated_at`) VALUES
(1, 259.0000, 32.0000, 227.0000, 22.5000, 16.0000, 41.8500, 0.0000, 0.0000, '2026-08-07 05:27:03');

-- --------------------------------------------------------

--
-- Table structure for table `deposit_requests`
--

CREATE TABLE `deposit_requests` (
  `id` int(11) NOT NULL,
  `user_id` varchar(8) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `tx_hash` varchar(255) DEFAULT NULL COMMENT 'Blockchain transaction hash',
  `proof_image` varchar(255) DEFAULT NULL COMMENT 'Path to uploaded proof screenshot',
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `admin_remarks` text DEFAULT NULL COMMENT 'Reason for rejection if applicable',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deposit_requests`
--

INSERT INTO `deposit_requests` (`id`, `user_id`, `amount`, `tx_hash`, `proof_image`, `status`, `admin_remarks`, `created_at`, `updated_at`) VALUES
(1, 'SA176865', 11.00, 'Gffg', 'assets/uploads/proofs/SA176865_1785860331.jpg', 'Approved', NULL, '2026-08-04 16:18:51', '2026-08-04 16:19:20'),
(2, 'SA176865', 10.00, 'Dddgg', 'assets/uploads/proofs/SA176865_1785860619.jpg', 'Approved', NULL, '2026-08-04 16:23:39', '2026-08-04 16:23:59'),
(3, 'SA000001', 10.00, 'Ffhfcc', 'assets/uploads/proofs/SA000001_1785860789.jpg', 'Approved', NULL, '2026-08-04 16:26:29', '2026-08-04 16:26:41'),
(4, 'SA559719', 21.00, 'Chfbjhb', 'assets/uploads/proofs/SA559719_1785861407.jpg', 'Approved', NULL, '2026-08-04 16:36:47', '2026-08-04 16:37:34'),
(5, 'SA135056', 21.00, 'Hiii', 'assets/uploads/proofs/SA135056_1785946951.jpg', 'Approved', NULL, '2026-08-05 16:22:31', '2026-08-05 16:23:05'),
(6, 'SA000001', 11.00, 'Gigvb', 'assets/uploads/proofs/SA000001_1785949442.jpg', 'Approved', NULL, '2026-08-05 17:04:02', '2026-08-05 17:04:18'),
(7, 'SA176865', 30.00, 'Sffvc', 'assets/uploads/proofs/SA176865_1785951917.jpg', 'Approved', NULL, '2026-08-05 17:45:17', '2026-08-05 17:47:32'),
(8, 'SA285964', 11.00, '1234567890-', 'assets/uploads/proofs/SA285964_1786001475.png', 'Approved', NULL, '2026-08-06 07:31:15', '2026-08-06 07:35:09'),
(9, 'SA016914', 11.00, '34567890-=', 'assets/uploads/proofs/SA016914_1786001706.png', 'Approved', NULL, '2026-08-06 07:35:06', '2026-08-06 07:35:17'),
(10, 'SA016914', 11.00, '34567890-=', 'assets/uploads/proofs/SA016914_1786001706.png', 'Approved', NULL, '2026-08-06 07:35:06', '2026-08-06 07:35:21'),
(11, 'SA000001', 90.00, 'Company ', 'assets/uploads/proofs/SA000001_1786003354.jpg', 'Approved', NULL, '2026-08-06 08:02:34', '2026-08-06 08:02:57'),
(12, 'SA514171', 11.00, '0x418107A2B80CaFae5E2Ab09353b1d5a18bE315A5', 'assets/uploads/proofs/SA514171_1786028081.jpg', 'Approved', NULL, '2026-08-06 14:54:41', '2026-08-06 14:57:27'),
(13, 'SA135056', 11.00, 'Your', 'assets/uploads/proofs/SA135056_1786079519.jpg', 'Approved', NULL, '2026-08-07 05:11:59', '2026-08-07 05:13:10');

-- --------------------------------------------------------

--
-- Table structure for table `package_matrices`
--

CREATE TABLE `package_matrices` (
  `id` int(11) NOT NULL,
  `user_id` varchar(8) NOT NULL,
  `package_type` enum('main_11','main_30','main_60','main_120','main_240','main_480') NOT NULL,
  `upline_id` varchar(8) DEFAULT NULL COMMENT 'Direct upline in this specific matrix',
  `position_slot` tinyint(1) NOT NULL COMMENT '1=Left, 2=Right',
  `matrix_level` int(11) DEFAULT 1 COMMENT 'Depth level in the matrix',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `package_matrices`
--

INSERT INTO `package_matrices` (`id`, `user_id`, `package_type`, `upline_id`, `position_slot`, `matrix_level`, `created_at`) VALUES
(1, 'SA000001', 'main_11', NULL, 1, 1, '2026-08-04 16:19:54'),
(2, 'SA176865', 'main_11', 'SA000001', 1, 2, '2026-08-04 16:19:54'),
(3, 'SA559719', 'main_11', 'SA000001', 2, 2, '2026-08-04 16:38:27'),
(4, 'SA135056', 'main_11', 'SA176865', 1, 3, '2026-08-05 16:24:39'),
(5, 'SA000001', 'main_11', 'SA176865', 2, 3, '2026-08-05 17:04:59'),
(6, 'SA000001', 'main_30', NULL, 1, 1, '2026-08-05 17:47:57'),
(7, 'SA176865', 'main_30', 'SA000001', 1, 2, '2026-08-05 17:47:57'),
(8, 'SA016914', 'main_11', 'SA559719', 1, 3, '2026-08-06 07:36:02'),
(9, 'SA000001', 'main_30', 'SA000001', 2, 2, '2026-08-06 08:03:28'),
(10, 'SA000001', 'main_60', NULL, 1, 1, '2026-08-06 08:03:33'),
(11, 'SA000001', 'main_60', 'SA000001', 1, 2, '2026-08-06 08:03:33'),
(12, 'SA285964', 'main_11', 'SA559719', 2, 3, '2026-08-06 14:27:15'),
(13, 'SA514171', 'main_11', 'SA135056', 1, 4, '2026-08-06 14:59:36');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_label` varchar(100) DEFAULT NULL COMMENT 'Human-readable label for admin UI',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_label`, `updated_at`) VALUES
(1, 'company_usdt_address', '0xb6dfCd1815423600BD8e969ADC44D59f93F57b6e', 'Company USDT Wallet Address (BEP20)', '2026-08-04 14:40:55'),
(2, 'company_qr_code_path', 'assets/company_qr.png', 'Company QR Code Image Path', '2026-08-06 15:56:22'),
(3, 'min_withdrawal_amount', '10', 'Minimum Withdrawal Amount ($)', '2026-08-06 15:57:39'),
(4, 'max_withdrawal_amount', '500', 'Maximum Withdrawal Amount ($)', '2026-07-20 18:24:08'),
(5, 'withdrawal_enabled', '1', 'Enable/Disable Withdrawals', '2026-07-19 11:26:03'),
(6, 'registration_enabled', '1', 'Enable/Disable New Registrations', '2026-07-19 11:26:03'),
(7, 'fund_transfer_fee_percent', '10', 'Fund Transfer Fee (%)', '2026-08-04 09:54:03');

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `user_id` varchar(8) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('Open','In Progress','Closed') DEFAULT 'Open',
  `admin_reply` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` varchar(8) NOT NULL COMMENT 'The user this transaction belongs to',
  `transaction_type` enum('deposit','withdrawal','package_purchase','booster_purchase','autopool_income','sponsor_income','level_income','booster_income','reward_income','sponsor_income_held','sponsor_income_released','internal_transfer','admin_charge','company_revenue','company_sweep') NOT NULL,
  `amount` decimal(15,4) NOT NULL,
  `wallet_type` varchar(20) DEFAULT NULL COMMENT 'e.g. main_deposit, earnings_11, booster_10',
  `status` enum('Pending','Approved','Rejected','Held','Released','Completed') DEFAULT 'Completed',
  `narration` text NOT NULL COMMENT 'Human-readable description of the transaction',
  `related_user_id` varchar(8) DEFAULT NULL COMMENT 'The other party involved (sponsor, buyer, etc.)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `blocked_by_user_id` varchar(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `transaction_type`, `amount`, `wallet_type`, `status`, `narration`, `related_user_id`, `created_at`, `blocked_by_user_id`) VALUES
(1, 'SA176865', 'deposit', 11.0000, NULL, 'Pending', 'Deposit request of $11.00 submitted. TxHash: Gffg', NULL, '2026-08-04 16:18:51', NULL),
(2, 'SA176865', 'deposit', 11.0000, 'main_deposit', 'Completed', 'Deposit of $11.00 approved by Admin', NULL, '2026-08-04 16:19:20', NULL),
(3, 'SA000001', 'deposit', 11.0000, 'company_wallet', 'Completed', 'Fund approved for user SA176865', NULL, '2026-08-04 16:19:20', NULL),
(4, 'SA176865', 'package_purchase', 11.0000, 'main_deposit', 'Completed', 'Purchased $11 Package — self activation', 'SA176865', '2026-08-04 16:19:54', NULL),
(5, 'SA000001', 'sponsor_income', 5.0000, 'earnings_11_wallet', 'Completed', 'Sponsor income $5 from SA176865 activating $11 Package', 'SA176865', '2026-08-04 16:19:54', NULL),
(6, 'SA000001', 'level_income', 0.1000, 'earnings_11_wallet', 'Completed', 'Level income $0.1 from SA176865 — Level 1 of $11 Package tree', 'SA176865', '2026-08-04 16:19:54', NULL),
(7, 'SA000001', 'reward_income', 1.0000, NULL, 'Completed', 'Reward reserve contribution $1 from SA176865 activating $11 Package', 'SA176865', '2026-08-04 16:19:54', NULL),
(8, 'SA176865', 'deposit', 10.0000, NULL, 'Pending', 'Deposit request of $10.00 submitted. TxHash: Dddgg', NULL, '2026-08-04 16:23:39', NULL),
(9, 'SA176865', 'deposit', 10.0000, 'main_deposit', 'Completed', 'Deposit of $10.00 approved by Admin', NULL, '2026-08-04 16:23:59', NULL),
(10, 'SA000001', 'deposit', 10.0000, 'company_wallet', 'Completed', 'Fund approved for user SA176865', NULL, '2026-08-04 16:23:59', NULL),
(11, 'SA176865', 'booster_purchase', 10.0000, 'main_deposit', 'Completed', 'Purchased 10 Booster — self activation', 'SA176865', '2026-08-04 16:24:52', NULL),
(12, 'SA000001', 'deposit', 10.0000, NULL, 'Pending', 'Deposit request of $10.00 submitted. TxHash: Ffhfcc', NULL, '2026-08-04 16:26:29', NULL),
(13, 'SA000001', 'deposit', 10.0000, 'main_deposit', 'Completed', 'Deposit of $10.00 approved by Admin', NULL, '2026-08-04 16:26:41', NULL),
(14, 'SA000001', 'deposit', 10.0000, 'company_wallet', 'Completed', 'Fund approved for user SA000001', NULL, '2026-08-04 16:26:41', NULL),
(15, 'SA000001', 'booster_purchase', 10.0000, 'main_deposit', 'Completed', 'Purchased 10 Booster — self activation', 'SA000001', '2026-08-04 16:27:06', NULL),
(16, 'SA559719', 'deposit', 21.0000, NULL, 'Pending', 'Deposit request of $21.00 submitted. TxHash: Chfbjhb', NULL, '2026-08-04 16:36:47', NULL),
(17, 'SA559719', 'deposit', 21.0000, 'main_deposit', 'Completed', 'Deposit of $21.00 approved by Admin', NULL, '2026-08-04 16:37:34', NULL),
(18, 'SA000001', 'deposit', 21.0000, 'company_wallet', 'Completed', 'Fund approved for user SA559719', NULL, '2026-08-04 16:37:34', NULL),
(19, 'SA559719', 'package_purchase', 11.0000, 'main_deposit', 'Completed', 'Purchased $11 Package — self activation', 'SA559719', '2026-08-04 16:38:27', NULL),
(20, 'SA000001', 'autopool_income', 0.2500, 'earnings_11_wallet', 'Completed', 'Autopool pair completion income $0.25 from pair in $11 Matrix (Upline L1)', 'SA559719', '2026-08-04 16:38:27', NULL),
(21, 'SA176865', 'sponsor_income', 5.0000, 'earnings_11_wallet', 'Completed', 'Sponsor income $5 from SA559719 activating $11 Package', 'SA559719', '2026-08-04 16:38:27', NULL),
(22, 'SA176865', 'level_income', 0.1000, 'earnings_11_wallet', 'Pending', 'Level income $0.1 from SA559719 — Level 1 of $11 Package tree', 'SA559719', '2026-08-04 16:38:27', 'SA176865'),
(23, 'SA000001', 'level_income', 0.1000, 'earnings_11_wallet', 'Completed', 'Level income $0.1 from SA559719 — Level 2 of $11 Package tree', 'SA559719', '2026-08-04 16:38:27', NULL),
(24, 'SA000001', 'reward_income', 1.0000, NULL, 'Completed', 'Reward reserve contribution $1 from SA559719 activating $11 Package', 'SA559719', '2026-08-04 16:38:27', NULL),
(25, 'SA559719', 'booster_purchase', 10.0000, 'main_deposit', 'Completed', 'Purchased 10 Booster — self activation', 'SA559719', '2026-08-04 16:38:49', NULL),
(26, 'SA135056', 'deposit', 21.0000, NULL, 'Pending', 'Deposit request of $21.00 submitted. TxHash: Hiii', NULL, '2026-08-05 16:22:31', NULL),
(27, 'SA135056', 'deposit', 21.0000, 'main_deposit', 'Completed', 'Deposit of $21.00 approved by Admin', NULL, '2026-08-05 16:23:05', NULL),
(28, 'SA000001', 'deposit', 21.0000, 'company_wallet', 'Completed', 'Fund approved for user SA135056', NULL, '2026-08-05 16:23:05', NULL),
(29, 'SA135056', 'package_purchase', 11.0000, 'main_deposit', 'Completed', 'Purchased $11 Package — self activation', 'SA135056', '2026-08-05 16:24:39', NULL),
(30, 'SA176865', 'sponsor_income', 5.0000, 'earnings_11_wallet', 'Completed', 'Sponsor income $5 from SA135056 activating $11 Package', 'SA135056', '2026-08-05 16:24:39', NULL),
(31, 'SA176865', 'level_income', 0.1000, 'earnings_11_wallet', 'Pending', 'Level income $0.1 from SA135056 — Level 1 of $11 Package tree', 'SA135056', '2026-08-05 16:24:39', 'SA176865'),
(32, 'SA000001', 'level_income', 0.1000, 'earnings_11_wallet', 'Completed', 'Level income $0.1 from SA135056 — Level 2 of $11 Package tree', 'SA135056', '2026-08-05 16:24:39', NULL),
(33, 'SA000001', 'reward_income', 1.0000, NULL, 'Completed', 'Reward reserve contribution $1 from SA135056 activating $11 Package', 'SA135056', '2026-08-05 16:24:39', NULL),
(34, 'SA176865', 'withdrawal', 10.0000, 'earnings_11_wallet', 'Pending', 'Withdrawal request of $10.00 submitted (Net: $9.00) to address 0xBFc95c...', NULL, '2026-08-05 16:29:55', NULL),
(35, 'SA000001', 'deposit', 11.0000, NULL, 'Pending', 'Deposit request of $11.00 submitted. TxHash: Gigvb', NULL, '2026-08-05 17:04:02', NULL),
(36, 'SA000001', 'deposit', 11.0000, 'main_deposit', 'Completed', 'Deposit of $11.00 approved by Admin', NULL, '2026-08-05 17:04:18', NULL),
(37, 'SA000001', 'deposit', 11.0000, 'company_wallet', 'Completed', 'Fund approved for user SA000001', NULL, '2026-08-05 17:04:18', NULL),
(38, 'SA000001', 'package_purchase', 11.0000, 'main_deposit', 'Completed', 'Purchased $11 Package — self activation', 'SA000001', '2026-08-05 17:04:59', NULL),
(39, 'SA000001', 'reward_income', 1.0000, NULL, 'Completed', 'Reward reserve contribution $1 from SA000001 activating $11 Package', 'SA000001', '2026-08-05 17:04:59', NULL),
(40, 'SA176865', 'deposit', 30.0000, NULL, 'Pending', 'Deposit request of $30.00 submitted. TxHash: Sffvc', NULL, '2026-08-05 17:45:17', NULL),
(41, 'SA176865', 'deposit', 30.0000, 'main_deposit', 'Completed', 'Deposit of $30.00 approved by Admin', NULL, '2026-08-05 17:47:32', NULL),
(42, 'SA000001', 'deposit', 30.0000, 'company_wallet', 'Completed', 'Fund approved for user SA176865', NULL, '2026-08-05 17:47:32', NULL),
(43, 'SA176865', 'package_purchase', 30.0000, 'main_deposit', 'Completed', 'Purchased $30 Package — self activation', 'SA176865', '2026-08-05 17:47:57', NULL),
(44, 'SA000001', 'sponsor_income', 10.0000, 'earnings_30_wallet', 'Completed', 'Sponsor income $10 from SA176865 activating $30 Package', 'SA176865', '2026-08-05 17:47:57', NULL),
(45, 'SA000001', 'level_income', 0.3000, 'earnings_30_wallet', 'Completed', 'Level income $0.3 from SA176865 — Level 1 of $30 Package tree', 'SA176865', '2026-08-05 17:47:57', NULL),
(46, 'SA000001', 'company_sweep', 4.0000, NULL, 'Completed', 'Company revenue $4 from SA176865 activating $30 Package', 'SA176865', '2026-08-05 17:47:57', NULL),
(47, 'SA285964', 'deposit', 11.0000, NULL, 'Pending', 'Deposit request of $11.00 submitted. TxHash: 1234567890-', NULL, '2026-08-06 07:31:15', NULL),
(48, 'SA016914', 'deposit', 11.0000, NULL, 'Pending', 'Deposit request of $11.00 submitted. TxHash: 34567890-=', NULL, '2026-08-06 07:35:06', NULL),
(49, 'SA016914', 'deposit', 11.0000, NULL, 'Pending', 'Deposit request of $11.00 submitted. TxHash: 34567890-=', NULL, '2026-08-06 07:35:06', NULL),
(50, 'SA285964', 'deposit', 11.0000, 'main_deposit', 'Completed', 'Deposit of $11.00 approved by Admin', NULL, '2026-08-06 07:35:09', NULL),
(51, 'SA000001', 'deposit', 11.0000, 'company_wallet', 'Completed', 'Fund approved for user SA285964', NULL, '2026-08-06 07:35:09', NULL),
(52, 'SA016914', 'deposit', 11.0000, 'main_deposit', 'Completed', 'Deposit of $11.00 approved by Admin', NULL, '2026-08-06 07:35:17', NULL),
(53, 'SA000001', 'deposit', 11.0000, 'company_wallet', 'Completed', 'Fund approved for user SA016914', NULL, '2026-08-06 07:35:17', NULL),
(54, 'SA016914', 'deposit', 11.0000, 'main_deposit', 'Completed', 'Deposit of $11.00 approved by Admin', NULL, '2026-08-06 07:35:21', NULL),
(55, 'SA000001', 'deposit', 11.0000, 'company_wallet', 'Completed', 'Fund approved for user SA016914', NULL, '2026-08-06 07:35:21', NULL),
(56, 'SA016914', 'package_purchase', 11.0000, 'main_deposit', 'Completed', 'Purchased $11 Package — self activation', 'SA016914', '2026-08-06 07:36:02', NULL),
(57, 'SA000001', 'sponsor_income', 5.0000, 'earnings_11_wallet', 'Completed', 'Sponsor income $5 from SA016914 activating $11 Package', 'SA016914', '2026-08-06 07:36:02', NULL),
(58, 'SA000001', 'level_income', 0.1000, 'earnings_11_wallet', 'Completed', 'Level income $0.1 from SA016914 — Level 1 of $11 Package tree', 'SA016914', '2026-08-06 07:36:02', NULL),
(59, 'SA000001', 'reward_income', 1.0000, NULL, 'Completed', 'Reward reserve contribution $1 from SA016914 activating $11 Package', 'SA016914', '2026-08-06 07:36:02', NULL),
(60, 'SA000001', 'deposit', 90.0000, NULL, 'Pending', 'Deposit request of $90.00 submitted. TxHash: Company ', NULL, '2026-08-06 08:02:34', NULL),
(61, 'SA000001', 'deposit', 90.0000, 'main_deposit', 'Completed', 'Deposit of $90.00 approved by Admin', NULL, '2026-08-06 08:02:57', NULL),
(62, 'SA000001', 'deposit', 90.0000, 'company_wallet', 'Completed', 'Fund approved for user SA000001', NULL, '2026-08-06 08:02:57', NULL),
(63, 'SA000001', 'package_purchase', 30.0000, 'main_deposit', 'Completed', 'Purchased $30 Package — self activation', 'SA000001', '2026-08-06 08:03:28', NULL),
(64, 'SA000001', 'company_sweep', 4.0000, NULL, 'Completed', 'Company revenue $4 from SA000001 activating $30 Package', 'SA000001', '2026-08-06 08:03:28', NULL),
(65, 'SA000001', 'package_purchase', 60.0000, 'main_deposit', 'Completed', 'Purchased $60 Package — self activation', 'SA000001', '2026-08-06 08:03:33', NULL),
(66, 'SA000001', 'company_sweep', 8.0000, NULL, 'Completed', 'Company revenue $8 from SA000001 activating $60 Package', 'SA000001', '2026-08-06 08:03:33', NULL),
(67, 'SA000001', 'withdrawal', 10.0000, 'earnings_11_wallet', 'Pending', 'Withdrawal request of $10.00 submitted (Net: $9.00) to address 10121212...', NULL, '2026-08-06 11:47:35', NULL),
(68, 'SA000001', 'withdrawal', 10.0000, 'earnings_11_wallet', 'Completed', 'Withdrawal approved', NULL, '2026-08-06 11:59:24', NULL),
(69, 'SA176865', 'withdrawal', 10.0000, 'earnings_11_wallet', 'Rejected', 'Withdrawal rejected & refunded', NULL, '2026-08-06 12:26:10', NULL),
(70, 'SA176865', 'withdrawal', 10.0000, 'earnings_11_wallet', 'Pending', 'Withdrawal request of $10.00 submitted (Net: $9.00) to address 0x64Ca6d...', NULL, '2026-08-06 12:35:01', NULL),
(71, 'SA176865', 'withdrawal', 10.0000, 'earnings_11_wallet', 'Completed', 'Withdrawal approved', NULL, '2026-08-06 12:35:57', NULL),
(72, 'SA285964', 'package_purchase', 11.0000, 'main_deposit', 'Completed', 'Purchased $11 Package — self activation', 'SA285964', '2026-08-06 14:27:15', NULL),
(73, 'SA559719', 'autopool_income', 0.2500, 'earnings_11_wallet', 'Completed', 'Autopool pair completion income $0.25 from pair in $11 Matrix (Upline L1)', 'SA285964', '2026-08-06 14:27:15', NULL),
(74, 'SA000001', 'autopool_income', 0.2500, 'earnings_11_wallet', 'Completed', 'Autopool pair completion income $0.25 from pair in $11 Matrix (Upline L2)', 'SA285964', '2026-08-06 14:27:15', NULL),
(75, 'SA176865', 'sponsor_income', 5.0000, 'earnings_11_wallet', 'Completed', 'Sponsor income $5 from SA285964 activating $11 Package', 'SA285964', '2026-08-06 14:27:15', NULL),
(76, 'SA176865', 'level_income', 0.1000, 'earnings_11_wallet', 'Completed', 'Level income $0.1 from SA285964 — Level 1 of $11 Package tree', 'SA285964', '2026-08-06 14:27:15', NULL),
(77, 'SA000001', 'level_income', 0.1000, 'earnings_11_wallet', 'Completed', 'Level income $0.1 from SA285964 — Level 2 of $11 Package tree', 'SA285964', '2026-08-06 14:27:15', NULL),
(78, 'SA000001', 'reward_income', 1.0000, NULL, 'Completed', 'Reward reserve contribution $1 from SA285964 activating $11 Package', 'SA285964', '2026-08-06 14:27:15', NULL),
(79, 'SA514171', 'deposit', 11.0000, NULL, 'Pending', 'Deposit request of $11.00 submitted. TxHash: 0x418107A2B80CaFae5E2Ab09353b1d5a18bE315A5', NULL, '2026-08-06 14:54:41', NULL),
(80, 'SA514171', 'deposit', 11.0000, 'main_deposit', 'Completed', 'Deposit of $11.00 approved by Admin', NULL, '2026-08-06 14:57:27', NULL),
(81, 'SA000001', 'deposit', 11.0000, 'company_wallet', 'Completed', 'Fund approved for user SA514171', NULL, '2026-08-06 14:57:27', NULL),
(82, 'SA514171', 'package_purchase', 11.0000, 'main_deposit', 'Completed', 'Purchased $11 Package — self activation', 'SA514171', '2026-08-06 14:59:36', NULL),
(83, 'SA176865', 'sponsor_income', 5.0000, 'earnings_11_wallet', 'Completed', 'Sponsor income $5 from SA514171 activating $11 Package', 'SA514171', '2026-08-06 14:59:36', NULL),
(84, 'SA176865', 'level_income', 0.1000, 'earnings_11_wallet', 'Completed', 'Level income $0.1 from SA514171 — Level 1 of $11 Package tree', 'SA514171', '2026-08-06 14:59:36', NULL),
(85, 'SA000001', 'level_income', 0.1000, 'earnings_11_wallet', 'Completed', 'Level income $0.1 from SA514171 — Level 2 of $11 Package tree', 'SA514171', '2026-08-06 14:59:36', NULL),
(86, 'SA000001', 'reward_income', 1.0000, NULL, 'Completed', 'Reward reserve contribution $1 from SA514171 activating $11 Package', 'SA514171', '2026-08-06 14:59:36', NULL),
(87, 'SA135056', 'deposit', 11.0000, NULL, 'Pending', 'Deposit request of $11.00 submitted. TxHash: Your', NULL, '2026-08-07 05:11:59', NULL),
(88, 'SA135056', 'deposit', 11.0000, 'main_deposit', 'Completed', 'Deposit of $11.00 approved by Admin', NULL, '2026-08-07 05:13:10', NULL),
(89, 'SA000001', 'deposit', 11.0000, 'company_wallet', 'Completed', 'Fund approved for user SA135056', NULL, '2026-08-07 05:13:10', NULL),
(90, 'SA176865', 'withdrawal', 5.0000, 'earnings_11_wallet', 'Pending', 'Withdrawal request of $5.00 submitted (Net: $4.50) to address 0x64Ca6d...', NULL, '2026-08-07 05:15:35', NULL),
(91, 'SA176865', 'withdrawal', 5.0000, 'earnings_11_wallet', 'Completed', 'Withdrawal approved', NULL, '2026-08-07 05:27:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_id` varchar(8) NOT NULL COMMENT 'Alphanumeric ID: SA followed by 6 digits',
  `sponsor_id` varchar(8) DEFAULT NULL COMMENT 'References parent user_id in sponsor tree',
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Bcrypt hashed',
  `status` enum('Active','Inactive','Blocked') DEFAULT 'Inactive' COMMENT 'Inactive = registered but no package',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `wallet_address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `sponsor_id`, `name`, `email`, `phone`, `password`, `status`, `created_at`, `updated_at`, `wallet_address`) VALUES
(1, 'SA000001', NULL, 'SAPG', 'admin@asmultisoftware.in', '0000000000', '$2y$10$UUJymPzhECKsU98iwAHt..IbBqbM1a3nhrueH3Qp5vOLG5I9WiHBi', 'Active', '2026-07-19 05:56:03', '2026-08-05 18:38:07', 'ggjhghjgjg'),
(2, 'SA342668', 'SA000001', 'Nargish khatoon', 'mdatajul12@gmail.com', '7970799916', '$2y$10$BPhpSzfz4W/Qrx4XY2NT3u7sPg4ZPXHdeG3sqKYOZV.6DHkjnOMOe', 'Inactive', '2026-08-04 12:21:01', '2026-08-04 12:21:01', NULL),
(3, 'SA176865', 'SA000001', 'Koushal', 'abhijeet8521744@gmail.com', '8521744940', '$2y$10$w0W4R.MAkNpB0prOWpAu2uiazJJoEypXXRFbRW1AwMGy8vH1I.hA2', 'Active', '2026-08-04 14:29:27', '2026-08-04 16:19:54', '0x64Ca6d08C828F87b24b11C18c41A307FC261E24F'),
(4, 'SA285964', 'SA176865', 'Md Mujaffar', 'abhijeet8521744@gmail.com', '8578595808', '$2y$10$stQdHvu.BcAVGYkID6g5x.v2E0BbgXTwVRnMM3HCapJkE3iaFb3cS', 'Active', '2026-08-04 14:51:31', '2026-08-06 14:27:15', NULL),
(5, 'SA016914', 'SA000001', 'Test-USER', 'abhijeet8521744@gmail.com', '8521744940', '$2y$10$9efzgaLkCF3MJ3i5EjeMDucOvpQuvX2tezWqv2rKzhtWcR0MC0bOO', 'Active', '2026-08-04 15:10:13', '2026-08-07 02:53:43', NULL),
(6, 'SA264370', 'SA176865', 'Maksud Ahmed Barbhuiya', 'maksudbarbhuiya25@gmail.com', '9365208634', '$2y$10$Bhj0x0UWm2wP3hgp3JOZYebCZGjiZ7d87W14I/gbEGMKNC4wvjoB2', 'Inactive', '2026-08-04 15:21:39', '2026-08-04 15:21:39', NULL),
(7, 'SA514171', 'SA176865', 'Shubham', 'bishwasrajkumar66@gmail.com', '09771209029', '$2y$10$OaeJzIvKMSjA7TWe5n/96ulvwRe96NvCg2j4vTQhBqRKHIXzWLVTq', 'Active', '2026-08-04 15:26:37', '2026-08-06 14:59:36', NULL),
(8, 'SA135056', 'SA176865', 'NRGS', 'mdatajul12@gmail.com', '07970799916', '$2y$10$zF.4lSTK10bazEJtvEjFHuCdHuqQ3Vj7WL5Cg.NlPQh2kdOeMkYL2', 'Active', '2026-08-04 15:26:37', '2026-08-05 16:24:39', NULL),
(9, 'SA466439', 'SA176865', 'Mehzabeen Daneen Barbhuiya', 'maksudbarbhuiya25@gmail.com', '9365208634', '$2y$10$RnZ.c7fIjB1aHrW2xs2rROlt.gmPtv.fQNFP.4E.DlwXFQm31552S', 'Inactive', '2026-08-04 15:32:15', '2026-08-04 15:32:15', NULL),
(10, 'SA903673', 'SA342668', 'Sarjina khatoon', 'sarjinakhatoon26@gmail.com', '07644954990', '$2y$10$tujX7weXXDNdy4MBbvUbVeLyPp/n1fvECjcC/HrAQ9VZk8IolAari', 'Inactive', '2026-08-04 15:48:17', '2026-08-04 15:48:17', NULL),
(11, 'SA640030', 'SA264370', 'Abdul Mazid', 'abdulmazid3331@gmail.com', '9365215264', '$2y$10$SasnBkghqobgfzIPYUAO4OrxV/Vo/nTpnd40Qr9//n8.IuTwl303y', 'Inactive', '2026-08-04 16:13:18', '2026-08-04 16:13:18', NULL),
(12, 'SA559719', 'SA176865', 'Abutallah', 'abhijeet8521744@gmail.com', '9999999999', '$2y$10$jsZbzs68JLNPV6sb/91DQeYUNoVnWD.W6J1zIzkg/j8G4YvJfskVa', 'Active', '2026-08-04 16:22:24', '2026-08-05 16:19:40', NULL),
(13, 'SA237609', 'SA176865', 'Raja Singh', 'rajasingh912879@gmail.com', '9294971177', '$2y$10$zEu7BXVpJM3bKRVtnCZSH..mvmMgXo5cPz49jfjvIidSsnrxTi3je', 'Inactive', '2026-08-04 16:55:51', '2026-08-04 16:55:51', NULL),
(14, 'SA206314', 'SA264370', 'Chamanlal', 'chamanlalkhurja97@gmail.com', '9758975039', '$2y$10$X0JD9W5qO/5JehqQ6MTrO.a4GBY/F64GqkwhHtMJwZflJF8x/dSfy', 'Inactive', '2026-08-05 01:54:40', '2026-08-05 01:54:40', NULL),
(15, 'SA614179', 'SA264370', 'Chamanlal', 'chamanlalkhurja97@gmail.com', '9758975039', '$2y$10$4LiFUv0hC72/zh2Vv202NOhDCRqcOgAoTW38xOthaoSSVKmvzy5ae', 'Inactive', '2026-08-05 01:57:47', '2026-08-05 01:57:47', NULL),
(16, 'SA016017', 'SA342668', 'Sahil  khan', 'sahilk98667@gmail.com', '6201428507', '$2y$10$l0neUmdJizAfgpJ6Ezs80.eRNXdf1mdtN2uFnx6DqeI2AqAFXJbGi', 'Inactive', '2026-08-05 04:11:57', '2026-08-05 04:11:57', NULL),
(17, 'SA703396', 'SA342668', 'Sahil  khan', 'sahilk98667@gmail.com', '6201428507', '$2y$10$3DK1NjQ7FxR6C89OKfX/guMqtGY4YAJeQquHyF1Dhux1eR9UGapnm', 'Inactive', '2026-08-05 04:12:33', '2026-08-05 04:12:33', NULL),
(18, 'SA448318', 'SA342668', 'Sahil  khan', 'sahilk98667@gmail.com', '6201428507', '$2y$10$jQNtMcIovjIoGVfYzXt31OiW1kqZ/kOe3E/qmNtTpVvDpUzfoI202', 'Inactive', '2026-08-05 04:13:41', '2026-08-05 04:13:41', NULL),
(19, 'SA105520', 'SA176865', 'Vinod Kumar Bishwas', 'vinodkumar6299343821@gmail.com', '06200834476', '$2y$10$xzBDjAbvAvrDoXoWcfsL4.L1uzM8WgNb8Ph6XsbhRflBwtCsh2UEO', 'Inactive', '2026-08-05 14:58:52', '2026-08-05 14:58:52', NULL),
(20, 'SA247910', 'SA176865', 'Vinod Kumar Bishwas', 'vinodkumar6299343821@gmail.com', '06200834476', '$2y$10$RiUt7Rhq83SmVteO3snyBOSlYlKyVlz32tcA7IcqGdOtBQ/frpxby', 'Inactive', '2026-08-05 15:00:49', '2026-08-05 15:00:49', NULL),
(21, 'SA296759', 'SA176865', 'VINOD KUMAR BISHWAS', 'vinkdkumar6299343821@gmail.com', '+91 6200834476', '$2y$10$3kiV855WB0s7n/w5Vej3UO2Y9Rr8aZIZB968CltChuDeIn5OaYYXG', 'Inactive', '2026-08-05 15:02:22', '2026-08-05 15:02:22', NULL),
(22, 'SA082884', 'SA105520', 'VINOD KUMAR BISHWAS', 'vinkdkumar6299343821@gmail.com', '+91 6200834476', '$2y$10$68mYhd2XotEDTzb0Keolee5X5BuZBpCBBXWSwVcxvpEqK8fKlAcLq', 'Inactive', '2026-08-05 16:29:39', '2026-08-05 16:29:39', NULL),
(23, 'SA130960', 'SA105520', 'VINOD KUMAR BISHWAS', 'vinkdkumar6299343821@gmail.com', '+91 6200834476', '$2y$10$.3GYnq1VLMdI6RrcRkRQkuXjbk3K7R.R27.mLM03AZJ9upf/vGxVy', 'Inactive', '2026-08-05 16:32:25', '2026-08-05 16:32:25', NULL),
(24, 'SA449799', 'SA135056', 'Sarjina khatoon', 'sarjinakhatoon26@gmail.com', '07644954990', '$2y$10$fWGkZzdt6v.j19GRVH6zU.rwobwwlZEUIkwfWFtZ0DkhoqJPcFuWO', 'Inactive', '2026-08-07 04:35:55', '2026-08-07 04:35:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_financial_summary`
--

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_financial_summary`
--

INSERT INTO `user_financial_summary` (`user_id`, `my_package`, `direct_team_count`, `total_active_team_count`, `total_inactive_team_count`, `strong_leg_count`, `other_legs_count`, `main_deposit_balance`, `earnings_11_wallet`, `earnings_30_wallet`, `earnings_60_wallet`, `earnings_120_wallet`, `earnings_240_wallet`, `earnings_480_wallet`, `booster_10_wallet`, `booster_20_wallet`, `booster_40_wallet`, `booster_80_wallet`, `booster_160_wallet`, `booster_320_wallet`, `total_direct_referral_income`, `total_team_level_income`, `total_global_autopool_income`, `total_booster_income`, `total_reward_income`, `total_withdrawal_amount`, `net_income`, `updated_at`) VALUES
('SA000001', 60.00, 3, 0, 3, 0, 0, 0.0000, 1.1000, 10.3000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 20.0000, 0.9000, 0.5000, 0.0000, 0.0000, 0.0000, 20.9500, '2026-08-06 14:59:36'),
('SA016017', 0.00, 0, 0, 0, 0, 0, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-05 04:11:57'),
('SA016914', 11.00, 0, 0, 0, 0, 0, 11.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-06 07:36:02'),
('SA082884', 0.00, 0, 0, 0, 0, 0, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-05 16:29:39'),
('SA105520', 0.00, 2, 0, 2, 0, 0, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-05 16:32:25'),
('SA130960', 0.00, 0, 0, 0, 0, 0, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-05 16:32:25'),
('SA135056', 11.00, 1, 0, 1, 0, 0, 21.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-07 05:13:10'),
('SA176865', 30.00, 10, 0, 10, 0, 0, 0.0000, 5.2000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 20.0000, 0.2000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-07 05:15:35'),
('SA206314', 0.00, 0, 0, 0, 0, 0, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-05 01:54:40'),
('SA237609', 0.00, 0, 0, 0, 0, 0, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-04 16:55:51'),
('SA247910', 0.00, 0, 0, 0, 0, 0, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-05 15:00:49'),
('SA264370', 0.00, 3, 0, 3, 0, 0, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-05 01:57:47'),
('SA285964', 11.00, 0, 0, 0, 0, 0, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-06 14:27:15'),
('SA296759', 0.00, 0, 0, 0, 0, 0, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-05 15:02:22'),
('SA342668', 0.00, 4, 0, 4, 0, 0, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-05 04:13:41'),
('SA448318', 0.00, 0, 0, 0, 0, 0, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-05 04:13:41'),
('SA449799', 0.00, 0, 0, 0, 0, 0, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-07 04:35:55'),
('SA466439', 0.00, 0, 0, 0, 0, 0, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-04 15:32:15'),
('SA514171', 11.00, 0, 0, 0, 0, 0, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-06 14:59:36'),
('SA559719', 11.00, 0, 0, 0, 0, 0, 0.0000, 0.2500, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.2500, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-06 14:27:15'),
('SA614179', 0.00, 0, 0, 0, 0, 0, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-05 01:57:47'),
('SA640030', 0.00, 0, 0, 0, 0, 0, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-04 16:13:18'),
('SA703396', 0.00, 0, 0, 0, 0, 0, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-05 04:12:33'),
('SA903673', 0.00, 0, 0, 0, 0, 0, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, '2026-08-04 15:48:17');

-- --------------------------------------------------------

--
-- Table structure for table `user_packages`
--

CREATE TABLE `user_packages` (
  `id` int(11) NOT NULL,
  `user_id` varchar(8) NOT NULL,
  `package_type` enum('main_11','main_30','main_60','main_120','main_240','main_480','booster_10','booster_20','booster_40','booster_80','booster_160','booster_320') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `activated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `funded_by` varchar(8) DEFAULT NULL COMMENT 'user_id of the person who paid for this package'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_packages`
--

INSERT INTO `user_packages` (`id`, `user_id`, `package_type`, `is_active`, `activated_at`, `funded_by`) VALUES
(1, 'SA176865', 'main_11', 1, '2026-08-04 16:19:54', 'SA176865'),
(2, 'SA176865', 'booster_10', 1, '2026-08-04 16:24:52', 'SA176865'),
(3, 'SA000001', 'booster_10', 1, '2026-08-04 16:27:06', 'SA000001'),
(4, 'SA559719', 'main_11', 1, '2026-08-04 16:38:27', 'SA559719'),
(5, 'SA559719', 'booster_10', 1, '2026-08-04 16:38:49', 'SA559719'),
(6, 'SA135056', 'main_11', 1, '2026-08-05 16:24:39', 'SA135056'),
(7, 'SA000001', 'main_11', 1, '2026-08-05 17:04:59', 'SA000001'),
(8, 'SA176865', 'main_30', 1, '2026-08-05 17:47:57', 'SA176865'),
(9, 'SA016914', 'main_11', 1, '2026-08-06 07:36:02', 'SA016914'),
(10, 'SA000001', 'main_30', 1, '2026-08-06 08:03:28', 'SA000001'),
(11, 'SA000001', 'main_60', 1, '2026-08-06 08:03:33', 'SA000001'),
(12, 'SA285964', 'main_11', 1, '2026-08-06 14:27:15', 'SA285964'),
(13, 'SA514171', 'main_11', 1, '2026-08-06 14:59:36', 'SA514171');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_configurations`
--

CREATE TABLE `wallet_configurations` (
  `id` int(11) NOT NULL,
  `wallet_type` varchar(20) NOT NULL,
  `wallet_label` varchar(50) NOT NULL COMMENT 'Human-readable label',
  `internal_transfer_fee_percent` decimal(5,2) DEFAULT 5.00,
  `external_withdrawal_fee_percent` decimal(5,2) DEFAULT 5.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallet_configurations`
--

INSERT INTO `wallet_configurations` (`id`, `wallet_type`, `wallet_label`, `internal_transfer_fee_percent`, `external_withdrawal_fee_percent`, `updated_at`) VALUES
(1, 'earnings_11', '$11 Package Wallet', 10.00, 10.00, '2026-08-04 16:03:07'),
(2, 'earnings_30', '$30 Package Wallet', 10.00, 10.00, '2026-08-04 16:03:14'),
(3, 'earnings_60', '$60 Package Wallet', 5.00, 5.00, '2026-07-19 11:26:03'),
(4, 'earnings_120', '$120 Package Wallet', 5.00, 5.00, '2026-07-19 11:26:03'),
(5, 'earnings_240', '$240 Package Wallet', 5.00, 5.00, '2026-07-19 11:26:03'),
(6, 'earnings_480', '$480 Package Wallet', 5.00, 5.00, '2026-07-19 11:26:03'),
(7, 'booster_10', '$10 Booster Wallet', 5.00, 5.00, '2026-07-19 11:26:03'),
(8, 'booster_20', '$20 Booster Wallet', 5.00, 5.00, '2026-07-19 11:26:03'),
(9, 'booster_40', '$40 Booster Wallet', 5.00, 5.00, '2026-07-19 11:26:03'),
(10, 'booster_80', '$80 Booster Wallet', 5.00, 5.00, '2026-07-19 11:26:03'),
(11, 'booster_160', '$160 Booster Wallet', 5.00, 5.00, '2026-07-19 11:26:03'),
(12, 'booster_320', '$320 Booster Wallet', 5.00, 5.00, '2026-07-19 11:26:03');

-- --------------------------------------------------------

--
-- Table structure for table `withdrawal_requests`
--

CREATE TABLE `withdrawal_requests` (
  `id` int(11) NOT NULL,
  `user_id` varchar(8) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `wallet_type` varchar(20) NOT NULL COMMENT 'Which earning wallet this is from',
  `fee_amount` decimal(15,2) DEFAULT 0.00,
  `net_amount` decimal(15,2) DEFAULT 0.00 COMMENT 'Amount after fee deduction',
  `destination_address` varchar(255) NOT NULL COMMENT 'External USDT wallet address',
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `admin_remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `withdrawal_requests`
--

INSERT INTO `withdrawal_requests` (`id`, `user_id`, `amount`, `wallet_type`, `fee_amount`, `net_amount`, `destination_address`, `status`, `admin_remarks`, `created_at`, `updated_at`) VALUES
(1, 'SA176865', 10.00, 'earnings_11_wallet', 1.00, 9.00, '0xBFc95cdf2f66Ea10CeA10386861091F47D289697', 'Rejected', NULL, '2026-08-05 16:29:55', '2026-08-06 12:26:10'),
(2, 'SA000001', 10.00, 'earnings_11_wallet', 1.00, 9.00, '10121212121', 'Approved', NULL, '2026-08-06 11:47:35', '2026-08-06 11:59:24'),
(3, 'SA176865', 10.00, 'earnings_11_wallet', 1.00, 9.00, '0x64Ca6d08C828F87b24b11C18c41A307FC261E24F', 'Approved', NULL, '2026-08-06 12:35:01', '2026-08-06 12:35:57'),
(4, 'SA176865', 5.00, 'earnings_11_wallet', 0.50, 4.50, '0x64Ca6d08C828F87b24b11C18c41A307FC261E24F', 'Approved', NULL, '2026-08-07 05:15:35', '2026-08-07 05:27:03');

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
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `booster_matrices`
--
ALTER TABLE `booster_matrices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_upline` (`upline_id`,`booster_type`),
  ADD KEY `idx_booster` (`booster_type`),
  ADD KEY `idx_board` (`board_id`);

--
-- Indexes for table `company_ledger`
--
ALTER TABLE `company_ledger`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deposit_requests`
--
ALTER TABLE `deposit_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `package_matrices`
--
ALTER TABLE `package_matrices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_upline` (`upline_id`,`package_type`),
  ADD KEY `idx_package` (`package_type`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_type` (`transaction_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_wallet` (`wallet_type`),
  ADD KEY `idx_user_type` (`user_id`,`transaction_type`),
  ADD KEY `idx_blocked_by` (`blocked_by_user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_sponsor` (`sponsor_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `user_financial_summary`
--
ALTER TABLE `user_financial_summary`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_packages`
--
ALTER TABLE `user_packages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_package` (`user_id`,`package_type`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_type` (`package_type`);

--
-- Indexes for table `wallet_configurations`
--
ALTER TABLE `wallet_configurations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wallet_type` (`wallet_type`);

--
-- Indexes for table `withdrawal_requests`
--
ALTER TABLE `withdrawal_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booster_matrices`
--
ALTER TABLE `booster_matrices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `deposit_requests`
--
ALTER TABLE `deposit_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `package_matrices`
--
ALTER TABLE `package_matrices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `user_packages`
--
ALTER TABLE `user_packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `wallet_configurations`
--
ALTER TABLE `wallet_configurations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `withdrawal_requests`
--
ALTER TABLE `withdrawal_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_financial_summary`
--
ALTER TABLE `user_financial_summary`
  ADD CONSTRAINT `user_financial_summary_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
