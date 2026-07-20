<?php 
require_once '../libs/db.php';
require_once '../libs/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'] ?? '';

include '../includes/header.php'; 
?>

<div id="ticketSubmitSection" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">Ticket Submit</div>
        <div class="profile-breadcrumb"><a href="index.php">Home</a> &raquo; Ticket Submit</div>
    </div>
    
    <form id="ticketSubmitForm" class="form-container" style="max-width: 700px;">
        <div class="form-group">
            <label>User ID</label>
            <input type="text" class="form-control form-control-disabled" value="<?php echo htmlspecialchars($user_id); ?>" readonly>
        </div>
        <div class="form-group">
            <label>Subject</label>
            <input type="text" name="subject" class="form-control" placeholder="Enter Subject" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="message" class="textarea-control" placeholder="Enter Description" required style="width: 100%; height: 150px; background: #061121; color: #fff; border: 1px solid #ffb703; border-radius: 4px; padding: 10px;"></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-submit-gold" style="padding: 10px 20px;">Create Ticket</button>
        </div>
    </form>
</div>

<script>
document.getElementById('ticketSubmitForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'create');

    fetch('api/tickets.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({icon: 'success', title: 'Success', text: data.message, background: '#1a1a2e', color: '#fff'})
            .then(() => {
                window.location.href = 'ticketReport.php';
            });
        } else {
            Swal.fire({icon: 'error', title: 'Error', text: data.message, background: '#1a1a2e', color: '#fff'});
        }
    })
    .catch(err => {
        Swal.fire({icon: 'error', title: 'Error', text: 'An error occurred while submitting', background: '#1a1a2e', color: '#fff'});
    });
});
</script>

<?php include '../includes/footer.php'; ?>