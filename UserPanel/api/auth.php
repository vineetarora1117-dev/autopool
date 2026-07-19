<?php
session_start();
require_once __DIR__ . '/../../libs/db.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'login') {
    $userId = $_POST['user_id'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!$userId || !$password) {
        echo json_encode(['success' => false, 'message' => 'User ID and password are required']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT id, user_id, password, status FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] === 'Blocked') {
            echo json_encode(['success' => false, 'message' => 'Account is blocked']);
            exit;
        }
        
        $_SESSION['user_id'] = $user['user_id'];
        echo json_encode(['success' => true, 'message' => 'Login successful', 'data' => ['user_id' => $user['user_id']]]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    }
} elseif ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
