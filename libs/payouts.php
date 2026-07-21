<?php

require_once __DIR__ . '/matrix.php';

/**
 * Payout distribution engine for packages.
 */

require_once __DIR__ . '/config.php';

function checkPackageEligibility($pdo, $userId, $packageType) {
    global $PACKAGE_CONFIG;
    if (!isset($PACKAGE_CONFIG[$packageType])) {
        return ['eligible' => false, 'reason' => 'Invalid package.'];
    }
    
    $config = $PACKAGE_CONFIG[$packageType];
    
    // Check if already active
    $stmt = $pdo->prepare("SELECT id FROM user_packages WHERE user_id = ? AND package_type = ? AND is_active = 1");
    $stmt->execute([$userId, $packageType]);
    if ($stmt->fetch()) {
        return ['eligible' => false, 'reason' => 'Package already active.'];
    }
    
    // Check previous package
    if ($config['prev_package']) {
        $stmt->execute([$userId, $config['prev_package']]);
        if (!$stmt->fetch()) {
            return ['eligible' => false, 'reason' => 'You must purchase the previous package first.'];
        }
    }
    
    // Check active direct downlines (status='Active' and package >= 11)
    if ($config['req_downlines'] > 0) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM users u 
            INNER JOIN user_financial_summary ufs ON u.user_id = ufs.user_id 
            WHERE u.sponsor_id = ? AND u.status = 'Active' AND ufs.my_package >= 11
        ");
        $stmt->execute([$userId]);
        $activeDirectCount = $stmt->fetchColumn() ?: 0;
        
        if ($activeDirectCount < $config['req_downlines']) {
            return ['eligible' => false, 'reason' => "You need at least {$config['req_downlines']} active direct downlines (with active package >= $11). You have {$activeDirectCount}."];
        }
    }
    
    return ['eligible' => true, 'reason' => ''];
}

function getSponsorTreeUplines($pdo, $userId, $levels) {
    $uplines = [];
    $currentUserId = $userId;
    
    $stmt = $pdo->prepare("SELECT sponsor_id FROM users WHERE user_id = ?");
    
    for ($i = 0; $i < $levels; $i++) {
        $stmt->execute([$currentUserId]);
        $sponsorId = $stmt->fetchColumn();
        
        if ($sponsorId) {
            $uplines[] = $sponsorId;
            $currentUserId = $sponsorId;
        } else {
            break;
        }
    }
    
    return $uplines;
}

function insertTransaction($pdo, $userId, $type, $amount, $walletType, $status, $narration, $relatedId = null, $blockedByUserId = null) {
    $stmt = $pdo->prepare("
        INSERT INTO transactions (user_id, transaction_type, amount, wallet_type, status, narration, related_user_id, blocked_by_user_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$userId, $type, $amount, $walletType, $status, $narration, $relatedId, $blockedByUserId]);
}

function releaseBlockedTransactions($pdo, $blockerUserId, $packageType, $walletType) {
    $stmt = $pdo->prepare("
        SELECT id, user_id, amount, transaction_type 
        FROM transactions 
        WHERE blocked_by_user_id = ? AND wallet_type = ? AND status = 'Pending'
    ");
    $stmt->execute([$blockerUserId, $walletType]);
    $pendingTxs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($pendingTxs as $tx) {
        // Update transaction status to Completed and clear blocker
        $stmtUpd = $pdo->prepare("UPDATE transactions SET status = 'Completed', blocked_by_user_id = NULL WHERE id = ?");
        $stmtUpd->execute([$tx['id']]);
        
        // Credit the user's wallet and update counts
        if ($tx['transaction_type'] === 'autopool_income') {
            $stmtBal = $pdo->prepare("
                UPDATE user_financial_summary 
                SET {$walletType} = {$walletType} + ?, total_global_autopool_income = total_global_autopool_income + ? 
                WHERE user_id = ?
            ");
            $stmtBal->execute([$tx['amount'], $tx['amount'], $tx['user_id']]);
        } elseif ($tx['transaction_type'] === 'level_income') {
            $stmtBal = $pdo->prepare("
                UPDATE user_financial_summary 
                SET {$walletType} = {$walletType} + ?, total_team_level_income = total_team_level_income + ? 
                WHERE user_id = ?
            ");
            $stmtBal->execute([$tx['amount'], $tx['amount'], $tx['user_id']]);
        }
        
        // Update company ledger liability
        $stmtLiab = $pdo->prepare("UPDATE company_ledger SET total_payout_liability_main = total_payout_liability_main + ? WHERE id = 1");
        $stmtLiab->execute([$tx['amount']]);
    }
}

function sweepToCompany($pdo, $amount, $type, $narration, $relatedId = null) {
    // Increment company wallet balance
    $stmt = $pdo->prepare("UPDATE company_ledger SET company_wallet_balance = company_wallet_balance + ? WHERE id = 1");
    $stmt->execute([$amount]);
    // Add transaction for SA000001
    insertTransaction($pdo, 'SA000001', 'company_sweep', $amount, null, 'Completed', $narration, $relatedId);
}

function hasPackageActive($pdo, $userId, $packageType) {
    $stmt = $pdo->prepare("SELECT id FROM user_packages WHERE user_id = ? AND package_type = ? AND is_active = 1");
    $stmt->execute([$userId, $packageType]);
    return (bool)$stmt->fetch();
}

function processPackagePayout($pdo, $buyerUserId, $packageType, $fundedByUserId = null) {
    global $PACKAGE_CONFIG;
    $config = $PACKAGE_CONFIG[$packageType];
    $cost = $config['cost'];
    $payerId = $fundedByUserId ?: $buyerUserId;
    
    $pdo->beginTransaction();
    
    try {
        // a) Deduct from buyer's wallet
        $stmt = $pdo->prepare("UPDATE user_financial_summary SET main_deposit_balance = main_deposit_balance - ? WHERE user_id = ?");
        $stmt->execute([$cost, $payerId]);
        
        $narration = $payerId === $buyerUserId 
            ? "Purchased $" . $cost . " Package — self activation"
            : "Purchased $" . $cost . " Package for " . $buyerUserId . " — gifted by " . $payerId;
        insertTransaction($pdo, $payerId, 'package_purchase', $cost, 'main_deposit', 'Completed', $narration, $buyerUserId);

        // b) Update company ledger
        $stmtLedger = $pdo->prepare("UPDATE company_ledger SET unutilized_funds = unutilized_funds - ?, invested_funds = invested_funds + ? WHERE id = 1");
        $stmtLedger->execute([$cost, $cost]);
        
        // c) Activate package
        $stmt = $pdo->prepare("INSERT INTO user_packages (user_id, package_type, is_active, funded_by) VALUES (?, ?, 1, ?)");
        $stmt->execute([$buyerUserId, $packageType, $payerId]);
        
        $stmt = $pdo->prepare("UPDATE user_financial_summary SET my_package = GREATEST(my_package, ?) WHERE user_id = ?");
        $stmt->execute([$cost, $buyerUserId]);
        
        $stmt = $pdo->prepare("UPDATE users SET status = 'Active' WHERE user_id = ?");
        $stmt->execute([$buyerUserId]);
        
        // d) Place in matrix
        $pos = placeInMatrix($pdo, $buyerUserId, $packageType);
        
        // e) Autopool Income Distribution (Payable on Pair Completion, i.e. when position slot is 2)
        if ((int)$pos['slot'] === 2) {
            $matrixUplines = getMatrixUplines($pdo, $buyerUserId, $packageType, $config['autopool_levels']);
            foreach ($matrixUplines as $levelIdx => $upline) {
                $uplineLevel = $levelIdx + 1; // Level 1 to 8 in matrix above buyer
                $perMemberRate = ($uplineLevel <= 4) ? $config['autopool_l1_4'] : $config['autopool_l5_8'];
                $pairAmount = 2 * $perMemberRate; // Full pair completion amount
                $narration = "Autopool pair completion income $$pairAmount from pair in $$cost Matrix (Upline L$uplineLevel)";
                
                if ($upline === 'SA000001') {
                    sweepToCompany($pdo, $pairAmount, 'autopool_income', $narration, $buyerUserId);
                } else {
                    $stmt = $pdo->prepare("UPDATE user_financial_summary SET {$config['wallet']} = {$config['wallet']} + ?, total_global_autopool_income = total_global_autopool_income + ? WHERE user_id = ?");
                    $stmt->execute([$pairAmount, $pairAmount, $upline]);
                    
                    $stmtLiab = $pdo->prepare("UPDATE company_ledger SET total_payout_liability_main = total_payout_liability_main + ? WHERE id = 1");
                    $stmtLiab->execute([$pairAmount]);
                    
                    insertTransaction($pdo, $upline, 'autopool_income', $pairAmount, $config['wallet'], 'Completed', $narration, $buyerUserId);
                }
            }
        }
        
        // f) Sponsor Income
        $stmt = $pdo->prepare("SELECT sponsor_id FROM users WHERE user_id = ?");
        $stmt->execute([$buyerUserId]);
        $sponsorId = $stmt->fetchColumn();
        
        if ($sponsorId) {
            $amt = $config['sponsor_amount'];
            if ($sponsorId === 'SA000001') {
                $narration = "Sponsor income $$amt from $buyerUserId activating $$cost Package";
                sweepToCompany($pdo, $amt, 'sponsor_income', $narration, $buyerUserId);
            } else {
                $sponsorActive = hasPackageActive($pdo, $sponsorId, $packageType);
                if ($sponsorActive) {
                    $narration = "Sponsor income $$amt from $buyerUserId activating $$cost Package";
                    $stmt = $pdo->prepare("UPDATE user_financial_summary SET {$config['wallet']} = {$config['wallet']} + ?, total_direct_referral_income = total_direct_referral_income + ? WHERE user_id = ?");
                    $stmt->execute([$amt, $amt, $sponsorId]);
                    $stmtLiab = $pdo->prepare("UPDATE company_ledger SET total_payout_liability_main = total_payout_liability_main + ? WHERE id = 1");
                $stmtLiab->execute([$amt]);
                    insertTransaction($pdo, $sponsorId, 'sponsor_income', $amt, $config['wallet'], 'Completed', $narration, $buyerUserId);
                } else {
                    $narration = "Sponsor income $$amt HELD — $buyerUserId activated $$cost Package, but you have not purchased $$cost Package yet";
                    $stmtHeld = $pdo->prepare("UPDATE company_ledger SET total_held_sponsor_income = total_held_sponsor_income + ? WHERE id = 1");
                    $stmtHeld->execute([$amt]);
                    insertTransaction($pdo, $sponsorId, 'sponsor_income_held', $amt, $config['wallet'], 'Held', $narration, $buyerUserId);
                }
            }
        }
        
        // g) Level Income
        $sponsorUplines = getSponsorTreeUplines($pdo, $buyerUserId, $config['level_levels']);
        $current_level_blocker = null;
        foreach ($sponsorUplines as $levelIdx => $upline) {
            $amt = $config['level_amount'];
            $actualLevel = $levelIdx + 1;
            if ($upline === 'SA000001') {
                $narration = "Level income $$amt from $buyerUserId — Level $actualLevel of $$cost Package tree";
                sweepToCompany($pdo, $amt, 'level_income', $narration, $buyerUserId);
            } else {
                $uplineActive = hasPackageActive($pdo, $upline, $packageType);
                
                // Check if this upline has < 2 children in this package matrix (blocker logic)
                $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM package_matrices WHERE upline_id = ? AND package_type = ?");
                $stmtCheck->execute([$upline, $packageType]);
                $uplineChildren = (int)$stmtCheck->fetchColumn();
                
                if ($uplineChildren < 2) {
                    $current_level_blocker = $upline;
                }
                
                if ($uplineActive) {
                    $narration = "Level income $$amt from $buyerUserId — Level $actualLevel of $$cost Package tree";
                    if ($current_level_blocker !== null) {
                        // Blocked: Insert as Pending with blocker
                        insertTransaction($pdo, $upline, 'level_income', $amt, $config['wallet'], 'Pending', $narration, $buyerUserId, $current_level_blocker);
                    } else {
                        // Completed: Update wallet and ledger
                        $stmt = $pdo->prepare("UPDATE user_financial_summary SET {$config['wallet']} = {$config['wallet']} + ?, total_team_level_income = total_team_level_income + ? WHERE user_id = ?");
                        $stmt->execute([$amt, $amt, $upline]);
                        $stmtLiab = $pdo->prepare("UPDATE company_ledger SET total_payout_liability_main = total_payout_liability_main + ? WHERE id = 1");
                        $stmtLiab->execute([$amt]);
                        insertTransaction($pdo, $upline, 'level_income', $amt, $config['wallet'], 'Completed', $narration, $buyerUserId);
                    }
                } else {
                    $narration = "Level income $$amt HELD — $buyerUserId activated $$cost Package, but you have not purchased $$cost Package yet";
                    insertTransaction($pdo, $upline, 'sponsor_income_held', $amt, $config['wallet'], 'Held', $narration, $buyerUserId);
                }
            }
        }
        
        // h) Company Revenue
        if ($config['company_revenue'] > 0) {
            $amt = $config['company_revenue'];
            $narration = "Company revenue $$amt from $buyerUserId activating $$cost Package";
            sweepToCompany($pdo, $amt, 'company_revenue', $narration, $buyerUserId);
        }
        
        // i) Reward Reserve
        if ($config['reward_reserve'] > 0) {
            $amt = $config['reward_reserve'];
            $narration = "Reward reserve contribution $$amt from $buyerUserId activating $$cost Package";
            insertTransaction($pdo, 'SA000001', 'reward_income', $amt, null, 'Completed', $narration, $buyerUserId);
        }
        
        // j) Release Held Income for this buyer user (now that they bought this package)
        $stmt = $pdo->prepare("
            SELECT id, amount, related_user_id, narration 
            FROM transactions 
            WHERE user_id = ? AND wallet_type = ? AND status = 'Held'
        ");
        $stmt->execute([$buyerUserId, $config['wallet']]);
        $heldTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($heldTransactions as $heldTx) {
            $amt = $heldTx['amount'];
            $originalUser = $heldTx['related_user_id'];
            
            // Check if this buyer user has < 2 children in this package matrix (blocker logic)
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM package_matrices WHERE upline_id = ? AND package_type = ?");
            $stmtCheck->execute([$buyerUserId, $packageType]);
            $buyerChildren = (int)$stmtCheck->fetchColumn();
            
            if ($buyerChildren < 2) {
                // Blocked: Update held transaction to Pending with blocker
                $stmtUpd = $pdo->prepare("UPDATE transactions SET status = 'Pending', blocked_by_user_id = ? WHERE id = ?");
                $stmtUpd->execute([$buyerUserId, $heldTx['id']]);
                
                // Decrement company ledger held metrics
                $stmtRelease = $pdo->prepare("UPDATE company_ledger SET total_held_sponsor_income = total_held_sponsor_income - ? WHERE id = 1");
                $stmtRelease->execute([$amt]);
            } else {
                // Completed: Update transaction to Released
                $stmtUpd = $pdo->prepare("UPDATE transactions SET status = 'Released' WHERE id = ?");
                $stmtUpd->execute([$heldTx['id']]);
                
                // Credit wallet
                $stmtBal = $pdo->prepare("
                    UPDATE user_financial_summary 
                    SET {$config['wallet']} = {$config['wallet']} + ?,
                        total_direct_referral_income = total_direct_referral_income + ? 
                    WHERE user_id = ?
                ");
                $stmtBal->execute([$amt, $amt, $buyerUserId]);
                
                // Add new transaction
                $narration = "Income $$amt RELEASED — previously held from $originalUser's $$cost Package. You are now eligible";
                insertTransaction($pdo, $buyerUserId, 'sponsor_income_released', $amt, $config['wallet'], 'Completed', $narration, $originalUser);
                
                // Update company ledger held metrics and liability
                $stmtRelease = $pdo->prepare("UPDATE company_ledger SET total_held_sponsor_income = total_held_sponsor_income - ?, total_payout_liability_main = total_payout_liability_main + ? WHERE id = 1");
                $stmtRelease->execute([$amt, $amt]);
            }
        }
        
        $pdo->commit();
        return true;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Places a user into the next available slot in the specified booster's 4x2 matrix.
 * Uses BFS (level-order traversal) under the sponsor (with fallback to root).
 */
function placeInBoosterMatrix($pdo, $userId, $boosterType) {
    // 1. Check if SA000001 is in the matrix. If not, insert it as root
    $stmt = $pdo->prepare("SELECT id FROM booster_matrices WHERE user_id = 'SA000001' AND booster_type = ?");
    $stmt->execute([$boosterType]);
    if (!$stmt->fetch()) {
        $stmtInsertRoot = $pdo->prepare("INSERT INTO booster_matrices (user_id, booster_type, upline_id, position_slot, matrix_level) VALUES ('SA000001', ?, NULL, 1, 1)");
        $stmtInsertRoot->execute([$boosterType]);
    }
    
    // 2. Find the sponsor's position in this matrix to support sponsor-based spillover.
    $stmtSponsor = $pdo->prepare("SELECT sponsor_id FROM users WHERE user_id = ?");
    $stmtSponsor->execute([$userId]);
    $sponsorId = $stmtSponsor->fetchColumn();
    
    $startUplineId = 'SA000001';
    if ($sponsorId) {
        $stmtSponsorPos = $pdo->prepare("SELECT user_id FROM booster_matrices WHERE user_id = ? AND booster_type = ?");
        $stmtSponsorPos->execute([$sponsorId, $boosterType]);
        if ($stmtSponsorPos->fetch()) {
            $startUplineId = $sponsorId;
        }
    }
    
    // 3. Perform BFS (level-order traversal) under the $startUplineId to find the first node with < 4 children.
    $queue = [$startUplineId];
    $targetUplineId = null;
    
    while (!empty($queue)) {
        $current = array_shift($queue);
        
        // Find existing children of current node
        $stmtChildren = $pdo->prepare("SELECT user_id, position_slot FROM booster_matrices WHERE upline_id = ? AND booster_type = ?");
        $stmtChildren->execute([$current, $boosterType]);
        $existingChildren = $stmtChildren->fetchAll(PDO::FETCH_ASSOC);
        $childrenCount = count($existingChildren);
        
        if ($childrenCount < 4) {
            $targetUplineId = $current;
            break;
        }
        
        // Push children to queue to keep searching BFS (spillover)
        foreach ($existingChildren as $child) {
            $queue[] = $child['user_id'];
        }
    }
    
    if (!$targetUplineId) {
        $targetUplineId = 'SA000001';
    }
    
    // Get the level of the target upline
    $stmtUplineLevel = $pdo->prepare("SELECT matrix_level FROM booster_matrices WHERE user_id = ? AND booster_type = ?");
    $stmtUplineLevel->execute([$targetUplineId, $boosterType]);
    $uplineMatrixLevel = (int)$stmtUplineLevel->fetchColumn() ?: 1;
    $matrixLevel = $uplineMatrixLevel + 1;
    
    // Check which slot is available (1 to 4)
    $stmtSlot = $pdo->prepare("SELECT position_slot FROM booster_matrices WHERE upline_id = ? AND booster_type = ?");
    $stmtSlot->execute([$targetUplineId, $boosterType]);
    $existingSlots = $stmtSlot->fetchAll(PDO::FETCH_COLUMN);
    
    $positionSlot = 1;
    for ($s = 1; $s <= 4; $s++) {
        if (!in_array($s, $existingSlots)) {
            $positionSlot = $s;
            break;
        }
    }
    
    $stmtInsert = $pdo->prepare("
        INSERT INTO booster_matrices (user_id, booster_type, upline_id, position_slot, matrix_level) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmtInsert->execute([$userId, $boosterType, $targetUplineId, $positionSlot, $matrixLevel]);
    
    return [
        'upline_id' => $targetUplineId,
        'slot' => $positionSlot,
        'level' => $matrixLevel
    ];
}

/**
 * Checks if a user's 4x2 matrix board is complete (20 downlines) and triggers payouts.
 */
function checkAndProcessBoosterBoardCompletion($pdo, $uplineId, $boosterType) {
    if ($uplineId === 'SA000001' || empty($uplineId)) {
        return;
    }
    
    // Count descendants at Level 1 and Level 2
    $stmtL1 = $pdo->prepare("SELECT user_id FROM booster_matrices WHERE upline_id = ? AND booster_type = ?");
    $stmtL1->execute([$uplineId, $boosterType]);
    $l1Users = $stmtL1->fetchAll(PDO::FETCH_COLUMN);
    
    $l1Count = count($l1Users);
    $l2Count = 0;
    if ($l1Count > 0) {
        $inQuery = implode(',', array_fill(0, $l1Count, '?'));
        $stmtL2 = $pdo->prepare("SELECT COUNT(*) FROM booster_matrices WHERE upline_id IN ($inQuery) AND booster_type = ?");
        $stmtL2->execute(array_merge($l1Users, [$boosterType]));
        $l2Count = (int)$stmtL2->fetchColumn();
    }
    
    $totalDescendants = $l1Count + $l2Count;
    if ($totalDescendants === 20) {
        processBoosterPayout($pdo, $uplineId, $boosterType);
    }
}

/**
 * Distributes user earnings, sponsor income (with upgrade verification), auto-upgrades, and re-entries.
 */
function processBoosterPayout($pdo, $userId, $boosterType) {
    global $BOOSTER_CONFIG;
    if (!isset($BOOSTER_CONFIG[$boosterType])) return;
    
    $config = $BOOSTER_CONFIG[$boosterType];
    $wallet = $config['wallet'];
    
    // Find user sponsor
    $stmtSponsor = $pdo->prepare("SELECT sponsor_id FROM users WHERE user_id = ?");
    $stmtSponsor->execute([$userId]);
    $sponsorId = $stmtSponsor->fetchColumn();
    
    $pdo->beginTransaction();
    try {
        // 1. Credit User Earnings
        $stmtUser = $pdo->prepare("
            UPDATE user_financial_summary 
            SET {$wallet} = {$wallet} + ?, total_booster_income = total_booster_income + ? 
            WHERE user_id = ?
        ");
        $stmtUser->execute([$config['user_earnings'], $config['user_earnings'], $userId]);
        
        $narrationUser = "Booster earnings from completed " . $config['name'] . " board";
        insertTransaction($pdo, $userId, 'booster_income', $config['user_earnings'], $wallet, 'Completed', $narrationUser);
        
        // Update company ledger booster liability
        $stmtLiab = $pdo->prepare("UPDATE company_ledger SET total_payout_liability_booster = total_payout_liability_booster + ? WHERE id = 1");
        $stmtLiab->execute([$config['user_earnings']]);
        
        // 2. Sponsor Income
        if ($sponsorId) {
            $sponsorActive = hasPackageActive($pdo, $sponsorId, $boosterType);
            $amtSponsor = $config['sponsor_amount'];
            
            if ($sponsorId === 'SA000001') {
                $narrationSponsor = "Booster sponsor income from $userId completed " . $config['name'] . " board";
                sweepToCompany($pdo, $amtSponsor, 'booster_income', $narrationSponsor, $userId);
            } else {
                if ($sponsorActive) {
                    $narrationSponsor = "Booster sponsor income from $userId completed " . $config['name'] . " board";
                    $stmtSponsorCredit = $pdo->prepare("
                        UPDATE user_financial_summary 
                        SET {$wallet} = {$wallet} + ?, total_direct_referral_income = total_direct_referral_income + ? 
                        WHERE user_id = ?
                    ");
                    $stmtSponsorCredit->execute([$amtSponsor, $amtSponsor, $sponsorId]);
                    insertTransaction($pdo, $sponsorId, 'sponsor_income', $amtSponsor, $wallet, 'Completed', $narrationSponsor, $userId);
                    
                    // Update liability
                    $stmtLiab->execute([$amtSponsor]);
                } else {
                    $narrationSponsor = "Booster sponsor income from $userId completed " . $config['name'] . " board (HELD — you are not active in " . $config['name'] . ")";
                    $stmtHeld = $pdo->prepare("UPDATE company_ledger SET total_held_sponsor_income = total_held_sponsor_income + ? WHERE id = 1");
                    $stmtHeld->execute([$amtSponsor]);
                    insertTransaction($pdo, $sponsorId, 'sponsor_income_held', $amtSponsor, $wallet, 'Held', $narrationSponsor, $userId);
                }
            }
        }
        
        // 3. Upgrade Reserve / Re-entry
        if ($config['upgrade_reserve'] > 0) {
            // Find next booster type
            $boosterKeys = array_keys($BOOSTER_CONFIG);
            $currentIndex = array_search($boosterType, $boosterKeys);
            $nextBoosterType = $boosterKeys[$currentIndex + 1];
            
            // Auto upgrade
            $stmtInsertPkg = $pdo->prepare("INSERT INTO user_packages (user_id, package_type, is_active, funded_by) VALUES (?, ?, 1, 'system') ON DUPLICATE KEY UPDATE is_active = 1");
            $stmtInsertPkg->execute([$userId, $nextBoosterType]);
            
            $posNext = placeInBoosterMatrix($pdo, $userId, $nextBoosterType);
            
            $narrationUpgrade = "Auto upgraded to " . $BOOSTER_CONFIG[$nextBoosterType]['name'] . " (Reserve: $" . $config['upgrade_reserve'] . ")";
            insertTransaction($pdo, $userId, 'booster_purchase', $config['upgrade_reserve'], 'main_deposit', 'Completed', $narrationUpgrade);
            
            // Check if upline of next booster completed board
            checkAndProcessBoosterBoardCompletion($pdo, $posNext['upline_id'], $nextBoosterType);
        }
        
        // If it's final tier booster_320, process 40 re-entries
        if ($boosterType === 'booster_320') {
            for ($r = 1; $r <= 40; $r++) {
                $posReentry = placeInBoosterMatrix($pdo, $userId, 'booster_10');
                checkAndProcessBoosterBoardCompletion($pdo, $posReentry['upline_id'], 'booster_10');
            }
            
            $narrationReentry = "Completed Diamond board — generated 40 re-entries in 10 Booster";
            insertTransaction($pdo, $userId, 'booster_income', 0.00, $wallet, 'Completed', $narrationReentry);
        }
        
        // 4. Release Held Income for this booster
        $stmtHeldTx = $pdo->prepare("
            SELECT id, amount, related_user_id 
            FROM transactions 
            WHERE user_id = ? AND wallet_type = ? AND status = 'Held'
        ");
        $stmtHeldTx->execute([$userId, $wallet]);
        $heldTxs = $stmtHeldTx->fetchAll(PDO::FETCH_ASSOC);
        foreach ($heldTxs as $held) {
            $amt = $held['amount'];
            $origUser = $held['related_user_id'];
            
            $stmtUpd = $pdo->prepare("UPDATE transactions SET status = 'Released' WHERE id = ?");
            $stmtUpd->execute([$held['id']]);
            
            $stmtBal = $pdo->prepare("
                UPDATE user_financial_summary 
                SET {$wallet} = {$wallet} + ?, total_direct_referral_income = total_direct_referral_income + ? 
                WHERE user_id = ?
            ");
            $stmtBal->execute([$amt, $amt, $userId]);
            
            $narrationRelease = "Booster sponsor income released from $origUser's board completion";
            insertTransaction($pdo, $userId, 'sponsor_income_released', $amt, $wallet, 'Completed', $narrationRelease, $origUser);
            
            $stmtReleaseLedger = $pdo->prepare("UPDATE company_ledger SET total_held_sponsor_income = total_held_sponsor_income - ?, total_payout_liability_booster = total_payout_liability_booster + ? WHERE id = 1");
            $stmtReleaseLedger->execute([$amt, $amt]);
        }
        
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Handles purchase and initial placement of a booster package.
 */
function processBoosterPurchase($pdo, $buyerUserId, $boosterType, $fundedByUserId = null) {
    global $BOOSTER_CONFIG;
    if (!isset($BOOSTER_CONFIG[$boosterType])) {
        throw new Exception("Invalid booster type.");
    }
    
    $config = $BOOSTER_CONFIG[$boosterType];
    $cost = $config['cost'];
    $payerId = $fundedByUserId ?: $buyerUserId;
    
    // Check sequential eligibility: must have previous booster active
    $boosterKeys = array_keys($BOOSTER_CONFIG);
    $currentIndex = array_search($boosterType, $boosterKeys);
    if ($currentIndex > 0) {
        $prevBooster = $boosterKeys[$currentIndex - 1];
        if (!hasPackageActive($pdo, $buyerUserId, $prevBooster)) {
            throw new Exception("You must purchase " . $BOOSTER_CONFIG[$prevBooster]['name'] . " first.");
        }
    }
    
    // Check balance
    $stmt = $pdo->prepare("SELECT main_deposit_balance FROM user_financial_summary WHERE user_id = ?");
    $stmt->execute([$payerId]);
    $balance = $stmt->fetchColumn() ?: 0;
    
    if ($balance < $cost) {
        throw new Exception("Insufficient main deposit balance.");
    }
    
    // Check if already active
    if (hasPackageActive($pdo, $buyerUserId, $boosterType)) {
        throw new Exception("This booster is already active.");
    }
    
    $pdo->beginTransaction();
    try {
        // a) Deduct cost
        $stmt = $pdo->prepare("UPDATE user_financial_summary SET main_deposit_balance = main_deposit_balance - ? WHERE user_id = ?");
        $stmt->execute([$cost, $payerId]);
        
        $narration = $payerId === $buyerUserId 
            ? "Purchased " . $config['name'] . " — self activation"
            : "Purchased " . $config['name'] . " for " . $buyerUserId . " — gifted by " . $payerId;
        insertTransaction($pdo, $payerId, 'booster_purchase', $cost, 'main_deposit', 'Completed', $narration, $buyerUserId);
        
        // b) Update company ledger
        $stmtLedger = $pdo->prepare("UPDATE company_ledger SET unutilized_funds = unutilized_funds - ?, invested_funds = invested_funds + ? WHERE id = 1");
        $stmtLedger->execute([$cost, $cost]);
        
        // c) Activate package in user_packages
        $stmt = $pdo->prepare("INSERT INTO user_packages (user_id, package_type, is_active, funded_by) VALUES (?, ?, 1, ?)");
        $stmt->execute([$buyerUserId, $boosterType, $payerId]);
        
        // d) Place in booster matrix
        $pos = placeInBoosterMatrix($pdo, $buyerUserId, $boosterType);
        
        $pdo->commit();
        
        // e) Check if upline completed board
        checkAndProcessBoosterBoardCompletion($pdo, $pos['upline_id'], $boosterType);
        
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
