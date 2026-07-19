<?php require_once 'includes/header.php'; ?>
<div class="breadcrumb">
    <a href="index.php">Dashboard</a> / Settings
</div>

<div class="card">
    <h3 class="card-title">General Settings</h3>
    <form style="display: flex; flex-direction: column; gap: 15px; max-width: 600px;">
        <div class="form-group">
            <label>Company USDT Address</label>
            <input type="text" placeholder="TRC20 Address">
        </div>
        <div class="form-group">
            <label>QR Code</label>
            <input type="file" accept="image/*">
        </div>
        <div class="form-group">
            <label>Min Withdrawal Amount</label>
            <input type="number" step="0.01">
        </div>
        <div class="form-group">
            <label>Max Withdrawal Amount</label>
            <input type="number" step="0.01">
        </div>
        <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
            <label style="margin: 0;">Registration Enabled</label>
            <input type="checkbox" checked style="width: auto;">
        </div>
        <button type="button" class="btn btn-gold" onclick="updateSettings()">Save Settings</button>
    </form>
</div>

<div class="card">
    <h3 class="card-title">Wallet Configurations</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Wallet Type</th>
                    <th>Fee Percentage</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4" style="text-align: center;">No configurations found</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
function updateSettings() {
    Swal.fire({
        title: 'Save Settings?',
        text: 'Are you sure you want to update the settings?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, save',
        background: '#061121', color: '#fff'
    }).then((result) => {
        if(result.isConfirmed) {
            showSuccess('Settings updated successfully (Simulated)');
        }
    });
}
</script>
<?php require_once 'includes/footer.php'; ?>
