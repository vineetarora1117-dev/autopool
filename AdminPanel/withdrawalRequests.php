<?php require_once 'includes/header.php'; ?>
<div class="breadcrumb">
    <a href="index.php">Dashboard</a> / Withdrawal Requests
</div>

<div class="card">
    <h3 class="card-title">Withdrawal Requests</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Amount</th>
                    <th>Wallet Type</th>
                    <th>Fee</th>
                    <th>Net Amount</th>
                    <th>Destination</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="10" style="text-align: center;">No data found</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="pagination" style="margin-top:15px; display:flex; gap:10px; justify-content:flex-end;">
        <button class="btn btn-gold" disabled>Prev</button>
        <button class="btn btn-gold" disabled>Next</button>
    </div>
</div>

<script>
function processWithdrawal(id, status) {
    fetch('api/withdrawals.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=update_status&id=${id}&status=${status}`
    }).then(res => res.json()).then(data => {
        if(data.success) showSuccess(data.message);
        else showError(data.message);
    });
}
</script>
<?php require_once 'includes/footer.php'; ?>
