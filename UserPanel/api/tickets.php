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

if ($action === 'create') {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($subject) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Subject and description are required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO support_tickets (user_id, subject, message, status) VALUES (?, ?, ?, 'Open')");
        $stmt->execute([$userId, $subject, $message]);
        echo json_encode(['success' => true, 'message' => 'Ticket created successfully!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
