<?php
session_start();
require_once __DIR__ . '/../../libs/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'verify_user') {
    $targetId = trim($_POST['target_user_id'] ?? $_GET['target_user_id'] ?? '');
    if (empty($targetId)) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit;
    }
    
    if (strcasecmp($targetId, $userId) === 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot transfer funds to yourself']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT user_id, name, status FROM users WHERE user_id = ?");
    $stmt->execute([$targetId]);
    $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userRow) {
        echo json_encode(['success' => false, 'message' => 'User ID not found']);
        exit;
    }
    
    if ($userRow['status'] === 'Blocked') {
        echo json_encode(['success' => false, 'message' => 'User account is blocked']);
        exit;
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'User verified successfully', 
        'name' => $userRow['name'],
        'user_id' => $userRow['user_id']
    ]);
    exit;
}

if ($action === 'transfer') {
    $targetId = trim($_POST['target_user_id'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    
    if (empty($targetId) || $amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid target user ID or transfer amount.']);
        exit;
    }
    
    if (strcasecmp($targetId, $userId) === 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot transfer funds to yourself.']);
        exit;
    }
    
    // Begin Database Transaction
    $pdo->beginTransaction();
    
    try {
        // 1. Verify sender exists and fetch balance
        $stmtSender = $pdo->prepare("SELECT main_deposit_balance FROM user_financial_summary WHERE user_id = ? FOR UPDATE");
        $stmtSender->execute([$userId]);
        $senderBalance = $stmtSender->fetchColumn();
        
        if ($senderBalance === false) {
            echo json_encode(['success' => false, 'message' => 'Sender account details not found.']);
            $pdo->rollBack();
            exit;
        }
        
        $senderBalance = floatval($senderBalance);
        if ($senderBalance < $amount) {
            echo json_encode(['success' => false, 'message' => 'Insufficient balance in your Main Wallet. Available: $' . number_format($senderBalance, 2)]);
            $pdo->rollBack();
            exit;
        }
        
        // 2. Verify recipient exists and is not blocked
        $stmtRecipient = $pdo->prepare("SELECT u.name, u.status, ufs.user_id FROM users u JOIN user_financial_summary ufs ON u.user_id = ufs.user_id WHERE u.user_id = ? FOR UPDATE");
        $stmtRecipient->execute([$targetId]);
        $recipientRow = $stmtRecipient->fetch(PDO::FETCH_ASSOC);
        
        if (!$recipientRow) {
            echo json_encode(['success' => false, 'message' => 'Recipient User ID not found.']);
            $pdo->rollBack();
            exit;
        }
        
        if ($recipientRow['status'] === 'Blocked') {
            echo json_encode(['success' => false, 'message' => 'Recipient account is blocked.']);
            $pdo->rollBack();
            exit;
        }
        
        $recipientName = $recipientRow['name'];
        $recipientRealId = $recipientRow['user_id'];
        
        // 3. Fetch sender name
        $stmtSenderName = $pdo->prepare("SELECT name FROM users WHERE user_id = ?");
        $stmtSenderName->execute([$userId]);
        $senderName = $stmtSenderName->fetchColumn() ?: $userId;
        
        // 4. Fetch the dynamic transfer fee percentage from settings table
        $stmtFee = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'fund_transfer_fee_percent'");
        $stmtFee->execute();
        $feePercent = floatval($stmtFee->fetchColumn() ?: 10.00);
        
        // 5. Calculate admin fee and net transfer amount
        $feeAmount = ($amount * $feePercent) / 100;
        $netAmount = $amount - $feeAmount;
        
        // 6. Deduct full transfer amount from sender
        $stmtDeduct = $pdo->prepare("UPDATE user_financial_summary SET main_deposit_balance = main_deposit_balance - ? WHERE user_id = ?");
        $stmtDeduct->execute([$amount, $userId]);
        
        // 7. Credit net amount to recipient
        $stmtCredit = $pdo->prepare("UPDATE user_financial_summary SET main_deposit_balance = main_deposit_balance + ? WHERE user_id = ?");
        $stmtCredit->execute([$netAmount, $recipientRealId]);
        
        // 8. Update company ledger (add fee to company_wallet_balance, deduct fee from unutilized_funds)
        $stmtLedger = $pdo->prepare("
            UPDATE company_ledger 
            SET company_wallet_balance = company_wallet_balance + ?,
                unutilized_funds = unutilized_funds - ?
            WHERE id = 1
        ");
        $stmtLedger->execute([$feeAmount, $feeAmount]);
        
        // 9. Record transactions
        // Transaction A: Debit for Sender (Net transferred amount)
        $narrationSender = "Fund transfer of $" . number_format($netAmount, 2) . " to " . htmlspecialchars($recipientName) . " (" . htmlspecialchars($recipientRealId) . ")";
        $stmtTxSender = $pdo->prepare("
            INSERT INTO transactions (user_id, transaction_type, amount, wallet_type, status, narration) 
            VALUES (?, 'wallet_transfer_sent', ?, 'main_deposit', 'Completed', ?)
        ");
        $stmtTxSender->execute([$userId, $netAmount, $narrationSender]);
        
        // Transaction B: Admin fee deduction for Sender (if > 0)
        if ($feeAmount > 0) {
            $narrationFee = number_format($feePercent, 2) . "% admin fee deducted on fund transfer of $" . number_format($amount, 2) . " to " . htmlspecialchars($recipientRealId);
            $stmtTxFee = $pdo->prepare("
                INSERT INTO transactions (user_id, transaction_type, amount, wallet_type, status, narration) 
                VALUES (?, 'admin_charge', ?, 'main_deposit', 'Completed', ?)
            ");
            $stmtTxFee->execute([$userId, $feeAmount, $narrationFee]);
        }
        
        // Transaction C: Credit for Recipient (Net amount after fee)
        $narrationRecipient = "Received fund transfer of $" . number_format($netAmount, 2) . " (Transfer amount: $" . number_format($amount, 2) . ") from " . htmlspecialchars($senderName) . " (" . htmlspecialchars($userId) . ")";
        $stmtTxRecipient = $pdo->prepare("
            INSERT INTO transactions (user_id, transaction_type, amount, wallet_type, status, narration) 
            VALUES (?, 'wallet_transfer_received', ?, 'main_deposit', 'Completed', ?)
        ");
        $stmtTxRecipient->execute([$recipientRealId, $netAmount, $narrationRecipient]);
        
        // Commit Transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Successfully transferred $' . number_format($netAmount, 2) . ' to ' . htmlspecialchars($recipientName) . ' (' . htmlspecialchars($recipientRealId) . ').'
        ]);
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>
