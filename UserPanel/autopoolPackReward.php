<?php 
require_once 'includes/check_package.php';
include '../includes/header.php'; 

$pack = isset($_GET['pack']) ? (int)$_GET['pack'] : 1;

if ($pack !== 1) {
    echo "<div style='padding: 24px; color: #e74c3c; font-weight: bold;'>Reward Income is only applicable for Autopool Pack 1 (Starter).</div>";
    include '../includes/footer.php';
    exit;
}

// Query reward transactions for this user
$stmt = $pdo->prepare("
    SELECT * FROM transactions 
    WHERE user_id = ? AND transaction_type = 'reward_income' 
    ORDER BY id DESC
");
$stmt->execute([$user_id]);
$rewards = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="autopoolPack<?php echo $pack; ?>Reward" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">Autopool Pack <?php echo $pack; ?> - Reward Income</div>
        <div class="profile-breadcrumb">
            <a href="index.php">Home</a> &raquo; 
            Autopool Pack <?php echo $pack; ?> &raquo; 
            Reward Income
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