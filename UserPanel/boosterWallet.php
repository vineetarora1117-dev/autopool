<?php
require_once '../libs/db.php';
require_once '../libs/auth.php';
require_once '../libs/config.php';

requireLogin();
$user_id = $_SESSION['user_id'];

// Fetch booster wallet balance
$stmtSummary = $pdo->prepare("SELECT booster_wallet, total_booster_income FROM user_financial_summary WHERE user_id = ?");
$stmtSummary->execute([$user_id]);
$summary = $stmtSummary->fetch(PDO::FETCH_ASSOC);

$boosterWallet = floatval($summary['booster_wallet'] ?? 0);

// Fetch booster transactions (Both Purchases and Income/Payouts)
$stmtLedger = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? AND (wallet_type = 'booster_wallet' OR transaction_type IN ('booster_purchase', 'booster_income')) ORDER BY id DESC LIMIT 50");
$stmtLedger->execute([$user_id]);
$ledger = $stmtLedger->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="content-section active-view" style="padding: 20px; max-width: 1100px; margin: 0 auto;">
    <div class="profile-header-bar">
        <div class="profile-header-title">
            <i class="fa-solid fa-wallet"></i> Growth Engine Wallet & Activity
        </div>
        <div class="profile-breadcrumb">
            <a href="index.php">Home</a> &raquo; Growth Engine &raquo; Growth Engine Wallet
        </div>
    </div>

    <!-- Wallet Summary & Actions Grid -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 25px;">
        <div class="db-gold-card">
            <div class="db-card-label">Available Growth Engine Wallet Balance</div>
            <div class="db-card-value" style="font-size: 32px;">$<?php echo number_format($boosterWallet, 2); ?></div>
            <div class="db-card-watermark"><i class="fa-solid fa-bolt fa-2x"></i></div>
        </div>

        <a href="newWithdrawal" style="text-decoration: none;">
            <div class="db-action-btn" style="height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; gap: 10px;">
                <i class="fa-solid fa-money-bill-transfer fa-2x"></i>
                <span>Withdraw USDT</span>
            </div>
        </a>
    </div>

    <!-- Complete Growth Engine Activity Log (Purchases & Income Ledger) -->
    <div class="table-container" style="background: rgba(6, 17, 33, 0.75); border: 1px solid #ffb703; border-radius: 12px; padding: 25px;">
        <h3 style="color: #ffb703; margin-bottom: 15px; font-size: 18px; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-receipt"></i> Growth Engine Activity Ledger (Purchases & Income)
        </h3>

        <div style="overflow-x: auto;">
            <table class="custom-table" style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="background: rgba(255, 183, 3, 0.15); color: #ffb703; height: 42px;">
                        <th>Tx ID</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Wallet Used</th>
                        <th>Narration</th>
                        <th>Status</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ledger)): ?>
                        <tr>
                            <td colspan="7" class="empty-row-msg">No booster purchases or income transactions recorded yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ledger as $tx): ?>
                            <?php 
                            $type = $tx['transaction_type'];
                            $walletType = $tx['wallet_type'] ?? '-';
                            $amount = floatval($tx['amount']);

                            // Format type and amount badges
                            if ($type === 'booster_income') {
                                $typeLabel = '<span style="color: #2ecc71; font-weight: bold;"><i class="fa-solid fa-arrow-down"></i> Income</span>';
                                $amountDisplay = '<span style="color: #2ecc71; font-weight: bold;">+$' . number_format($amount, 2) . '</span>';
                            } elseif ($type === 'booster_purchase') {
                                if ($walletType === 'auto_reentry') {
                                    $typeLabel = '<span style="color: #9b59b6; font-weight: bold;"><i class="fa-solid fa-arrows-rotate"></i> Auto Re-entry</span>';
                                    $amountDisplay = '<span style="color: #9b59b6; font-weight: bold;">$' . number_format($amount, 2) . '</span>';
                                } else {
                                    $typeLabel = '<span style="color: #e74c3c; font-weight: bold;"><i class="fa-solid fa-cart-shopping"></i> Purchase</span>';
                                    $amountDisplay = '<span style="color: #e74c3c; font-weight: bold;">-$' . number_format($amount, 2) . '</span>';
                                }
                            } else {
                                $typeLabel = '<span style="color: #ffb703; font-weight: bold;">' . htmlspecialchars(ucwords(str_replace('_', ' ', $type))) . '</span>';
                                $amountDisplay = '<span style="color: #ffb703; font-weight: bold;">$' . number_format($amount, 2) . '</span>';
                            }
                            ?>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); height: 48px;">
                                <td>#<?php echo htmlspecialchars($tx['id']); ?></td>
                                <td><?php echo $typeLabel; ?></td>
                                <td><?php echo $amountDisplay; ?></td>
                                <td><span style="color: #a0aec0; text-transform: capitalize;"><?php echo htmlspecialchars(str_replace('_', ' ', $walletType)); ?></span></td>
                                <td><?php echo htmlspecialchars($tx['narration']); ?></td>
                                <td><span style="color: #2ecc71; font-weight: bold;"><?php echo htmlspecialchars($tx['status']); ?></span></td>
                                <td style="color: #a0aec0; font-size: 13px;"><?php echo date('d M Y, h:i A', strtotime($tx['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
