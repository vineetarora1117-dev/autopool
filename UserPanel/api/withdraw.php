<?php
session_start();
require_once __DIR__ . '/../../libs/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$sourceWallet = $_POST['source_wallet'] ?? '';
$destinationAddress = trim($_POST['destination_address'] ?? '');
$amount = floatval($_POST['amount'] ?? 0);

if (empty($sourceWallet) || empty($destinationAddress) || $amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters. Please verify inputs.']);
    exit;
}

// Map allowed earning wallets
$allowedWallets = [
    'earnings_11_wallet'  => 'earnings_11',
    'earnings_30_wallet'  => 'earnings_30',
    'earnings_60_wallet'  => 'earnings_60',
    'earnings_120_wallet' => 'earnings_120',
    'earnings_240_wallet' => 'earnings_240',
    'earnings_480_wallet' => 'earnings_480',
];

if (!array_key_exists($sourceWallet, $allowedWallets)) {
    echo json_encode(['success' => false, 'message' => 'Invalid source wallet selected.']);
    exit;
}

$walletConfigKey = $allowedWallets[$sourceWallet];

// Begin Database Transaction
$pdo->beginTransaction();

try {
    // 1. Fetch current financial summary to verify balance and directs count
    $stmt = $pdo->prepare("SELECT * FROM user_financial_summary WHERE user_id = ? FOR UPDATE");
    $stmt->execute([$userId]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$summary || floatval($summary[$sourceWallet]) < $amount) {
        echo json_encode(['success' => false, 'message' => 'Insufficient balance in the selected earning wallet.']);
        $pdo->rollBack();
        exit;
    }

    // 2. Check referral qualification criteria: no of directs >= Level No * 2
    $levelMap = [
        'earnings_11_wallet'  => 1,
        'earnings_30_wallet'  => 2,
        'earnings_60_wallet'  => 3,
        'earnings_120_wallet' => 4,
        'earnings_240_wallet' => 5,
        'earnings_480_wallet' => 6,
    ];
    $levelNo = $levelMap[$sourceWallet];
    $requiredDirects = $levelNo * 2;
    $directsCount = intval($summary['direct_team_count'] ?? 0);

    if ($directsCount < $requiredDirects) {
        echo json_encode([
            'success' => false, 
            'message' => "Withdrawal Blocked: Withdrawals from the level $levelNo wallet require a minimum of $requiredDirects direct referrals. You currently have $directsCount directs."
        ]);
        $pdo->rollBack();
        exit;
    }

    // 3. Fetch withdrawal fee percentage configuration
    $stmtConfig = $pdo->prepare("SELECT withdrawal_fee_percent FROM wallet_configurations WHERE wallet_type = ?");
    $stmtConfig->execute([$walletConfigKey]);
    $feePercent = floatval($stmtConfig->fetchColumn() ?: 10.00);

    // Calculate fee and net amount
    $feeAmount = ($amount * $feePercent) / 100;
    $netAmount = $amount - $feeAmount;

    // 4. Deduct immediately from user's earning wallet to lock it
    $stmtDeduct = $pdo->prepare("UPDATE user_financial_summary SET $sourceWallet = $sourceWallet - ? WHERE user_id = ?");
    $stmtDeduct->execute([$amount, $userId]);

    // 5. Insert withdrawal request row (Pending)
    $stmtRequest = $pdo->prepare("
        INSERT INTO withdrawal_requests (user_id, amount, wallet_type, fee_amount, net_amount, destination_address, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'Pending')
    ");
    $stmtRequest->execute([$userId, $amount, $sourceWallet, $feeAmount, $netAmount, $destinationAddress]);

    // 6. Record transaction ledger log (Pending status)
    $narration = "Withdrawal request of $" . number_format($amount, 2) . " submitted (Net: $" . number_format($netAmount, 2) . ") to address " . substr($destinationAddress, 0, 8) . "...";
    $stmtTx = $pdo->prepare("
        INSERT INTO transactions (user_id, transaction_type, amount, wallet_type, status, narration) 
        VALUES (?, 'withdrawal', ?, ?, 'Pending', ?)
    ");
    $stmtTx->execute([$userId, $amount, $sourceWallet, $narration]);

    // Commit Transaction
    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Withdrawal request for $' . number_format($amount, 2) . ' submitted successfully. It is now pending admin review.'
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
