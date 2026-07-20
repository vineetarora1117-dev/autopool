<?php
require_once __DIR__ . '/../libs/db.php';

// Handle AJAX deposit request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'request_deposit') {
    header('Content-Type: application/json');
    $userId = $_POST['user_id'] ?? '';
    $amount = floatval($_POST['amount'] ?? 0);
    
    if (!$userId || $amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid User ID or amount.']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        $txHash = 'SIM_USDT_' . strtolower(bin2hex(random_bytes(8)));
        
        // 1. Insert into deposit_requests as Pending
        $stmt = $pdo->prepare("
            INSERT INTO deposit_requests (user_id, amount, tx_hash, proof_image, status) 
            VALUES (?, ?, ?, NULL, 'Pending')
        ");
        $stmt->execute([$userId, $amount, $txHash]);
        
        // 2. Insert transaction as Pending
        $narration = "USDT Deposit Request of $" . number_format($amount, 2) . " via Simulation Bench (TxHash: $txHash)";
        $stmtTx = $pdo->prepare("
            INSERT INTO transactions (user_id, transaction_type, amount, status, narration) 
            VALUES (?, 'deposit', ?, 'Pending', ?)
        ");
        $stmtTx->execute([$userId, $amount, $narration]);
        
        $pdo->commit();
        echo json_encode([
            'success' => true, 
            'message' => "Pending fund request of $" . number_format($amount, 2) . " submitted successfully for $userId! Proceed to Admin Panel to approve."
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

try {
    // Fetch users with their current main deposit wallet balance and pending request count
    $stmt = $pdo->query("
        SELECT 
            u.id, 
            u.user_id, 
            u.name, 
            COALESCE(f.main_deposit_balance, 0.00) as current_funds,
            (SELECT COUNT(*) FROM deposit_requests WHERE user_id = u.user_id AND status = 'Pending') as pending_count
        FROM users u
        LEFT JOIN user_financial_summary f ON u.user_id = f.user_id
        ORDER BY u.id ASC
    ");
    $usersList = $stmt->fetchAll();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Funds - Simulation Bench</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-main: #0f0f12;
            --bg-sidebar: #151518;
            --bg-card: #1d1d22;
            --border-color: #282830;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --success: #10b981;
            --success-bg: rgba(16, 185, 129, 0.1);
            --danger: #ef4444;
            --danger-bg: rgba(239, 68, 68, 0.1);
            --warning: #f59e0b;
        }

        .status-pending {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
            border: 1px solid rgba(245, 158, 11, 0.2);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            width: 100%;
            max-width: 800px;
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        header {
            margin-bottom: 24px;
            text-align: center;
        }

        header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 6px;
        }

        header p {
            color: var(--text-muted);
            font-size: 14px;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 20px;
            text-align: left;
        }

        th, td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
        }

        th {
            background-color: #121215;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        td {
            color: var(--text-main);
        }

        .user-cell {
            font-weight: 600;
            color: #fff;
        }

        .funds-cell {
            font-family: monospace;
            color: var(--success);
            font-weight: 600;
        }

        /* Buttons Grid inside Cell */
        .add-options {
            display: flex;
            gap: 6px;
        }

        .btn-add {
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            border: 1px solid var(--primary);
            background-color: rgba(99, 102, 241, 0.1);
            color: #c7d2fe;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-add:hover {
            background-color: var(--primary);
            color: #fff;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--text-muted);
            font-size: 14px;
            text-decoration: none;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: #fff;
        }

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
            display: none;
        }

        .alert-success {
            background-color: var(--success-bg);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .alert-danger {
            background-color: var(--danger-bg);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>Add Funds (Simulation)</h1>
        <p>Select a user to request funds. Approval must be processed in the Admin Panel.</p>
    </header>

    <div id="statusAlert" class="alert"></div>

    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>Current Funds</th>
                <th>Status</th>
                <th>Add</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($usersList)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-muted);">No users found. Please create one first.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($usersList as $u): ?>
                    <tr id="row-<?php echo htmlspecialchars($u['user_id']); ?>">
                        <td class="user-cell"><?php echo htmlspecialchars($u['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($u['name']); ?></td>
                        <td class="funds-cell">$<?php echo number_format($u['current_funds'], 2); ?></td>
                        <td class="status-cell">
                            <?php if ($u['pending_count'] > 0): ?>
                                <span class="status-pending">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="add-options">
                                <button class="btn-add" onclick="submitDeposit('<?php echo htmlspecialchars($u['user_id']); ?>', 11)">+$11</button>
                                <button class="btn-add" onclick="submitDeposit('<?php echo htmlspecialchars($u['user_id']); ?>', 20)">+$20</button>
                                <button class="btn-add" onclick="submitDeposit('<?php echo htmlspecialchars($u['user_id']); ?>', 50)">+$50</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="simulation.php" class="back-link">← Return to Simulation Panel</a>
</div>

<script>
    async function submitDeposit(userId, amount) {
        const alertBox = document.getElementById('statusAlert');
        alertBox.style.display = 'none';
        
        const fd = new FormData();
        fd.append('action', 'request_deposit');
        fd.append('user_id', userId);
        fd.append('amount', amount);
        
        try {
            const res = await fetch('add_funds.php', {
                method: 'POST',
                body: fd
            });
            const data = await res.json();
            
            if (data.success) {
                alertBox.className = "alert alert-success";
                alertBox.innerText = data.message;
                alertBox.style.display = 'block';
                
                // Update status badge dynamically
                const row = document.getElementById(`row-${userId}`);
                if (row) {
                    const statusCell = row.querySelector('.status-cell');
                    if (statusCell) {
                        statusCell.innerHTML = '<span class="status-pending">Pending</span>';
                    }
                }
                
                // Scroll to top to see notification
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                alertBox.className = "alert alert-danger";
                alertBox.innerText = "Error: " + (data.message || 'Unknown error');
                alertBox.style.display = 'block';
            }
        } catch (error) {
            alertBox.className = "alert alert-danger";
            alertBox.innerText = "Failed to submit request.";
            alertBox.style.display = 'block';
        }
    }
</script>
</body>
</html>
