<?php 
require_once '../libs/db.php';
// require_once '../libs/admin_auth.php'; // assume it's here

$viewId = isset($_GET['view']) ? (int)$_GET['view'] : 0;
$viewRequest = null;

if ($viewId > 0) {
    $stmt = $pdo->prepare("
        SELECT d.*, u.name, u.email, u.phone 
        FROM deposit_requests d 
        LEFT JOIN users u ON d.user_id = u.user_id 
        WHERE d.id = ?
    ");
    $stmt->execute([$viewId]);
    $viewRequest = $stmt->fetch(PDO::FETCH_ASSOC);
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$stmt = $pdo->query("SELECT COUNT(*) FROM deposit_requests");
$total_rows = $stmt->fetchColumn();
$total_pages = max(1, ceil($total_rows / $limit));

$stmt = $pdo->prepare("SELECT d.*, u.name FROM deposit_requests d LEFT JOIN users u ON d.user_id = u.user_id ORDER BY CASE WHEN d.status = 'Pending' THEN 0 ELSE 1 END ASC, d.id DESC LIMIT ? OFFSET ?");
$stmt->execute([$limit, $offset]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php'; 
?>
<div class="breadcrumb">
    <a href="index.php">Dashboard</a> / <a href="fundRequests.php">Fund Requests</a>
    <?php if ($viewRequest): ?>
        / Review Request #<?php echo $viewRequest['id']; ?>
    <?php endif; ?>
</div>

<?php if ($viewRequest): ?>
<div class="card" style="background: transparent; border: none; box-shadow: none; padding: 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="color: #fff; display: flex; align-items: center; gap: 10px; margin: 0; font-size: 24px;">
            <i class="fa-solid fa-folder-open" style="color: #ffb703;"></i> Deposit Request Review: #<?php echo $viewRequest['id']; ?>
        </h2>
        <a href="fundRequests.php" class="btn btn-gold" style="background: transparent; border: 1px solid #ffb703; color: #ffb703; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-weight: bold; transition: background 0.3s; display: inline-flex; align-items: center; gap: 8px;"><i class="fa-solid fa-arrow-left"></i> Back to List</a>
    </div>

    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 30px;">
        <!-- TRANSACTION DATA DETAILS -->
        <div class="card" style="margin: 0; display: flex; flex-direction: column; gap: 20px;">
            <h4 style="border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; margin: 0; color: #a0aec0; letter-spacing: 1px;">TRANSACTION DATA DETAILS</h4>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label style="color: #718096; font-size: 13px; display: block; margin-bottom: 5px;">Submitting Member</label>
                    <div style="font-weight: bold; color: #fff; font-size: 15px; margin-bottom: 4px;">ID : <?php echo htmlspecialchars($viewRequest['user_id']); ?></div>
                    <div style="font-weight: bold; color: #fff; font-size: 15px;">Name : <?php echo htmlspecialchars($viewRequest['name']); ?></div>
                </div>
                <div>
                    <label style="color: #718096; font-size: 13px; display: block; margin-bottom: 5px;">Email Address</label>
                    <div style="color: #e2e8f0; font-size: 15px;"><?php echo htmlspecialchars($viewRequest['email'] ?? '-'); ?></div>
                    <?php if (!empty($viewRequest['phone'])): ?>
                        <div style="color: #a0aec0; font-size: 13px; margin-top: 4px;">Phone: <?php echo htmlspecialchars($viewRequest['phone']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label style="color: #718096; font-size: 13px; display: block; margin-bottom: 5px;">USDT Amount</label>
                    <div style="color: #2ecc71; font-weight: bold; font-size: 24px;">$<?php echo number_format($viewRequest['amount'], 2); ?></div>
                </div>
                <div>
                    <label style="color: #718096; font-size: 13px; display: block; margin-bottom: 5px;">Payment Method Route</label>
                    <div style="color: #e2e8f0; font-size: 15px; font-weight: bold;">TRC20 (USDT)</div>
                </div>
            </div>

            <div>
                <label style="color: #718096; font-size: 13px; display: block; margin-bottom: 5px;">Submitted Timestamp</label>
                <div style="color: #e2e8f0; font-size: 15px;"><?php echo htmlspecialchars($viewRequest['created_at']); ?></div>
            </div>

            <div>
                <label style="color: #718096; font-size: 13px; display: block; margin-bottom: 5px;">Block Explorer TXID / Transaction Hash</label>
                <div style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); color: #ffb703; padding: 12px; border-radius: 4px; font-family: monospace; font-size: 14px; word-break: break-all;">
                    <?php echo htmlspecialchars($viewRequest['tx_hash']); ?>
                </div>
            </div>

            <div>
                <label style="color: #718096; font-size: 13px; display: block; margin-bottom: 5px;">Current Verification Status</label>
                <?php 
                $statusColor = '';
                $bgStatus = '';
                if ($viewRequest['status'] === 'Approved') {
                    $statusColor = '#ffffff';
                    $bgStatus = '#2e7d32';
                } elseif ($viewRequest['status'] === 'Rejected') {
                    $statusColor = '#ffffff';
                    $bgStatus = '#c62828';
                } else {
                    $statusColor = '#000000';
                    $bgStatus = '#f9a825';
                }
                ?>
                <span style="background: <?php echo $bgStatus; ?>; color: <?php echo $statusColor; ?>; padding: 6px 16px; border-radius: 4px; font-weight: bold; display: inline-block; font-size: 14px; text-transform: uppercase;">
                    <?php echo htmlspecialchars($viewRequest['status']); ?>
                </span>
            </div>

            <?php if ($viewRequest['status'] === 'Pending'): ?>
                <div style="display: flex; gap: 15px; margin-top: 15px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
                    <button class="btn btn-gold" style="flex: 1; padding: 12px; background: #2ecc71; color: #fff; font-weight: bold; font-size: 15px;" onclick="updateStatus(<?php echo $viewRequest['id']; ?>, 'Approved')">Approve Request</button>
                    <button class="btn btn-gold" style="flex: 1; padding: 12px; background: #ff4d4d; color: #fff; font-weight: bold; font-size: 15px;" onclick="updateStatus(<?php echo $viewRequest['id']; ?>, 'Rejected')">Reject Request</button>
                </div>
            <?php endif; ?>
        </div>

        <!-- RECEIPT IMAGE SCREENSHOT -->
        <div class="card" style="margin: 0; display: flex; flex-direction: column; gap: 20px;">
            <h4 style="border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; margin: 0; color: #a0aec0; letter-spacing: 1px;">RECEIPT IMAGE SCREENSHOT</h4>
            
            <div style="background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 15px; display: flex; justify-content: center; align-items: center; min-height: 400px; max-height: 600px; overflow-y: auto;">
                <?php if (!empty($viewRequest['proof_image'])): ?>
                    <img src="../<?php echo htmlspecialchars($viewRequest['proof_image']); ?>" alt="Receipt Proof" style="max-width: 100%; height: auto; border-radius: 4px; box-shadow: 0 4px 10px rgba(0,0,0,0.5);">
                <?php else: ?>
                    <div style="color: #718096; font-style: italic;">No screenshot uploaded</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <h3 class="card-title">Fund Requests</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User ID (Name)</th>
                    <th>Amount</th>
                    <th>TxHash</th>
                    <th>Proof Image</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                <tr>
                    <td colspan="8" style="text-align: center;">No data found</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($requests as $req): ?>
                    <tr>
                        <td>#<?php echo $req['id']; ?></td>
                        <td><?php echo htmlspecialchars($req['user_id'] . ' (' . $req['name'] . ')'); ?></td>
                        <td>$<?php echo number_format($req['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($req['tx_hash']); ?></td>
                        <td>
                            <?php if ($req['proof_image']): ?>
                                <a href="../<?php echo htmlspecialchars($req['proof_image']); ?>" target="_blank" style="color: #ffb703;">View Image</a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $color = $req['status'] === 'Pending' ? 'orange' : ($req['status'] === 'Approved' ? '#2ecc71' : '#ff4d4d');
                            echo "<span style='color: {$color}'>" . ucfirst($req['status']) . "</span>";
                            ?>
                        </td>
                        <td><?php echo date('M d, Y H:i', strtotime($req['created_at'])); ?></td>
                        <td>
                            <div style="display: flex; gap: 5px;">
                                <a href="?view=<?php echo $req['id']; ?>" class="btn btn-gold" style="padding: 4px 8px; font-size: 12px; background: #3498db; text-decoration: none; color: #fff;">View</a>
                                <?php if ($req['status'] === 'Pending'): ?>
                                    <button class="btn btn-gold" style="padding: 4px 8px; font-size: 12px; background: #2ecc71;" onclick="updateStatus(<?php echo $req['id']; ?>, 'Approved')">Approve</button>
                                    <button class="btn btn-gold" style="padding: 4px 8px; font-size: 12px; background: #ff4d4d;" onclick="updateStatus(<?php echo $req['id']; ?>, 'Rejected')">Reject</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination" style="margin-top:15px; display:flex; gap:10px; justify-content:flex-end;">
        <a href="?page=<?php echo max(1, $page - 1); ?>" class="btn btn-gold" <?php if($page <= 1) echo 'style="pointer-events: none; opacity: 0.5;"'; ?>>Prev</a>
        <a href="?page=<?php echo min($total_pages, $page + 1); ?>" class="btn btn-gold" <?php if($page >= $total_pages) echo 'style="pointer-events: none; opacity: 0.5;"'; ?>>Next</a>
    </div>
</div>
<?php endif; ?>

<script>
function updateStatus(id, status) {
    Swal.fire({
        title: 'Confirm ' + status,
        text: 'Are you sure you want to ' + status + ' this request?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: status === 'Approved' ? '#2ecc71' : '#ff4d4d',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes',
        background: '#1a1a2e',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({title: 'Processing...', allowOutsideClick: false, background: '#1a1a2e', color: '#fff', didOpen: () => Swal.showLoading()});
            
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('id', id);
            formData.append('status', status);

            fetch('api/funds.php', {
                method: 'POST',
                body: formData
            }).then(res => res.json()).then(data => {
                if(data.success) {
                    Swal.fire({icon: 'success', title: 'Success', text: data.message || 'Updated successfully', background: '#1a1a2e', color: '#fff'})
                    .then(() => {
                        // If in view detailed mode, redirect to index or list to see updated status
                        const urlParams = new URLSearchParams(window.location.search);
                        if (urlParams.has('view')) {
                            window.location.href = 'fundRequests.php';
                        } else {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire({icon: 'error', title: 'Error', text: data.message || 'Action failed', background: '#1a1a2e', color: '#fff'});
                }
            }).catch(err => {
                Swal.fire({icon: 'error', title: 'Error', text: 'An error occurred', background: '#1a1a2e', color: '#fff'});
            });
        }
    });
}
</script>
<?php require_once 'includes/footer.php'; ?>
