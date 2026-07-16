<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autopool MLM Simulator</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --card-hover: rgba(51, 65, 85, 0.9);
            --primary: #3b82f6;
            --primary-glow: rgba(59, 130, 246, 0.5);
            --secondary: #8b5cf6;
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-light);
            min-height: 100vh;
            background: radial-gradient(circle at top right, #1e1b4b, var(--bg-dark));
            overflow-x: auto;
            padding: 2rem;
        }

        /* Header / Start Section */
        .header {
            text-align: center;
            margin-bottom: 2rem;
            animation: fadeInDown 1s ease-out;
        }

        .header h1 {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(to right, #60a5fa, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .start-container {
            text-align: center;
            margin: 3rem 0;
        }

        .btn-start {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 1rem 2.5rem;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 20px var(--primary-glow);
        }

        .btn-start:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px var(--primary-glow);
        }

        .btn-clear {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.4);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            z-index: 100;
        }
        .btn-clear:hover {
            background: #ef4444;
            color: white;
        }

        /* Tree Structure */
        #tree-container {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            overflow-x: auto;
            padding-bottom: 50px;
        }

        .tree ul {
            padding-top: 20px; position: relative;
            display: flex; justify-content: center;
            transition: all 0.5s;
        }

        .tree li {
            float: left; text-align: center;
            list-style-type: none;
            position: relative;
            padding: 20px 10px 0 10px;
            transition: all 0.5s;
        }

        /* Connectors */
        .tree li::before, .tree li::after{
            content: '';
            position: absolute; top: 0; right: 50%;
            border-top: 2px solid #60a5fa;
            width: 50%; height: 20px;
        }
        .tree li::after{
            right: auto; left: 50%;
            border-left: 2px solid #60a5fa;
        }

        .tree li:only-child::after, .tree li:only-child::before {
            display: none;
        }
        .tree li:only-child{ padding-top: 0;}
        
        .tree li:first-child::before, .tree li:last-child::after{
            border: 0 none;
        }
        
        .tree li:last-child::before{
            border-right: 2px solid #60a5fa;
            border-radius: 0 5px 0 0;
        }
        .tree li:first-child::after{
            border-radius: 5px 0 0 0;
        }

        .tree ul ul::before{
            content: '';
            position: absolute; top: 0; left: 50%;
            border-left: 2px solid #60a5fa;
            width: 0; height: 20px;
        }

        /* User Card Design */
        .user-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.5rem 0.3rem;
            width: 100px;
            display: inline-block;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            position: relative;
            z-index: 2;
        }

        .user-card:hover {
            transform: translateY(-3px);
            background: var(--card-hover);
            border-color: #60a5fa;
        }

        .card-id {
            font-size: 0.75rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 0.2rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .card-earnings {
            font-size: 0.85rem;
            font-weight: 800;
            color: #4ade80; /* bright green */
            margin-bottom: 0.4rem;
        }

        .add-user-btn {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.2);
            padding: 0.2rem 0.3rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.7rem;
            transition: all 0.2s;
            width: 100%;
        }

        .add-user-btn:hover {
            background: var(--primary);
            border-color: var(--primary);
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #1e293b;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            padding: 2rem;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            animation: fadeInUp 0.4s ease-out;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
        }

        .modal-header h2 {
            font-size: 1.5rem;
        }

        .close-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 2rem;
            cursor: pointer;
        }
        .close-btn:hover { color: #fff; }

        .statement-table {
            width: 100%;
            border-collapse: collapse;
        }

        .statement-table th, .statement-table td {
            text-align: left;
            padding: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .statement-table th {
            color: var(--text-muted);
            font-weight: 400;
            font-size: 0.9rem;
        }

        .statement-table td {
            font-size: 1rem;
        }

        .amt-positive {
            color: #4ade80;
            font-weight: 600;
        }

        .badge-sponsor { background: rgba(59, 130, 246, 0.2); color: #60a5fa; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; }
        .badge-autopool { background: rgba(139, 92, 246, 0.2); color: #c084fc; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <button class="btn-clear" onclick="resetMatrix()" id="clearBtn" style="display:none;">Clear Matrix</button>
    <div class="header">
        <h1>Autopool Matrix</h1>
        <p>Live Placement & Financial Simulation</p>
    </div>

    <div class="start-container" id="startContainer">
        <button class="btn-start" onclick="startSimulation()">Initialize Simulation</button>
    </div>

    <div id="tree-container" class="tree"></div>

    <!-- Statements Modal -->
    <div class="modal-overlay" id="statementModal" onclick="closeModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2 id="modalTitle">Statements</h2>
                <button class="close-btn" onclick="closeModal(event, true)">&times;</button>
            </div>
            <table class="statement-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>From User</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody id="statementBody">
                    <!-- Rows will be injected here via JS -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            loadTree();
        });

        async function startSimulation() {
            const btn = document.querySelector('.btn-start');
            btn.innerText = "Simulating...";
            
            const fd = new FormData();
            fd.append('action', 'start_simulation');
            
            const res = await fetch('api.php', { method: 'POST', body: fd });
            const data = await res.json();
            
            if (data.success) {
                loadTree();
            } else {
                alert(data.error);
                btn.innerText = "Initialize Simulation";
            }
        }

        async function addUser(sponsorId) {
            const fd = new FormData();
            fd.append('action', 'add_user');
            fd.append('sponsor_id', sponsorId);
            
            const res = await fetch('api.php', { method: 'POST', body: fd });
            const data = await res.json();
            
            if (data.success) {
                loadTree();
            } else {
                alert(data.error);
            }
        }

        async function loadTree() {
            const res = await fetch('api.php?action=get_tree');
            const data = await res.json();
            
            if (data.success && data.users.length > 0) {
                document.getElementById('startContainer').style.display = 'none';
                document.getElementById('clearBtn').style.display = 'block';
                const treeHtml = buildTree(data.users, null);
                document.getElementById('tree-container').innerHTML = treeHtml;
            }
        }

        async function resetMatrix() {
            if (confirm("Are you sure you want to clear the entire matrix and database?")) {
                const fd = new FormData();
                fd.append('action', 'clear_db');
                await fetch('api.php', { method: 'POST', body: fd });
                
                document.getElementById('tree-container').innerHTML = '';
                document.getElementById('clearBtn').style.display = 'none';
                document.getElementById('startContainer').style.display = 'block';
                const btn = document.querySelector('.btn-start');
                btn.innerText = "Initialize Simulation";
            }
        }

        function buildTree(users, parentId) {
            const children = users.filter(u => u.upline_id == parentId);
            if (children.length === 0) return '';

            let html = '<ul>';
            children.forEach(child => {
                html += `<li>
                    <div class="user-card" onclick="window.open('statement.php?user_id=' + ${child.id}, '_blank')">
                        <div class="card-id">(${child.id}) ${child.name}</div>
                        <div class="card-earnings">$${parseFloat(child.total_earnings).toFixed(4)}</div>
                        <button class="add-user-btn" onclick="event.stopPropagation(); addUser(${child.id})">+ Add Downline</button>
                    </div>
                    ${buildTree(users, child.id)}
                </li>`;
            });
            html += '</ul>';
            return html;
        }

        async function showStatement(userId, userName) {
            document.getElementById('modalTitle').innerText = `(${userId}) ${userName} - Earnings`;
            document.getElementById('statementBody').innerHTML = '<tr><td colspan="4" style="text-align:center;">Loading...</td></tr>';
            document.getElementById('statementModal').style.display = 'flex';

            const res = await fetch(`api.php?action=get_statement&user_id=${userId}`);
            const data = await res.json();

            let tbody = '';
            if (data.success) {
                if (data.transactions.length === 0) {
                    tbody = '<tr><td colspan="4" style="text-align:center; color: var(--text-muted);">No earnings yet.</td></tr>';
                } else {
                    data.transactions.forEach(t => {
                        const date = new Date(t.created_at).toLocaleString();
                        const badgeClass = t.type === 'sponsor' ? 'badge-sponsor' : 'badge-autopool';
                        const typeLabel = t.type === 'sponsor' ? 'Sponsor Bonus' : `Autopool (Lvl ${t.level})`;
                        
                        tbody += `
                            <tr>
                                <td style="color: var(--text-muted); font-size: 0.9rem;">${date}</td>
                                <td><span class="${badgeClass}">${typeLabel}</span></td>
                                <td>(${t.from_user_id}) ${t.from_name}</td>
                                <td class="amt-positive">+$${parseFloat(t.amount).toFixed(4)}</td>
                            </tr>
                        `;
                    });
                }
            } else {
                tbody = `<tr><td colspan="4" style="text-align:center; color: red;">Error: ${data.error}</td></tr>`;
            }
            document.getElementById('statementBody').innerHTML = tbody;
        }

        function closeModal(event, force = false) {
            if (force || event.target.id === 'statementModal') {
                document.getElementById('statementModal').style.display = 'none';
            }
        }
    </script>
</body>
</html>
