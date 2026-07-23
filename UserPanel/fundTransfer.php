<?php
require_once '../libs/db.php';
require_once '../libs/auth.php';
require_once '../libs/config.php';

requireLogin();
$user_id = $_SESSION['user_id'];

// Fetch financial summary (for sender's main deposit balance)
$stmt = $pdo->prepare("SELECT main_deposit_balance FROM user_financial_summary WHERE user_id = ?");
$stmt->execute([$user_id]);
$main_balance = $stmt->fetchColumn() ?: 0.00;

// Fetch fee setting
$stmtFee = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'fund_transfer_fee_percent'");
$stmtFee->execute();
$feePercent = floatval($stmtFee->fetchColumn() ?: 10.00);

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
.verify-badge {
    background: rgba(46, 204, 113, 0.15);
    border: 1px solid rgba(46, 204, 113, 0.4);
    color: #2ecc71;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
    display: none;
    font-size: 14px;
}
.verify-badge i {
    margin-right: 8px;
}
.transfer-fields {
    display: none;
}
</style>

<div id="fundTransferSection" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">Wallet-to-Wallet Transfer</div>
        <div class="profile-breadcrumb">
            <a href="index.php">Home</a> &raquo; 
            <a href="myWallet.php">My Wallet</a> &raquo; 
            Wallet-to-Wallet Transfer
        </div>
    </div>

    <div class="transfer-container">
        <div class="card-header-title">
            <i class="fa-solid fa-paper-plane"></i> Send Funds to User Wallet
        </div>
        
        <div style="margin-bottom: 20px; font-size: 15px; color: #fff;">
            Your Main Wallet Balance: <strong style="color: #ffb703;">$<?php echo number_format($main_balance, 2); ?></strong>
        </div>

        <form id="transferForm">
            <!-- Recipient Input and Verification -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display:block; margin-bottom:8px; font-size:14px; color:#a0aec0; font-weight: 500;">Recipient User ID</label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" name="target_user_id" id="target_user_id" class="form-control" style="flex: 1; background:rgba(0,0,0,0.5); border:1px solid rgba(255,183,3,0.3); color:#fff; padding:12px; border-radius:6px; outline:none; font-size: 14px;" required placeholder="Enter User ID (e.g. SA000002)">
                    <button type="button" id="btnVerify" class="btn-submit-gold" style="width: auto; padding: 0 20px; margin: 0;">Verify</button>
                </div>
            </div>

            <!-- Verified Recipient Badge -->
            <div id="verifiedRecipient" class="verify-badge">
                <i class="fa-solid fa-circle-check"></i> Verified Recipient: <span id="recipientDetails" style="font-weight: bold;"></span>
            </div>

            <!-- Transfer Details (Shown only when verified) -->
            <div class="transfer-fields" id="transferFields">
                <div class="form-group" style="margin-bottom: 25px;">
                    <label style="display:block; margin-bottom:8px; font-size:14px; color:#a0aec0; font-weight: 500;">Transfer Amount ($)</label>
                    <input type="number" name="amount" id="transfer_amount" step="0.01" min="1" class="form-control" style="width:100%; background:rgba(0,0,0,0.5); border:1px solid rgba(255,183,3,0.3); color:#fff; padding:12px; border-radius:6px; outline:none; font-size: 14px;" required placeholder="0.00">
                    <div class="fee-note" id="feeNote">
                        Admin Charge: <?php echo number_format($feePercent, 2); ?>%. Net transfer amount will be calculated below.
                    </div>
                </div>
                
                <button type="submit" class="btn-submit-gold" style="width:100%; padding:14px; font-size: 15px; font-weight:bold; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="fa-solid fa-paper-plane"></i> Send Funds Now
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$envConfig = parse_ini_file(__DIR__ . '/../.env');
$siteUrl = rtrim($envConfig['SITE_URL'] ?? 'http://localhost/autopool', '/');
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const targetUserIdInput = document.getElementById('target_user_id');
    const btnVerify = document.getElementById('btnVerify');
    const verifiedRecipientDiv = document.getElementById('verifiedRecipient');
    const recipientDetailsSpan = document.getElementById('recipientDetails');
    const transferFieldsDiv = document.getElementById('transferFields');
    const amountInput = document.getElementById('transfer_amount');
    const feeNote = document.getElementById('feeNote');

    const feePercent = <?php echo $feePercent; ?>;
    let isVerified = false;

    // Reset verification state if the target ID changes
    targetUserIdInput.addEventListener('input', function() {
        if (isVerified) {
            isVerified = false;
            verifiedRecipientDiv.style.display = 'none';
            transferFieldsDiv.style.display = 'none';
            amountInput.value = '';
        }
    });

    btnVerify.addEventListener('click', function() {
        const targetId = targetUserIdInput.value.trim();
        if (!targetId) {
            Swal.fire({icon: 'error', title: 'Error', text: 'Please enter a User ID first.', background: '#1a1a2e', color: '#fff'});
            return;
        }

        Swal.fire({
            title: 'Verifying User...',
            allowOutsideClick: false,
            background: '#1a1a2e',
            color: '#fff',
            didOpen: () => { Swal.showLoading(); }
        });

        fetch('<?php echo $siteUrl; ?>/UserPanel/api/walletTransfer.php?action=verify_user&target_user_id=' + encodeURIComponent(targetId))
        .then(res => res.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                isVerified = true;
                recipientDetailsSpan.textContent = `${data.name} (${data.user_id})`;
                verifiedRecipientDiv.style.display = 'block';
                transferFieldsDiv.style.display = 'block';
                updateFeeDisplay();
            } else {
                Swal.fire({icon: 'error', title: 'Verification Failed', text: data.message, background: '#1a1a2e', color: '#fff'});
            }
        })
        .catch(() => {
            Swal.close();
            Swal.fire({icon: 'error', title: 'Error', text: 'Connection error during verification.', background: '#1a1a2e', color: '#fff'});
        });
    });

    function updateFeeDisplay() {
        const amount = parseFloat(amountInput.value) || 0;
        const fee = (amount * feePercent) / 100;
        const net = amount - fee;
        feeNote.innerHTML = `<span style="color:#e74c3c">Admin Charge: ${feePercent}% ($${fee.toFixed(2)}) will be deducted</span>. Receiver gets: <strong style="color:#2ecc71">$${net.toFixed(2)}</strong>`;
    }

    amountInput.addEventListener('input', updateFeeDisplay);

    document.getElementById('transferForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!isVerified) {
            Swal.fire({icon: 'error', title: 'Error', text: 'Please verify the recipient ID first.', background: '#1a1a2e', color: '#fff'});
            return;
        }

        const targetId = targetUserIdInput.value.trim();
        const amount = parseFloat(amountInput.value);

        if (isNaN(amount) || amount <= 0) {
            Swal.fire({icon: 'error', title: 'Error', text: 'Please enter a valid amount.', background: '#1a1a2e', color: '#fff'});
            return;
        }

        const fee = (amount * feePercent) / 100;
        const net = amount - fee;

        Swal.fire({
            title: 'Confirm Transfer',
            html: `You are transferring <strong>$${amount.toFixed(2)}</strong>.<br><br>` +
                  `<span style="color: #ffb703;">Recipient:</span> ${recipientDetailsSpan.textContent}<br>` +
                  `<span style="color: #e74c3c;">Fee (${feePercent}%):</span> -$${fee.toFixed(2)}<br>` +
                  `<span style="color: #2ecc71;">Receiver Gets:</span> $${net.toFixed(2)}<br><br>` +
                  `Are you sure you want to proceed?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffb703',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Send Funds!',
            background: '#1a1a2e',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({title: 'Processing Transfer...', allowOutsideClick: false, background: '#1a1a2e', color: '#fff', didOpen: () => { Swal.showLoading(); }});
                
                const formData = new FormData();
                formData.append('action', 'transfer');
                formData.append('target_user_id', targetId);
                formData.append('amount', amount);

                fetch('<?php echo $siteUrl; ?>/UserPanel/api/walletTransfer.php', {
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
