<?php
require_once __DIR__ . '/../libs/db.php';

try {
    echo "1. Disabling foreign key checks...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    echo "2. Deleting historical activity, matrices, transactions, and logs...\n";
    $tablesToDelete = [
        'transactions',
        'withdrawal_requests',
        'deposit_requests',
        'support_tickets',
        'package_matrices',
        'user_boosters',
        'booster_transactions',
        'booster_matrices'
    ];
    foreach ($tablesToDelete as $t) {
        $pdo->exec("DELETE FROM `$t`;");
        $pdo->exec("ALTER TABLE `$t` AUTO_INCREMENT = 1;");
        echo "   - Cleared `$t`\n";
    }

    echo "3. Deleting non-root users...\n";
    $pdo->exec("DELETE FROM users WHERE user_id != 'SA000001';");
    $pdo->exec("DELETE FROM user_financial_summary WHERE user_id != 'SA000001';");
    $pdo->exec("DELETE FROM user_packages WHERE user_id != 'SA000001';");

    echo "4. Ensuring Root User (SA000001) exists & is Active...\n";
    $stmtRoot = $pdo->prepare("SELECT user_id FROM users WHERE user_id = 'SA000001'");
    $stmtRoot->execute();
    if (!$stmtRoot->fetch()) {
        $hashedPass = password_hash('123456', PASSWORD_BCRYPT);
        $pdo->exec("
            INSERT INTO users (id, user_id, sponsor_id, name, email, phone, password, status) 
            VALUES (1, 'SA000001', NULL, 'SAPG', 'admin@sapg.com', '0000000000', '$hashedPass', 'Active')
        ");
    } else {
        $pdo->exec("UPDATE users SET status = 'Active' WHERE user_id = 'SA000001';");
    }

    echo "5. Resetting Root User Financial Summary...\n";
    $pdo->exec("
        INSERT INTO user_financial_summary (
            user_id, my_package, direct_team_count, total_active_team_count, total_inactive_team_count,
            strong_leg_count, other_legs_count, main_deposit_balance, earnings_11_wallet, earnings_30_wallet,
            earnings_60_wallet, earnings_120_wallet, earnings_240_wallet, earnings_480_wallet,
            booster_10_wallet, booster_20_wallet, booster_40_wallet, booster_80_wallet,
            booster_160_wallet, booster_320_wallet, total_direct_referral_income, total_team_level_income,
            total_global_autopool_income, total_booster_income, total_reward_income, total_withdrawal_amount, net_income, booster_wallet
        ) VALUES (
            'SA000001', 480.00, 0, 0, 0,
            0, 0, 0.0000, 0.0000, 0.0000,
            0.0000, 0.0000, 0.0000, 0.0000,
            0.0000, 0.0000, 0.0000, 0.0000,
            0.0000, 0.0000, 0.0000, 0.0000,
            0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000
        ) ON DUPLICATE KEY UPDATE
            my_package = 480.00, direct_team_count = 0, total_active_team_count = 0, total_inactive_team_count = 0,
            strong_leg_count = 0, other_legs_count = 0, main_deposit_balance = 0.0000, earnings_11_wallet = 0.0000,
            earnings_30_wallet = 0.0000, earnings_60_wallet = 0.0000, earnings_120_wallet = 0.0000, earnings_240_wallet = 0.0000,
            earnings_480_wallet = 0.0000, booster_10_wallet = 0.0000, booster_20_wallet = 0.0000, booster_40_wallet = 0.0000,
            booster_80_wallet = 0.0000, booster_160_wallet = 0.0000, booster_320_wallet = 0.0000, total_direct_referral_income = 0.0000,
            total_team_level_income = 0.0000, total_global_autopool_income = 0.0000, total_booster_income = 0.0000,
            total_reward_income = 0.0000, total_withdrawal_amount = 0.0000, net_income = 0.0000, booster_wallet = 0.0000;
    ");

    echo "6. Activating all 12 packages for Root User (SA000001)...\n";
    $allPackages = [
        'main_11', 'main_30', 'main_60', 'main_120', 'main_240', 'main_480',
        'booster_10', 'booster_20', 'booster_40', 'booster_80', 'booster_160', 'booster_320'
    ];
    $stmtPkg = $pdo->prepare("
        INSERT INTO user_packages (user_id, package_type, is_active, funded_by) 
        VALUES ('SA000001', ?, 1, 'SA000001') 
        ON DUPLICATE KEY UPDATE is_active = 1, funded_by = 'SA000001'
    ");
    foreach ($allPackages as $pkg) {
        $stmtPkg->execute([$pkg]);
        echo "   - Activated package `$pkg` for SA000001\n";
    }

    echo "7. Seeding Root User matrix root nodes in package_matrices...\n";
    $stmtMatrix = $pdo->prepare("
        INSERT INTO package_matrices (user_id, package_type, upline_id, position_slot, matrix_level) 
        VALUES ('SA000001', ?, NULL, 1, 1)
    ");
    foreach (['main_11', 'main_30', 'main_60', 'main_120', 'main_240', 'main_480'] as $mpkg) {
        $stmtMatrix->execute([$mpkg]);
    }

    echo "8. Resetting Company Ledger metrics to 0...\n";
    $pdo->exec("
        UPDATE company_ledger SET 
            total_funds_received = 0.0000,
            unutilized_funds = 0.0000,
            invested_funds = 0.0000,
            total_usdt_paid_out = 0.0000,
            company_wallet_balance = 0.0000,
            total_payout_liability_main = 0.0000,
            total_payout_liability_booster = 0.0000,
            total_held_sponsor_income = 0.0000
        WHERE id = 1;
    ");

    echo "9. Re-enabling foreign key checks...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "\n[SUCCESS] Database reset complete! Only root user SA000001 remains, with all 12 packages activated.\n";

} catch (Exception $e) {
    echo "[ERROR] Database reset failed: " . $e->getMessage() . "\n";
}
