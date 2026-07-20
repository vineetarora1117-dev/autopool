<?php 
require_once '../libs/db.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$filter_user_id = isset($_GET['user_id']) ? trim($_GET['user_id']) : '';
$filter_type = isset($_GET['type']) ? trim($_GET['type']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';

$where_clauses = [];
$params = [];

if ($filter_user_id !== '') {
    $where_clauses[] = "user_id = ?";
    $params[] = $filter_user_id;
}
if ($filter_type !== '') {
    $where_clauses[] = "transaction_type = ?";
    $params[] = $filter_type;
}
if ($filter_status !== '') {
    $where_clauses[] = "status = ?";
    $params[] = $filter_status;
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Count query
$count_query = "SELECT COUNT(*) FROM transactions $where_sql";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_rows = $stmt->fetchColumn();
$total_pages = max(1, ceil($total_rows / $limit));

// Data query
$data_query = "SELECT * FROM transactions $where_sql ORDER BY created_at DESC, id DESC LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($data_query);
$index = 1;
foreach ($params as $val) {
    $stmt->bindValue($index++, $val);
}
$stmt->bindValue($index++, $limit, PDO::PARAM_INT);
$stmt->bindValue($index++, $offset, PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query_params = $_GET;
unset($query_params['page']);
$query_string = http_build_query($query_params);
if ($query_string !== '') {
    $query_string = '&' . $query_string;
}

require_once 'includes/header.php'; 
?>
<div class="breadcrumb">
    <a href="index.php">Dashboard</a> / Transaction Logs
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
        <h3 class="card-title" style="margin: 0;">Transaction Logs</h3>
        <div>
            <form method="GET" action="" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <input type="text" name="user_id" placeholder="User ID" value="<?php echo htmlspecialchars($filter_user_id); ?>" class="form-control" style="padding: 5px; background: #061121; color: #fff; border: 1px solid #ffb703; width: 120px;">
                
                <select name="type" class="form-control" style="padding: 5px; background: #061121; color: #fff; border: 1px solid #ffb703;">
                    <option value="">All Types</option>
                    <option value="deposit" <?php echo $filter_type === 'deposit' ? 'selected' : ''; ?>>Deposit</option>
                    <option value="withdrawal" <?php echo $filter_type === 'withdrawal' ? 'selected' : ''; ?>>Withdrawal</option>
                    <option value="package_purchase" <?php echo $filter_type === 'package_purchase' ? 'selected' : ''; ?>>Package Purchase</option>
                    <option value="booster_purchase" <?php echo $filter_type === 'booster_purchase' ? 'selected' : ''; ?>>Booster Purchase</option>
                    <option value="autopool_income" <?php echo $filter_type === 'autopool_income' ? 'selected' : ''; ?>>Autopool Income</option>
                    <option value="sponsor_income" <?php echo $filter_type === 'sponsor_income' ? 'selected' : ''; ?>>Sponsor Income</option>
                    <option value="level_income" <?php echo $filter_type === 'level_income' ? 'selected' : ''; ?>>Level Income</option>
                    <option value="booster_income" <?php echo $filter_type === 'booster_income' ? 'selected' : ''; ?>>Booster Income</option>
                    <option value="reward_income" <?php echo $filter_type === 'reward_income' ? 'selected' : ''; ?>>Reward Income</option>
                    <option value="sponsor_income_held" <?php echo $filter_type === 'sponsor_income_held' ? 'selected' : ''; ?>>Sponsor Income (Held)</option>
                    <option value="sponsor_income_released" <?php echo $filter_type === 'sponsor_income_released' ? 'selected' : ''; ?>>Sponsor Income (Released)</option>
                    <option value="internal_transfer" <?php echo $filter_type === 'internal_transfer' ? 'selected' : ''; ?>>Internal Transfer</option>
                    <option value="admin_charge" <?php echo $filter_type === 'admin_charge' ? 'selected' : ''; ?>>Admin Charge</option>
                    <option value="company_revenue" <?php echo $filter_type === 'company_revenue' ? 'selected' : ''; ?>>Company Revenue</option>
                    <option value="company_sweep" <?php echo $filter_type === 'company_sweep' ? 'selected' : ''; ?>>Company Sweep</option>
                </select>
                
                <select name="status" class="form-control" style="padding: 5px; background: #061121; color: #fff; border: 1px solid #ffb703;">
                    <option value="">All Statuses</option>
                    <option value="Pending" <?php echo $filter_status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Approved" <?php echo $filter_status === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="Rejected" <?php echo $filter_status === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                    <option value="Held" <?php echo $filter_status === 'Held' ? 'selected' : ''; ?>>Held</option>
                    <option value="Released" <?php echo $filter_status === 'Released' ? 'selected' : ''; ?>>Released</option>
                    <option value="Completed" <?php echo $filter_status === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
                
                <button type="submit" class="btn btn-gold">Filter</button>
                <?php if ($filter_user_id !== '' || $filter_type !== '' || $filter_status !== ''): ?>
                    <a href="transactionLogs.php" class="btn btn-danger" style="text-decoration: none; padding: 8px 16px;">Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Wallet</th>
                    <th>Status</th>
                    <th>Narration</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                <tr>
                    <td colspan="8" style="text-align: center;">No data found</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $tx): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($tx['id']); ?></td>
                        <td><?php echo htmlspecialchars($tx['user_id']); ?></td>
                        <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $tx['transaction_type']))); ?></td>
                        <td>$<?php echo number_format($tx['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $tx['wallet_type'] ?? '-'))); ?></td>
                        <td>
                            <?php 
                            $status = $tx['status'];
                            $color = '#e2e8f0'; // Default
                            if ($status === 'Completed' || $status === 'Approved' || $status === 'Released') {
                                $color = '#2ecc71';
                            } elseif ($status === 'Pending' || $status === 'Held') {
                                $color = '#ffb703';
                            } elseif ($status === 'Rejected') {
                                $color = '#ff4d4d';
                            }
                            echo "<span style='color: {$color}'>" . htmlspecialchars($status) . "</span>";
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($tx['narration']); ?></td>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($tx['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination" style="margin-top:15px; display:flex; gap:10px; justify-content:flex-end;">
        <a href="?page=<?php echo max(1, $page - 1) . $query_string; ?>" class="btn btn-gold" <?php if($page <= 1) echo 'style="pointer-events: none; opacity: 0.5;"'; ?>>Prev</a>
        <a href="?page=<?php echo min($total_pages, $page + 1) . $query_string; ?>" class="btn btn-gold" <?php if($page >= $total_pages || $total_pages <= 1) echo 'style="pointer-events: none; opacity: 0.5;"'; ?>>Next</a>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
