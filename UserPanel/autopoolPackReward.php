<?php 
require_once 'includes/check_package.php';
include '../includes/header.php'; 

$pack = isset($_GET['pack']) ? (int)$_GET['pack'] : 1;

if ($pack !== 1) {
    echo "<div style='padding: 24px; color: #e74c3c; font-weight: bold;'>Reward Income is only applicable for Infinity Crypto Hub 1 (Starter).</div>";
    include '../includes/footer.php';
    exit;
}

// Fetch user's leg counts for reward milestones
$stmtSum = $pdo->prepare("SELECT strong_leg_count, other_legs_count FROM user_financial_summary WHERE user_id = ?");
$stmtSum->execute([$user_id]);
$legData = $stmtSum->fetch(PDO::FETCH_ASSOC);
$strong_leg = (int)($legData['strong_leg_count'] ?? 0);
$other_legs = (int)($legData['other_legs_count'] ?? 0);

// Query reward transactions for this user
$stmt = $pdo->prepare("
    SELECT * FROM transactions 
    WHERE user_id = ? AND transaction_type = 'reward_income' 
    ORDER BY id DESC
");
$stmt->execute([$user_id]);
$rewards = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Define reward milestones
$milestones = [
    1 => ['strong' => 15, 'other' => 15, 'amount' => 15],
    2 => ['strong' => 18, 'other' => 18, 'amount' => 3],
    3 => ['strong' => 24, 'other' => 24, 'amount' => 6],
    4 => ['strong' => 36, 'other' => 36, 'amount' => 12],
    5 => ['strong' => 60, 'other' => 60, 'amount' => 24],
    6 => ['strong' => 108, 'other' => 108, 'amount' => 48],
    7 => ['strong' => 204, 'other' => 204, 'amount' => 96],
    8 => ['strong' => 396, 'other' => 396, 'amount' => 192],
    9 => ['strong' => 780, 'other' => 780, 'amount' => 384],
    10 => ['strong' => 1548, 'other' => 1548, 'amount' => 768]
];
?>

<div id="autopoolPack<?php echo $pack; ?>Reward" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">Infinity Crypto Hub <?php echo $pack; ?> - Reward Income & Milestones</div>
        <div class="profile-breadcrumb">
            <a href="index.php">Home</a> &raquo; 
            Infinity Crypto Hub <?php echo $pack; ?> &raquo; 
            Reward Income
        </div>
    </div>
    
    <!-- User Progress Summary -->
    <div style="display: flex; gap: 20px; margin-top: 20px; flex-wrap: wrap;">
        <div class="db-gold-card" style="flex: 1; min-width: 250px;">
            <div class="db-card-label">Strong Leg Count</div>
            <div class="db-card-value"><?php echo number_format($strong_leg); ?></div>
        </div>
        <div class="db-gold-card" style="flex: 1; min-width: 250px;">
            <div class="db-card-label">Other Legs Count</div>
            <div class="db-card-value"><?php echo number_format($other_legs); ?></div>
        </div>
    </div>

    <!-- Milestones Progress -->
    <div class="table-container" style="background: rgba(6, 17, 33, 0.75); border: 1px solid rgba(255, 183, 3, 0.2); border-radius: 12px; padding: 24px; margin-top: 20px;">
        <h3 style="color: #ffb703; margin-bottom: 15px;">Reward Milestones Progress</h3>
        <table class="custom-table" style="width: 100%; text-align: left; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid rgba(255,183,3,0.3); color:#ffb703; height:45px;">
                    <th>Level</th>
                    <th>Target (Strong + Other)</th>
                    <th>Achieved (Strong + Other)</th>
                    <th>Reward</th>
                    <th>Progress</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($milestones as $lvl => $m): 
                    $targetStrong = $m['strong'];
                    $targetOther = $m['other'];
                    
                    $achievedStrong = min($strong_leg, $targetStrong);
                    $achievedOther = min($other_legs, $targetOther);
                    
                    $totalTarget = $targetStrong + $targetOther;
                    $totalAchieved = $achievedStrong + $achievedOther;
                    $percent = ($totalTarget > 0) ? floor(($totalAchieved / $totalTarget) * 100) : 0;
                    
                    $status = ($percent >= 100) ? '<span class="badge badge-active">Achieved</span>' : '<span class="badge" style="background:#e67e22;">Pending</span>';
                ?>
                <tr style="height: 50px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <td>Level <?php echo $lvl; ?></td>
                    <td><?php echo number_format($targetStrong); ?> + <?php echo number_format($targetOther); ?></td>
                    <td style="color: #cbd5e0;"><?php echo number_format($achievedStrong); ?> + <?php echo number_format($achievedOther); ?></td>
                    <td style="font-weight: 600; color: #2ecc71;">$<?php echo number_format($m['amount'], 2); ?></td>
                    <td style="width: 200px;">
                        <div style="background: rgba(255,255,255,0.1); border-radius: 4px; height: 8px; width: 100%; margin-top: 5px;">
                            <div style="background: #2ecc71; height: 8px; border-radius: 4px; width: <?php echo $percent; ?>%;"></div>
                        </div>
                        <div style="font-size: 11px; text-align: right; margin-top: 3px; color: #a0aec0;"><?php echo $percent; ?>%</div>
                    </td>
                    <td><?php echo $status; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Payout History -->
    <div class="table-container" style="background: rgba(6, 17, 33, 0.75); border: 1px solid rgba(255, 183, 3, 0.2); border-radius: 12px; padding: 24px; margin-top: 20px;">
        <h3 style="color: #ffb703; margin-bottom: 15px;">Payout History</h3>
        <table class="custom-table" style="width: 100%; text-align: left; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid rgba(255,183,3,0.3); color:#ffb703; height:45px;">
                    <th>#</th>
                    <th>Amount</th>
                    <th>Narration</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rewards)): ?>
                    <tr style="height: 50px; border-bottom: 1px solid rgba(255,255,255,0.05); color:#a0aec0;">
                        <td colspan="5" style="text-align: center;">No reward income payouts recorded yet.</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $idx = 1;
                    foreach ($rewards as $r): 
                        $formattedDate = date('d M Y H:i', strtotime($r['created_at']));
                    ?>
                        <tr style="height: 50px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td><?php echo $idx++; ?></td>
                            <td style="font-weight: 600; color: #2ecc71;">$<?php echo number_format($r['amount'], 2); ?></td>
                            <td style="color:#cbd5e0;"><?php echo htmlspecialchars($r['narration']); ?></td>
                            <td>
                                <span class="badge badge-active">
                                    <?php echo htmlspecialchars($r['status']); ?>
                                </span>
                            </td>
                            <td style="color:#a0aec0;"><?php echo $formattedDate; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>