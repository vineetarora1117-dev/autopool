<?php
/**
 * BoosterEngine.php
 * Core 1x3 Auto-Cycling Matrix Engine for Infinity Booster Module
 */

if (!function_exists('getUserLastManualBoosterTime')) {
    /**
     * Get unix timestamp of user's last manual booster purchase
     */
    function getUserLastManualBoosterTime($pdo, $userId) {
        $stmt = $pdo->prepare("SELECT UNIX_TIMESTAMP(created_at) FROM user_boosters WHERE user_id = ? AND purchase_type = 'manual' ORDER BY id DESC LIMIT 1");
        $stmt->execute([$userId]);
        $lastTime = $stmt->fetchColumn();
        return $lastTime ? intval($lastTime) : 0;
    }
}

if (!function_exists('getBoosterCooldownSecondsRemaining')) {
    /**
     * Calculate seconds remaining for manual purchase lock using MySQL server clock
     */
    function getBoosterCooldownSecondsRemaining($pdo, $userId) {
        $stmtLast = $pdo->prepare("SELECT UNIX_TIMESTAMP(created_at) FROM user_boosters WHERE user_id = ? AND purchase_type = 'manual' ORDER BY id DESC LIMIT 1");
        $stmtLast->execute([$userId]);
        $lastPurchaseTime = $stmtLast->fetchColumn();
        if (!$lastPurchaseTime) {
            return 0; // No previous manual purchase
        }
        
        // Fetch current MySQL server time as UNIX timestamp
        $stmtNow = $pdo->query("SELECT UNIX_TIMESTAMP(NOW())");
        $dbNow = intval($stmtNow->fetchColumn());

        $cooldownWindow = 6; // TEMPORARY TESTING VALUE: 6 seconds (Production: 6 * 3600 = 6 hours)
        $elapsed = $dbNow - intval($lastPurchaseTime);
        $remaining = $cooldownWindow - $elapsed;
        return ($remaining > 0) ? $remaining : 0;
    }
}

if (!function_exists('purchaseBooster')) {
    /**
     * Manual Booster Purchase ($10.00 from main_deposit_balance)
     */
    function purchaseBooster($pdo, $userId) {
        // 1. Check 6-hour cooldown constraint
        $remaining = getBoosterCooldownSecondsRemaining($pdo, $userId);
        if ($remaining > 0) {
            $hours = floor($remaining / 3600);
            $minutes = floor(($remaining % 3600) / 60);
            $seconds = $remaining % 60;
            return [
                'success' => false,
                'message' => sprintf("Cooldown Active: Please wait %02dh %02dm %02ds before purchasing another booster.", $hours, $minutes, $seconds)
            ];
        }

        // 2. Check main deposit wallet balance
        $stmt = $pdo->prepare("SELECT main_deposit_balance FROM user_financial_summary WHERE user_id = ? FOR UPDATE");
        $stmt->execute([$userId]);
        $balance = $stmt->fetchColumn();

        if ($balance === false || floatval($balance) < 10.00) {
            return [
                'success' => false,
                'message' => "Insufficient Main Wallet balance. You need at least $10.00 to purchase a Booster."
            ];
        }

        $pdo->beginTransaction();
        try {
            // 3. Deduct $10.00 from main deposit wallet
            $stmtDeduct = $pdo->prepare("UPDATE user_financial_summary SET main_deposit_balance = main_deposit_balance - 10.00 WHERE user_id = ?");
            $stmtDeduct->execute([$userId]);

            // 4. Log transaction in master transactions table
            $stmtTx = $pdo->prepare("INSERT INTO transactions (user_id, transaction_type, amount, wallet_type, status, narration) VALUES (?, 'booster_purchase', 10.00, 'main_deposit', 'Completed', ?)");
            $stmtTx->execute([$userId, "Purchased 1x3 Booster ($10.00)"]);

            // 5. Process matrix placement queue
            $placedBoosterId = processBoosterPlacementQueue($pdo, $userId, 'manual');

            $pdo->commit();

            return [
                'success' => true,
                'message' => "Booster #{$placedBoosterId} purchased successfully!",
                'booster_id' => $placedBoosterId
            ];

        } catch (Exception $e) {
            $pdo->rollBack();
            return [
                'success' => false,
                'message' => "Purchase Error: " . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('processBoosterPlacementQueue')) {
    /**
     * Non-recursive Iterative Queue Processor for Booster Placements & Domino Cycle Completions
     * Protected by MySQL GET_LOCK for Hostinger concurrency safety.
     */
    function processBoosterPlacementQueue($pdo, $initialUserId, $initialPurchaseType = 'manual') {
        // Acquire MySQL application lock (Wait up to 5 seconds)
        $stmtLock = $pdo->query("SELECT GET_LOCK('booster_matrix_queue_lock', 5)");
        $lockAcquired = $stmtLock->fetchColumn();

        if (!$lockAcquired) {
            throw new Exception("Server busy: Could not acquire matrix lock. Please try again.");
        }

        $placedInitialBoosterId = null;

        try {
            $queue = [
                [
                    'user_id' => $initialUserId,
                    'type'    => $initialPurchaseType,
                    'from_booster_id' => null
                ]
            ];

            $maxIterations = 500;
            $iterations = 0;

            while (!empty($queue) && $iterations < $maxIterations) {
                $iterations++;
                $current = array_shift($queue);
                $currentUserId = $current['user_id'];
                $currentType   = $current['type'];
                $fromBoosterId = $current['from_booster_id'];

                // Find next available parent node in global 1x3 matrix (Top-to-Bottom, Left-to-Right)
                $stmtParent = $pdo->query("SELECT id, user_id, downline_count FROM user_boosters WHERE status = 'active' AND downline_count < 3 ORDER BY id ASC LIMIT 1 FOR UPDATE");
                $parent = $stmtParent->fetch(PDO::FETCH_ASSOC);

                $uplineBoosterId = $parent ? $parent['id'] : null;

                // Create new booster node
                $stmtInsert = $pdo->prepare("INSERT INTO user_boosters (user_id, upline_booster_id, downline_count, purchase_type, status) VALUES (?, ?, 0, ?, 'active')");
                $stmtInsert->execute([$currentUserId, $uplineBoosterId, $currentType]);
                $newBoosterId = $pdo->lastInsertId();

                if ($placedInitialBoosterId === null) {
                    $placedInitialBoosterId = $newBoosterId;
                }

                // If this entry was an auto re-entry, log in booster_transactions
                if ($currentType === 'reentry') {
                    $stmtLog = $pdo->prepare("INSERT INTO booster_transactions (booster_id, user_id, from_booster_id, from_user_id, amount, type, narration) VALUES (?, ?, ?, ?, 10.00, 'auto_reentry', ?)");
                    $stmtLog->execute([
                        $newBoosterId,
                        $currentUserId,
                        $fromBoosterId,
                        $currentUserId,
                        "Auto Re-entry Booster #{$newBoosterId} created from Booster #{$fromBoosterId} cycle completion"
                    ]);

                    // Also log in master transactions table
                    $stmtMasterTx = $pdo->prepare("INSERT INTO transactions (user_id, transaction_type, amount, wallet_type, status, narration) VALUES (?, 'booster_purchase', 10.00, 'auto_reentry', 'Completed', ?)");
                    $stmtMasterTx->execute([
                        $currentUserId,
                        "Auto Re-entry Booster #{$newBoosterId} placed in global matrix"
                    ]);
                }

                // If attached under a parent, update parent's downline count
                if ($uplineBoosterId !== null) {
                    $stmtUpdateParent = $pdo->prepare("UPDATE user_boosters SET downline_count = downline_count + 1 WHERE id = ?");
                    $stmtUpdateParent->execute([$uplineBoosterId]);

                    // Re-fetch parent downline count
                    $stmtCheckParent = $pdo->prepare("SELECT id, user_id, downline_count FROM user_boosters WHERE id = ?");
                    $stmtCheckParent->execute([$uplineBoosterId]);
                    $updatedParent = $stmtCheckParent->fetch(PDO::FETCH_ASSOC);

                    // IF PARENT REACHED 3 DOWNLINES -> CYCLE COMPLETED!
                    if ($updatedParent && intval($updatedParent['downline_count']) >= 3) {
                        $parentBoosterId = $updatedParent['id'];
                        $parentOwnerId   = $updatedParent['user_id'];

                        // Mark parent booster as completed
                        $stmtComplete = $pdo->prepare("UPDATE user_boosters SET status = 'completed', completed_at = CURRENT_TIMESTAMP WHERE id = ?");
                        $stmtComplete->execute([$parentBoosterId]);

                        // 1. Credit $10.00 to parent owner's booster_10_wallet in user_financial_summary
                        $stmtCreditUser = $pdo->prepare("UPDATE user_financial_summary SET booster_10_wallet = booster_10_wallet + 10.00, total_booster_income = total_booster_income + 10.00 WHERE user_id = ?");
                        $stmtCreditUser->execute([$parentOwnerId]);

                        // Log in booster_transactions
                        $stmtLogEarning = $pdo->prepare("INSERT INTO booster_transactions (booster_id, user_id, from_booster_id, from_user_id, amount, type, narration) VALUES (?, ?, ?, ?, 10.00, 'user_earning', ?)");
                        $stmtLogEarning->execute([
                            $parentBoosterId,
                            $parentOwnerId,
                            $newBoosterId,
                            $currentUserId,
                            "Earned $10.00 from Booster #{$parentBoosterId} 1x3 cycle completion"
                        ]);

                        // Log in master transactions
                        $stmtMasterUser = $pdo->prepare("INSERT INTO transactions (user_id, transaction_type, amount, wallet_type, status, narration) VALUES (?, 'booster_income', 10.00, 'booster_10_wallet', 'Completed', ?)");
                        $stmtMasterUser->execute([
                            $parentOwnerId,
                            "Earned $10.00 from Booster #{$parentBoosterId} 1x3 cycle completion"
                        ]);

                        // 2. Credit $10.00 to Company Wallet
                        $stmtCreditCompany = $pdo->prepare("UPDATE company_ledger SET company_wallet_balance = company_wallet_balance + 10.00 WHERE id = 1");
                        $stmtCreditCompany->execute();
                        if ($stmtCreditCompany->rowCount() === 0) {
                            $pdo->exec("INSERT INTO company_ledger (id, company_wallet_balance) VALUES (1, 10.00) ON DUPLICATE KEY UPDATE company_wallet_balance = company_wallet_balance + 10.00");
                        }

                        // Log in master transactions for Company (SA000001)
                        $stmtMasterComp = $pdo->prepare("INSERT INTO transactions (user_id, transaction_type, amount, wallet_type, status, narration) VALUES ('SA000001', 'company_revenue', 10.00, 'company_wallet', 'Completed', ?)");
                        $stmtMasterComp->execute([
                            "Company revenue $10.00 from Booster #{$parentBoosterId} cycle completion"
                        ]);

                        // 3. Push parent owner's Auto Re-entry request onto queue to continue domino chain
                        $queue[] = [
                            'user_id' => $parentOwnerId,
                            'type'    => 'reentry',
                            'from_booster_id' => $parentBoosterId
                        ];
                    }
                }
            }

            // Release MySQL lock
            $pdo->query("SELECT RELEASE_LOCK('booster_matrix_queue_lock')");

            return $placedInitialBoosterId;

        } catch (Exception $e) {
            $pdo->query("SELECT RELEASE_LOCK('booster_matrix_queue_lock')");
            throw $e;
        }
    }
}

if (!function_exists('getUserBoosterSummary')) {
    /**
     * Fetch user's active & completed boosters with progress
     */
    function getUserBoosterSummary($pdo, $userId) {
        $stmt = $pdo->prepare("SELECT * FROM user_boosters WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
