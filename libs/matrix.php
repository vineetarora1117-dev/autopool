<?php

/**
 * 2xN Binary Matrix Placement and Traversal for Autopools
 */

/**
 * Places a user into the next available slot in the specified package's 2xN binary matrix.
 * Uses BFS (level-order traversal) on package_matrices table.
 * 
 * @param PDO $pdo
 * @param string $userId
 * @param string $packageType
 * @return array Position info (upline_id, slot, level)
 */
function placeInMatrix($pdo, $userId, $packageType) {
    // Check if SA000001 is already in this matrix
    $stmt = $pdo->prepare("SELECT id FROM package_matrices WHERE user_id = 'SA000001' AND package_type = ?");
    $stmt->execute([$packageType]);
    if (!$stmt->fetch()) {
        // If not, insert SA000001 as root
        $stmtInsertRoot = $pdo->prepare("INSERT INTO package_matrices (user_id, package_type, upline_id, position_slot, matrix_level) VALUES ('SA000001', ?, NULL, 1, 1)");
        $stmtInsertRoot->execute([$packageType]);
    }

    // Now, find the first available slot using BFS.
    // The root is always SA000001
    // We queue nodes, and check their children.
    
    // In SQL, we can find the node with less than 2 children by sorting by id ascending (level order).
    $query = "
        SELECT pm.user_id, pm.matrix_level
        FROM package_matrices pm
        LEFT JOIN package_matrices child ON child.upline_id = pm.user_id AND child.package_type = pm.package_type
        WHERE pm.package_type = ?
        GROUP BY pm.id, pm.user_id, pm.matrix_level
        HAVING COUNT(child.id) < 2
        ORDER BY pm.id ASC
        LIMIT 1
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$packageType]);
    $targetNode = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$targetNode) {
        // Fallback to SA000001 if something is weird
        $targetNode = ['user_id' => 'SA000001', 'matrix_level' => 1];
    }
    
    $uplineId = $targetNode['user_id'];
    $matrixLevel = $targetNode['matrix_level'] + 1;
    
    // Check which slot is available
    $stmtSlot = $pdo->prepare("SELECT position_slot FROM package_matrices WHERE upline_id = ? AND package_type = ?");
    $stmtSlot->execute([$uplineId, $packageType]);
    $existingSlots = $stmtSlot->fetchAll(PDO::FETCH_COLUMN);
    
    $positionSlot = 1; // Left
    if (in_array(1, $existingSlots)) {
        $positionSlot = 2; // Right
    }
    
    $stmtInsert = $pdo->prepare("
        INSERT INTO package_matrices (user_id, package_type, upline_id, position_slot, matrix_level) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmtInsert->execute([$userId, $packageType, $uplineId, $positionSlot, $matrixLevel]);
    
    return [
        'upline_id' => $uplineId,
        'slot' => $positionSlot,
        'level' => $matrixLevel
    ];
}

/**
 * Walks UP the matrix tree from a given user position, returning an array of upline user_ids up to N levels.
 * 
 * @param PDO $pdo
 * @param string $userId
 * @param string $packageType
 * @param int $levels Number of levels to traverse up
 * @return array Array of user_ids of the uplines
 */
function getMatrixUplines($pdo, $userId, $packageType, $levels = 8) {
    $uplines = [];
    $currentUserId = $userId;
    
    $stmt = $pdo->prepare("SELECT upline_id FROM package_matrices WHERE user_id = ? AND package_type = ?");
    
    for ($i = 0; $i < $levels; $i++) {
        $stmt->execute([$currentUserId, $packageType]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && $result['upline_id']) {
            $uplines[] = $result['upline_id'];
            $currentUserId = $result['upline_id'];
        } else {
            break;
        }
    }
    
    return $uplines;
}

/**
 * Returns immediate children in the matrix (for network explorer).
 * 
 * @param PDO $pdo
 * @param string $userId
 * @param string $packageType
 * @return array
 */
function getDirectMatrixChildren($pdo, $userId, $packageType) {
    $stmt = $pdo->prepare("
        SELECT pm.user_id, pm.position_slot, pm.matrix_level, u.name 
        FROM package_matrices pm
        JOIN users u ON pm.user_id = u.user_id
        WHERE pm.upline_id = ? AND pm.package_type = ?
        ORDER BY pm.position_slot ASC
    ");
    $stmt->execute([$userId, $packageType]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
