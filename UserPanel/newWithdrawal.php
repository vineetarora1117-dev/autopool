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

// Fetch dynamic withdrawal fee configurations from wallet_configurations
$stmtFees = $pdo->query("SELECT wallet_type, withdrawal_fee_percent FROM wallet_configurations");
$feesList = $stmtFees->fetchAll(PDO::FETCH_KEY_PAIR);

// Fetch recent 10 withdrawal requests
$stmtWr = $pdo->prepare("SELECT * FROM withdrawal_requests WHERE user_id = ? ORDER BY id DESC LIMIT 10");
$stmtWr->execute([$user_id]);
$requests = $stmtWr->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>
<style>
.withdrawal-section-row {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 20px;
    margin-top: 20px;
}
@media (max-width: 900px) {
    .withdrawal-section-row {
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
    font-size: 13px;
    color: #a0aec0;
    margin-top: 8px;
}
.badge-pending {
    background: rgba(241, 196, 15, 0.2);
    color: #f1c40f;
    border: 1px solid #f1c40f;
}
.badge-approved {
    background: rgba(46, 204, 113, 0.2);
    color: #2ecc71;
    border: 1px solid #2ecc71;
}
.badge-rejected {
    background: rgba(231, 76, 60, 0.2);
    color: #e74c3c;
    border: 1px solid #e74c3c;
}
</style>

<div id="newWithdrawalSection" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">USDT Withdrawal Request</div>
        <div class="profile-breadcrumb">
            <a href="index.php">Home</a> &raquo; 
            Withdrawal Request
        </div>
    </div>

    <!-- Info Box or Notice -->
    <div style="background: rgba(255, 183, 3, 0.1); border: 1px solid #ffb703; border-radius: 8px; padding: 15px; margin-top: 20px; font-size: 14px; color:#fff; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-clock" style="color: #ffb703; font-size: 18px;"></i>
        <span><strong>Withdrawal Hours:</strong> Daily from 06:00 AM to 07:00 PM. Verification rules (Directs &gt;= Level * 2) will be applied strictly.</span>
    </div>

    <div class="withdrawal-section-row">
        <!-- Withdrawal Form Card -->
        <div class="form-card">
            <div class="card-header-title"><i class="fa-solid fa-money-bill-transfer"></i> Withdraw Earning</div>
            <form id="withdrawalForm">
                <div class="form-group" style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:8px; font-size:14px; color:#a0aec0;">Select Source Earning Wallet</label>
                    <select name="source_wallet" id="source_wallet" class="form-control" style="width:100%; background:rgba(0,0,0,0.5); border:1px solid rgba(255,183,3,0.3); color:#fff; padding:10px; border-radius:6px; outline:none;" required>
                        <option value="">Select source wallet...</option>
                        <option value="earnings_11_wallet" data-fee="<?php echo $feesList['earnings_11'] ?? 10; ?>">$11 Package Wallet ($<?php echo number_format($summary['earnings_11_wallet'] ?? 0, 2); ?>)</option>
                        <option value="earnings_30_wallet" data-fee="<?php echo $feesList['earnings_30'] ?? 10; ?>">$30 Package Wallet ($<?php echo number_format($summary['earnings_30_wallet'] ?? 0, 2); ?>)</option>
                        <option value="earnings_60_wallet" data-fee="<?php echo $feesList['earnings_60'] ?? 10; ?>">$60 Package Wallet ($<?php echo number_format($summary['earnings_60_wallet'] ?? 0, 2); ?>)</option>
                        <option value="earnings_120_wallet" data-fee="<?php echo $feesList['earnings_120'] ?? 10; ?>">$120 Package Wallet ($<?php echo number_format($summary['earnings_120_wallet'] ?? 0, 2); ?>)</option>
                        <option value="earnings_240_wallet" data-fee="<?php echo $feesList['earnings_240'] ?? 10; ?>">$240 Package Wallet ($<?php echo number_format($summary['earnings_240_wallet'] ?? 0, 2); ?>)</option>
                        <option value="earnings_480_wallet" data-fee="<?php echo $feesList['earnings_480'] ?? 10; ?>">$480 Package Wallet ($<?php echo number_format($summary['earnings_480_wallet'] ?? 0, 2); ?>)</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:8px; font-size:14px; color:#a0aec0;">USDT TRC20 Address</label>
                    <input type="text" name="destination_address" id="destination_address" class="form-control" style="width:100%; background:rgba(0,0,0,0.5); border:1px solid rgba(255,183,3,0.3); color:#fff; padding:10px; border-radius:6px; outline:none;" required placeholder="T...">
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:8px; font-size:14px; color:#a0aec0;">Withdrawal Amount ($)</label>
                    <input type="number" name="amount" id="withdrawal_amount" step="0.01" min="1" class="form-control" style="width:100%; background:rgba(0,0,0,0.5); border:1px solid rgba(255,183,3,0.3); color:#fff; padding:10px; border-radius:6px; outline:none;" required placeholder="0.00">
                    <div class="fee-note" id="feeNote">Select a wallet to see the withdrawal fee.</div>
                </div>
                <button type="submit" class="btn-submit-gold" style="width:100%; padding:12px; font-weight:bold; margin-top:10px;"><i class="fa-solid fa-paper-plane"></i> Submit Request</button>
            </form>
        </div>

        <!-- Withdrawal Requests History Table -->
        <div class="table-card">
            <div class="card-header-title"><i class="fa-solid fa-list-check"></i> Withdrawal History</div>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; text-align:left; font-size:14px;">
                    <thead>
                        <tr style="border-bottom: 2px solid rgba(255,183,3,0.3); color:#ffb703; height:40px;">
                            <th>Wallet</th>
                            <th>Amount</th>
                            <th>Fee</th>
                            <th>Net Amt</th>
                            <th>Destination</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($requests)): ?>
                            <tr style="height:50px; border-bottom: 1px solid rgba(255,255,255,0.05); color:#a0aec0;">
                                <td colspan="7" style="text-align:center;">No withdrawal requests placed yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($requests as $req): 
                                $statusBadge = 'badge-pending';
                                if ($req['status'] === 'Approved') $statusBadge = 'badge-approved';
                                if ($req['status'] === 'Rejected') $statusBadge = 'badge-rejected';
                            ?>
                                <tr style="height:50px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <td style="color:#a0aec0; text-transform:capitalize;"><?php echo str_replace('_', ' ', htmlspecialchars(str_replace('earnings_', '', str_replace('_wallet', '', $req['wallet_type'])))); ?></td>
                                    <td>$<?php echo number_format($req['amount'], 2); ?></td>
                                    <td>$<?php echo number_format($req['fee_amount'], 2); ?></td>
                                    <td style="font-weight:600; color:#2ecc71;">$<?php echo number_format($req['net_amount'], 2); ?></td>
                                    <td style="color:#cbd5e0; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($req['destination_address']); ?>"><?php echo htmlspecialchars($req['destination_address']); ?></td>
                                    <td><span class="badge <?php echo $statusBadge; ?>"><?php echo htmlspecialchars($req['status']); ?></span></td>
                                    <td style="color:#a0aec0;"><?php echo date('d M Y H:i', strtotime($req['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$envConfig = parse_ini_file(__DIR__ . '/../.env');
$siteUrl = rtrim($envConfig['SITE_URL'] ?? 'http://localhost/autopool', '/');
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('source_wallet');
    const amountInput = document.getElementById('withdrawal_amount');
    const feeNote = document.getElementById('feeNote');

    function updateFee() {
        const option = select.options[select.selectedIndex];
        const feePercent = option.getAttribute('data-fee');
        const amount = parseFloat(amountInput.value) || 0;

        if (feePercent !== null) {
            const fee = (amount * parseFloat(feePercent)) / 100;
            const net = amount - fee;
            feeNote.innerHTML = `<span style="color:#e74c3c">Withdrawal Fee: ${feePercent}% ($${fee.toFixed(2)})</span>. Net payout to USDT address: <strong>$${net.toFixed(2)}</strong>`;
        } else {
            feeNote.innerHTML = 'Select a wallet to see the withdrawal fee.';
        }
    }

    select.addEventListener('change', updateFee);
    amountInput.addEventListener('input', updateFee);

    document.getElementById('withdrawalForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const option = select.options[select.selectedIndex];
        const walletName = option.text.split(' (')[0];
        const amount = parseFloat(amountInput.value);
        const address = document.getElementById('destination_address').value;

        Swal.fire({
            title: 'Confirm Withdrawal',
            html: `
                <p>Are you sure you want to withdraw <strong>$${amount}</strong> from ${walletName}?</p>
                <p style="font-size: 13px; color: #ffb703; word-break: break-all;"><strong>USDT TRC20 Address:</strong><br>${address}</p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffb703',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, submit request!',
            background: '#1a1a2e',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({title: 'Submitting request...', allowOutsideClick: false, background: '#1a1a2e', color: '#fff', didOpen: () => { Swal.showLoading(); }});
                
                const formData = new FormData(this);
                fetch('<?php echo $siteUrl; ?>/UserPanel/api/withdraw.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({icon: 'success', title: 'Request Placed!', text: data.message, background: '#1a1a2e', color: '#fff'})
                        .then(() => location.reload());
                    } else {
                        Swal.fire({icon: 'error', title: 'Withdrawal Failed', text: data.message || 'Transaction error occurred.', background: '#1a1a2e', color: '#fff'});
                    }
                })
                .catch(() => {
                    Swal.fire({icon: 'error', title: 'Error', text: 'Connection error.', background: '#1a1a2e', color: '#fff'});
                });
            }
        });
    });
});
</script>
<?php include '../includes/footer.php'; ?>