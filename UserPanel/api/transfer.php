<?php
session_start();
require_once __DIR__ . '/../../libs/db.php';
require_once __DIR__ . '/../../libs/payouts.php'; // Includes matrix & configurations

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$sourceWallet = $_POST['source_wallet'] ?? '';
$amount = floatval($_POST['amount'] ?? 0);

if (empty($sourceWallet) || $amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid source wallet or transfer amount.']);
    exit;
}

// Allowed package earning wallets
$allowedWallets = [
    'earnings_11_wallet'  => 'earnings_11',
    'earnings_30_wallet'  => 'earnings_30',
    'earnings_60_wallet'  => 'earnings_60',
    'earnings_120_wallet' => 'earnings_120',
    'earnings_240_wallet' => 'earnings_240',
    'earnings_480_wallet' => 'earnings_480',
    'booster_10_wallet'   => 'booster_10',
    'booster_20_wallet'   => 'booster_20',
    'booster_40_wallet'   => 'booster_40',
    'booster_80_wallet'   => 'booster_80',
    'booster_160_wallet'  => 'booster_160',
    'booster_320_wallet'  => 'booster_320',
];

if (!array_key_exists($sourceWallet, $allowedWallets)) {
    echo json_encode(['success' => false, 'message' => 'Invalid source wallet selected.']);
    exit;
}

$walletConfigKey = $allowedWallets[$sourceWallet];

// Begin Database Transaction
$pdo->beginTransaction();

try {
    // 1. Fetch current financial summary to verify balance and direct team count
    $stmt = $pdo->prepare("SELECT * FROM user_financial_summary WHERE user_id = ? FOR UPDATE");
    $stmt->execute([$userId]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$summary || floatval($summary[$sourceWallet]) < $amount) {
        echo json_encode(['success' => false, 'message' => 'Insufficient balance in the selected package wallet.']);
        $pdo->rollBack();
        exit;
    }

    // 1b. Check referral qualification: no of directs >= Level No * 2 (only for main packages)
    $levelMap = [
        'earnings_11_wallet'  => 1,
        'earnings_30_wallet'  => 2,
        'earnings_60_wallet'  => 3,
        'earnings_120_wallet' => 4,
        'earnings_240_wallet' => 5,
        'earnings_480_wallet' => 6,
    ];
    if (array_key_exists($sourceWallet, $levelMap)) {
        $levelNo = $levelMap[$sourceWallet];
        $requiredDirects = $levelNo * 2;
        
        // Check active direct referrals qualification criteria (status='Active' and package >= 11)
        $stmtActive = $pdo->prepare("
            SELECT COUNT(*) 
            FROM users u 
            INNER JOIN user_financial_summary ufs ON u.user_id = ufs.user_id 
            WHERE u.sponsor_id = ? AND u.status = 'Active' AND ufs.my_package >= 11
        ");
        $stmtActive->execute([$userId]);
        $activeDirectsCount = (int)$stmtActive->fetchColumn();

        if ($activeDirectsCount < $requiredDirects) {
            echo json_encode([
                'success' => false,
                'message' => "Transfer Blocked: This operation requires at least $requiredDirects active direct referrals (with active package >= $11). You currently have $activeDirectsCount active directs."
            ]);
            $pdo->rollBack();
            exit;
        }
    }

    // 2. Fetch the dynamic admin charge percentage from wallet_configurations
    $stmtConfig = $pdo->prepare("SELECT internal_transfer_fee_percent FROM wallet_configurations WHERE wallet_type = ?");
    $stmtConfig->execute([$walletConfigKey]);
    $feePercent = floatval($stmtConfig->fetchColumn() ?: 5.00);

    // Calculate admin fee and net transfer amount
    $feeAmount = ($amount * $feePercent) / 100;
    $netAmount = $amount - $feeAmount;

    // 3. Deduct from source package wallet
    $stmtDeduct = $pdo->prepare("UPDATE user_financial_summary SET $sourceWallet = $sourceWallet - ? WHERE user_id = ?");
    $stmtDeduct->execute([$amount, $userId]);

    // 4. Add to Main Deposit Wallet
    $stmtCredit = $pdo->prepare("UPDATE user_financial_summary SET main_deposit_balance = main_deposit_balance + ? WHERE user_id = ?");
    $stmtCredit->execute([$netAmount, $userId]);

    // 5. Update company ledger: decrease total liability, increase company wallet with admin fee, decrease unutilized_funds
    $stmtLedger = $pdo->prepare("
        UPDATE company_ledger 
        SET total_payout_liability_main = total_payout_liability_main - ?,
            company_wallet_balance = company_wallet_balance + ?,
            unutilized_funds = unutilized_funds + ? 
        WHERE id = 1
    ");
    // total liability decreases by the full amount removed from user's package balance
    // company gets the fee
    // unutilized increases by the net amount added to user's main deposit wallet
    $stmtLedger->execute([$amount, $feeAmount, $netAmount]);

    // 6. Record transactions
    // Transaction A: Deduction from package earnings wallet
    $narrationDeduct = "Internal transfer of $" . number_format($amount, 2) . " from " . str_replace('_', ' ', $sourceWallet) . " to Main Wallet";
    $stmtTxA = $pdo->prepare("
        INSERT INTO transactions (user_id, transaction_type, amount, wallet_type, status, narration) 
        VALUES (?, 'internal_transfer', ?, ?, 'Completed', ?)
    ");
    $stmtTxA->execute([$userId, $amount, $sourceWallet, $narrationDeduct]);

    // Transaction B: Admin fee deduction
    if ($feeAmount > 0) {
        $narrationFee = "Admin charge of " . $feePercent . "% ($" . number_format($feeAmount, 2) . ") applied on internal transfer";
        $stmtTxB = $pdo->prepare("
            INSERT INTO transactions (user_id, transaction_type, amount, wallet_type, status, narration) 
            VALUES (?, 'admin_charge', ?, ?, 'Completed', ?)
        ");
        $stmtTxB->execute([$userId, $feeAmount, $sourceWallet, $narrationFee]);
    }

    // Transaction C: Credit to Main Deposit Wallet
    $narrationCredit = "Credit of $" . number_format($netAmount, 2) . " via internal transfer (Net after fee)";
    $stmtTxC = $pdo->prepare("
        INSERT INTO transactions (user_id, transaction_type, amount, wallet_type, status, narration) 
        VALUES (?, 'deposit', ?, 'main_deposit', 'Completed', ?)
    ");
    $stmtTxC->execute([$userId, $netAmount, $narrationCredit]);

    // Commit Transaction
    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Successfully transferred $' . number_format($netAmount, 2) . ' to your Main Wallet (Admin Charge: ' . $feePercent . '% / $' . number_format($feeAmount, 2) . ').'
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
