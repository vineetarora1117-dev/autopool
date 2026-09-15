<?php
require_once '../libs/db.php';
require_once '../libs/admin_auth.php';
requireAdminLogin();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$stmt = $pdo->query("SELECT COUNT(*) FROM withdrawal_requests");
$total_rows = $stmt->fetchColumn();
$total_pages = max(1, ceil($total_rows / $limit));

$stmt = $pdo->prepare("
    SELECT w.*, u.name 
    FROM withdrawal_requests w 
    LEFT JOIN users u ON w.user_id = u.user_id 
    ORDER BY CASE WHEN w.status = 'Pending' THEN 0 ELSE 1 END ASC, w.id DESC 
    LIMIT ? OFFSET ?
");
$stmt->execute([$limit, $offset]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>
<div class="breadcrumb">
    <a href="index.php">Dashboard</a> / Withdrawal Requests
</div>

<div class="card">
    <h3 class="card-title">Withdrawal Requests</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Amount</th>
                    <th>Wallet Type</th>
                    <th>Fee</th>
                    <th>Net Amount</th>
                    <th>Destination (TRC20)</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($requests) > 0): ?>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($req['id']); ?></td>
                            <td><strong><?php echo htmlspecialchars($req['user_id']); ?></strong></td>
                            <td><?php echo htmlspecialchars($req['name'] ?? '-'); ?></td>
                            <td>$<?php echo number_format($req['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars(str_replace('_', ' ', $req['wallet_type'])); ?></td>
                            <td>$<?php echo number_format($req['fee_amount'], 2); ?></td>
                            <td style="color:#2ecc71; font-weight:bold;">$<?php echo number_format($req['net_amount'], 2); ?></td>
                            <td><code style="background:#0a192f; padding:4px 8px; border-radius:4px; font-size:12px; color:#ffb703;"><?php echo htmlspecialchars($req['destination_address']); ?></code></td>
                            <td>
                                <?php if ($req['status'] === 'Pending'): ?>
                                    <span class="badge badge-warning" style="background:#f39c12; color:#fff; padding:4px 8px; border-radius:4px; font-size:12px;">Pending</span>
                                <?php elseif ($req['status'] === 'Approved'): ?>
                                    <span class="badge badge-success" style="background:#2ecc71; color:#fff; padding:4px 8px; border-radius:4px; font-size:12px;">Approved</span>
                                <?php else: ?>
                                    <span class="badge badge-danger" style="background:#e74c3c; color:#fff; padding:4px 8px; border-radius:4px; font-size:12px;">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($req['created_at']); ?></td>
                            <td>
                                <?php if ($req['status'] === 'Pending'): ?>
                                    <button class="btn btn-gold" style="padding: 4px 8px; font-size: 12px; margin-right: 5px;" onclick="confirmWithdrawal(<?php echo $req['id']; ?>, 'approved')">Approve</button>
                                    <button class="btn btn-reset-pink" style="padding: 4px 8px; font-size: 12px; background:#e74c3c; color:#fff; border:none;" onclick="confirmWithdrawal(<?php echo $req['id']; ?>, 'rejected')">Reject</button>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" style="text-align: center;">No withdrawal requests found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="pagination" style="margin-top:15px; display:flex; gap:10px; justify-content:flex-end;">
        <button class="btn btn-gold" onclick="window.location.href='?page=<?php echo max(1, $page - 1); ?>'" <?php echo ($page <= 1) ? 'disabled' : ''; ?>>Prev</button>
        <button class="btn btn-gold" onclick="window.location.href='?page=<?php echo min($total_pages, $page + 1); ?>'" <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>>Next</button>
    </div>
</div>

<script>
function confirmWithdrawal(id, status) {
    const actionText = status === 'approved' ? 'Approve' : 'Reject';
    Swal.fire({
        title: actionText + ' Request?',
        text: `Are you sure you want to ${status} this withdrawal request?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffb703',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, ' + actionText,
        background: '#1a1a2e',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            processWithdrawal(id, status);
        }
    });
}

function processWithdrawal(id, status) {
    Swal.fire({
        title: 'Processing...',
        allowOutsideClick: false,
        background: '#1a1a2e',
        color: '#fff',
        didOpen: () => { Swal.showLoading(); }
    });

    fetch('api/withdrawals.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=update_status&id=${id}&status=${status}`
    })
    .then(res => res.json())
    .then(data => {
        Swal.close();
        if(data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: data.message,
                background: '#1a1a2e',
                color: '#fff',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                background: '#1a1a2e',
                color: '#fff',
                confirmButtonColor: '#ffb703'
            });
        }
    })
    .catch(() => {
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Connection error while processing.',
            background: '#1a1a2e',
            color: '#fff'
        });
    });
}
</script>
<?php require_once 'includes/footer.php'; ?>
