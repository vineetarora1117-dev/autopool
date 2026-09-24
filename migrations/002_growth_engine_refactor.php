<?php
require_once __DIR__ . '/../libs/db.php';

try {
    // 1. Rename tables (ignore if already renamed)
    $pdo->exec("RENAME TABLE `user_boosters` TO `user_growth_engines`");
} catch (Exception $e) { echo "Note: " . $e->getMessage() . "\n"; }

try {
    $pdo->exec("RENAME TABLE `booster_transactions` TO `growth_engine_transactions`");
} catch (Exception $e) { echo "Note: " . $e->getMessage() . "\n"; }

try {
    // 2. Add columns to user_financial_summary
    $pdo->exec("ALTER TABLE `user_financial_summary` CHANGE `booster_wallet` `growth_engine_wallet` DECIMAL(15,4) DEFAULT 0.0000");
} catch (Exception $e) { echo "Note: " . $e->getMessage() . "\n"; }

try {
    $pdo->exec("ALTER TABLE `user_financial_summary` ADD COLUMN IF NOT EXISTS `total_growth_engine_income` DECIMAL(15,4) DEFAULT 0.0000");
} catch (Exception $e) { echo "Note: " . $e->getMessage() . "\n"; }

try {
    // 3. Update master transactions log
    $pdo->exec("UPDATE `transactions` SET `wallet_type` = 'growth_engine_wallet' WHERE `wallet_type` = 'booster_wallet'");
    $pdo->exec("UPDATE `transactions` SET `transaction_type` = 'growth_engine_purchase' WHERE `transaction_type` = 'booster_purchase'");
    $pdo->exec("UPDATE `transactions` SET `transaction_type` = 'growth_engine_income' WHERE `transaction_type` = 'booster_income' AND `wallet_type` = 'growth_engine_wallet'");

    // 4. Update wallet_configurations
    $pdo->exec("UPDATE `wallet_configurations` SET `wallet_type` = 'growth_engine_wallet' WHERE `wallet_type` = 'booster_wallet'");

    echo "[SUCCESS] Database refactored for Growth Engine.\n";

} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}
?>
