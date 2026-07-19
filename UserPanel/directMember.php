<?php
require_once '../libs/db.php';
require_once '../libs/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$target_user_id = isset($_GET['user_id']) ? trim($_GET['user_id']) : $user_id;

// Security check: Target user must be a descendant of the logged-in user in the sponsor tree
if ($target_user_id !== $user_id) {
    $isDescendant = false;
    $currentId = $target_user_id;
    for ($i = 0; $i < 30; $i++) {
        $stmt = $pdo->prepare("SELECT sponsor_id FROM users WHERE user_id = ?");
        $stmt->execute([$currentId]);
        $sponsor = $stmt->fetchColumn();
        if (!$sponsor) break;
        if ($sponsor === $user_id) {
            $isDescendant = true;
            break;
        }
        $currentId = $sponsor;
    }
    if (!$isDescendant) {
        die("Access denied. Target user is not in your referral tree.");
    }
}

// Fetch direct members sponsored by the target user
$stmt = $pdo->prepare("
    SELECT u.user_id, u.name, u.sponsor_id, u.phone, u.status, u.created_at, ufs.my_package 
    FROM users u 
    LEFT JOIN user_financial_summary ufs ON u.user_id = ufs.user_id 
    WHERE u.sponsor_id = ?
    ORDER BY u.id DESC
");
$stmt->execute([$target_user_id]);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get parent sponsor of the target user to support Back navigation
$stmtParent = $pdo->prepare("SELECT sponsor_id FROM users WHERE user_id = ?");
$stmtParent->execute([$target_user_id]);
$parentId = $stmtParent->fetchColumn() ?: null;

include '../includes/header.php';
?>
<style>
.btn-nav-tree {
    background: transparent;
    color: #ffb703;
    border: 1px solid #ffb703;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    cursor: pointer;
    font-weight: bold;
    text-decoration: none;
    transition: 0.2s;
    display: inline-block;
}
.btn-nav-tree:hover {
    background: rgba(255, 183, 3, 0.1);
}
.btn-explore {
    background: #ffb703;
    color: #000;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    font-weight: bold;
    text-decoration: none;
    transition: 0.2s;
    display: inline-block;
}
.btn-explore:hover {
    background: #bfa100;
}
</style>

<div id="directMemberSection" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">Direct Sponsor Network</div>
        <div class="profile-breadcrumb">
            <a href="index.php">Home</a> &raquo; 
            Direct Team Explorer
        </div>
    </div>
    
    <div class="table-container" style="background: rgba(6, 17, 33, 0.75); border: 1px solid rgba(255, 183, 3, 0.2); border-radius: 12px; padding: 24px; margin-top: 20px;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <div style="font-size: 16px; color: #fff;">
                Sponsor Team of: <strong style="color: #ffb703;"><?php echo htmlspecialchars($target_user_id); ?></strong>
            </div>
            <div style="display: flex; gap: 10px;">
                <?php if ($target_user_id !== $user_id): ?>
                    <a href="directMember.php" class="btn-nav-tree"><i class="fa-solid fa-house-user"></i> Back to My Tree</a>
                    <?php if ($parentId): ?>
                        <a href="directMember.php?user_id=<?php echo urlencode($parentId); ?>" class="btn-nav-tree"><i class="fa-solid fa-arrow-up-long"></i> Up One Level</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <table class="custom-table" style="width: 100%; text-align: left; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid rgba(255,183,3,0.3); color:#ffb703; height:45px;">
                    <th>#</th>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Sponsor ID</th>
                    <th>Mobile No</th>
                    <th>Active Package</th>
                    <th>Status</th>
                    <th>Joining Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($members)): ?>
                    <tr style="height: 50px; border-bottom: 1px solid rgba(255,255,255,0.05); color:#a0aec0;">
                        <td colspan="9" style="text-align: center;">No referrals sponsored under this member.</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $idx = 1;
                    foreach ($members as $m): 
                        $formattedDate = date('d M Y', strtotime($m['created_at']));
                        $pkgVal = $m['my_package'] > 0 ? '$' . number_format($m['my_package'], 2) : 'No Active Package';
                    ?>
                        <tr style="height: 50px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td><?php echo $idx++; ?></td>
                            <td style="font-weight: bold; color: #ffb703;"><?php echo htmlspecialchars($m['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($m['name']); ?></td>
                            <td style="color:#a0aec0;"><?php echo htmlspecialchars($m['sponsor_id']); ?></td>
                            <td><?php echo htmlspecialchars($m['phone']); ?></td>
                            <td style="font-weight: 500;"><?php echo $pkgVal; ?></td>
                            <td>
                                <span class="badge <?php echo $m['status'] === 'Active' ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?php echo htmlspecialchars($m['status']); ?>
                                </span>
                            </td>
                            <td style="color:#a0aec0;"><?php echo $formattedDate; ?></td>
                            <td>
                                <a href="directMember.php?user_id=<?php echo urlencode($m['user_id']); ?>" class="btn-explore">
                                    <i class="fa-solid fa-eye"></i> View Team
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>