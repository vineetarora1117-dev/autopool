<?php require_once 'includes/header.php'; ?>
<div class="breadcrumb">
    <a href="index.php">Dashboard</a> / Support Tickets
</div>

<div class="card">
    <h3 class="card-title">Support Tickets</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" style="text-align: center;">No tickets found</td>
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
