<?php
require_once __DIR__ . '/../../libs/db.php';
require_once __DIR__ . '/../../libs/admin_auth.php';

header('Content-Type: application/json');
requireAdminLogin();

$action = $_POST['action'] ?? '';

if ($action === 'update_status') {
    $id = $_POST['id'] ?? 0;
    $status = $_POST['status'] ?? ''; // approved or rejected

    if (!$id || !in_array($status, ['approved', 'rejected'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT * FROM withdrawal_requests WHERE id = ? AND status = 'pending' FOR UPDATE");
        $stmt->execute([$id]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$request) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Request not found or already processed']);
            exit;
        }

        $updateStmt = $pdo->prepare("UPDATE withdrawal_requests SET status = ?, updated_at = NOW() WHERE id = ?");
        $updateStmt->execute([$status, $id]);

        if ($status === 'approved') {
            // Deduct already happened on request? Assuming yes, otherwise deduct here. Let's assume deducted on request creation.
            // If deducted on creation, we just update company ledger here.
            $pdo->prepare("UPDATE company_ledger SET total_usdt_paid_out = total_usdt_paid_out + ?")->execute([$request['net_amount']]);
            
            // Insert transaction
            $pdo->prepare("INSERT INTO transactions (user_id, amount, type, wallet_type, status, narration) VALUES (?, ?, 'withdrawal', ?, 'success', ?)")->execute([$request['user_id'], $request['amount'], $request['wallet_type'], "Withdrawal approved"]);
        } else {
            // Refund to user wallet
            $walletField = $request['wallet_type'];
            // Needs mapping to summary column
            if ($walletField) {
                // simple mapping assumption
                $pdo->prepare("UPDATE user_financial_summary SET $walletField = $walletField + ? WHERE user_id = ?")->execute([$request['amount'], $request['user_id']]);
            }
             // Insert transaction
             $pdo->prepare("INSERT INTO transactions (user_id, amount, type, wallet_type, status, narration) VALUES (?, ?, 'withdrawal', ?, 'failed', ?)")->execute([$request['user_id'], $request['amount'], $request['wallet_type'], "Withdrawal rejected & refunded"]);
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
