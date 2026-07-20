<?php
require_once __DIR__ . '/../../libs/db.php';
require_once __DIR__ . '/../../libs/admin_auth.php';

header('Content-Type: application/json');
requireAdminLogin();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'get_network') {
    $userId = $_GET['user_id'] ?? '';
    $matrixType = $_GET['matrix_type'] ?? 'main'; // 'main' or 'booster'
    $packageType = $_GET['package_type'] ?? '';

    if (empty($userId)) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit;
    }

    // Verify user exists
    $stmt = $pdo->prepare("SELECT name FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $userExists = $stmt->fetch();
    if (!$userExists) {
        echo json_encode(['success' => false, 'message' => "User ID '{$userId}' does not exist"]);
        exit;
    }

    if ($matrixType === 'booster') {
        // Fetch booster matrix children
        $stmt = $pdo->prepare("
            SELECT bm.user_id, bm.position_slot, bm.matrix_level, bm.board_id, u.name, u.sponsor_id, u.status, u.created_at, bm.upline_id
            FROM booster_matrices bm
            JOIN users u ON bm.user_id = u.user_id
            WHERE bm.upline_id = ? AND bm.booster_type = ?
            ORDER BY bm.position_slot ASC
        ");
        $stmt->execute([$userId, $packageType]);
        $children = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch parent
        $stmtParent = $pdo->prepare("SELECT upline_id FROM booster_matrices WHERE user_id = ? AND booster_type = ?");
        $stmtParent->execute([$userId, $packageType]);
        $parent = $stmtParent->fetchColumn() ?: null;
    } else {
        // Fetch main package matrix children
        $stmt = $pdo->prepare("
            SELECT pm.user_id, pm.position_slot, pm.matrix_level, u.name, u.sponsor_id, u.status, u.created_at, pm.upline_id
            FROM package_matrices pm
            JOIN users u ON pm.user_id = u.user_id
            WHERE pm.upline_id = ? AND pm.package_type = ?
            ORDER BY pm.position_slot ASC
        ");
        $stmt->execute([$userId, $packageType]);
        $children = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch parent
        $stmtParent = $pdo->prepare("SELECT upline_id FROM package_matrices WHERE user_id = ? AND package_type = ?");
        $stmtParent->execute([$userId, $packageType]);
        $parent = $stmtParent->fetchColumn() ?: null;
    }

    echo json_encode([
        'success' => true,
        'target_id' => $userId,
        'parent_id' => $parent,
        'children' => $children
    ]);
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
