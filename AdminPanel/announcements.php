<?php require_once 'includes/header.php'; ?>
<div class="breadcrumb">
    <a href="index.php">Dashboard</a> / Announcements
</div>

<div class="card">
    <h3 class="card-title">Create Announcement</h3>
    <form style="display: flex; flex-direction: column; gap: 15px;">
        <div class="form-group">
            <label>Title</label>
            <input type="text" placeholder="Announcement Title" required>
        </div>
        <div class="form-group">
            <label>Message</label>
            <textarea rows="4" placeholder="Announcement Details" required></textarea>
        </div>
        <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
            <label style="margin: 0;">Active</label>
            <input type="checkbox" checked style="width: auto;">
        </div>
        <button type="submit" class="btn btn-gold" style="width: fit-content;">Create</button>
    </form>
</div>

<div class="card">
    <h3 class="card-title">Existing Announcements</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" style="text-align: center;">No announcements found</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
