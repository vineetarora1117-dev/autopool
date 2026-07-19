-- =====================================================
-- SAPG Database Schema
-- Autopool + Personal Network System
-- =====================================================

CREATE DATABASE IF NOT EXISTS `SAPG` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `SAPG`;

-- =====================================================
-- 1. USERS — Core Identity
-- =====================================================
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(8) NOT NULL UNIQUE COMMENT 'Alphanumeric ID: SA followed by 6 digits',
    `sponsor_id` VARCHAR(8) DEFAULT NULL COMMENT 'References parent user_id in sponsor tree',
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `password` VARCHAR(255) NOT NULL COMMENT 'Bcrypt hashed',
    `status` ENUM('Active', 'Inactive', 'Blocked') DEFAULT 'Inactive' COMMENT 'Inactive = registered but no package',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_sponsor` (`sponsor_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB;

-- =====================================================
-- 2. ADMINS — Admin Panel Authentication
-- =====================================================
CREATE TABLE `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL COMMENT 'Bcrypt hashed',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- 3. USER_FINANCIAL_SUMMARY — Pre-calculated Dashboard Cache
-- One row per user. Updated incrementally on every financial event.
-- This is what powers the UserPanel dashboard gold cards instantly.
-- =====================================================
CREATE TABLE `user_financial_summary` (
    `user_id` VARCHAR(8) PRIMARY KEY,
    -- Team Counts
    `my_package` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Highest active package amount',
    `direct_team_count` INT DEFAULT 0 COMMENT 'Number of direct referrals',
    `total_active_team_count` INT DEFAULT 0 COMMENT 'All downlines with at least $11 package',
    `total_inactive_team_count` INT DEFAULT 0 COMMENT 'All downlines with no package',
    `strong_leg_count` INT DEFAULT 0 COMMENT 'Size of largest single leg',
    `other_legs_count` INT DEFAULT 0 COMMENT 'Sum of all other legs',
    -- Main Deposit Wallet (for buying packages)
    `main_deposit_balance` DECIMAL(15,2) DEFAULT 0.00,
    -- Segregated Package Earning Wallets (6 wallets)
    `earnings_11_wallet` DECIMAL(15,2) DEFAULT 0.00,
    `earnings_30_wallet` DECIMAL(15,2) DEFAULT 0.00,
    `earnings_60_wallet` DECIMAL(15,2) DEFAULT 0.00,
    `earnings_120_wallet` DECIMAL(15,2) DEFAULT 0.00,
    `earnings_240_wallet` DECIMAL(15,2) DEFAULT 0.00,
    `earnings_480_wallet` DECIMAL(15,2) DEFAULT 0.00,
    -- Segregated Booster Earning Wallets (6 wallets)
    `booster_10_wallet` DECIMAL(15,2) DEFAULT 0.00,
    `booster_20_wallet` DECIMAL(15,2) DEFAULT 0.00,
    `booster_40_wallet` DECIMAL(15,2) DEFAULT 0.00,
    `booster_80_wallet` DECIMAL(15,2) DEFAULT 0.00,
    `booster_160_wallet` DECIMAL(15,2) DEFAULT 0.00,
    `booster_320_wallet` DECIMAL(15,2) DEFAULT 0.00,
    -- Income Totals (for dashboard display)
    `total_direct_referral_income` DECIMAL(15,2) DEFAULT 0.00,
    `total_team_level_income` DECIMAL(15,2) DEFAULT 0.00,
    `total_global_autopool_income` DECIMAL(15,2) DEFAULT 0.00,
    `total_booster_income` DECIMAL(15,2) DEFAULT 0.00,
    `total_reward_income` DECIMAL(15,2) DEFAULT 0.00,
    `total_withdrawal_amount` DECIMAL(15,2) DEFAULT 0.00,
    `net_income` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Total earned minus total withdrawn',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- 4. COMPANY_LEDGER — Single-row Admin Dashboard Cache
-- Powers the Admin dashboard financial metrics instantly.
-- =====================================================
CREATE TABLE `company_ledger` (
    `id` INT PRIMARY KEY DEFAULT 1,
    `total_funds_received` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Cumulative USDT deposited into system',
    `unutilized_funds` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Total in all users main wallets (not spent)',
    `invested_funds` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Total spent on packages/boosters',
    `total_usdt_paid_out` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Total USDT that left system via withdrawals',
    `company_wallet_balance` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Company earnings (fees + revenue + SA000001 sweep)',
    `total_payout_liability_main` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Total sitting in all users package earning wallets',
    `total_payout_liability_booster` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Total sitting in all users booster earning wallets',
    `total_held_sponsor_income` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Total sponsor income held pending sponsor upgrade',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- 5. TRANSACTIONS — Master Ledger with Narrations
-- Every single money movement is recorded here.
-- =====================================================
CREATE TABLE `transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(8) NOT NULL COMMENT 'The user this transaction belongs to',
    `transaction_type` ENUM(
        'deposit', 'withdrawal',
        'package_purchase', 'booster_purchase',
        'autopool_income', 'sponsor_income', 'level_income',
        'booster_income', 'reward_income',
        'sponsor_income_held', 'sponsor_income_released',
        'internal_transfer', 'admin_charge',
        'company_revenue', 'company_sweep'
    ) NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `wallet_type` VARCHAR(20) DEFAULT NULL COMMENT 'e.g. main_deposit, earnings_11, booster_10',
    `status` ENUM('Pending', 'Approved', 'Rejected', 'Held', 'Released', 'Completed') DEFAULT 'Completed',
    `narration` TEXT NOT NULL COMMENT 'Human-readable description of the transaction',
    `related_user_id` VARCHAR(8) DEFAULT NULL COMMENT 'The other party involved (sponsor, buyer, etc.)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_type` (`transaction_type`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created` (`created_at`),
    INDEX `idx_wallet` (`wallet_type`),
    INDEX `idx_user_type` (`user_id`, `transaction_type`)
) ENGINE=InnoDB;

-- =====================================================
-- 6. DEPOSIT_REQUESTS — User Deposit Queue
-- =====================================================
CREATE TABLE `deposit_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(8) NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `tx_hash` VARCHAR(255) DEFAULT NULL COMMENT 'Blockchain transaction hash',
    `proof_image` VARCHAR(255) DEFAULT NULL COMMENT 'Path to uploaded proof screenshot',
    `status` ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    `admin_remarks` TEXT DEFAULT NULL COMMENT 'Reason for rejection if applicable',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB;

-- =====================================================
-- 7. WITHDRAWAL_REQUESTS — User Withdrawal Queue
-- =====================================================
CREATE TABLE `withdrawal_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(8) NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `wallet_type` VARCHAR(20) NOT NULL COMMENT 'Which earning wallet this is from',
    `fee_amount` DECIMAL(15,2) DEFAULT 0.00,
    `net_amount` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Amount after fee deduction',
    `destination_address` VARCHAR(255) NOT NULL COMMENT 'External USDT wallet address',
    `status` ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    `admin_remarks` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB;

-- =====================================================
-- 8. USER_PACKAGES — Tracks Active Packages per User
-- =====================================================
CREATE TABLE `user_packages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(8) NOT NULL,
    `package_type` ENUM(
        'main_11', 'main_30', 'main_60', 'main_120', 'main_240', 'main_480',
        'booster_10', 'booster_20', 'booster_40', 'booster_80', 'booster_160', 'booster_320'
    ) NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `activated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `funded_by` VARCHAR(8) DEFAULT NULL COMMENT 'user_id of the person who paid for this package',
    INDEX `idx_user` (`user_id`),
    INDEX `idx_type` (`package_type`),
    UNIQUE KEY `unique_user_package` (`user_id`, `package_type`)
) ENGINE=InnoDB;

-- =====================================================
-- 9. PACKAGE_MATRICES — 2xN Autopool Positions (Main Packages)
-- =====================================================
CREATE TABLE `package_matrices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(8) NOT NULL,
    `package_type` ENUM('main_11', 'main_30', 'main_60', 'main_120', 'main_240', 'main_480') NOT NULL,
    `upline_id` VARCHAR(8) DEFAULT NULL COMMENT 'Direct upline in this specific matrix',
    `position_slot` TINYINT(1) NOT NULL COMMENT '1=Left, 2=Right',
    `matrix_level` INT DEFAULT 1 COMMENT 'Depth level in the matrix',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_upline` (`upline_id`, `package_type`),
    INDEX `idx_package` (`package_type`)
) ENGINE=InnoDB;

-- =====================================================
-- 10. BOOSTER_MATRICES — 4x2 Autopool Positions (Infinity Boosters)
-- =====================================================
CREATE TABLE `booster_matrices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(8) NOT NULL,
    `booster_type` ENUM('booster_10', 'booster_20', 'booster_40', 'booster_80', 'booster_160', 'booster_320') NOT NULL,
    `upline_id` VARCHAR(8) DEFAULT NULL COMMENT 'Direct upline in this booster matrix',
    `position_slot` TINYINT(1) NOT NULL COMMENT '1-4 positions per level',
    `matrix_level` TINYINT(1) NOT NULL COMMENT '1=Level 1 (4 slots), 2=Level 2 (16 slots)',
    `board_id` INT DEFAULT NULL COMMENT 'Groups nodes into individual 20-person boards',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_upline` (`upline_id`, `booster_type`),
    INDEX `idx_booster` (`booster_type`),
    INDEX `idx_board` (`board_id`)
) ENGINE=InnoDB;

-- =====================================================
-- 11. WALLET_CONFIGURATIONS — Dynamic Admin Fees per Wallet
-- =====================================================
CREATE TABLE `wallet_configurations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `wallet_type` VARCHAR(20) NOT NULL UNIQUE,
    `wallet_label` VARCHAR(50) NOT NULL COMMENT 'Human-readable label',
    `internal_transfer_fee_percent` DECIMAL(5,2) DEFAULT 5.00,
    `external_withdrawal_fee_percent` DECIMAL(5,2) DEFAULT 5.00,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- 12. ANNOUNCEMENTS — Admin Broadcast Messages
-- =====================================================
CREATE TABLE `announcements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- 13. SUPPORT_TICKETS — User Helpdesk
-- =====================================================
CREATE TABLE `support_tickets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(8) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('Open', 'In Progress', 'Closed') DEFAULT 'Open',
    `admin_reply` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB;

-- =====================================================
-- 14. SETTINGS — Dynamic Key-Value Config (Admin-changeable)
-- =====================================================
CREATE TABLE `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT DEFAULT NULL,
    `setting_label` VARCHAR(100) DEFAULT NULL COMMENT 'Human-readable label for admin UI',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- SEED DATA
-- =====================================================

-- Admin account (password: admin, bcrypt hashed)
INSERT INTO `admins` (`username`, `password`) VALUES
('admin', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy');

-- Company root user SA000001
INSERT INTO `users` (`user_id`, `sponsor_id`, `name`, `email`, `phone`, `password`, `status`) VALUES
('SA000001', NULL, 'SAPG', 'admin@sapg.com', '0000000000', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'Active');

-- Financial summary for root user
INSERT INTO `user_financial_summary` (`user_id`) VALUES ('SA000001');

-- Company ledger initialization (all zeros)
INSERT INTO `company_ledger` (`id`) VALUES (1);

-- Wallet configurations for all 12 wallet types
INSERT INTO `wallet_configurations` (`wallet_type`, `wallet_label`, `internal_transfer_fee_percent`, `external_withdrawal_fee_percent`) VALUES
('earnings_11',  '$11 Package Wallet',   5.00, 5.00),
('earnings_30',  '$30 Package Wallet',   5.00, 5.00),
('earnings_60',  '$60 Package Wallet',   5.00, 5.00),
('earnings_120', '$120 Package Wallet',  5.00, 5.00),
('earnings_240', '$240 Package Wallet',  5.00, 5.00),
('earnings_480', '$480 Package Wallet',  5.00, 5.00),
('booster_10',   '$10 Booster Wallet',   5.00, 5.00),
('booster_20',   '$20 Booster Wallet',   5.00, 5.00),
('booster_40',   '$40 Booster Wallet',   5.00, 5.00),
('booster_80',   '$80 Booster Wallet',   5.00, 5.00),
('booster_160',  '$160 Booster Wallet',  5.00, 5.00),
('booster_320',  '$320 Booster Wallet',  5.00, 5.00);

-- Default dynamic settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_label`) VALUES
('company_usdt_address', '', 'Company USDT Wallet Address (TRC20)'),
('company_qr_code_path', '', 'Company QR Code Image Path'),
('min_withdrawal_amount', '10', 'Minimum Withdrawal Amount ($)'),
('max_withdrawal_amount', '5000', 'Maximum Withdrawal Amount ($)'),
('withdrawal_enabled', '1', 'Enable/Disable Withdrawals'),
('registration_enabled', '1', 'Enable/Disable New Registrations');
