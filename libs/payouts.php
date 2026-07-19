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
    
    // Check direct downlines
    if ($config['req_downlines'] > 0) {
        $stmt = $pdo->prepare("SELECT direct_team_count FROM user_financial_summary WHERE user_id = ?");
        $stmt->execute([$userId]);
        $directCount = $stmt->fetchColumn() ?: 0;
        
        if ($directCount < $config['req_downlines']) {
            return ['eligible' => false, 'reason' => "You need at least {$config['req_downlines']} direct downlines. You have {$directCount}."];
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

function insertTransaction($pdo, $userId, $type, $amount, $walletType, $status, $narration, $relatedId = null) {
    $stmt = $pdo->prepare("
        INSERT INTO transactions (user_id, transaction_type, amount, wallet_type, status, narration, related_user_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$userId, $type, $amount, $walletType, $status, $narration, $relatedId]);
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
        
        // e) Autopool Income Distribution
        $matrixUplines = getMatrixUplines($pdo, $buyerUserId, $packageType, $config['autopool_levels']);
        foreach ($matrixUplines as $upline) {
            $amt = $config['autopool_amount'];
            $narration = "Autopool income $$amt from $buyerUserId entering $$cost Matrix (Position: L{$pos['level']}-{$pos['slot']})";
            if ($upline === 'SA000001') {
                sweepToCompany($pdo, $amt, 'autopool_income', $narration, $buyerUserId);
            } else {
                $stmt = $pdo->prepare("UPDATE user_financial_summary SET {$config['wallet']} = {$config['wallet']} + ?, total_global_autopool_income = total_global_autopool_income + ? WHERE user_id = ?");
                $stmt->execute([$amt, $amt, $upline]);
                $stmtLiab = $pdo->prepare("UPDATE company_ledger SET total_payout_liability_main = total_payout_liability_main + ? WHERE id = 1");
                $stmtLiab->execute([$amt]);
                insertTransaction($pdo, $upline, 'autopool_income', $amt, $config['wallet'], 'Completed', $narration, $buyerUserId);
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
        foreach ($sponsorUplines as $levelIdx => $upline) {
            $amt = $config['level_amount'];
            $actualLevel = $levelIdx + 1;
            if ($upline === 'SA000001') {
                $narration = "Level income $$amt from $buyerUserId — Level $actualLevel of $$cost Package tree";
                sweepToCompany($pdo, $amt, 'level_income', $narration, $buyerUserId);
            } else {
                $uplineActive = hasPackageActive($pdo, $upline, $packageType);
                if ($uplineActive) {
                    $narration = "Level income $$amt from $buyerUserId — Level $actualLevel of $$cost Package tree";
                    $stmt = $pdo->prepare("UPDATE user_financial_summary SET {$config['wallet']} = {$config['wallet']} + ?, total_team_level_income = total_team_level_income + ? WHERE user_id = ?");
                    $stmt->execute([$amt, $amt, $upline]);
                    $stmtLiab = $pdo->prepare("UPDATE company_ledger SET total_payout_liability_main = total_payout_liability_main + ? WHERE id = 1");
                $stmtLiab->execute([$amt]);
                    insertTransaction($pdo, $upline, 'level_income', $amt, $config['wallet'], 'Completed', $narration, $buyerUserId);
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
            // Update transaction to Released
            $stmtUpd = $pdo->prepare("UPDATE transactions SET status = 'Released' WHERE id = ?");
            $stmtUpd->execute([$heldTx['id']]);
            
            // Credit wallet
            $stmtBal = $pdo->prepare("
                UPDATE user_financial_summary 
                SET {$config['wallet']} = {$config['wallet']} + ?,
                    total_direct_referral_income = total_direct_referral_income + ? 
                WHERE user_id = ?
            ");
            // Simplified, assuming all held income is sponsor income (level income was also held as 'sponsor_income_held' per requirement)
            $stmtBal->execute([$amt, $amt, $buyerUserId]);
            
            // Add new transaction
            $originalUser = $heldTx['related_user_id'];
            $narration = "Income $$amt RELEASED — previously held from $originalUser's $$cost Package. You are now eligible";
            insertTransaction($pdo, $buyerUserId, 'sponsor_income_released', $amt, $config['wallet'], 'Completed', $narration, $originalUser);
            
            // Update company ledger held metrics
            $stmtRelease = $pdo->prepare("UPDATE company_ledger SET total_held_sponsor_income = total_held_sponsor_income - ?, total_payout_liability_main = total_payout_liability_main + ? WHERE id = 1");
            $stmtRelease->execute([$amt, $amt]);
        }
        
        $pdo->commit();
        return true;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
