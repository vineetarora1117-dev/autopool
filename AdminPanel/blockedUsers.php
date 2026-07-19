<?php require_once 'includes/header.php'; ?>
<div class="breadcrumb">
    <a href="index.php">Dashboard</a> / Blocked Users
</div>

<div class="card">
    <h3 class="card-title">Blocked Users</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Package</th>
                    <th>Direct Team</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="9" style="text-align: center;">No data found</td>
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
