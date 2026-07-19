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

include '../includes/header.php';
?>
<style>
.transfer-container {
    max-width: 600px;
    margin: 40px auto;
    background: rgba(6, 17, 33, 0.75);
    border: 1px solid rgba(255, 183, 3, 0.3);
    border-radius: 12px;
    padding: 30px;
}
.card-header-title {
    font-size: 20px;
    font-weight: bold;
    color: #ffb703;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid rgba(255, 183, 3, 0.2);
    padding-bottom: 15px;
}
.fee-note {
    font-size: 13px;
    color: #a0aec0;
    margin-top: 8px;
}
</style>

<div id="internalTransferSection" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">Transfer Funds</div>
        <div class="profile-breadcrumb">
            <a href="index.php">Home</a> &raquo; 
            <a href="myWallet.php">My Wallet</a> &raquo; 
            Transfer Funds
        </div>
    </div>

    <div class="transfer-container">
        <div class="card-header-title">
            <i class="fa-solid fa-arrow-right-arrow-left"></i> Transfer Earning Wallet to Main Wallet
        </div>
        
        <form id="transferForm">
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display:block; margin-bottom:8px; font-size:14px; color:#a0aec0; font-weight: 500;">Select Source Earning Wallet</label>
                <select name="source_wallet" id="source_wallet" class="form-control" style="width:100%; background:rgba(0,0,0,0.5); border:1px solid rgba(255,183,3,0.3); color:#fff; padding:12px; border-radius:6px; outline:none; font-size: 14px;" required>
                    <option value="">Choose wallet...</option>
                    <option value="earnings_11_wallet" data-fee="<?php echo $feesList['earnings_11'] ?? 5; ?>">$11 Package Wallet ($<?php echo number_format($summary['earnings_11_wallet'] ?? 0, 2); ?>)</option>
                    <option value="earnings_30_wallet" data-fee="<?php echo $feesList['earnings_30'] ?? 5; ?>">$30 Package Wallet ($<?php echo number_format($summary['earnings_30_wallet'] ?? 0, 2); ?>)</option>
                    <option value="earnings_60_wallet" data-fee="<?php echo $feesList['earnings_60'] ?? 5; ?>">$60 Package Wallet ($<?php echo number_format($summary['earnings_60_wallet'] ?? 0, 2); ?>)</option>
                    <option value="earnings_120_wallet" data-fee="<?php echo $feesList['earnings_120'] ?? 5; ?>">$120 Package Wallet ($<?php echo number_format($summary['earnings_120_wallet'] ?? 0, 2); ?>)</option>
                    <option value="earnings_240_wallet" data-fee="<?php echo $feesList['earnings_240'] ?? 5; ?>">$240 Package Wallet ($<?php echo number_format($summary['earnings_240_wallet'] ?? 0, 2); ?>)</option>
                    <option value="earnings_480_wallet" data-fee="<?php echo $feesList['earnings_480'] ?? 5; ?>">$480 Package Wallet ($<?php echo number_format($summary['earnings_480_wallet'] ?? 0, 2); ?>)</option>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 25px;">
                <label style="display:block; margin-bottom:8px; font-size:14px; color:#a0aec0; font-weight: 500;">Transfer Amount ($)</label>
                <input type="number" name="amount" id="transfer_amount" step="0.01" min="1" class="form-control" style="width:100%; background:rgba(0,0,0,0.5); border:1px solid rgba(255,183,3,0.3); color:#fff; padding:12px; border-radius:6px; outline:none; font-size: 14px;" required placeholder="0.00">
                <div class="fee-note" id="feeNote">Select a wallet to view the admin fee.</div>
            </div>
            
            <button type="submit" class="btn-submit-gold" style="width:100%; padding:14px; font-size: 15px; font-weight:bold; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i class="fa-solid fa-rotate"></i> Process Transfer
            </button>
        </form>
    </div>
</div>

<?php
$envConfig = parse_ini_file(__DIR__ . '/../.env');
$siteUrl = rtrim($envConfig['SITE_URL'] ?? 'http://localhost/autopool', '/');
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('source_wallet');
    const amountInput = document.getElementById('transfer_amount');
    const feeNote = document.getElementById('feeNote');

    function updateFee() {
        const option = select.options[select.selectedIndex];
        const feePercent = option.getAttribute('data-fee');
        const amount = parseFloat(amountInput.value) || 0;

        if (feePercent !== null) {
            const fee = (amount * parseFloat(feePercent)) / 100;
            const net = amount - fee;
            feeNote.innerHTML = `<span style="color:#2ecc71">Admin Charge: ${feePercent}% ($${fee.toFixed(2)})</span>. Net credit: <strong>$${net.toFixed(2)}</strong>`;
        } else {
            feeNote.innerHTML = 'Select a wallet to view the admin fee.';
        }
    }

    select.addEventListener('change', updateFee);
    amountInput.addEventListener('input', updateFee);

    document.getElementById('transferForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const option = select.options[select.selectedIndex];
        const walletName = option.text.split(' (')[0];
        const amount = parseFloat(amountInput.value);

        Swal.fire({
            title: 'Confirm Transfer',
            text: `Are you sure you want to transfer $${amount} from ${walletName} to your Main Deposit Wallet?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffb703',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, transfer!',
            background: '#1a1a2e',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({title: 'Processing...', allowOutsideClick: false, background: '#1a1a2e', color: '#fff', didOpen: () => { Swal.showLoading(); }});
                
                const formData = new FormData(this);
                fetch('<?php echo $siteUrl; ?>/UserPanel/api/transfer.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({icon: 'success', title: 'Transfer Complete!', text: data.message, background: '#1a1a2e', color: '#fff'})
                        .then(() => { window.location.href = 'myWallet.php'; });
                    } else {
                        Swal.fire({icon: 'error', title: 'Transfer Failed', text: data.message || 'Transaction error occurred.', background: '#1a1a2e', color: '#fff'});
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
