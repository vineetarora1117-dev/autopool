<?php 
require_once '../libs/db.php';
// require_once '../libs/admin_auth.php';
// requireAdminLogin();

// Fetch company ledger
$stmt = $pdo->query("SELECT * FROM company_ledger LIMIT 1");
$ledger = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
    'total_funds_received' => 0, 'unutilized_funds' => 0, 'invested_funds' => 0, 'total_usdt_paid_out' => 0,
    'company_wallet_balance' => 0, 'total_payout_liability_main' => 0, 'total_payout_liability_booster' => 0, 'total_held_sponsor_income' => 0
];

// Fetch recent deposit requests
$stmt = $pdo->query("SELECT d.*, u.name FROM deposit_requests d LEFT JOIN users u ON d.user_id = u.user_id ORDER BY d.id DESC LIMIT 10");
$deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent transactions
$stmt = $pdo->query("SELECT * FROM transactions ORDER BY id DESC LIMIT 10");
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php'; 
?>

<div class="breadcrumb">
    <a href="index.php">Dashboard</a> / Overview
</div>

<div class="grid-4">
    <div class="metric-card">
        <div>Total Funds Received</div>
        <div class="metric-value">$<?php echo number_format($ledger['total_funds_received'], 2); ?></div>
    </div>
    <div class="metric-card">
        <div>Unutilized Funds</div>
        <div class="metric-value">$<?php echo number_format($ledger['unutilized_funds'], 2); ?></div>
    </div>
    <div class="metric-card">
        <div>Invested Funds</div>
        <div class="metric-value">$<?php echo number_format($ledger['invested_funds'], 2); ?></div>
    </div>
    <div class="metric-card">
        <div>Total USDT Paid Out</div>
        <div class="metric-value">$<?php echo number_format($ledger['total_usdt_paid_out'], 2); ?></div>
    </div>
</div>

<div class="grid-4" style="margin-top: 20px;">
    <div class="metric-card">
        <div>Company Wallet Balance</div>
        <div class="metric-value">$<?php echo number_format($ledger['company_wallet_balance'], 2); ?></div>
    </div>
    <div class="metric-card">
        <div>Payout Liability (Main)</div>
        <div class="metric-value">$<?php echo number_format($ledger['total_payout_liability_main'], 2); ?></div>
    </div>
    <div class="metric-card">
        <div>Payout Liability (Booster)</div>
        <div class="metric-value">$<?php echo number_format($ledger['total_payout_liability_booster'], 2); ?></div>
    </div>
    <div class="metric-card">
        <div>Held Sponsor Income</div>
        <div class="metric-value">$<?php echo number_format($ledger['total_held_sponsor_income'], 2); ?></div>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <h3 class="card-title">Recent Deposit Requests</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($deposits)): ?>
                    <tr><td colspan="5" style="text-align: center;">No data found</td></tr>
                <?php else: ?>
                    <?php foreach ($deposits as $d): ?>
                    <tr>
                        <td>#<?php echo $d['id']; ?></td>
                        <td><?php echo htmlspecialchars($d['user_id']); ?></td>
                        <td>$<?php echo number_format($d['amount'], 2); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($d['created_at'])); ?></td>
                        <td>
                            <?php 
                            $color = $d['status'] === 'pending' ? 'orange' : ($d['status'] === 'approved' ? '#2ecc71' : '#ff4d4d');
                            echo "<span style='color: {$color}'>" . ucfirst($d['status']) . "</span>";
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h3 class="card-title">Recent Transactions</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Amount</th>
                    <th>Type</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr><td colspan="5" style="text-align: center;">No data found</td></tr>
                <?php else: ?>
                    <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td>#<?php echo $t['id']; ?></td>
                        <td><?php echo htmlspecialchars($t['user_id']); ?></td>
                        <td>$<?php echo number_format($t['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($t['transaction_type'])); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($t['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
