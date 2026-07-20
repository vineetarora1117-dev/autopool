<?php 
require_once '../libs/db.php';
require_once '../libs/auth.php';
require_once '../libs/config.php';

requireLogin();
$user_id = $_SESSION['user_id'] ?? '';

$pack = isset($_GET['pack']) ? (int)$_GET['pack'] : 1;
$wallet_map = [
    1 => 'booster_10_wallet',
    2 => 'booster_20_wallet',
    3 => 'booster_40_wallet',
    4 => 'booster_80_wallet',
    5 => 'booster_160_wallet',
    6 => 'booster_320_wallet'
];
$wallet_type = $wallet_map[$pack] ?? 'booster_10_wallet';

// Fetch booster transactions
$stmt = $pdo->prepare("
    SELECT * FROM transactions 
    WHERE user_id = ? AND transaction_type IN ('booster_income', 'sponsor_income', 'sponsor_income_released') AND wallet_type = ? 
    ORDER BY id DESC
");
$stmt->execute([$user_id, $wallet_type]);
$incomes = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php'; 
?>

<div id="infinityPack<?php echo $pack; ?>IncomeReport" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">Infinity Pack <?php echo $pack; ?> - Income Report</div>
        <div class="profile-breadcrumb">
            <a href="index.php">Home</a> &raquo; 
            Infinity Pack <?php echo $pack; ?> &raquo; 
            Income Report
        </div>
    </div>
    
    <div class="table-container" style="background: rgba(6, 17, 33, 0.75); border: 1px solid rgba(255, 183, 3, 0.2); border-radius: 12px; padding: 24px; margin-top: 20px;">
        <table class="custom-table" style="width: 100%; text-align: left; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid rgba(255,183,3,0.3); color:#ffb703; height:45px;">
                    <th>#</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Narration</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($incomes)): ?>
                    <tr style="height: 50px; border-bottom: 1px solid rgba(255,255,255,0.05); color:#a0aec0;">
                        <td colspan="6" style="text-align: center;">No booster income payouts recorded for this pack yet.</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $idx = 1;
                    foreach ($incomes as $inc): 
                        $formattedDate = date('d M Y H:i', strtotime($inc['created_at']));
                        
                        // Badge style depending on status
                        $badgeColor = '#a0aec0';
                        if ($inc['status'] === 'Completed' || $inc['status'] === 'Released') {
                            $badgeColor = '#2ecc71';
                        } elseif ($inc['status'] === 'Pending' || $inc['status'] === 'Held') {
                            $badgeColor = '#f39c12';
                        }
                        
                        $typeLabel = '';
                        if ($inc['transaction_type'] === 'booster_income') {
                            $typeLabel = 'Board Earnings';
                        } elseif ($inc['transaction_type'] === 'sponsor_income') {
                            $typeLabel = 'Sponsor Income';
                        } elseif ($inc['transaction_type'] === 'sponsor_income_released') {
                            $typeLabel = 'Released Sponsor';
                        }
                    ?>
                        <tr style="height: 50px; border-bottom: 1px solid rgba(255,255,255,0.05); color:#e2e8f0;">
                            <td><?php echo $idx++; ?></td>
                            <td><strong style="color: #ffb703;"><?php echo $typeLabel; ?></strong></td>
                            <td style="color: #2ecc71; font-weight: bold;">$<?php echo number_format($inc['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($inc['narration']); ?></td>
                            <td><span style="color: <?php echo $badgeColor; ?>; font-weight: bold;"><?php echo htmlspecialchars($inc['status']); ?></span></td>
                            <td><?php echo $formattedDate; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>