<?php 
require_once 'includes/check_package.php';
include '../includes/header.php'; 

$pack = isset($_GET['pack']) ? (int)$_GET['pack'] : 1;
$wallet_map = [
    1 => 'earnings_11_wallet',
    2 => 'earnings_30_wallet',
    3 => 'earnings_60_wallet',
    4 => 'earnings_120_wallet',
    5 => 'earnings_240_wallet',
    6 => 'earnings_480_wallet'
];
$wallet_type = $wallet_map[$pack] ?? 'earnings_11_wallet';

// Query autopool transactions for this user and package wallet
$stmt = $pdo->prepare("
    SELECT * FROM transactions 
    WHERE user_id = ? AND transaction_type = 'autopool_income' AND wallet_type = ? 
    ORDER BY id DESC
");
$stmt->execute([$user_id, $wallet_type]);
$incomes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="autopoolPack<?php echo $pack; ?>Income" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">Autopool Pack <?php echo $pack; ?> - Autopool Income</div>
        <div class="profile-breadcrumb">
            <a href="index.php">Home</a> &raquo; 
            Autopool Pack <?php echo $pack; ?> &raquo; 
            Autopool Income
        </div>
    </div>
    
    <div class="table-container" style="background: rgba(6, 17, 33, 0.75); border: 1px solid rgba(255, 183, 3, 0.2); border-radius: 12px; padding: 24px; margin-top: 20px;">
        <table class="custom-table" style="width: 100%; text-align: left; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid rgba(255,183,3,0.3); color:#ffb703; height:45px;">
                    <th>#</th>
                    <th>Trigger Member</th>
                    <th>Amount</th>
                    <th>Narration</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($incomes)): ?>
                    <tr style="height: 50px; border-bottom: 1px solid rgba(255,255,255,0.05); color:#a0aec0;">
                        <td colspan="6" style="text-align: center;">No autopool income payouts recorded for this pack yet.</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $idx = 1;
                    foreach ($incomes as $inc): 
                        $formattedDate = date('d M Y H:i', strtotime($inc['created_at']));
                    ?>
                        <tr style="height: 50px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td><?php echo $idx++; ?></td>
                            <td style="font-weight: bold; color: #ffb703;"><?php echo htmlspecialchars($inc['related_user_id'] ?? 'System/Root'); ?></td>
                            <td style="font-weight: 600; color: #2ecc71;">$<?php echo number_format($inc['amount'], 2); ?></td>
                            <td style="color:#cbd5e0;"><?php echo htmlspecialchars($inc['narration']); ?></td>
                            <td>
                                <span class="badge <?php echo $inc['status'] === 'Completed' ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?php echo htmlspecialchars($inc['status']); ?>
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