<?php
session_start();
require_once __DIR__ . '/../../libs/db.php';
require_once __DIR__ . '/../../libs/auth.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$userId = $_SESSION['user_id'];

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'update_wallet') {
    $walletAddress = trim($_POST['wallet_address'] ?? '');

    if (empty($walletAddress)) {
        echo json_encode(['success' => false, 'message' => 'Wallet address cannot be empty']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET wallet_address = ? WHERE user_id = ?");
        $stmt->execute([$walletAddress, $userId]);
        echo json_encode(['success' => true, 'message' => 'Wallet address updated successfully!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'update_profile') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($email) || empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'All profile fields are required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE user_id = ?");
        $stmt->execute([$name, $email, $phone, $userId]);
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
