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
                    <th>Amount</th>
                    <th>Narration</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($incomes)): ?>
                    <tr style="height: 50px; border-bottom: 1px solid rgba(255,255,255,0.05); color:#a0aec0;">
                        <td colspan="5" style="text-align: center;">No autopool income payouts recorded for this pack yet.</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $idx = 1;
                    foreach ($incomes as $inc): 
                        $formattedDate = date('d M Y H:i', strtotime($inc['created_at']));
                        $displayNarration = str_ireplace('Upline L', 'Level ', $inc['narration']);
                        
                        // Parse level number (e.g. Level 1, Level 2)
                        $levelNum = 0;
                        if (preg_match('/Level\s+(\d+)/i', $displayNarration, $matches)) {
                            $levelNum = (int)$matches[1];
                        }
                        
                        // Map levels to bright, vibrant colors
                        $colors = [
                            1 => '#00ffff', // Cyan
                            2 => '#ffb700', // Bright Amber/Orange
                            3 => '#ff33ff', // Bright Magenta/Pink
                            4 => '#33ff33', // Bright Green
                            5 => '#ffff33', // Bright Yellow
                            6 => '#00bfff', // Deep Sky Blue
                            7 => '#cc66ff', // Bright Violet
                            8 => '#ff4500'  // Bright Orange Red
                        ];
                        $levelColor = $colors[$levelNum] ?? '#cbd5e0';
                    ?>
                        <tr style="height: 50px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td><?php echo $idx++; ?></td>
                            <td style="font-weight: 600; color: #2ecc71;">$<?php echo number_format($inc['amount'], 2); ?></td>
                            <td style="font-weight: 500; color: <?php echo $levelColor; ?>;"><?php echo htmlspecialchars($displayNarration); ?></td>
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