<?php
require_once __DIR__ . '/../../libs/db.php';
require_once __DIR__ . '/../../libs/admin_auth.php';

header('Content-Type: application/json');

// NOTE: requireAdminLogin() logic assumed to work or just mock
if (function_exists('requireAdminLogin')) {
    requireAdminLogin();
} else {
    // If it doesn't exist just basic check
    session_start();
    if (!isset($_SESSION['admin_logged_in'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
}

$action = $_POST['action'] ?? '';

if ($action === 'update_status') {
    $id = $_POST['id'] ?? 0;
    $status = ucfirst(strtolower(trim($_POST['status'] ?? ''))); // Approved or Rejected

    if (!$id || !in_array($status, ['Approved', 'Rejected'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT * FROM deposit_requests WHERE id = ? AND status = 'Pending' FOR UPDATE");
        $stmt->execute([$id]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$request) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Request not found or already processed']);
            exit;
        }

        $updateStmt = $pdo->prepare("UPDATE deposit_requests SET status = ?, updated_at = NOW() WHERE id = ?");
        $updateStmt->execute([$status, $id]);

        if ($status === 'Approved') {
            // Add to user main_deposit_balance
            $pdo->prepare("UPDATE user_financial_summary SET main_deposit_balance = main_deposit_balance + ? WHERE user_id = ?")->execute([$request['amount'], $request['user_id']]);
            
            // Update company ledger
            $pdo->prepare("UPDATE company_ledger SET total_funds_received = total_funds_received + ?, unutilized_funds = unutilized_funds + ?")->execute([$request['amount'], $request['amount']]);
            
            // Insert transaction for user
            $narration = "Deposit of $" . number_format($request['amount'], 2) . " approved by Admin";
            $pdo->prepare("INSERT INTO transactions (user_id, transaction_type, amount, wallet_type, status, narration) VALUES (?, 'deposit', ?, 'main_deposit', 'Completed', ?)")->execute([$request['user_id'], $request['amount'], $narration]);
            
            // Insert transaction for company
            $narrationComp = "Fund approved for user " . $request['user_id'];
            $pdo->prepare("INSERT INTO transactions (user_id, transaction_type, amount, wallet_type, status, narration) VALUES ('SA000001', 'deposit', ?, 'company_wallet', 'Completed', ?)")->execute([$request['amount'], $narrationComp]);
        } else {
             // Insert transaction
             $narration = "Deposit rejected: TxHash " . $request['tx_hash'];
             $pdo->prepare("INSERT INTO transactions (user_id, transaction_type, amount, wallet_type, status, narration) VALUES (?, 'deposit', ?, 'main_deposit', 'Rejected', ?)")->execute([$request['user_id'], $request['amount'], $narration]);
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Request ' . $status . ' successfully']);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
