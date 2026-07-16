<?php
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Statement</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0f172a;
            --bg-card: #1e293b;
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.1);
            
            --c-sponsor: #3b82f6;
            --c-sponsor-bg: rgba(59, 130, 246, 0.15);
            
            --c-autopool: #8b5cf6;
            --c-autopool-bg: rgba(139, 92, 246, 0.15);
            
            --c-level: #10b981;
            --c-level-bg: rgba(16, 185, 129, 0.15);
            
            --c-all: #f59e0b;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }
        body { background-color: var(--bg-dark); color: var(--text-light); padding: 2rem; display: flex; justify-content: center; min-height: 100vh;}
        
        .layout-wrapper { display: flex; gap: 2rem; max-width: 1200px; width: 100%; }
        
        .sidebar { width: 300px; display: flex; flex-direction: column; gap: 1rem; }
        .main-content { flex: 1; background: var(--bg-card); padding: 2rem; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: 0 25px 50px rgba(0,0,0,0.5); }
        
        h2 { margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; font-size: 1.8rem; }
        
        .tab {
            background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px;
            padding: 1.5rem; cursor: pointer; transition: all 0.3s ease;
            display: flex; flex-direction: column; gap: 0.5rem;
        }
        .tab:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.3); }
        .tab.active { border-color: var(--c-all); background: rgba(245, 158, 11, 0.05); }
        
        .tab.tab-sponsor.active { border-color: var(--c-sponsor); background: var(--c-sponsor-bg); }
        .tab.tab-autopool.active { border-color: var(--c-autopool); background: var(--c-autopool-bg); }
        .tab.tab-level.active { border-color: var(--c-level); background: var(--c-level-bg); }
        
        .tab-title { font-size: 1.2rem; color: var(--text-light); font-weight: 800; }
        .tab-amount { font-size: 1.1rem; color: var(--text-muted); font-weight: 500; }
        
        .statement-table { width: 100%; border-collapse: collapse; }
        .statement-table th, .statement-table td { text-align: left; padding: 1.2rem; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .statement-table th { color: var(--text-muted); font-weight: 400; font-size: 1rem; }
        .statement-table td { font-size: 1.1rem; }
        .amt-positive { color: #4ade80; font-weight: 600; }
        
        .badge { padding: 6px 12px; border-radius: 6px; font-size: 0.9rem; font-weight: 500;}
        .badge-sponsor { background: var(--c-sponsor-bg); color: #60a5fa; }
        .badge-autopool { background: var(--c-autopool-bg); color: #c084fc; }
        .badge-level { background: var(--c-level-bg); color: #34d399; }
        
        .pending-row { opacity: 0.6; }
        .badge-status-pending { background: rgba(245, 158, 11, 0.2); color: #fbbf24; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; margin-left: 8px;}
    </style>
</head>
<body>
    <div class="layout-wrapper">
        <div class="sidebar">
            <div class="tab active" id="tab-all" onclick="filterTransactions('all')">
                <div class="tab-title">Cumulative Earnings</div>
                <div class="tab-amount" id="amt-all">$0.0000</div>
            </div>
            <div class="tab tab-sponsor" id="tab-sponsor" onclick="filterTransactions('sponsor')">
                <div class="tab-title">Sponsor Earnings</div>
                <div class="tab-amount" id="amt-sponsor">$0.0000</div>
            </div>
            <div class="tab tab-autopool" id="tab-autopool" onclick="filterTransactions('autopool')">
                <div class="tab-title">Autopool Earnings</div>
                <div class="tab-amount" id="amt-autopool">$0.0000</div>
            </div>
            <div class="tab tab-level" id="tab-level" onclick="filterTransactions('level')">
                <div class="tab-title">Level Income</div>
                <div class="tab-amount" id="amt-level">$0.0000</div>
            </div>
        </div>
        
        <div class="main-content">
            <h2 id="modalTitle">User Statement</h2>
            <table class="statement-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Income Type</th>
                        <th>Triggered By User</th>
                        <th>Amount Earned</th>
                    </tr>
                </thead>
                <tbody id="statementBody">
                    <tr><td colspan="4" style="text-align:center;">Loading statement data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const userId = <?php echo $user_id; ?>;
        let allTransactions = [];
        let currentFilter = 'all';
        
        async function loadStatement() {
            if (!userId) {
                document.getElementById('modalTitle').innerText = 'Invalid User ID';
                document.getElementById('statementBody').innerHTML = '<tr><td colspan="4" style="text-align:center; color: red;">Error: No user ID provided</td></tr>';
                return;
            }
            
            const res = await fetch(`api.php?action=get_statement&user_id=${userId}`);
            const data = await res.json();
            
            if (data.success) {
                document.getElementById('modalTitle').innerText = `Statement for User ID: (${userId})`;
                allTransactions = data.transactions;
                
                calculateTotals();
                renderTable();
            } else {
                document.getElementById('statementBody').innerHTML = `<tr><td colspan="4" style="text-align:center; color: red;">Error: ${data.error}</td></tr>`;
            }
        }
        
        function calculateTotals() {
            let sums = { all: 0, sponsor: 0, autopool: 0, level: 0 };
            
            allTransactions.forEach(t => {
                if (t.status === 'completed') {
                    const amt = parseFloat(t.amount);
                    sums.all += amt;
                    if (sums[t.type] !== undefined) sums[t.type] += amt;
                }
            });
            
            document.getElementById('amt-all').innerText = `$${sums.all.toFixed(4)}`;
            document.getElementById('amt-sponsor').innerText = `$${sums.sponsor.toFixed(4)}`;
            document.getElementById('amt-autopool').innerText = `$${sums.autopool.toFixed(4)}`;
            document.getElementById('amt-level').innerText = `$${sums.level.toFixed(4)}`;
        }
        
        function filterTransactions(type) {
            currentFilter = type;
            document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
            document.getElementById(`tab-${type}`).classList.add('active');
            renderTable();
        }
        
        function renderTable() {
            let tbody = '';
            const filtered = currentFilter === 'all' ? allTransactions : allTransactions.filter(t => t.type === currentFilter);
            
            if (filtered.length === 0) {
                tbody = '<tr><td colspan="4" style="text-align:center; color: var(--text-muted);">No earnings found in this category.</td></tr>';
            } else {
                filtered.forEach(t => {
                    const date = new Date(t.created_at).toLocaleString();
                    
                    let badgeClass = 'badge-sponsor';
                    let typeLabel = 'Sponsor Bonus';
                    
                    if (t.type === 'autopool') {
                        badgeClass = 'badge-autopool';
                        typeLabel = `Autopool (Level ${t.level})`;
                    } else if (t.type === 'level') {
                        badgeClass = 'badge-level';
                        typeLabel = `Level Income (Level ${t.level})`;
                    }
                    
                    const isPending = t.status === 'pending';
                    const rowClass = isPending ? 'pending-row' : '';
                    const statusBadge = isPending ? `<span class="badge-status-pending">Pending</span>` : '';
                    
                    tbody += `
                        <tr class="${rowClass}">
                            <td style="color: var(--text-muted); font-size: 0.9rem;">${date}</td>
                            <td><span class="badge ${badgeClass}">${typeLabel}</span>${statusBadge}</td>
                            <td>(${t.from_user_id}) ${t.from_name || 'System'}</td>
                            <td class="amt-positive">+$${parseFloat(t.amount).toFixed(4)}</td>
                        </tr>
                    `;
                });
            }
            document.getElementById('statementBody').innerHTML = tbody;
        }
        
        loadStatement();
    </script>
</body>
</html>
