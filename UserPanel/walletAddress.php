<?php 
require_once '../libs/db.php';
require_once '../libs/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'] ?? '';

// Fetch current wallet address
$stmt = $pdo->prepare("SELECT wallet_address FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$current_wallet = $stmt->fetchColumn() ?: '';

include '../includes/header.php'; 
?>

<div id="walletAddressSection" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">Update Wallet Address</div>
        <div class="profile-breadcrumb"><a href="index.php">Home</a> &raquo; Wallet Update</div>
    </div>
    
    <form id="walletAddressForm" class="form-container">
        <div class="info-alert">
            <i class="fa-solid fa-circle-info"></i>
            <span>Please enter a valid <strong>USDT BEP20</strong> wallet address carefully.</span>
        </div>
        <div class="form-group">
            <label>USDT BEP20 Wallet Address</label>
            <div class="input-with-icon">
                <div class="input-icon-box"><i class="fa-solid fa-wallet"></i></div>
                <input type="text" name="wallet_address" id="wallet_address" value="<?php echo htmlspecialchars($current_wallet); ?>" placeholder="Enter Wallet Address" required style="width: 100%; background: #061121; color: #fff; border: 1px solid #ffb703; border-radius: 4px; padding: 10px; padding-left: 45px;">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-gold"><i class="fa-solid fa-floppy-disk"></i> Save Wallet</button>
            <button type="reset" class="btn-reset"><i class="fa-solid fa-arrow-rotate-left"></i> Reset</button>
        </div>
    </form>
</div>

<script>
document.getElementById('walletAddressForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const walletAddress = document.getElementById('wallet_address').value.trim();
    
    Swal.fire({
        title: 'Save Wallet?',
        text: 'Are you sure you want to update your BEP20 wallet address?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, save',
        confirmButtonColor: '#ffb703',
        background: '#1a1a2e',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'update_wallet');
            formData.append('wallet_address', walletAddress);
            
            fetch('api/profile.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({icon: 'success', title: 'Saved!', text: data.message, background: '#1a1a2e', color: '#fff'})
                    .then(() => location.reload());
                } else {
                    Swal.fire({icon: 'error', title: 'Error', text: data.message, background: '#1a1a2e', color: '#fff'});
                }
            })
            .catch(err => {
                Swal.fire({icon: 'error', title: 'Error', text: 'An error occurred while saving', background: '#1a1a2e', color: '#fff'});
            });
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>