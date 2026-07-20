<?php
require_once __DIR__ . '/../libs/db.php';

try {
    // Fetch users ordered by FIFO (primary key id ascending)
    $stmt = $pdo->query("SELECT id, user_id, name FROM users ORDER BY id ASC");
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
    <title>Create Users - Simulation Bench</title>
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
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 500px;
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-main);
        }

        .form-control {
            width: 100%;
            padding: 12px;
            background-color: #121215;
            border: 1px solid var(--border-color);
            color: #fff;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary);
        }

        .btn {
            width: 100%;
            font-family: inherit;
            font-size: 15px;
            font-weight: 600;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: var(--primary);
            color: #fff;
        }

        .btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
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
        <h1>Create Simulation User</h1>
        <p>Register a new user under a selected sponsor using the system API.</p>
    </header>

    <div id="statusAlert" class="alert"></div>

    <form id="createForm">
        <!-- Sponsor Dropdown -->
        <div class="form-group">
            <label for="sponsor_id">Select Sponsor (FIFO Order)</label>
            <select name="sponsor_id" id="sponsor_id" class="form-control" required>
                <option value="">-- Choose Sponsor --</option>
                <?php foreach ($usersList as $u): ?>
                    <option value="<?php echo htmlspecialchars($u['user_id']); ?>">
                        <?php echo htmlspecialchars($u['user_id']); ?> (SRNO: <?php echo htmlspecialchars($u['id']); ?>) - <?php echo htmlspecialchars($u['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Optional User Info Auto-generated -->
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" name="name" id="name" class="form-control" value="Test User <?php echo rand(100,999); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" class="form-control" value="test<?php echo rand(1000,9999); ?>@example.com" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" name="phone" id="phone" class="form-control" value="+1555<?php echo rand(100000,999999); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" value="password123" required>
        </div>

        <button type="submit" class="btn">
            👤 Register User
        </button>
    </form>

    <a href="index.php" class="back-link">← Return to Test Bench</a>
</div>

<script>
    document.getElementById('createForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const alertBox = document.getElementById('statusAlert');
        alertBox.style.display = 'none';
        
        const fd = new FormData(this);
        
        try {
            // Hit the existing register API
            const res = await fetch('../UserPanel/api/register.php', {
                method: 'POST',
                body: fd
            });
            const data = await res.json();
            
            if (data.success) {
                alertBox.className = "alert alert-success";
                alertBox.innerHTML = `<strong>Registration Successful!</strong><br>Generated User ID: <strong>${data.data.user_id}</strong><br>Password: <strong>${data.data.password}</strong>`;
                alertBox.style.display = 'block';
                
                // Add the newly registered user to the dropdown list
                const sponsorSelect = document.getElementById('sponsor_id');
                const newOpt = document.createElement('option');
                newOpt.value = data.data.user_id;
                newOpt.text = `${data.data.user_id} (New) - ${document.getElementById('name').value}`;
                sponsorSelect.add(newOpt);
                
                // Generate next random user details
                document.getElementById('name').value = "Test User " + Math.floor(Math.random() * 900 + 100);
                document.getElementById('email').value = "test" + Math.floor(Math.random() * 9000 + 1000) + "@example.com";
                document.getElementById('phone').value = "+1555" + Math.floor(Math.random() * 900000 + 100000);
            } else {
                alertBox.className = "alert alert-danger";
                alertBox.innerText = "Error: " + (data.message || 'Unknown error');
                alertBox.style.display = 'block';
            }
        } catch (error) {
            alertBox.className = "alert alert-danger";
            alertBox.innerText = "Failed to communicate with register API.";
            alertBox.style.display = 'block';
        }
    });
</script>
</body>
</html>
