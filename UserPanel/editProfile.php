<?php 
require_once '../libs/db.php';
require_once '../libs/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'] ?? '';

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

include '../includes/header.php'; 
?>

<div id="editProfileSection" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">Edit Profile</div>
        <div class="profile-breadcrumb"><a href="index.php">Home</a> &raquo; Edit Profile</div>
    </div>
    <form id="editProfileForm" class="form-container">
        <div class="form-grid-layout">
            <div class="form-group">
                <label>Sponsor ID</label>
                <input type="text" class="form-control form-control-disabled" value="<?php echo htmlspecialchars($user['sponsor_id'] ?? 'None'); ?>" readonly style="background: rgba(0, 0, 0, 0.4); border: 1px solid rgba(255, 183, 3, 0.1); color: #888;">
            </div>
            <div class="form-group">
                <label>User ID</label>
                <input type="text" class="form-control form-control-disabled" value="<?php echo htmlspecialchars($user['user_id']); ?>" readonly style="background: rgba(0, 0, 0, 0.4); border: 1px solid rgba(255, 183, 3, 0.1); color: #888;">
            </div>
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required style="background: #061121; border: 1px solid #ffb703; color: #fff;">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required style="background: #061121; border: 1px solid #ffb703; color: #fff;">
            </div>
            <div class="form-group">
                <label>Mobile</label>
                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required style="background: #061121; border: 1px solid #ffb703; color: #fff;">
            </div>
            <div class="form-group">
                <label>User Since</label>
                <input type="text" class="form-control form-control-disabled" value="<?php echo date('M d, Y H:i', strtotime($user['created_at'])); ?>" readonly style="background: rgba(0, 0, 0, 0.4); border: 1px solid rgba(255, 183, 3, 0.1); color: #888;">
            </div>
        </div>
        <div class="form-actions" style="justify-content: flex-end; margin-top: 15px;">
            <button type="submit" class="btn-submit-gold"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
            <button type="reset" class="btn-reset-pink"><i class="fa-solid fa-arrow-rotate-left"></i> Reset</button>
        </div>
    </form>
</div>

<script>
document.getElementById('editProfileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    Swal.fire({
        title: 'Save Changes?',
        text: 'Are you sure you want to update your profile details?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, save',
        confirmButtonColor: '#ffb703',
        background: '#1a1a2e',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData(this);
            formData.append('action', 'update_profile');
            
            fetch('api/profile.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({icon: 'success', title: 'Saved!', text: data.message, background: '#1a1a2e', color: '#fff'})
                    .then(() => {
                        window.location.href = 'profileView.php';
                    });
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