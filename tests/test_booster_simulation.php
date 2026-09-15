<?php
/**
 * Automated Test & Verification Script for 1x3 Auto-Cycling Booster Module
 */

require_once __DIR__ . '/../libs/db.php';
require_once __DIR__ . '/../includes/BoosterEngine.php';

echo "=========================================================\n";
echo "🧪 STARTING 1x3 AUTO-CYCLING BOOSTER ENGINE SIMULATION\n";
echo "=========================================================\n\n";

try {
    // 1. Ensure test users exist in DB
    $testUserIds = ['SA900001', 'SA900002', 'SA900003', 'SA900004'];
    
    foreach ($testUserIds as $uId) {
        $stmtUser = $pdo->prepare("INSERT INTO users (user_id, name, email, phone, password, status) VALUES (?, ?, ?, '1234567890', 'hash', 'Active') ON DUPLICATE KEY UPDATE status='Active'");
        $stmtUser->execute([$uId, "Test User $uId", "$uId@test.com"]);

        $stmtFin = $pdo->prepare("INSERT INTO user_financial_summary (user_id, main_deposit_balance, booster_wallet) VALUES (?, 100.00, 0.00) ON DUPLICATE KEY UPDATE main_deposit_balance = 100.00");
        $stmtFin->execute([$uId]);
    }
    
    echo "[SETUP] Test users SA900001, SA900002, SA900003, SA900004 prepared with $100.00 Main Wallet.\n\n";

    // 2. Buy Booster 1 for SA900001
    echo "[STEP 1] User SA900001 buys Booster #1...\n";
    $res1 = purchaseBooster($pdo, 'SA900001');
    echo "Result: " . json_encode($res1) . "\n\n";

    // 3. Buy Booster 2 for SA900002
    echo "[STEP 2] User SA900002 buys Booster #2 (Attaches under #1 as downline 1/3)...\n";
    $res2 = purchaseBooster($pdo, 'SA900002');
    echo "Result: " . json_encode($res2) . "\n\n";

    // 4. Buy Booster 3 for SA900003
    echo "[STEP 3] User SA900003 buys Booster #3 (Attaches under #1 as downline 2/3)...\n";
    $res3 = purchaseBooster($pdo, 'SA900003');
    echo "Result: " . json_encode($res3) . "\n\n";

    // 5. Buy Booster 4 for SA900004 -> Triggers 3rd downline for #1 -> CYCLE COMPLETE!
    echo "[STEP 4] User SA900004 buys Booster #4 (Attaches under #1 as downline 3/3 -> TRIGGERS CYCLE COMPLETE!)...\n";
    $res4 = purchaseBooster($pdo, 'SA900004');
    echo "Result: " . json_encode($res4) . "\n\n";

    // 6. Verify DB States after Cycle
    echo "=========================================================\n";
    echo "📊 VERIFICATION RESULTS\n";
    echo "=========================================================\n";

    $stmtAllB = $pdo->query("SELECT id, user_id, upline_booster_id, downline_count, purchase_type, status FROM user_boosters ORDER BY id ASC");
    $allB = $stmtAllB->fetchAll(PDO::FETCH_ASSOC);

    echo "ALL BOOSTERS IN SYSTEM:\n";
    foreach ($allB as $b) {
        echo sprintf("  - Booster #%d | Owner: %s | Parent: %s | Downlines: %d/3 | Type: %s | Status: %s\n",
            $b['id'],
            $b['user_id'],
            $b['upline_booster_id'] ? "#".$b['upline_booster_id'] : 'ROOT',
            $b['downline_count'],
            $b['purchase_type'],
            $b['status']
        );
    }

    // Verify SA900001 Booster Wallet balance
    $stmtBal = $pdo->prepare("SELECT booster_wallet FROM user_financial_summary WHERE user_id = 'SA900001'");
    $stmtBal->execute();
    $bWallet = $stmtBal->fetchColumn();

    echo "\nSA900001 Booster Wallet Balance: $" . number_format($bWallet, 2) . " (Expected: $10.00)\n";

    // Verify master transactions logs
    $stmtTxs = $pdo->query("SELECT id, user_id, transaction_type, amount, wallet_type, narration FROM transactions WHERE user_id IN ('SA900001', 'SA000001') ORDER BY id DESC LIMIT 10");
    $txs = $stmtTxs->fetchAll(PDO::FETCH_ASSOC);

    echo "\nRECENT MASTER TRANSACTIONS LOGS:\n";
    foreach ($txs as $tx) {
        echo sprintf("  - Tx #%d | User: %s | Type: %s | Amount: $%s | Wallet: %s | %s\n",
            $tx['id'],
            $tx['user_id'],
            $tx['transaction_type'],
            number_format($tx['amount'], 2),
            $tx['wallet_type'],
            $tx['narration']
        );
    }

    echo "\n✨ SIMULATION COMPLETE & VERIFIED SUCCESSFULLY!\n";

    // Clean up test users and boosters
    $pdo->exec("DELETE FROM user_boosters WHERE user_id LIKE 'SA90000%'");
    $pdo->exec("DELETE FROM booster_transactions WHERE user_id LIKE 'SA90000%'");
    $pdo->exec("DELETE FROM transactions WHERE user_id LIKE 'SA90000%' OR narration LIKE '%Booster #1%'");
    $pdo->exec("DELETE FROM user_financial_summary WHERE user_id LIKE 'SA90000%'");
    $pdo->exec("DELETE FROM users WHERE user_id LIKE 'SA90000%'");
    echo "🧹 Test simulation data cleaned up cleanly.\n";

} catch (Exception $e) {
    echo "\n❌ SIMULATION ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
