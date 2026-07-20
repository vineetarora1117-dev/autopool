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

<div id="profileViewSection" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">Profile View</div>
        <div class="profile-breadcrumb"><a href="index.php">Home</a> &raquo; Profile View</div>
    </div>
    <div class="profile-view-card">
        <div class="profile-row"><div class="profile-label">Sponsor ID</div><div class="profile-value"><?php echo htmlspecialchars($user['sponsor_id'] ?? 'None'); ?></div></div>
        <div class="profile-row"><div class="profile-label">My User ID</div><div class="profile-value"><?php echo htmlspecialchars($user['user_id']); ?></div></div>
        <div class="profile-row"><div class="profile-label">Name</div><div class="profile-value"><?php echo htmlspecialchars($user['name']); ?></div></div>
        <div class="profile-row"><div class="profile-label">Email</div><div class="profile-value"><?php echo htmlspecialchars($user['email']); ?></div></div>
        <div class="profile-row"><div class="profile-label">Mobile</div><div class="profile-value"><?php echo htmlspecialchars($user['phone']); ?></div></div>
        <div class="profile-row"><div class="profile-label">Registered Date</div><div class="profile-value"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></div></div>
        <button class="btn-gold" style="margin-top: 10px;" onclick="window.location.href='editProfile.php'"><i class="fa-solid fa-pen"></i> Edit</button>
    </div>
</div>

<?php include '../includes/footer.php'; ?>