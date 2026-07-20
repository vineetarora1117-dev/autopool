<?php
require_once '../libs/db.php';
require_once '../libs/auth.php';
require_once '../libs/config.php';

requireLogin();
$user_id = $_SESSION['user_id'];
$pack = isset($_GET['pack']) ? (int)$_GET['pack'] : 1;

$wallet_map = [
    1 => 'booster_10_wallet',
    2 => 'booster_20_wallet',
    3 => 'booster_40_wallet',
    4 => 'booster_80_wallet',
    5 => 'booster_160_wallet',
    6 => 'booster_320_wallet'
];
$wallet_name_map = [
    1 => '$10 Booster Earnings',
    2 => '$20 Booster Earnings',
    3 => '$40 Booster Earnings',
    4 => '$80 Booster Earnings',
    5 => '$160 Booster Earnings',
    6 => '$320 Booster Earnings'
];

if (!array_key_exists($pack, $wallet_map)) {
    die("Invalid booster wallet selected.");
}

$wallet_key = $wallet_map[$pack];
$wallet_name = $wallet_name_map[$pack];

// Fetch financial summary
$stmt = $pdo->prepare("SELECT * FROM user_financial_summary WHERE user_id = ?");
$stmt->execute([$user_id]);
$summary = $stmt->fetch(PDO::FETCH_ASSOC);

$balance = $summary[$wallet_key] ?? 0.00;

// Fetch the internal transfer fee percentage for this booster wallet from wallet_configurations
$config_wallet_type = str_replace('_wallet', '', $wallet_key);
$stmtFee = $pdo->prepare("SELECT internal_transfer_fee_percent FROM wallet_configurations WHERE wallet_type = ?");
$stmtFee->execute([$config_wallet_type]);
$transfer_fee_percent = floatval($stmtFee->fetchColumn() ?: 5.00);

// Fetch recent 10 transactions related to this booster wallet
$stmtTx = $pdo->prepare("
    SELECT * FROM transactions 
    WHERE user_id = ? AND wallet_type = ? 
    ORDER BY id DESC LIMIT 10
");
$stmtTx->execute([$user_id, $wallet_key]);
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

.table-card {
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
</style>

<div id="infinityPackWalletSection" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">Infinity Pack <?php echo $pack; ?> Wallet Statement</div>
        <div class="profile-breadcrumb">
            <a href="index.php">Home</a> &raquo; 
            Infinity Pack <?php echo $pack; ?> &raquo; 
            Wallet Statement
        </div>
    </div>

    <!-- Wallet Card Grid -->
    <div class="wallet-grid">
        <!-- Booster Wallet Card -->
        <div class="wallet-card main-wallet">
            <div class="wallet-title"><i class="fa-solid fa-wallet"></i> <?php echo $wallet_name; ?> Balance</div>
            <div class="wallet-balance">$<?php echo number_format($balance, 2); ?></div>
            <div class="wallet-actions" style="flex-wrap: wrap;">
                <a href="newWithdrawal.php" class="btn-wallet-action btn-gold-fill"><i class="fa-solid fa-money-bill-transfer"></i> Withdraw</a>
                <button onclick="triggerTransferMain()" class="btn-wallet-action btn-outline" style="border: 1px solid #ffb703; cursor: pointer;"><i class="fa-solid fa-arrow-right-arrow-left"></i> Transfer to Main Wallet</button>
            </div>
        </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="table-card" style="margin-top: 30px;">
        <div class="card-header-title"><i class="fa-solid fa-list"></i> Pack <?php echo $pack; ?> Wallet Transactions</div>
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:14px;">
                <thead>
                    <tr style="border-bottom: 2px solid rgba(255,183,3,0.3); color:#ffb703; height:40px;">
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Narration</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr style="height:50px; border-bottom: 1px solid rgba(255,255,255,0.05); color:#a0aec0;">
                            <td colspan="5" style="text-align:center;">No recent transactions found for this wallet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $tx): 
                            $badgeClass = 'badge-inactive';
                            if ($tx['status'] === 'Completed' || $tx['status'] === 'Released') {
                                $badgeClass = 'badge-active';
                            }
                        ?>
                            <tr style="height:50px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td style="text-transform:capitalize; font-weight:600; color:#ffb703;"><?php echo str_replace('_', ' ', htmlspecialchars($tx['transaction_type'])); ?></td>
                                <td style="font-weight: 600; color: <?php echo ($tx['transaction_type'] === 'internal_transfer') ? '#e74c3c' : '#2ecc71'; ?>;">
                                    <?php echo ($tx['transaction_type'] === 'internal_transfer') ? '-' : '+'; ?>$<?php echo number_format($tx['amount'], 2); ?>
                                </td>
                                <td style="color:#cbd5e0;" title="<?php echo htmlspecialchars($tx['narration']); ?>"><?php echo htmlspecialchars($tx['narration']); ?></td>
                                <td>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo htmlspecialchars($tx['status']); ?>
                                    </span>
                                </td>
                                <td style="color:#a0aec0;"><?php echo date('d M Y H:i', strtotime($tx['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$envConfig = parse_ini_file(__DIR__ . '/../.env');
$siteUrl = rtrim($envConfig['SITE_URL'] ?? 'http://localhost/autopool', '/');
?>
<script>
function triggerTransferMain() {
    const balance = <?php echo floatval($balance); ?>;
    const walletName = '<?php echo htmlspecialchars($wallet_name); ?>';
    const walletKey = '<?php echo htmlspecialchars($wallet_key); ?>';
    const feePercent = <?php echo floatval($transfer_fee_percent); ?>;
    const apiUrl = '<?php echo $siteUrl; ?>/UserPanel/api/transfer.php';

    if (balance <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Empty Wallet',
            text: 'You do not have any funds in this earning wallet to transfer.',
            background: '#1a1a2e',
            color: '#fff'
        });
        return;
    }

    const feeAmount = (balance * feePercent) / 100;
    const netAmount = balance - feeAmount;

    Swal.fire({
        title: 'Confirm Transfer',
        html: `
            <div style="text-align: left; padding: 10px; font-size: 15px;">
                <p>Transfer entire balance of <strong>$${balance.toFixed(2)}</strong> from ${walletName} to your Main Wallet?</p>
                <p style="color: #e74c3c;"><strong>Admin Fee (${feePercent}%):</strong> $${feeAmount.toFixed(2)}</p>
                <p style="color: #2ecc71;"><strong>Net Credit:</strong> $${netAmount.toFixed(2)}</p>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#ffb703',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, transfer now!',
        background: '#1a1a2e',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({title: 'Processing...', allowOutsideClick: false, background: '#1a1a2e', color: '#fff', didOpen: () => { Swal.showLoading(); }});
            
            const formData = new FormData();
            formData.append('source_wallet', walletKey);
            formData.append('amount', balance);

            fetch(apiUrl, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({icon: 'success', title: 'Transfer Complete!', text: data.message, background: '#1a1a2e', color: '#fff'})
                    .then(() => location.reload());
                } else {
                    Swal.fire({icon: 'error', title: 'Transfer Failed', text: data.message, background: '#1a1a2e', color: '#fff'});
                }
            })
            .catch(() => {
                Swal.fire({icon: 'error', title: 'Error', text: 'Connection error.', background: '#1a1a2e', color: '#fff'});
            });
        }
    });
}
</script>
<?php include '../includes/footer.php'; ?>
