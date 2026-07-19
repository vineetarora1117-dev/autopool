<?php
session_start();
require_once __DIR__ . '/../../libs/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$userId = $_SESSION['user_id'];

$amount = $_POST['amount'] ?? 0;
$txHash = $_POST['tx_hash'] ?? '';

if ($amount <= 0 || empty($txHash)) {
    echo json_encode(['success' => false, 'message' => 'Amount and TxHash are required']);
    exit;
}

$proofImage = null;
if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../../assets/uploads/proofs/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $ext = pathinfo($_FILES['proof_image']['name'], PATHINFO_EXTENSION);
    $allowedExt = ['jpg', 'jpeg', 'png', 'pdf'];
    if (in_array(strtolower($ext), $allowedExt)) {
        $filename = $userId . '_' . time() . '.' . $ext;
        $targetPath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['proof_image']['tmp_name'], $targetPath)) {
            $proofImage = 'assets/uploads/proofs/' . $filename;
        }
    }
}

try {
    $stmt = $pdo->prepare("INSERT INTO deposit_requests (user_id, amount, tx_hash, proof_image, status) VALUES (?, ?, ?, ?, 'Pending')");
    $stmt->execute([$userId, $amount, $txHash, $proofImage]);
    
    $narration = "Deposit request of $" . number_format($amount, 2) . " submitted. TxHash: $txHash";
    $stmtTx = $pdo->prepare("INSERT INTO transactions (user_id, transaction_type, amount, status, narration) VALUES (?, 'deposit', ?, 'Pending', ?)");
    $stmtTx->execute([$userId, $amount, $narration]);
    
    echo json_encode(['success' => true, 'message' => 'Deposit request submitted successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
