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
    echo json_encode(['success' => false, 'message' => 'Wallet-to-wallet transfers have been disabled. Only withdrawal requests can be submitted.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>
