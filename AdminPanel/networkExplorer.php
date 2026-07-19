<?php require_once 'includes/header.php'; ?>
<div class="breadcrumb">
    <a href="index.php">Dashboard</a> / Network Explorer
</div>

<div class="card">
    <h3 class="card-title">Network Explorer</h3>
    <form style="display: flex; gap: 10px; margin-bottom: 20px;">
        <input type="text" placeholder="Enter User ID" style="padding: 8px; background: #061121; color: #fff; border: 1px solid #ffb703; flex: 1;">
        <select style="padding: 8px; background: #061121; color: #fff; border: 1px solid #ffb703;">
            <option value="main">Main Package</option>
            <option value="booster">Booster</option>
        </select>
        <button type="submit" class="btn btn-gold">Search</button>
    </form>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Join Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" style="text-align: center;">Enter a User ID to explore network</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
