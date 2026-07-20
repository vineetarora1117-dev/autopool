<?php
require_once '../libs/db.php';
require_once '../libs/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'] ?? '';

// We can build the sponsor levels dynamically
// Level 1: users whose sponsor_id = $user_id
// Level 2: users whose sponsor_id is in Level 1
// ...
// Level 10: users whose sponsor_id is in Level 9

$level_data = [];
for ($i = 1; $i <= 10; $i++) {
    $level_data[$i] = [
        'total_users' => 0,
        'paid_users' => 0,
        'team_business' => 0.00
    ];
}

$current_level_user_ids = [$user_id];
for ($level = 1; $level <= 10; $level++) {
    if (empty($current_level_user_ids)) {
        break;
    }
    
    // Fetch all users whose sponsor_id is in $current_level_user_ids
    $inQuery = implode(',', array_fill(0, count($current_level_user_ids), '?'));
    $stmt = $pdo->prepare("
        SELECT u.user_id, u.status, ufs.my_package 
        FROM users u 
        LEFT JOIN user_financial_summary ufs ON u.user_id = ufs.user_id 
        WHERE u.sponsor_id IN ($inQuery)
    ");
    $stmt->execute($current_level_user_ids);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $next_level_user_ids = [];
    foreach ($rows as $row) {
        $level_data[$level]['total_users']++;
        if ($row['status'] === 'Active' || ($row['my_package'] ?? 0) > 0) {
            $level_data[$level]['paid_users']++;
        }
        $level_data[$level]['team_business'] += (float)($row['my_package'] ?? 0);
        $next_level_user_ids[] = $row['user_id'];
    }
    
    $current_level_user_ids = $next_level_user_ids;
}

include '../includes/header.php'; 
?>

<div id="levelTeamSection" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">Level Team</div>
        <div class="profile-breadcrumb"><a href="index.php">Home</a> &raquo; Level Team</div>
    </div>
    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Level</th>
                    <th>Total Users</th>
                    <th>Total Paid Users</th>
                    <th>Team Business</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <tr>
                        <td><?php echo $i; ?></td>
                        <td>Level-<?php echo $i; ?></td>
                        <td><?php echo $level_data[$i]['total_users']; ?></td>
                        <td><?php echo $level_data[$i]['paid_users']; ?></td>
                        <td>$<?php echo number_format($level_data[$i]['team_business'], 2); ?></td>
                        <td>
                            <a href="directMember.php?user_id=<?php echo urlencode($user_id); ?>" class="btn-table-action" style="text-decoration: none; display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; font-weight: bold; background: #00bcd4; color: #fff; border-radius: 4px; border: none; cursor: pointer; font-size: 13px;">
                                <i class="fa-solid fa-eye" style="font-size:11px;"></i> View Team
                            </a>
                        </td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>