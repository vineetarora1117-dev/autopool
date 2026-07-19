<?php
require_once __DIR__ . '/../../libs/db.php';
require_once __DIR__ . '/../../libs/admin_auth.php';

header('Content-Type: application/json');
requireAdminLogin();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$userId = $_POST['user_id'] ?? '';

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

try {
    if ($action === 'impersonate') {
        $result = loginAsUser($pdo, $userId);
        echo json_encode($result);
    } elseif ($action === 'block') {
        $stmt = $pdo->prepare("UPDATE users SET status = 'Blocked' WHERE user_id = ?");
        $stmt->execute([$userId]);
        echo json_encode(['success' => true, 'message' => 'User has been blocked.']);
    } elseif ($action === 'unblock') {
        // Find if user has active package to determine Active vs Inactive status
        $stmt = $pdo->prepare("SELECT my_package FROM user_financial_summary WHERE user_id = ?");
        $stmt->execute([$userId]);
        $summary = $stmt->fetch();
        $status = ($summary && $summary['my_package'] > 0) ? 'Active' : 'Inactive';

        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
        $stmt->execute([$status, $userId]);
        echo json_encode(['success' => true, 'message' => 'User has been unblocked.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
