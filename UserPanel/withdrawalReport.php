<?php
require_once '../libs/db.php';
require_once '../libs/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'] ?? '';

// Fetch withdrawals with user name
$stmt = $pdo->prepare("
    SELECT wr.*, u.name 
    FROM withdrawal_requests wr 
    JOIN users u ON wr.user_id = u.user_id 
    WHERE wr.user_id = ? 
    ORDER BY wr.id DESC
");
$stmt->execute([$user_id]);
$withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../includes/header.php'; ?>

<div id="withdrawalReportSection" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">Payout Report</div>
        <div class="profile-breadcrumb"><a href="index.php">Home</a> &raquo; Payout Report</div>
    </div>
    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Amount</th>
                    <th>Service Charge</th>
                    <th>Net Amount</th>
                    <th>Payout Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($withdrawals)): ?>
                    <tr><td colspan="8" class="empty-row-msg">No records found</td></tr>
                <?php else: ?>
                    <?php 
                    $index = 1;
                    foreach ($withdrawals as $w): 
                        $statusClass = '';
                        if ($w['status'] === 'Approved') {
                            $statusClass = 'color: #2ecc71; font-weight: bold;';
                        } elseif ($w['status'] === 'Rejected') {
                            $statusClass = 'color: #e74c3c; font-weight: bold;';
                        } else {
                            $statusClass = 'color: #f1c40f; font-weight: bold;';
                        }
                    ?>
                        <tr>
                            <td><?php echo $index++; ?></td>
                            <td><?php echo htmlspecialchars($w['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($w['name']); ?></td>
                            <td>$<?php echo number_format($w['amount'], 2); ?></td>
                            <td>$<?php echo number_format($w['fee_amount'], 2); ?></td>
                            <td style="font-weight: 600; color: #2ecc71;">$<?php echo number_format($w['net_amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($w['created_at']); ?></td>
                            <td style="<?php echo $statusClass; ?>"><?php echo htmlspecialchars($w['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>