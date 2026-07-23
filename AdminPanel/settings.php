<?php 
require_once '../libs/db.php';

// Fetch all general settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Fetch all wallet configurations
$stmt = $pdo->query("SELECT * FROM wallet_configurations ORDER BY id ASC");
$wallets = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php'; 
?>
<div class="breadcrumb">
    <a href="index.php">Dashboard</a> / Settings
</div>

<div class="card">
    <h3 class="card-title">General Settings</h3>
    <form id="generalSettingsForm" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 15px; max-width: 600px;">
        <div class="form-group">
            <label>Company USDT (BEP20) Address</label>
            <input type="text" name="company_usdt_address" value="<?php echo htmlspecialchars($settings['company_usdt_address'] ?? ''); ?>" placeholder="BEP20 Address" required style="padding: 8px; background: #061121; color: #fff; border: 1px solid #ffb703; width: 100%;">
        </div>
        <div class="form-group">
            <label>QR Code</label>
            <?php if (!empty($settings['company_qr_code_path'])): ?>
                <div style="margin-bottom: 10px;">
                    <img src="../<?php echo htmlspecialchars($settings['company_qr_code_path']); ?>" alt="Company QR Code" style="max-height: 150px; border: 1px solid #ffb703; border-radius: 4px;">
                </div>
            <?php endif; ?>
            <input type="file" name="company_qr_code" accept="image/*" style="padding: 8px; background: #061121; color: #fff; border: 1px solid #ffb703; width: 100%;">
        </div>
        <div class="form-group">
            <label>Min Withdrawal Amount ($)</label>
            <input type="number" step="0.01" name="min_withdrawal_amount" value="<?php echo htmlspecialchars($settings['min_withdrawal_amount'] ?? '0.00'); ?>" required style="padding: 8px; background: #061121; color: #fff; border: 1px solid #ffb703; width: 100%;">
        </div>
        <div class="form-group">
            <label>Max Withdrawal Amount ($)</label>
            <input type="number" step="0.01" name="max_withdrawal_amount" value="<?php echo htmlspecialchars($settings['max_withdrawal_amount'] ?? '0.00'); ?>" required style="padding: 8px; background: #061121; color: #fff; border: 1px solid #ffb703; width: 100%;">
        </div>
        <div class="form-group">
            <label>Fund Transfer Fee (%)</label>
            <input type="number" step="0.01" name="fund_transfer_fee_percent" value="<?php echo htmlspecialchars($settings['fund_transfer_fee_percent'] ?? '10.00'); ?>" required style="padding: 8px; background: #061121; color: #fff; border: 1px solid #ffb703; width: 100%;">
        </div>
        <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
            <label style="margin: 0;">Registration Enabled</label>
            <input type="checkbox" name="registration_enabled" value="1" <?php if (($settings['registration_enabled'] ?? '0') == '1') echo 'checked'; ?> style="width: auto;">
        </div>
        <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
            <label style="margin: 0;">Withdrawal Enabled</label>
            <input type="checkbox" name="withdrawal_enabled" value="1" <?php if (($settings['withdrawal_enabled'] ?? '0') == '1') echo 'checked'; ?> style="width: auto;">
        </div>
        <button type="submit" class="btn btn-gold">Save Settings</button>
    </form>
</div>

<div class="card">
    <h3 class="card-title">Wallet Configurations</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Wallet Type</th>
                    <th>Internal Transfer Fee (%)</th>
                    <th>External Withdrawal Fee (%)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($wallets)): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">No configurations found</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($wallets as $w): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($w['wallet_label']); ?></strong><br><small style="color: #a0aec0;"><?php echo htmlspecialchars($w['wallet_type']); ?></small></td>
                        <td>
                            <input type="number" step="0.01" value="<?php echo htmlspecialchars($w['internal_transfer_fee_percent']); ?>" id="internal_<?php echo $w['wallet_type']; ?>" style="padding: 6px; background: #061121; color: #fff; border: 1px solid #ffb703; width: 100px;">
                        </td>
                        <td>
                            <input type="number" step="0.01" value="<?php echo htmlspecialchars($w['external_withdrawal_fee_percent']); ?>" id="external_<?php echo $w['wallet_type']; ?>" style="padding: 6px; background: #061121; color: #fff; border: 1px solid #ffb703; width: 100px;">
                        </td>
                        <td>
                            <button class="btn btn-gold" style="padding: 4px 8px; font-size: 12px;" onclick="saveWalletFee('<?php echo $w['wallet_type']; ?>')">Save</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('generalSettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    Swal.fire({
        title: 'Save Settings?',
        text: 'Are you sure you want to update the settings?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, save',
        background: '#061121', color: '#fff'
    }).then((result) => {
        if(result.isConfirmed) {
            const formData = new FormData(this);
            formData.append('action', 'update_general');
            
            fetch('api/settings.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({icon: 'success', title: 'Saved!', text: data.message, background: '#061121', color: '#fff'})
                    .then(() => location.reload());
                } else {
                    Swal.fire({icon: 'error', title: 'Error', text: data.message, background: '#061121', color: '#fff'});
                }
            })
            .catch(err => {
                Swal.fire({icon: 'error', title: 'Error', text: 'An error occurred while saving', background: '#061121', color: '#fff'});
            });
        }
    });
});

function saveWalletFee(walletType) {
    const internalVal = document.getElementById('internal_' + walletType).value;
    const externalVal = document.getElementById('external_' + walletType).value;
    
    Swal.fire({
        title: 'Update Fees?',
        text: 'Update transaction fees for ' + walletType + '?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, update',
        background: '#061121', color: '#fff'
    }).then((result) => {
        if(result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'update_wallet_fee');
            formData.append('wallet_type', walletType);
            formData.append('internal_transfer_fee_percent', internalVal);
            formData.append('external_withdrawal_fee_percent', externalVal);
            
            fetch('api/settings.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({icon: 'success', title: 'Updated!', text: data.message, background: '#061121', color: '#fff'});
                } else {
                    Swal.fire({icon: 'error', title: 'Error', text: data.message, background: '#061121', color: '#fff'});
                }
            })
            .catch(err => {
                Swal.fire({icon: 'error', title: 'Error', text: 'An error occurred while updating fee', background: '#061121', color: '#fff'});
            });
        }
    });
}
</script>
<?php require_once 'includes/footer.php'; ?>
