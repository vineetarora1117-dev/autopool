<?php 
require_once '../libs/db.php';
require_once '../libs/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'] ?? '';

// Query support tickets
$stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php'; 
?>

<div id="ticketReportSection" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">Ticket Report</div>
        <div class="profile-breadcrumb"><a href="index.php">Home</a> &raquo; Ticket Report</div>
    </div>
    <div class="table-container">
        <table class="custom-table" style="width: 100%; text-align: left;">
            <thead>
                <tr>
                    <th>Sno</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Reply</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tickets)): ?>
                    <tr><td colspan="6" class="empty-row-msg" style="text-align: center; padding: 20px; color: #a0aec0;">No records found</td></tr>
                <?php else: ?>
                    <?php $sno = 1; foreach ($tickets as $t): ?>
                    <tr>
                        <td><?php echo $sno++; ?></td>
                        <td><strong><?php echo htmlspecialchars($t['subject']); ?></strong></td>
                        <td><?php echo nl2br(htmlspecialchars($t['message'])); ?></td>
                        <td>
                            <?php if (!empty($t['admin_reply'])): ?>
                                <span style="color: #2ecc71;"><?php echo nl2br(htmlspecialchars($t['admin_reply'])); ?></span>
                            <?php else: ?>
                                <span style="color: #a0aec0; font-style: italic;">No reply yet</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $status = $t['status'];
                            $color = $status === 'Open' ? '#ffb703' : ($status === 'In Progress' ? '#3498db' : '#2ecc71');
                            echo "<span style='color: {$color}; font-weight: bold;'>" . htmlspecialchars($status) . "</span>";
                            ?>
                        </td>
                        <td><?php echo date('M d, Y H:i', strtotime($t['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>