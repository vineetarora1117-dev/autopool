<?php 
require_once '../libs/db.php';
$stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('company_usdt_address', 'company_qr_code_path')");
$stmt->execute();
$db_settings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $db_settings[$row['setting_key']] = $row['setting_value'];
}
$usdt_address = $db_settings['company_usdt_address'] ?? 'TXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
$qr_code_path = $db_settings['company_qr_code_path'] ?? '';
?>
<?php include '../includes/header.php'; ?>
<style>
.quick-amount-btn {
    background: rgba(6, 17, 33, 0.55);
    border: 1px solid #ffb703;
    color: #ffb703;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    margin-right: 5px;
    margin-bottom: 5px;
}
.quick-amount-btn:hover {
    background: #ffb703;
    color: #000;
}
.qr-container {
    text-align: center;
    margin: 20px 0;
    padding: 20px;
    background: rgba(0,0,0,0.3);
    border-radius: 8px;
    border: 1px dashed rgba(255, 183, 3, 0.5);
}
.wallet-address {
    font-family: monospace;
    font-size: 16px;
    color: #ffb703;
    word-break: break-all;
    background: #000;
    padding: 10px;
    border-radius: 4px;
    margin-top: 10px;
}
</style>

<div id="depositSection" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">Deposit Funds</div>
        <div class="profile-breadcrumb"><a href="index.php">Home</a> &raquo; Deposit Funds</div>
    </div>
    
    <div class="qr-container">
        <h3>Company USDT (BEP20) Address</h3>
        <div class="wallet-address" id="companyWallet"><?php echo htmlspecialchars($usdt_address); ?></div>
        <button class="btn-submit-gold" style="padding: 5px 15px; margin-top: 10px; font-size: 12px;" onclick="navigator.clipboard.writeText(document.getElementById('companyWallet').innerText); Swal.fire({icon:'success', title:'Copied!', text:'Wallet Address Copied', timer:1500, showConfirmButton:false, background:'#1a1a2e', color:'#fff'});">Copy Address</button>
        <div style="margin-top: 20px;">
            <?php if (!empty($qr_code_path) && file_exists(__DIR__ . '/../' . $qr_code_path)): ?>
                <img src="../<?php echo htmlspecialchars($qr_code_path); ?>" alt="QR Code" style="max-width: 200px; border: 2px solid #ffb703; border-radius: 8px;">
            <?php else: ?>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($usdt_address); ?>&bgcolor=030b14&color=ffb703" alt="QR Code" style="max-width: 200px; border: 2px solid #ffb703; border-radius: 8px;">
            <?php endif; ?>
        </div>
    </div>

    <form id="depositForm" class="form-container">
        <div class="form-group">
            <label><i class="fa-solid fa-coins"></i> Fund Amount</label>
            <input type="number" name="amount" id="fundAmount" class="form-control" placeholder="Enter amount to deposit" required>
            <div style="margin-top: 10px;">
                <button type="button" class="quick-amount-btn" onclick="document.getElementById('fundAmount').value=11">$11</button>
                <button type="button" class="quick-amount-btn" onclick="document.getElementById('fundAmount').value=30">$30</button>
                <button type="button" class="quick-amount-btn" onclick="document.getElementById('fundAmount').value=50">$50</button>
                <button type="button" class="quick-amount-btn" onclick="document.getElementById('fundAmount').value=100">$100</button>
                <button type="button" class="quick-amount-btn" onclick="document.getElementById('fundAmount').value=200">$200</button>
                <button type="button" class="quick-amount-btn" onclick="document.getElementById('fundAmount').value=500">$500</button>
            </div>
        </div>
        <div class="form-group">
            <label><i class="fa-solid fa-receipt"></i> Transaction Hash</label>
            <input type="text" name="tx_hash" class="form-control" placeholder="Enter your transaction Hash" required>
        </div>
        <div class="form-group">
            <label><i class="fa-solid fa-image"></i> Payment Proof Screenshot</label>
            <input type="file" name="proof_image" class="form-control" accept="image/*" required>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-submit-gold"><i class="fa-solid fa-paper-plane"></i> Submit Deposit Request</button>
            <button type="reset" class="btn-reset-pink"><i class="fa-solid fa-arrow-rotate-left"></i> Reset</button>
        </div>
    </form>
</div>

<script>
document.getElementById('depositForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('api/deposit.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            Swal.fire({icon: 'success', title: 'Success', text: data.message || 'Deposit request submitted successfully!', background: '#1a1a2e', color: '#fff'})
            .then(() => {
                window.location.href = 'depositHistory.php';
            });
        } else {
            Swal.fire({icon: 'error', title: 'Error', text: data.message || 'Failed to submit deposit request.', background: '#1a1a2e', color: '#fff'});
        }
    })
    .catch(err => {
        Swal.fire({icon: 'error', title: 'Error', text: 'An error occurred while submitting.', background: '#1a1a2e', color: '#fff'});
    });
});
</script>

<?php include '../includes/footer.php'; ?>