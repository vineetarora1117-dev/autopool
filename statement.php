<?php
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Statement</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0f172a;
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }
        body { background-color: var(--bg-dark); color: var(--text-light); padding: 3rem; }
        .container { max-width: 900px; margin: 0 auto; background: #1e293b; padding: 2.5rem; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: 0 25px 50px rgba(0,0,0,0.5); }
        h2 { margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; font-size: 2rem; }
        .statement-table { width: 100%; border-collapse: collapse; }
        .statement-table th, .statement-table td { text-align: left; padding: 1.2rem; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .statement-table th { color: var(--text-muted); font-weight: 400; font-size: 1rem; }
        .statement-table td { font-size: 1.1rem; }
        .amt-positive { color: #4ade80; font-weight: 600; }
        .badge-sponsor { background: rgba(59, 130, 246, 0.2); color: #60a5fa; padding: 6px 12px; border-radius: 6px; font-size: 0.9rem; }
        .badge-autopool { background: rgba(139, 92, 246, 0.2); color: #c084fc; padding: 6px 12px; border-radius: 6px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="container">
        <h2 id="modalTitle">Loading User Statement...</h2>
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

    <script>
        const userId = <?php echo $user_id; ?>;
        
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
                
                let tbody = '';
                if (data.transactions.length === 0) {
                    tbody = '<tr><td colspan="4" style="text-align:center; color: var(--text-muted);">No earnings yet.</td></tr>';
                } else {
                    data.transactions.forEach(t => {
                        const date = new Date(t.created_at).toLocaleString();
                        const badgeClass = t.type === 'sponsor' ? 'badge-sponsor' : 'badge-autopool';
                        const typeLabel = t.type === 'sponsor' ? 'Sponsor Bonus' : `Autopool (Level ${t.level})`;
                        
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
                document.getElementById('statementBody').innerHTML = tbody;
            } else {
                document.getElementById('statementBody').innerHTML = `<tr><td colspan="4" style="text-align:center; color: red;">Error: ${data.error}</td></tr>`;
            }
        }
        
        loadStatement();
    </script>
</body>
</html>
