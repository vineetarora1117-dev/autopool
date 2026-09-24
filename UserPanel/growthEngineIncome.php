<?php
require_once '../libs/db.php';
require_once '../libs/auth.php';
require_once '../libs/config.php';
require_once '../includes/GrowthEngine.php';

requireLogin();
$user_id = $_SESSION['user_id'];

// Fetch user's boosters list
$stmtBoosters = $pdo->prepare("SELECT * FROM user_growth_engines WHERE user_id = ? ORDER BY id DESC");
$stmtBoosters->execute([$user_id]);
$boosters = $stmtBoosters->fetchAll(PDO::FETCH_ASSOC);

// Calculate metrics
$totalOwned = count($boosters);
$activeCount = 0;
foreach ($boosters as $b) {
    if ($b['status'] === 'active') {
        $activeCount++;
    }
}

// Fetch summary metrics
$stmtSummary = $pdo->prepare("SELECT growth_engine_wallet, total_growth_engine_income FROM user_financial_summary WHERE user_id = ?");
$stmtSummary->execute([$user_id]);
$summary = $stmtSummary->fetch(PDO::FETCH_ASSOC);

$totalBoosterIncome = floatval($summary['total_growth_engine_income'] ?? 0);

include '../includes/header.php';
?>

<div class="content-section active-view" style="padding: 20px; max-width: 1100px; margin: 0 auto;">
    <div class="profile-header-bar">
        <div class="profile-header-title">
            <i class="fa-solid fa-bolt"></i> Growth Engine Income & Matrix Progress
        </div>
        <div class="profile-breadcrumb">
            <a href="index.php">Home</a> &raquo; Growth Engine &raquo; Growth Engine Income
        </div>
    </div>

    <!-- Summary Metrics Grid: Total Growth Engines Owned | Active Growth Engines | Total Growth Engine Income Earned -->
    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 25px;">
        <div class="db-gold-card">
            <div class="db-card-label">Total Growth Engines Owned</div>
            <div class="db-card-value"><?php echo $totalOwned; ?></div>
            <div class="db-card-watermark"><i class="fa-solid fa-layer-group fa-2x"></i></div>
        </div>

        <div class="db-gold-card">
            <div class="db-card-label">Active Growth Engines</div>
            <div class="db-card-value" style="color: #2ecc71;"><?php echo $activeCount; ?></div>
            <div class="db-card-watermark"><i class="fa-solid fa-spinner fa-2x"></i></div>
        </div>

        <div class="db-gold-card">
            <div class="db-card-label">Total Growth Engine Income Earned</div>
            <div class="db-card-value">$<?php echo number_format($totalBoosterIncome, 2); ?></div>
            <div class="db-card-watermark"><i class="fa-solid fa-sack-dollar fa-2x"></i></div>
        </div>
    </div>

    <!-- MERGED SINGLE TABLE: My Growth Engines Progress & Income Payouts -->
    <div class="table-container" style="background: rgba(6, 17, 33, 0.75); border: 1px solid #ffb703; border-radius: 12px; padding: 25px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;">
            <h3 style="color: #ffb703; margin: 0; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-list-check"></i> My Growth Engines & Income Payout Log
            </h3>
            <a href="growthEngineTree.php" style="background: linear-gradient(135deg, #ffb703, #e6a100); color: #000; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 8px rgba(255, 183, 3, 0.3); transition: transform 0.2s;">
                <i class="fa-solid fa-sitemap"></i> Global Growth Engine Tree
            </a>
        </div>

        <div style="overflow-x: auto;">
            <table class="custom-table" style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="background: rgba(255, 183, 3, 0.15); color: #ffb703; height: 42px;">
                        <th>Growth Engine ID</th>
                        <th>Purchase Type</th>
                        <th>Downlines Filled</th>
                        <th>Progress</th>
                        <th>Income Generated</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($boosters)): ?>
                        <tr>
                            <td colspan="8" class="empty-row-msg">No Growth Engines purchased yet. <a href="buyGrowthEngine.php" style="color:#ffb703;">Buy your first Growth Engine now!</a></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($boosters as $b): ?>
                            <?php 
                            $count = intval($b['downline_count']);
                            $status = $b['status'];
                            $percent = min(100, round(($count / 3) * 100));
                            $incomeEarned = ($status === 'completed') ? 10.00 : 0.00;
                            
                            $statusBadge = ($status === 'completed') 
                                ? '<span style="background: rgba(46, 204, 113, 0.2); color: #2ecc71; padding: 4px 10px; border-radius: 12px; border: 1px solid #2ecc71; font-weight: bold;">Completed</span>' 
                                : '<span style="background: rgba(241, 196, 15, 0.2); color: #f1c40f; padding: 4px 10px; border-radius: 12px; border: 1px solid #f1c40f; font-weight: bold;">Active</span>';
                            
                            $typeBadge = ($b['purchase_type'] === 'manual') 
                                ? '<span style="color: #3498db; font-weight: bold;"><i class="fa-solid fa-hand-holding-dollar"></i> Manual</span>' 
                                : '<span style="color: #9b59b6; font-weight: bold;"><i class="fa-solid fa-arrows-rotate"></i> Auto Re-entry</span>';
                            ?>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); height: 48px;">
                                <td style="font-weight: bold; color: #ffb703;">#<?php echo htmlspecialchars($b['id']); ?></td>
                                <td><?php echo $typeBadge; ?></td>
                                <td style="font-weight: bold; color: #fff;"><?php echo $count; ?> / 3 Downlines</td>
                                <td style="width: 170px;">
                                    <div style="background: rgba(255,255,255,0.1); border-radius: 10px; height: 10px; overflow: hidden; position: relative;">
                                        <div style="background: linear-gradient(90deg, #ffcf00, #2ecc71); width: <?php echo $percent; ?>%; height: 100%;"></div>
                                    </div>
                                    <div style="font-size: 11px; color: #a0aec0; margin-top: 3px; text-align: right;"><?php echo $percent; ?>%</div>
                                </td>
                                <td style="font-weight: bold; color: <?php echo $incomeEarned > 0 ? '#2ecc71' : '#a0aec0'; ?>;">
                                    <?php echo $incomeEarned > 0 ? '+$' . number_format($incomeEarned, 2) : '$0.00'; ?>
                                </td>
                                <td><?php echo $statusBadge; ?></td>
                                <td style="color: #a0aec0; font-size: 13px;"><?php echo date('d M Y, h:i A', strtotime($b['created_at'])); ?></td>
                                <td>
                                    <a href="growthEngineTree.php?id=<?php echo $b['id']; ?>" style="color: #ffb703; font-weight: bold; text-decoration: none; background: rgba(255,183,3,0.1); border: 1px solid #ffb703; padding: 4px 10px; border-radius: 6px; font-size: 12px; display: inline-flex; align-items: center; gap: 5px;">
                                        <i class="fa-solid fa-sitemap"></i> Tree
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
