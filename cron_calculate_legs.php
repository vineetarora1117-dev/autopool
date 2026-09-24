<?php
require_once __DIR__ . '/libs/db.php';

// echo "Starting Leg Calculation Engine...\n";

// 1. Fetch all users and their sponsors
$stmt = $pdo->query("SELECT user_id, sponsor_id FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tree = []; // sponsor_id => array of user_ids
$allUsers = [];
foreach ($users as $u) {
    $uid = $u['user_id'];
    $sid = $u['sponsor_id'];
    $allUsers[] = $uid;
    
    if (!empty($sid)) {
        if (!isset($tree[$sid])) {
            $tree[$sid] = [];
        }
        $tree[$sid][] = $uid;
    }
}

// Function to calculate total downline size including the root itself
$memo = [];
function getDownlineSize($uid, &$tree, &$memo) {
    if (isset($memo[$uid])) {
        return $memo[$uid];
    }
    
    $size = 1; // Count self
    if (isset($tree[$uid])) {
        foreach ($tree[$uid] as $child) {
            $size += getDownlineSize($child, $tree, $memo);
        }
    }
    
    $memo[$uid] = $size;
    return $size;
}

// Pre-calculate all downline sizes via memoization
foreach ($allUsers as $uid) {
    getDownlineSize($uid, $tree, $memo);
}

// Now, for each user, calculate strong leg and other legs
$updateQuery = "UPDATE user_financial_summary SET direct_team_count = ?, strong_leg_count = ?, other_legs_count = ? WHERE user_id = ?";
$stmtUpdate = $pdo->prepare($updateQuery);

$reward_levels = [
    1 => ['target' => 15, 'reward' => 15.00],
    2 => ['target' => 18, 'reward' => 3.00],
    3 => ['target' => 24, 'reward' => 6.00],
    4 => ['target' => 36, 'reward' => 12.00],
    5 => ['target' => 60, 'reward' => 24.00],
    6 => ['target' => 108, 'reward' => 48.00],
    7 => ['target' => 204, 'reward' => 96.00],
    8 => ['target' => 396, 'reward' => 192.00],
    9 => ['target' => 780, 'reward' => 384.00],
    10 => ['target' => 1548, 'reward' => 768.00]
];
$stmtCheckReward = $pdo->prepare("SELECT id FROM user_rewards WHERE user_id = ? AND level = ?");
$stmtInsertReward = $pdo->prepare("INSERT INTO user_rewards (user_id, level, amount) VALUES (?, ?, ?)");
$stmtAddRewardMoney = $pdo->prepare("UPDATE user_financial_summary SET main_deposit_balance = main_deposit_balance + ?, total_reward_income = total_reward_income + ? WHERE user_id = ?");
$stmtAddTx = $pdo->prepare("INSERT INTO transactions (user_id, transaction_type, amount, description) VALUES (?, 'reward_income', ?, ?)");
$pdo->beginTransaction();

$count = 0;
foreach ($allUsers as $uid) {
    $directs = $tree[$uid] ?? [];
    $directCount = count($directs);
    
    if ($directCount == 0) {
        $stmtUpdate->execute([0, 0, 0, $uid]);
        $count++;
        continue;
    }
    
    $legSizes = [];
    foreach ($directs as $direct) {
        // Size of the leg is the downline size of the direct referral
        $legSizes[] = getDownlineSize($direct, $tree, $memo);
    }
    
    // Sort descending to easily find the strong leg
    rsort($legSizes); 
    
    $strongLeg = $legSizes[0];
    $otherLegs = 0;
    for ($i = 1; $i < count($legSizes); $i++) {
        $otherLegs += $legSizes[$i];
    }
    
    $stmtUpdate->execute([$directCount, $strongLeg, $otherLegs, $uid]);
    
    foreach ($reward_levels as $level => $req) {
        if ($strongLeg >= $req['target'] && $otherLegs >= $req['target']) {
            $stmtCheckReward->execute([$uid, $level]);
            if (!$stmtCheckReward->fetch()) {
                $stmtInsertReward->execute([$uid, $level, $req['reward']]);
                $stmtAddRewardMoney->execute([$req['reward'], $req['reward'], $uid]);
                $stmtAddTx->execute([$uid, 'reward_income', $req['reward'], "Reward Achieved for Level $level"]);
            }
        }
    }
    
    $count++;
}

$pdo->commit();

// echo "Finished. Updated leg counts for $count users.\n";
?>
