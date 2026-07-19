<?php 
require_once '../libs/db.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$total_rows = $stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

$stmt = $pdo->prepare("SELECT u.*, f.my_package, f.direct_team_count FROM users u LEFT JOIN user_financial_summary f ON u.user_id = f.user_id ORDER BY u.created_at DESC LIMIT ? OFFSET ?");
$stmt->execute([$limit, $offset]);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php'; 
?>
<div class="breadcrumb">
    <a href="index.php">Dashboard</a> / All Members
</div>

<div class="card">
    <h3 class="card-title">All Members</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Package</th>
                    <th>Direct Team</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($members)): ?>
                <tr>
                    <td colspan="9" style="text-align: center;">No data found</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($members as $m): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($m['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($m['name']); ?></td>
                        <td><?php echo htmlspecialchars($m['email']); ?></td>
                        <td><?php echo htmlspecialchars($m['phone']); ?></td>
                        <td>
                            <?php 
                            $color = $m['status'] === 'active' ? '#2ecc71' : ($m['status'] === 'blocked' ? '#ff4d4d' : 'orange');
                            echo "<span style='color: {$color}'>" . ucfirst($m['status']) . "</span>";
                            ?>
                        </td>
                        <td>$<?php echo number_format($m['my_package'] ?? 0, 2); ?></td>
                        <td><?php echo number_format($m['direct_team_count'] ?? 0); ?></td>
                        <td><?php echo date('M d, Y', strtotime($m['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-gold" style="padding: 4px 8px; font-size: 12px;" onclick="impersonate('<?php echo $m['user_id']; ?>')">Login</button>
                            <?php if ($m['status'] !== 'blocked'): ?>
                                <button class="btn btn-gold" style="padding: 4px 8px; font-size: 12px; background: #ff4d4d;" onclick="blockUser('<?php echo $m['user_id']; ?>')">Block</button>
                            <?php else: ?>
                                <button class="btn btn-gold" style="padding: 4px 8px; font-size: 12px; background: #2ecc71;" onclick="unblockUser('<?php echo $m['user_id']; ?>')">Unblock</button>
                            <?php endif; ?>
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

<script>
function impersonate(userId) {
    const formData = new FormData();
    formData.append('action', 'impersonate');
    formData.append('user_id', userId);

    fetch('api/users.php', {
        method: 'POST',
        body: formData
    }).then(res => res.json()).then(data => {
        if (data.success) {
            window.open('../UserPanel/index.php', '_blank');
        } else {
            Swal.fire({icon: 'error', title: 'Error', text: data.message || 'Failed to login as user', background: '#1a1a2e', color: '#fff'});
        }
    }).catch(err => {
        Swal.fire({icon: 'error', title: 'Error', text: 'An error occurred', background: '#1a1a2e', color: '#fff'});
    });
}

function blockUser(userId) {
    Swal.fire({
        title: 'Block User?',
        text: 'Are you sure you want to block user ' + userId + '?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff4d4d',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, block!',
        background: '#1a1a2e',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'block');
            formData.append('user_id', userId);

            fetch('api/users.php', {
                method: 'POST',
                body: formData
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    Swal.fire({icon: 'success', title: 'Blocked!', text: 'User has been blocked.', background: '#1a1a2e', color: '#fff'})
                    .then(() => location.reload());
                } else {
                    Swal.fire({icon: 'error', title: 'Error', text: data.message || 'Action failed', background: '#1a1a2e', color: '#fff'});
                }
            });
        }
    });
}

function unblockUser(userId) {
    Swal.fire({
        title: 'Unblock User?',
        text: 'Are you sure you want to unblock user ' + userId + '?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2ecc71',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, unblock!',
        background: '#1a1a2e',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'unblock');
            formData.append('user_id', userId);

            fetch('api/users.php', {
                method: 'POST',
                body: formData
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    Swal.fire({icon: 'success', title: 'Unblocked!', text: 'User has been unblocked.', background: '#1a1a2e', color: '#fff'})
                    .then(() => location.reload());
                } else {
                    Swal.fire({icon: 'error', title: 'Error', text: data.message || 'Action failed', background: '#1a1a2e', color: '#fff'});
                }
            });
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
