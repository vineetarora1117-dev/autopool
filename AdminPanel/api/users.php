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
    if ($action === 'get_user') {
        $stmt = $pdo->prepare("SELECT user_id, name, email, phone, password, status FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            echo json_encode(['success' => true, 'data' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
    } elseif ($action === 'update_user') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $newPassword = trim($_POST['password'] ?? '');

        if (!$name || !$email) {
            echo json_encode(['success' => false, 'message' => 'Name and Email are required']);
            exit;
        }

        if ($newPassword !== '') {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, password = ? WHERE user_id = ?");
            $stmt->execute([$name, $email, $phone, $newPassword, $userId]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE user_id = ?");
            $stmt->execute([$name, $email, $phone, $userId]);
        }
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } elseif ($action === 'impersonate') {
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
