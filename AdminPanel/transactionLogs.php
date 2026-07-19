<?php require_once 'includes/header.php'; ?>
<div class="breadcrumb">
    <a href="index.php">Dashboard</a> / Transaction Logs
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h3 class="card-title" style="margin: 0;">Transaction Logs</h3>
        <div style="display: flex; gap: 10px;">
            <select class="form-control" style="padding: 5px; background: #061121; color: #fff; border: 1px solid #ffb703;">
                <option value="">All Types</option>
                <option value="deposit">Deposit</option>
                <option value="withdrawal">Withdrawal</option>
                <option value="earning">Earning</option>
            </select>
            <select class="form-control" style="padding: 5px; background: #061121; color: #fff; border: 1px solid #ffb703;">
                <option value="">All Statuses</option>
                <option value="success">Success</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
            </select>
            <button class="btn btn-gold">Filter</button>
        </div>
    </div>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Wallet</th>
                    <th>Status</th>
                    <th>Narration</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="8" style="text-align: center;">No data found</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="pagination" style="margin-top:15px; display:flex; gap:10px; justify-content:flex-end;">
        <button class="btn btn-gold" disabled>Prev</button>
        <button class="btn btn-gold" disabled>Next</button>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
