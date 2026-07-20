<?php 
require_once '../libs/db.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$stmt = $pdo->query("SELECT COUNT(*) FROM support_tickets");
$total_rows = $stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

$stmt = $pdo->prepare("SELECT * FROM support_tickets ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->execute([$limit, $offset]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php'; 
?>
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
                    <th>Message</th>
                    <th>Admin Reply</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tickets)): ?>
                <tr>
                    <td colspan="8" style="text-align: center;">No tickets found</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($tickets as $t): ?>
                    <tr>
                        <td>#<?php echo htmlspecialchars($t['id']); ?></td>
                        <td style="font-weight: bold; color: #ffb703;"><?php echo htmlspecialchars($t['user_id']); ?></td>
                        <td><strong><?php echo htmlspecialchars($t['subject']); ?></strong></td>
                        <td><?php echo nl2br(htmlspecialchars($t['message'])); ?></td>
                        <td style="color: #2ecc71;">
                            <?php echo $t['admin_reply'] ? nl2br(htmlspecialchars($t['admin_reply'])) : '<span style="color:#a0aec0; font-style:italic;">No reply yet</span>'; ?>
                        </td>
                        <td>
                            <?php 
                            $status = $t['status'];
                            $color = $status === 'Open' ? '#ffb703' : ($status === 'In Progress' ? '#3498db' : '#2ecc71');
                            echo "<span style='color: {$color}; font-weight: bold;'>" . htmlspecialchars($status) . "</span>";
                            ?>
                        </td>
                        <td><?php echo date('M d, Y H:i', strtotime($t['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-gold" style="padding: 4px 8px; font-size: 12px;" onclick="replyTicket(<?php echo $t['id']; ?>, '<?php echo htmlspecialchars($t['status'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars(json_encode($t['admin_reply']), ENT_QUOTES); ?>')">Reply</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination" style="margin-top:15px; display:flex; gap:10px; justify-content:flex-end;">
        <a href="?page=<?php echo max(1, $page - 1); ?>" class="btn btn-gold" <?php if($page <= 1) echo 'style="pointer-events: none; opacity: 0.5;"'; ?>>Prev</a>
        <a href="?page=<?php echo min($total_pages, $page + 1); ?>" class="btn btn-gold" <?php if($page >= $total_pages) echo 'style="pointer-events: none; opacity: 0.5;"'; ?>>Next</a>
    </div>
</div>

<script>
function replyTicket(ticketId, currentStatus, currentReply) {
    let defaultReply = '';
    try {
        defaultReply = JSON.parse(currentReply) || '';
    } catch(e) {}

    Swal.fire({
        title: 'Reply to Ticket #' + ticketId,
        html: `
            <div style="text-align: left;">
                <label style="color:#fff; display:block; margin-bottom:5px;">Reply Message:</label>
                <textarea id="replyText" class="swal2-textarea" style="width: 90%; height: 100px; margin: 0 auto 15px auto; background:#061121; color:#fff; border:1px solid #ffb703;">${defaultReply}</textarea>
                <label style="color:#fff; display:block; margin-bottom:5px;">Status:</label>
                <select id="replyStatus" class="swal2-select" style="width: 90%; margin: 0 auto; background:#061121; color:#fff; border:1px solid #ffb703;">
                    <option value="Open" ${currentStatus === 'Open' ? 'selected' : ''}>Open</option>
                    <option value="In Progress" ${currentStatus === 'In Progress' ? 'selected' : ''}>In Progress</option>
                    <option value="Closed" ${currentStatus === 'Closed' ? 'selected' : ''}>Closed</option>
                </select>
            </div>
        `,
        background: '#1a1a2e',
        color: '#fff',
        showCancelButton: true,
        confirmButtonText: 'Submit Reply',
        confirmButtonColor: '#ffb703',
        preConfirm: () => {
            const reply = document.getElementById('replyText').value.trim();
            const status = document.getElementById('replyStatus').value;
            if (!reply) {
                Swal.showValidationMessage('Reply message cannot be empty');
                return false;
            }
            return { reply, status };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'reply');
            formData.append('ticket_id', ticketId);
            formData.append('reply', result.value.reply);
            formData.append('status', result.value.status);

            fetch('api/tickets.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({icon: 'success', title: 'Success', text: data.message, background: '#1a1a2e', color: '#fff'})
                    .then(() => location.reload());
                } else {
                    Swal.fire({icon: 'error', title: 'Error', text: data.message, background: '#1a1a2e', color: '#fff'});
                }
            })
            .catch(err => {
                Swal.fire({icon: 'error', title: 'Error', text: 'An error occurred', background: '#1a1a2e', color: '#fff'});
            });
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
