<?php
/**
 * Migration: 001_booster_module.php
 * Description: Database migration script for 1x3 Auto-Cycling Booster Module (new_module_booster)
 * Required Access Code: ?code=2123508
 */

header('Content-Type: text/html; charset=utf-8');

$REQUIRED_CODE = '2123508';
$isCli = (php_sapi_name() === 'cli');

if (!$isCli && (!isset($_GET['code']) || $_GET['code'] !== $REQUIRED_CODE)) {
    http_response_code(403);
    die('<div style="color:red; font-family:sans-serif; padding:20px; border:1px solid red; margin:20px;">
        <h2>Access Denied</h2>
        <p>Invalid or missing migration code. Access code in URL parameter is required to run this database migration.</p>
        <p>Example: <code>/migrations/001_booster_module.php?code=2123508</code></p>
    </div>');
}

$dbPath = __DIR__ . '/../libs/db.php';
if (!file_exists($dbPath)) {
    die("Database configuration file not found at: " . htmlspecialchars($dbPath));
}

require_once $dbPath;

echo "<div style='font-family: monospace; background: #1e1e1e; color: #00ff66; padding: 20px; border-radius: 8px; line-height: 1.6;'>";
echo "<h2>🚀 Running 1x3 Auto-Cycling Booster Module DB Migration...</h2>";

try {
    // 1. user_boosters table (Stores individual booster instances identified by booster_id)
    $sql1 = "CREATE TABLE IF NOT EXISTS `user_boosters` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql1);
    echo "[SUCCESS] Table 'user_boosters' created or verified.<br>";

    // 2. booster_transactions table (Ledger for $10 User Earning, $10 Company Revenue, $10 Auto Re-entry)
    $sql2 = "CREATE TABLE IF NOT EXISTS `booster_transactions` (
      `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
      `booster_id` BIGINT NOT NULL,
      `user_id` VARCHAR(50) NOT NULL,
      `from_booster_id` BIGINT DEFAULT NULL,
      `from_user_id` VARCHAR(50) DEFAULT NULL,
      `amount` DECIMAL(10,2) NOT NULL,
      `type` ENUM('user_earning', 'company_revenue', 'auto_reentry') NOT NULL,
      `narration` TEXT DEFAULT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      INDEX (`booster_id`),
      INDEX (`user_id`),
      INDEX (`type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql2);
    echo "[SUCCESS] Table 'booster_transactions' created or verified.<br>";

    // 3. Alter user_financial_summary to add booster_wallet & total_booster_income if missing
    try {
        $pdo->exec("ALTER TABLE `user_financial_summary` ADD COLUMN `booster_wallet` DECIMAL(15,4) DEFAULT 0.0000;");
        echo "[SUCCESS] Column 'booster_wallet' added to 'user_financial_summary'.<br>";
    } catch (PDOException $e) {
        echo "[INFO] Column 'booster_wallet' already present in 'user_financial_summary'.<br>";
    }

    try {
        $pdo->exec("ALTER TABLE `user_financial_summary` ADD COLUMN `total_booster_income` DECIMAL(15,4) DEFAULT 0.0000;");
        echo "[SUCCESS] Column 'total_booster_income' added to 'user_financial_summary'.<br>";
    } catch (PDOException $e) {
        echo "[INFO] Column 'total_booster_income' already present in 'user_financial_summary'.<br>";
    }

    // 4. Seed wallet_configurations for booster_wallet
    try {
        $pdo->exec("INSERT INTO `wallet_configurations` (`wallet_type`, `external_withdrawal_fee_percent`, `internal_transfer_fee_percent`) VALUES ('booster_wallet', 10.00, 5.00) ON DUPLICATE KEY UPDATE `wallet_type`='booster_wallet';");
        echo "[SUCCESS] Wallet configuration for 'booster_wallet' seeded.<br>";
    } catch (PDOException $e) {
        echo "[INFO] Wallet configuration step skipped: " . htmlspecialchars($e->getMessage()) . "<br>";
    }

    // 5. Ensure company_ledger row id=1 exists
    try {
        $pdo->exec("INSERT INTO `company_ledger` (`id`, `company_wallet_balance`) VALUES (1, 0.0000) ON DUPLICATE KEY UPDATE `id`=`id`;");
        echo "[SUCCESS] Company ledger initialized.<br>";
    } catch (PDOException $e) {
        echo "[INFO] Company ledger setup step skipped.<br>";
    }

    echo "<br><h3 style='color: #00ff66;'>✨ Migration Completed Successfully!</h3>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div style='color: #ff3333;'>❌ Migration Failed: " . htmlspecialchars($e->getMessage()) . "</div></div>";
}
