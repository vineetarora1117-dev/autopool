<?php
session_start();
require_once __DIR__ . '/../../libs/db.php';
require_once __DIR__ . '/../../libs/payouts.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$userId = $_SESSION['user_id'];

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'get_network') {
    $pack = intval($_GET['pack'] ?? 1);
    $targetId = $_GET['user_id'] ?? $userId;
    $type = $_GET['type'] ?? 'autopool';

    if ($type === 'infinity' || $type === 'booster') {
        // Security check: Target user must be logged-in user or downline
        if ($targetId !== $userId) {
            $isDescendant = false;
            $currentId = $targetId;
            for ($i = 0; $i < 30; $i++) {
                $stmt = $pdo->prepare("
                    SELECT ub_parent.user_id 
                    FROM user_boosters ub_child 
                    JOIN user_boosters ub_parent ON ub_child.upline_booster_id = ub_parent.id 
                    WHERE ub_child.user_id = ? 
                    LIMIT 1
                ");
                $stmt->execute([$currentId]);
                $upline = $stmt->fetchColumn();
                if (!$upline) break;
                if ($upline === $userId) {
                    $isDescendant = true;
                    break;
                }
                $currentId = $upline;
            }
            if (!$isDescendant) {
                echo json_encode(['success' => false, 'message' => 'Access denied: Target user is not in your downline tree']);
                exit;
            }
        }

        // Fetch boosters belonging to targetId
        $stmtBoosters = $pdo->prepare("SELECT id FROM user_boosters WHERE user_id = ?");
        $stmtBoosters->execute([$targetId]);
        $userBoosterIds = $stmtBoosters->fetchAll(PDO::FETCH_COLUMN);

        $children = [];
        if (!empty($userBoosterIds)) {
            $inClause = implode(',', array_map('intval', $userBoosterIds));
            $stmtChildren = $pdo->prepare("
                SELECT 
                    b.id AS booster_id, 
                    b.user_id, 
                    b.upline_booster_id, 
                    b.purchase_type, 
                    b.created_at, 
                    u.name, 
                    u.sponsor_id, 
                    u.status
                FROM user_boosters b
                JOIN users u ON b.user_id COLLATE utf8mb4_general_ci = u.user_id COLLATE utf8mb4_general_ci
                WHERE b.upline_booster_id IN ($inClause)
                ORDER BY b.id ASC
            ");
            $stmtChildren->execute();
            $rawChildren = $stmtChildren->fetchAll(PDO::FETCH_ASSOC);

            $slotCounter = [];
            foreach ($rawChildren as $child) {
                $uplineBId = $child['upline_booster_id'];
                $slotCounter[$uplineBId] = ($slotCounter[$uplineBId] ?? 0) + 1;
                $child['position_slot'] = $slotCounter[$uplineBId];
                $children[] = $child;
            }
        }

        // Parent user ID for moving back up
        $stmtParent = $pdo->prepare("
            SELECT ub_parent.user_id 
            FROM user_boosters ub_child 
            JOIN user_boosters ub_parent ON ub_child.upline_booster_id = ub_parent.id 
            WHERE ub_child.user_id = ? 
            LIMIT 1
        ");
        $stmtParent->execute([$targetId]);
        $parent = $stmtParent->fetchColumn() ?: null;

        echo json_encode([
            'success' => true,
            'target_id' => $targetId,
            'parent_id' => $parent,
            'children' => $children
        ]);
        exit;
    }

    $packagesKeys = ['main_11', 'main_30', 'main_60', 'main_120', 'main_240', 'main_480'];
    if ($pack < 1 || $pack > 6) {
        echo json_encode(['success' => false, 'message' => 'Invalid package level']);
        exit;
    }
    $packageType = $packagesKeys[$pack - 1];

    // Security check: Target user must be a descendant of logged-in user in this matrix
    if ($targetId !== $userId) {
        $isDescendant = false;
        $currentId = $targetId;
        for ($i = 0; $i < 30; $i++) {
            $stmt = $pdo->prepare("SELECT upline_id FROM package_matrices WHERE user_id = ? AND package_type = ?");
            $stmt->execute([$currentId, $packageType]);
            $upline = $stmt->fetchColumn();
            if (!$upline) break;
            if ($upline === $userId) {
                $isDescendant = true;
                break;
            }
            $currentId = $upline;
        }
        if (!$isDescendant) {
            echo json_encode(['success' => false, 'message' => 'Access denied: Target user is not in your downline tree']);
            exit;
        }
    }

    // Fetch immediate matrix children
    $stmt = $pdo->prepare("
        SELECT pm.user_id, pm.position_slot, pm.matrix_level, u.name, u.sponsor_id, u.status, u.created_at, pm.upline_id
        FROM package_matrices pm
        JOIN users u ON pm.user_id = u.user_id
        WHERE pm.upline_id = ? AND pm.package_type = ?
        ORDER BY pm.position_slot ASC
    ");
    $stmt->execute([$targetId, $packageType]);
    $children = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get parent/upline to allow moving back up
    $stmtParent = $pdo->prepare("SELECT upline_id FROM package_matrices WHERE user_id = ? AND package_type = ?");
    $stmtParent->execute([$targetId, $packageType]);
    $parent = $stmtParent->fetchColumn() ?: null;

    echo json_encode([
        'success' => true,
        'target_id' => $targetId,
        'parent_id' => $parent,
        'children' => $children
    ]);
    exit;
}

if ($action === 'verify_user') {
    $targetId = $_POST['user_id'] ?? $_GET['user_id'] ?? '';
    if (empty($targetId)) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT name, status FROM users WHERE user_id = ?");
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
    echo json_encode(['success' => true, 'message' => 'User verified successfully', 'name' => $userRow['name']]);
    exit;
}

if ($action === 'list') {
    global $PACKAGE_CONFIG;
    $response = [];
    foreach ($PACKAGE_CONFIG as $pkgKey => $pkgData) {
        $eligibility = checkPackageEligibility($pdo, $userId, $pkgKey);
        
        $stmt = $pdo->prepare("SELECT id FROM user_packages WHERE user_id = ? AND package_type = ? AND is_active = 1");
        $stmt->execute([$userId, $pkgKey]);
        $isActive = (bool)$stmt->fetch();
        
        $response[] = [
            'package_type' => $pkgKey,
            'cost' => $pkgData['cost'],
            'is_active' => $isActive,
            'is_eligible' => $eligibility['eligible'],
            'eligibility_reason' => $eligibility['reason']
        ];
    }
    echo json_encode(['success' => true, 'data' => $response]);
} elseif ($action === 'purchase') {
    $targetUserId = $_POST['target_user_id'] ?? $userId;
    $packageType = $_POST['package_type'] ?? '';
    
    if (empty($packageType)) {
        echo json_encode(['success' => false, 'message' => 'Package type is required']);
        exit;
    }
    
    // Check target user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE user_id = ?");
    $stmt->execute([$targetUserId]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Target user not found']);
        exit;
    }
    
    // Check if it's a booster purchase
    if (strpos($packageType, 'booster_') === 0) {
        try {
            processBoosterPurchase($pdo, $targetUserId, $packageType, $userId !== $targetUserId ? $userId : null);
            echo json_encode(['success' => true, 'message' => 'Booster purchased successfully!']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
    
    // Check eligibility
    $eligibility = checkPackageEligibility($pdo, $targetUserId, $packageType);
    if (!$eligibility['eligible']) {
        echo json_encode(['success' => false, 'message' => $eligibility['reason']]);
        exit;
    }
    
    global $PACKAGE_CONFIG;
    $cost = $PACKAGE_CONFIG[$packageType]['cost'];
    
    // Check balance
    $stmt = $pdo->prepare("SELECT main_deposit_balance FROM user_financial_summary WHERE user_id = ?");
    $stmt->execute([$userId]);
    $balance = $stmt->fetchColumn() ?: 0;
    
    if ($balance < $cost) {
        echo json_encode(['success' => false, 'message' => 'Insufficient main deposit balance']);
        exit;
    }
    
    try {
        processPackagePayout($pdo, $targetUserId, $packageType, $userId !== $targetUserId ? $userId : null);
        echo json_encode(['success' => true, 'message' => 'Package purchased successfully!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error processing purchase: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
