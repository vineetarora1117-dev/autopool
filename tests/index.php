<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Testing Panel - Autopool</title>
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
            min-height: 100vh;
        }

        /* Layout Structure */
        .sidebar {
            width: 280px;
            background-color: var(--bg-sidebar);
            border-right: 1px solid var(--border-color);
            padding: 24px;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }

        .main-content {
            flex-grow: 1;
            padding: 40px;
            overflow-y: auto;
            max-width: 1200px;
        }

        /* Sidebar Styling */
        .brand {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: #fff;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand span {
            background: linear-gradient(135deg, var(--primary), #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border-radius: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .nav-item:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.03);
        }

        .nav-item.active {
            color: #fff;
            background-color: rgba(99, 102, 241, 0.1);
            border-color: rgba(99, 102, 241, 0.2);
        }

        /* Content Styling */
        header {
            margin-bottom: 32px;
        }

        header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
        }

        header p {
            color: var(--text-muted);
            font-size: 14px;
        }

        .card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .card-header {
            margin-bottom: 16px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 6px;
        }

        .card-desc {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* Controls & Form Elements */
        .actions-group {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 20px;
        }

        .btn {
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.08);
        }

        /* Status & Alert Boxes */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 12px;
        }

        .status-badge.empty {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .status-badge.stored {
            background-color: var(--success-bg);
            color: var(--success);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 20px;
            padding: 16px;
            background-color: rgba(255, 255, 255, 0.01);
            border: 1px dashed var(--border-color);
            border-radius: 8px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
        }

        .info-value {
            font-size: 14px;
            font-weight: 500;
            color: #fff;
        }

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-top: 20px;
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

    <!-- Sidebar (Left Column) -->
    <aside class="sidebar">
        <div class="brand">
            🚀 <span>AP Test Bench</span>
        </div>
        <ul class="nav-list">
            <li>
                <div class="nav-item active" id="tab-database-btn">
                    📦 Database
                </div>
            </li>
        </ul>
    </aside>

    <!-- Main Content Panel (Right Column) -->
    <main class="main-content">
        <header>
            <h1>Database Configuration & States</h1>
            <p>Control states, snapshots, and prepare test database scenarios for Autopool simulations.</p>
        </header>

        <!-- Database Empty State Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">1) Empty State</h2>
                <p class="card-desc">
                    Allows you to capture the current state of the database to act as the "Empty State" baseline, and restore it instantly during testing.
                </p>
            </div>

            <!-- Status Info -->
            <div id="empty-state-badge" class="status-badge empty">Checking status...</div>

            <div class="info-grid" id="status-info-grid" style="display: none;">
                <div class="info-item">
                    <span class="info-label">Snapshot Name</span>
                    <span class="info-value">empty_state.sql</span>
                </div>
                <div class="info-item">
                    <span class="info-label">File Size</span>
                    <span class="info-value" id="val-size">-</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Last Saved</span>
                    <span class="info-value" id="val-modified">-</span>
                </div>
            </div>

            <!-- Controls -->
            <div class="actions-group">
                <button class="btn btn-primary" onclick="executeAction('store')">
                    💾 Store Current State
                </button>
                <button class="btn btn-secondary" id="btn-restore" onclick="executeAction('restore')" disabled>
                    🔄 Restore Empty State
                </button>
            </div>

            <!-- Messages/Alerts -->
            <div id="alert-box" class="alert"></div>
        </div>
    </main>

    <script>
        async function fetchStatus() {
            try {
                const res = await fetch('api.php?action=status');
                const data = await res.json();
                
                const badge = document.getElementById('empty-state-badge');
                const infoGrid = document.getElementById('status-info-grid');
                const restoreBtn = document.getElementById('btn-restore');
                
                if (data.success) {
                    if (data.exists) {
                        badge.className = "status-badge stored";
                        badge.innerText = "Empty State Snapshot Stored";
                        infoGrid.style.display = "grid";
                        document.getElementById('val-size').innerText = data.formatted_size;
                        document.getElementById('val-modified').innerText = data.modified;
                        restoreBtn.removeAttribute('disabled');
                    } else {
                        badge.className = "status-badge empty";
                        badge.innerText = "No Empty State Snapshot Saved";
                        infoGrid.style.display = "none";
                        restoreBtn.setAttribute('disabled', 'true');
                    }
                }
            } catch (e) {
                console.error("Error fetching status", e);
            }
        }

        async function executeAction(action) {
            const alertBox = document.getElementById('alert-box');
            alertBox.style.display = 'none';

            // Custom prompt or confirmation if restoring
            if (action === 'restore') {
                if (!confirm("Are you sure you want to restore the Empty State? This will overwrite the current database!")) {
                    return;
                }
            }

            const fd = new FormData();
            fd.append('action', action);

            try {
                const res = await fetch('api.php', {
                    method: 'POST',
                    body: fd
                });
                const data = await res.json();

                if (data.success) {
                    alertBox.className = "alert alert-success";
                    alertBox.innerText = data.message;
                    alertBox.style.display = "block";
                } else {
                    alertBox.className = "alert alert-danger";
                    alertBox.innerText = "Error: " + (data.error || 'Unknown error');
                    alertBox.style.display = "block";
                }
            } catch (e) {
                alertBox.className = "alert alert-danger";
                alertBox.innerText = "Communication failure with the server.";
                alertBox.style.display = "block";
            }

            // Refresh file status
            await fetchStatus();
        }

        // Initialize status load
        document.addEventListener('DOMContentLoaded', fetchStatus);
    </script>
</body>
</html>
