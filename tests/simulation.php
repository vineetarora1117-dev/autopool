<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulation Panel - Autopool Test Bench</title>
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
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
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
                <a class="nav-item" id="tab-database-btn" href="index.php" style="text-decoration: none;">
                    📦 Database
                </a>
            </li>
            <li>
                <a class="nav-item active" id="tab-simulation-btn" href="simulation.php" style="text-decoration: none;">
                    🎮 Simulation
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content Panel (Right Column) -->
    <main class="main-content">
        <header>
            <h1>System Simulation</h1>
            <p>Simulate MLM flows step by step using real pipeline classes and functions.</p>
        </header>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Pipeline Steps</h2>
                <p class="card-desc">Execute simulation steps sequentially to verify registration, funding, and commission payout logic.</p>
            </div>

            <div class="actions-group">
                <a href="create_users.php" class="btn btn-primary">
                    👥 Create Users
                </a>
                <a href="add_funds.php" class="btn btn-primary">
                    💰 Add Funds
                </a>
                <a href="assign_package.php" class="btn btn-primary">
                    📦 Assign Package
                </a>
            </div>
        </div>
    </main>

</body>
</html>
