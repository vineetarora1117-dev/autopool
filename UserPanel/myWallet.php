<?php
require_once '../libs/db.php';
require_once '../libs/auth.php';
require_once '../libs/config.php';

requireLogin();
$user_id = $_SESSION['user_id'];

// Fetch financial summary
$stmt = $pdo->prepare("SELECT * FROM user_financial_summary WHERE user_id = ?");
$stmt->execute([$user_id]);
$summary = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch dynamic fee configurations from wallet_configurations
$stmtFees = $pdo->query("SELECT wallet_type, internal_transfer_fee_percent FROM wallet_configurations");
$feesList = $stmtFees->fetchAll(PDO::FETCH_KEY_PAIR);

// Fetch recent 10 completed transactions related specifically to the Main Deposit Wallet
$stmtTx = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? AND wallet_type = 'main_deposit' AND status = 'Completed' ORDER BY id DESC LIMIT 10");
$stmtTx->execute([$user_id]);
$transactions = $stmtTx->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>
<style>
.wallet-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 20px;
}
.wallet-card {
    background: rgba(6, 17, 33, 0.75);
    border: 1px solid rgba(255, 183, 3, 0.3);
    border-radius: 12px;
    padding: 20px;
    position: relative;
    overflow: hidden;
    transition: 0.3s;
}
.wallet-card:hover {
    border-color: #ffb703;
    transform: translateY(-2px);
}
.wallet-card.main-wallet {
    border-color: #ffb703;
    background: linear-gradient(135deg, rgba(6, 17, 33, 0.9), rgba(191, 161, 0, 0.15));
}
.wallet-title {
    font-size: 14px;
    color: #a0aec0;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.wallet-balance {
    font-size: 26px;
    font-weight: bold;
    color: #fff;
    margin-bottom: 15px;
}
.wallet-card.main-wallet .wallet-balance {
    color: #ffb703;
}
.wallet-actions {
    display: flex;
    gap: 10px;
}
.btn-wallet-action {
    flex: 1;
    padding: 8px 12px;
    font-size: 13px;
    font-weight: bold;
    border-radius: 6px;
    text-align: center;
    text-decoration: none;
    cursor: pointer;
    transition: 0.2s;
}
.btn-gold-fill {
    background: #ffb703;
    color: #000;
    border: none;
}
.btn-gold-fill:hover {
    background: #bfa100;
}
.btn-outline {
    background: transparent;
    color: #ffb703;
    border: 1px solid #ffb703;
}
.btn-outline:hover {
    background: rgba(255, 183, 3, 0.1);
}

.wallet-section-row {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 20px;
    margin-top: 30px;
}
@media (max-width: 900px) {
    .wallet-section-row {
        grid-template-columns: 1fr;
    }
}
.form-card, .table-card {
    background: rgba(6, 17, 33, 0.75);
    border: 1px solid rgba(255, 183, 3, 0.2);
    border-radius: 12px;
    padding: 24px;
}
.card-header-title {
    font-size: 18px;
    font-weight: bold;
    color: #ffb703;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.fee-note {
    font-size: 12px;
    color: #a0aec0;
    margin-top: 5px;
}
</style>

<div id="myWalletSection" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">My Wallet Dashboard</div>
        <div class="profile-breadcrumb"><a href="index.php">Home</a> &raquo; My Wallet</div>
    </div>

    <!-- Wallet Cards Grid -->
    <div class="wallet-grid">
        <!-- Main Wallet -->
        <div class="wallet-card main-wallet">
            <div class="wallet-title"><i class="fa-solid fa-vault"></i> Main Deposit Wallet</div>
            <div class="wallet-balance">$<?php echo number_format($summary['main_deposit_balance'] ?? 0.00, 2); ?></div>
            <div class="wallet-actions" style="flex-wrap: wrap;">
                <a href="deposit.php" class="btn-wallet-action btn-gold-fill"><i class="fa-solid fa-plus"></i> Deposit</a>
                <a href="newWithdrawal.php" class="btn-wallet-action btn-outline"><i class="fa-solid fa-money-bill-transfer"></i> Withdraw</a>
                <a href="autopoolPackage.php" class="btn-wallet-action btn-outline"><i class="fa-solid fa-cart-shopping"></i> Buy Package</a>
                <a href="internalTransfer.php" class="btn-wallet-action btn-outline" style="flex-basis: 100%; margin-top: 8px; font-weight: bold;"><i class="fa-solid fa-rotate"></i> Transfer Funds to this Wallet</a>
            </div>
        </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="table-card" style="margin-top: 30px;">
        <div class="card-header-title"><i class="fa-solid fa-list"></i> Recent Wallet Transactions</div>
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:14px;">
                <thead>
                    <tr style="border-bottom: 2px solid rgba(255,183,3,0.3); color:#ffb703; height:40px;">
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Wallet</th>
                        <th>Narration</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr style="height:50px; border-bottom: 1px solid rgba(255,255,255,0.05); color:#a0aec0;">
                            <td colspan="5" style="text-align:center;">No recent transactions found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $tx): ?>
                            <tr style="height:50px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td style="text-transform:capitalize; font-weight:600; color:#ffb703;"><?php echo str_replace('_', ' ', htmlspecialchars($tx['transaction_type'])); ?></td>
                                <td>$<?php echo number_format($tx['amount'], 2); ?></td>
                                <td style="color:#a0aec0;"><?php echo htmlspecialchars($tx['wallet_type'] ?? 'Company'); ?></td>
                                <td style="color:#cbd5e0; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($tx['narration']); ?>"><?php echo htmlspecialchars($tx['narration']); ?></td>
                                <td style="color:#a0aec0;"><?php echo date('d M Y H:i', strtotime($tx['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
