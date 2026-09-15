# Infinity Booster Module Specification & Architecture (`new_module_booster`)

> **STATUS**: CONTEXT & SPECIFICATION PHASE ONLY.  
> **RULE**: NO CODE IMPLEMENTATION UNTIL THE USER EXPLICITLY COMMANDS `:go`.

---

## 1. Overview & Business Model

The **Booster Module** operates as a perpetual ("entry with no exit"), automated **1x3 Auto-Cycling Global Matrix**.

### Key Rules:
- **Entry Cost**: **$10.00** per Booster.
- **Matrix Type**: 1x3 Global Forced Matrix (Top-to-Bottom, Left-to-Right across the entire platform).
- **Capacity per Lifecycle**: Exactly **3 downline booster positions** below each booster node.
- **Lifecycle Completion (Cycling)**: Once 3 downlines join under a booster position, that specific booster position's lifecycle is marked **COMPLETED**.
- **Perpetual Engine**: Cycles continue indefinitely (no final exit).

---

## 2. Matrix Architecture: Booster ID vs. User ID

Because a single user can own **multiple booster entries** (from manual purchases and automated re-entries), matrix nodes **cannot** use `user_id` as the matrix node primary key.

- **Primary Node Key**: `booster_id` (Unique auto-incrementing ID for every individual booster instance, e.g. `BST#1001`).
- **Owner Key**: `user_id` (Foreign key linking back to the user who owns the booster, e.g. `SA123456`).
- **Parent Node Pointer**: `upline_booster_id` (Points to the parent `booster_id` in the 1x3 matrix, NOT parent `user_id`).

---

## 3. Purchase Constraints & Cooldown Rule

- **Multiple Boosters**: A user can own/purchase as many booster entries as they want over time.
- **6-Hour Cooldown Constraint**: 
  - A user **cannot** purchase 2 boosters at the same instance.
  - There must be a strict **minimum gap of 6 hours** (`6 * 3600 seconds`) between two **manual booster purchases** by the same user.
  - *Automated Re-entries* triggered by cycle completions bypass the manual cooldown and process instantly to keep the matrix moving.
- **Refresh Protection**: Post-Redirect-Get (PRG) pattern + unique form request token (`token`) to prevent accidental duplicate purchases on page refresh.

---

## 4. Payout & Revenue Breakdown per Cycle (3 Downlines = $30 Total)

When 3 booster nodes fill under a target `booster_id`, **$30.00 total revenue** is processed as follows:

| Allocation | Amount | Destination / Description |
|:---|:---:|:---|
| **User Earnings** | **$10.00** | Credited to **`booster_wallet`** in `user_financial_summary` (Withdrawable). |
| **Company Revenue** | **$10.00** | Transferred directly to the Company Wallet. |
| **Auto Re-entry Booster** | **$10.00** | Automatically creates a **new `booster_id`** for the same `user_id`, placed at the end of the global matrix (Top-to-Bottom, Left-to-Right) to start a new lifecycle. |
| **TOTAL** | **$30.00** | Fully accounted for (100% distribution). |

---

## 5. UserPanel UI & Navigation Structure (Finalized)

### A. Left Sidebar Menu
1. **Buy Package Menu**:
   - Sub-menu link: **Buy Booster** (`/UserPanel/buyBooster.php`).
   - Displays live 6-hour countdown timer if user is on cooldown.
2. **Main Navigation Category**:
   - Positioned **Below "More" - Above "Autopool pack 1"**.
   - Category Label: **Booster**
   - Sub-menu items:
     1. **Booster Income** (`/UserPanel/boosterIncome.php`)
     2. **Booster Wallet** (`/UserPanel/boosterWallet.php`)

### B. "Booster Income" Page (`/UserPanel/boosterIncome.php`)
- **Top Section - Active & Completed Boosters**: Table of owned boosters (`Booster ID`, `Purchase Type`, `Global Position/Progress` e.g. `0/3`, `1/3`, `2/3`, `Completed`, `Created Date`).
- **Bottom Section - Booster Income Logs**: Historical table of all $10 payouts received from completed booster cycles.

### C. "Booster Wallet" Page (`/UserPanel/boosterWallet.php`) & Uniform Wallet Behavior
- **Unified Wallet Rules**: Behaves identically to all existing segregated earning wallets:
  - **External Withdrawal**: Available in `newWithdrawal.php` with standard withdrawal fee applied.
  - **Internal Transfer**: Can be transferred to `main_deposit_balance` (for package/booster buying) with standard admin charge percentage applied.
- **Booster Wallet View**: Displays balance, transfer/withdrawal shortcuts, and complete debit/credit transaction history.

---

## 6. Admin Panel Integration & Transaction Logging (`/AdminPanel/transactionLogs.php`)

All booster financial movements automatically write records into the master `transactions` table, rendering natively inside **`http://localhost/autopool/AdminPanel/transactionLogs.php`**:

### Logged Event Types:
1. **Manual Booster Purchase**: `transaction_type`: `'booster_purchase'`, `wallet_type`: `'main_deposit'`, `amount`: `10.00`.
2. **Booster Cycle Income Payout**: `transaction_type`: `'booster_income'`, `wallet_type`: `'booster_wallet'`, `amount`: `10.00`.
3. **Company Revenue Cut**: `transaction_type`: `'company_revenue'`, `wallet_type`: `'company_wallet'`, `amount`: `10.00`.
4. **Auto Re-entry Creation**: `transaction_type`: `'booster_purchase'`, `wallet_type`: `'auto_reentry'`, `amount`: `10.00`.

---

## 7. Cascading Chain Reaction & Queue Engine (Handling Domino Cycles)

- **Non-Recursive Queue Loop (`while (!empty($queue))` pattern)**.
- **Database Transaction Guard**: `$pdo->beginTransaction()` ... `$pdo->commit()`.
- **Safety Limit**: `max 500 cycles per transaction`.

---

## 8. Concurrency & Hostinger Shared Server Safety (Race Condition Prevention)

- **MySQL Named Lock (`GET_LOCK`)**: `SELECT GET_LOCK('booster_matrix_queue_lock', 5)`.
- **Row-Level Locking (`FOR UPDATE`)**: `SELECT ... FOR UPDATE`.

---

## 9. Database Schema Specification

```sql
-- 1. user_boosters Table
CREATE TABLE IF NOT EXISTS `user_boosters` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `user_id` VARCHAR(50) NOT NULL,
  `upline_booster_id` BIGINT DEFAULT NULL,
  `downline_count` INT DEFAULT 0,
  `purchase_type` ENUM('manual', 'reentry') NOT NULL DEFAULT 'manual',
  `status` ENUM('active', 'completed') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `completed_at` TIMESTAMP NULL DEFAULT NULL,
  INDEX (`user_id`),
  INDEX (`upline_booster_id`),
  INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Financial Summary Alteration (Add booster_wallet)
ALTER TABLE `user_financial_summary` ADD COLUMN IF NOT EXISTS `booster_wallet` DECIMAL(15,4) DEFAULT 0.0000;
```

---

## 10. Database Migration Setup
- **Folder Path**: `/migrations/`
- **Migration Script**: [`migrations/001_booster_module.php`](file:///c:/xampp/htdocs/autopool/migrations/001_booster_module.php)
- **Static Security Code**: `code=2123508`
- **Execution URL**: `SITE_URL/migrations/001_booster_module.php?code=2123508`
