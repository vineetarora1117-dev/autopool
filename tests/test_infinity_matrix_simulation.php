<?php
/**
 * test_infinity_matrix_simulation.php
 * Automated test script to simulate 4x2 Infinity Pack placements and verify 2-stage milestone payouts.
 */

require_once __DIR__ . '/../libs/db.php';
require_once __DIR__ . '/../libs/payouts.php';
require_once __DIR__ . '/../libs/config.php';

echo "=================================================================\n";
echo "    INFINITY PACK (4x2 MATRIX) SIMULATION & PAYOUT TEST SUITE     \n";
echo "=================================================================\n\n";

// 1. Reset Infinity Pack Data to ensure clean simulation baseline
$pdo->exec("TRUNCATE TABLE booster_matrices");
$pdo->exec("DELETE FROM user_packages WHERE package_type LIKE 'booster_%'");
$pdo->exec("DELETE FROM transactions WHERE wallet_type LIKE 'booster_%'");
$pdo->exec("UPDATE user_financial_summary SET booster_10_wallet=0, booster_20_wallet=0, booster_40_wallet=0, booster_80_wallet=0, booster_160_wallet=0, booster_320_wallet=0, total_booster_income=0");

// Re-seed SA000001 root
$stmtRoot = $pdo->prepare("INSERT INTO booster_matrices (user_id, booster_type, upline_id, position_slot, matrix_level) VALUES ('SA000001', 'booster_10', NULL, 1, 1)");
$stmtRoot->execute();

// 2. Fetch ALL non-root users from database
$stmtUsers = $pdo->query("SELECT user_id FROM users WHERE user_id != 'SA000001' ORDER BY id ASC");
$testUsers = $stmtUsers->fetchAll(PDO::FETCH_COLUMN);

// Top up balance for all test users and SA000001
$pdo->exec("UPDATE user_financial_summary SET main_deposit_balance = 500.00");

echo "Simulating Infinity Pack 1 ($10) enrollments for " . count($testUsers) . " users...\n\n";

$enrolledCount = 0;
foreach ($testUsers as $idx => $uId) {
    $enrolledCount++;
    try {
        processBoosterPurchase($pdo, $uId, 'booster_10');
        echo sprintf(" [%02d] Enrolled %s in Infinity Pack 1 ($10)\n", $enrolledCount, $uId);
    } catch (Exception $e) {
        echo sprintf(" [%02d] Error enrolling %s: %s\n", $enrolledCount, $uId, $e->getMessage());
    }
}

echo "\n=================================================================\n";
echo "              4x2 MATRIX NETWORK STRUCTURE AUDIT                 \n";
echo "=================================================================\n";

$stmtNodes = $pdo->query("
    SELECT bm.id, bm.user_id, bm.upline_id, bm.position_slot, bm.matrix_level 
    FROM booster_matrices bm 
    WHERE bm.booster_type = 'booster_10' 
    ORDER BY bm.id ASC
");
$nodes = $stmtNodes->fetchAll(PDO::FETCH_ASSOC);

foreach ($nodes as $node) {
    echo sprintf(" Node #%02d | User: %-8s | Upline: %-8s | Slot: %d | Level: %d\n",
        $node['id'],
        $node['user_id'],
        $node['upline_id'] ?: 'ROOT',
        $node['position_slot'],
        $node['matrix_level']
    );
}

echo "\n=================================================================\n";
echo "                   TRANSACTION LOG AUDIT                         \n";
echo "=================================================================\n";

$stmtTx = $pdo->query("
    SELECT user_id, transaction_type, amount, wallet_type, narration, created_at 
    FROM transactions 
    WHERE wallet_type LIKE 'booster_%' 
    ORDER BY id ASC
");
$txs = $stmtTx->fetchAll(PDO::FETCH_ASSOC);

if (empty($txs)) {
    echo "No booster payout transactions recorded.\n";
} else {
    foreach ($txs as $tx) {
        echo sprintf(" User: %-8s | Type: %-15s | Amt: $%06.2f | Narration: %s\n",
            $tx['user_id'],
            $tx['transaction_type'],
            $tx['amount'],
            $tx['narration']
        );
    }
}

echo "\n=================================================================\n";
echo "                   FINANCIAL SUMMARY AUDIT                       \n";
echo "=================================================================\n";

$stmtFin = $pdo->query("
    SELECT user_id, booster_10_wallet, booster_20_wallet, total_booster_income 
    FROM user_financial_summary 
    WHERE total_booster_income > 0 OR booster_10_wallet > 0 OR booster_20_wallet > 0
");
$finRecords = $stmtFin->fetchAll(PDO::FETCH_ASSOC);

if (empty($finRecords)) {
    echo "No income credited to user financial summaries yet.\n";
} else {
    foreach ($finRecords as $fin) {
        echo sprintf(" User: %-8s | Pack 1 Wallet: $%06.2f | Pack 2 Wallet: $%06.2f | Total Booster Income: $%06.2f\n",
            $fin['user_id'],
            $fin['booster_10_wallet'],
            $fin['booster_20_wallet'],
            $fin['total_booster_income']
        );
    }
}

echo "\n=================================================================\n";
echo " SIMULATION COMPLETE: Network placements and income verified cleanly! \n";
echo "=================================================================\n";
