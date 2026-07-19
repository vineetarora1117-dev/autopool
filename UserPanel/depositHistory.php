<?php
require_once '../libs/db.php';
require_once '../libs/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'] ?? '';

// Fetch deposit requests for the logged-in user
$stmt = $pdo->prepare("SELECT * FROM deposit_requests WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../includes/header.php'; ?>

<div id="depositHistorySection" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">Deposit History</div>
        <div class="profile-breadcrumb"><a href="index.php">Home</a> &raquo; Deposit History</div>
    </div>
    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User ID</th>
                    <th>Amount</th>
                    <th>Tx Hash</th>
                    <th>Proof Image</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($deposits)): ?>
                    <tr><td colspan="8" class="empty-row-msg">No records found</td></tr>
                <?php else: ?>
                    <?php 
                    $index = 1;
                    foreach ($deposits as $dep): 
                        $statusClass = '';
                        if ($dep['status'] === 'Approved') {
                            $statusClass = 'color: #2ecc71; font-weight: bold;';
                        } elseif ($dep['status'] === 'Rejected') {
                            $statusClass = 'color: #e74c3c; font-weight: bold;';
                        } else {
                            $statusClass = 'color: #f1c40f; font-weight: bold;';
                        }
                    ?>
                        <tr>
                            <td><?php echo $index++; ?></td>
                            <td><?php echo htmlspecialchars($dep['user_id']); ?></td>
                            <td>$<?php echo number_format($dep['amount'], 2); ?></td>
                            <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?php echo htmlspecialchars($dep['tx_hash']); ?>
                            </td>
                            <td>
                                <?php if (!empty($dep['proof_image'])): ?>
                                    <a href="<?php echo htmlspecialchars('../' . $dep['proof_image']); ?>" target="_blank" style="color: #ffb703; text-decoration: underline;">View Proof</a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td style="<?php echo $statusClass; ?>"><?php echo htmlspecialchars($dep['status']); ?></td>
                            <td><?php echo htmlspecialchars($dep['admin_remarks'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($dep['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
