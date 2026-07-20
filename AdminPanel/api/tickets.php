<?php
require_once __DIR__ . '/../../libs/db.php';
require_once __DIR__ . '/../../libs/admin_auth.php';

header('Content-Type: application/json');
requireAdminLogin();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'reply') {
    $ticketId = intval($_POST['ticket_id'] ?? 0);
    $reply = trim($_POST['reply'] ?? '');
    $status = $_POST['status'] ?? 'Closed';

    if (!$ticketId || empty($reply)) {
        echo json_encode(['success' => false, 'message' => 'Ticket ID and reply message are required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE support_tickets SET admin_reply = ?, status = ? WHERE id = ?");
        $stmt->execute([$reply, $status, $ticketId]);
        echo json_encode(['success' => true, 'message' => 'Ticket updated successfully!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
