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
            
            --c-reward: #ec4899;
            --c-reward-bg: rgba(236, 72, 153, 0.15);
            
            --c-all: #f59e0b;

            --c-network: #06b6d4;
            --c-network-bg: rgba(6, 182, 212, 0.15);
            
            --c-rewards: #f43f5e;
            --c-rewards-bg: rgba(244, 63, 94, 0.15);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }
        body { background-color: var(--bg-dark); color: var(--text-light); padding: 2rem; display: flex; justify-content: center; min-height: 100vh;}
        
        .layout-wrapper { display: flex; flex-direction: column; gap: 2rem; max-width: 1200px; width: 100%; }
        
        .dashboard-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; }
        .main-content { flex: 1; background: var(--bg-card); padding: 2rem; border-radius: 20px; border: 1px solid var(--border-color); box-shadow: 0 25px 50px rgba(0,0,0,0.5); display: none; }
        
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
        .tab.tab-reward.active { border-color: var(--c-reward); background: var(--c-reward-bg); }
        .tab.tab-network.active { border-color: var(--c-network); background: var(--c-network-bg); }
        .tab.tab-rewards.active { border-color: var(--c-rewards); background: var(--c-rewards-bg); }
        .tab.tab-all.active { border-color: var(--c-all); background: rgba(245, 158, 11, 0.15); }
        
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
        .badge-reward { background: var(--c-reward-bg); color: #f472b6; }
        
        .pending-row { opacity: 0.6; }
        .badge-status-pending { background: rgba(245, 158, 11, 0.2); color: #fbbf24; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; margin-left: 8px;}
    </style>
</head>
<body>
    <div class="layout-wrapper">
        <div class="dashboard-cards">
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
            <div class="tab tab-reward" id="tab-reward" onclick="filterTransactions('reward')">
                <div class="tab-title">Reward Income</div>
                <div class="tab-amount" id="amt-reward">$0.0000</div>
            </div>
            <div class="tab tab-network" id="tab-network" onclick="filterTransactions('network')">
                <div class="tab-title">My Network</div>
                <div class="tab-amount" id="cnt-network">0 Members</div>
            </div>
            <div class="tab tab-rewards" id="tab-rewards" onclick="filterTransactions('rewards')">
                <div class="tab-title">My Rewards</div>
                <div class="tab-amount" id="cnt-rewards">Lvl 0 Achieved</div>
            </div>
            <div class="tab tab-all" id="tab-all" onclick="filterTransactions('all')">
                <div class="tab-title" style="display: flex; align-items: center; gap: 0.5rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--c-all);"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"></path><path d="M4 6v12a2 2 0 0 0 2 2h14v-4"></path><path d="M18 12a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h4v-6z"></path></svg>
                    My Wallet
                </div>
                <div class="tab-amount" id="amt-all">$0.0000</div>
            </div>
        </div>
        
        <div class="main-content">
            <h2 id="modalTitle" style="display: flex; justify-content: space-between; align-items: center;">
                <span id="titleText">User Statement</span>
                <span id="headerBalance" style="font-size: 1.4rem; color: #4ade80; font-weight: 800; display: none;">Balance: $0.0000</span>
            </h2>

            <!-- Transactions View -->
            <div id="view-transactions">
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

            <!-- Network View -->
            <div id="view-network" style="display: none;">
                <!-- Stats container -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                    <div style="background: rgba(255,255,255,0.02); padding: 1.25rem; border-radius: 12px; border: 1px solid var(--border-color);">
                        <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.25rem;">Total Network</div>
                        <div id="stat-total-network" style="font-size: 1.8rem; font-weight: 800; color: var(--c-network);">0</div>
                    </div>
                    <div style="background: rgba(255,255,255,0.02); padding: 1.25rem; border-radius: 12px; border: 1px solid var(--border-color);">
                        <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.25rem;">Strongest Leg</div>
                        <div id="stat-strong-leg" style="font-size: 1.8rem; font-weight: 800; color: var(--c-network);">0</div>
                        <div id="stat-strong-leg-info" style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">-</div>
                    </div>
                    <div style="background: rgba(255,255,255,0.02); padding: 1.25rem; border-radius: 12px; border: 1px solid var(--border-color);">
                        <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.25rem;">Other Legs Combined</div>
                        <div id="stat-other-legs" style="font-size: 1.8rem; font-weight: 800; color: var(--c-network);">0</div>
                    </div>
                </div>

                <div>
                    <h3 style="margin-bottom: 1rem; color: var(--c-network); font-size: 1.4rem;">Sponsor Downlines</h3>
                    <table class="statement-table">
                        <thead>
                            <tr>
                                <th>Downline Level</th>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Sponsor Team Size</th>
                                <th>Leg Contribution (Team Size + 1)</th>
                                <th>Registration Date</th>
                            </tr>
                        </thead>
                        <tbody id="referralsBody">
                            <tr><td colspan="6" style="text-align:center; color: var(--text-muted);">No direct downlines found.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Rewards View -->
            <div id="view-rewards" style="display: none;">
                <div style="background: rgba(255,255,255,0.02); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; border: 1px solid var(--border-color); display: flex; gap: 4rem;">
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 0.25rem;">Strong Leg Count</div>
                        <div id="val-strong-leg" style="font-size: 2.2rem; font-weight: 800; color: var(--c-rewards);">0</div>
                    </div>
                    <div style="border-left: 1px solid var(--border-color); padding-left: 4rem;">
                        <div style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 0.25rem;">Other Legs Count</div>
                        <div id="val-other-legs" style="font-size: 2.2rem; font-weight: 800; color: var(--c-rewards);">0</div>
                    </div>
                </div>
                <table class="statement-table">
                    <thead>
                        <tr>
                            <th>Reward Level</th>
                            <th>Strong Leg Target</th>
                            <th>Other Legs Target</th>
                            <th>Reward Amount</th>
                            <th>Status / Progress</th>
                        </tr>
                    </thead>
                    <tbody id="rewardsBody">
                        <tr><td colspan="5" style="text-align:center; color: var(--text-muted);">Loading rewards data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const userId = <?php echo $user_id; ?>;
        let allTransactions = [];
        let networkAndRewardsData = null;
        let currentFilter = null;
        let userName = "User";
        
        async function loadStatement() {
            if (!userId) {
                document.getElementById('titleText').innerText = 'Invalid User ID';
                document.getElementById('statementBody').innerHTML = '<tr><td colspan="4" style="text-align:center; color: red;">Error: No user ID provided</td></tr>';
                return;
            }
            
            try {
                const res = await fetch(`api.php?action=get_statement&user_id=${userId}`);
                const data = await res.json();
                
                if (data.success) {
                    allTransactions = data.transactions;
                    calculateTotals();
                } else {
                    document.getElementById('statementBody').innerHTML = `<tr><td colspan="4" style="text-align:center; color: red;">Error: ${data.error}</td></tr>`;
                }
            } catch (err) {
                console.error("Error loading statement:", err);
            }

            try {
                const resNet = await fetch(`api.php?action=get_network_and_rewards&user_id=${userId}`);
                const dataNet = await resNet.json();
                if (dataNet.success) {
                    networkAndRewardsData = dataNet;
                    userName = dataNet.user_name;
                    
                    document.getElementById('titleText').innerText = `Statement for ${userName} (User ID: ${userId})`;
                    document.getElementById('cnt-network').innerText = `${dataNet.referrals.length} Members`;
                    
                    const achievedLevels = Object.keys(dataNet.achieved_levels).map(Number);
                    const maxLevel = achievedLevels.length > 0 ? Math.max(...achievedLevels) : 0;
                    document.getElementById('cnt-rewards').innerText = `Lvl ${maxLevel} Achieved`;
                }
            } catch (err) {
                console.error("Error loading network and rewards:", err);
            }
        }
        
        function calculateTotals() {
            let sums = { all: 0, sponsor: 0, autopool: 0, level: 0, reward: 0 };
            
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
            document.getElementById('amt-reward').innerText = `$${sums.reward.toFixed(4)}`;
        }
        
        function filterTransactions(type) {
            currentFilter = type;
            document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
            document.getElementById(`tab-${type}`).classList.add('active');
            document.querySelector('.main-content').style.display = 'block';
            
            // Hide all views first
            document.getElementById('view-transactions').style.display = 'none';
            document.getElementById('view-network').style.display = 'none';
            document.getElementById('view-rewards').style.display = 'none';

            if (type === 'network') {
                document.getElementById('titleText').innerText = `My Network - ${userName} (User ID: ${userId})`;
                document.getElementById('headerBalance').style.display = 'none';
                document.getElementById('view-network').style.display = 'block';
                renderNetwork();
            } else if (type === 'rewards') {
                document.getElementById('titleText').innerText = `My Rewards - ${userName} (User ID: ${userId})`;
                document.getElementById('headerBalance').style.display = 'none';
                document.getElementById('view-rewards').style.display = 'block';
                renderRewards();
            } else {
                document.getElementById('titleText').innerText = `Statement for ${userName} (User ID: ${userId})`;
                document.getElementById('view-transactions').style.display = 'block';
                
                if (type === 'all') {
                    const totalBalance = document.getElementById('amt-all').innerText;
                    document.getElementById('headerBalance').innerText = `Total Balance: ${totalBalance}`;
                    document.getElementById('headerBalance').style.display = 'block';
                } else {
                    document.getElementById('headerBalance').style.display = 'none';
                }
                
                renderTable();
            }
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
                    } else if (t.type === 'reward') {
                        badgeClass = 'badge-reward';
                        typeLabel = `Reward (Level ${t.level})`;
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

        function renderNetwork() {
            if (!networkAndRewardsData) return;

            // Fill stats
            document.getElementById('stat-total-network').innerText = `${networkAndRewardsData.referrals.length} Members`;
            document.getElementById('stat-strong-leg').innerText = `${networkAndRewardsData.strong_leg} Members`;
            if (networkAndRewardsData.strong_leg_id) {
                document.getElementById('stat-strong-leg-info').innerText = `(${networkAndRewardsData.strong_leg_name} - ID: ${networkAndRewardsData.strong_leg_id})`;
            } else {
                document.getElementById('stat-strong-leg-info').innerText = "N/A";
            }
            document.getElementById('stat-other-legs').innerText = `${networkAndRewardsData.other_legs} Members`;

            // Render referrals (recursive sponsor downlines)
            let refHtml = '';
            if (networkAndRewardsData.referrals.length === 0) {
                refHtml = '<tr><td colspan="6" style="text-align:center; color: var(--text-muted);">No downlines found.</td></tr>';
            } else {
                networkAndRewardsData.referrals.forEach(ref => {
                    const date = new Date(ref.created_at).toLocaleString();
                    const legContrib = parseInt(ref.sponsor_team_size) + 1;
                    refHtml += `
                        <tr>
                            <td><strong>Level ${ref.level}</strong></td>
                            <td>${ref.id}</td>
                            <td>${ref.name}</td>
                            <td>${ref.sponsor_team_size}</td>
                            <td class="amt-positive">${legContrib}</td>
                            <td style="color: var(--text-muted); font-size: 0.9rem;">${date}</td>
                        </tr>
                    `;
                });
            }
            document.getElementById('referralsBody').innerHTML = refHtml;
        }

        function renderRewards() {
            if (!networkAndRewardsData) return;

            document.getElementById('val-strong-leg').innerText = networkAndRewardsData.strong_leg;
            document.getElementById('val-other-legs').innerText = networkAndRewardsData.other_legs;

            let rewardsHtml = '';
            networkAndRewardsData.reward_targets.forEach(tgt => {
                const isAchieved = networkAndRewardsData.achieved_levels[tgt.level] !== undefined;
                
                const strongLegPct = Math.min(100, (networkAndRewardsData.strong_leg / tgt.strong_leg_target) * 100);
                const otherLegsPct = Math.min(100, (networkAndRewardsData.other_legs / tgt.other_legs_target) * 100);

                let statusBadge = '';
                if (isAchieved) {
                    const achievedDate = new Date(networkAndRewardsData.achieved_levels[tgt.level]).toLocaleString();
                    statusBadge = `<span class="badge badge-level" style="display:inline-block; margin-bottom: 0.25rem;">Achieved</span><br><small style="color: var(--text-muted);">${achievedDate}</small>`;
                } else {
                    const isQualifying = (networkAndRewardsData.strong_leg >= tgt.strong_leg_target) && (networkAndRewardsData.other_legs >= tgt.other_legs_target);
                    if (isQualifying) {
                        statusBadge = `<span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">Qualifying (Pending Add)</span>`;
                    } else {
                        statusBadge = `<span class="badge" style="background: rgba(255,255,255,0.05); color: var(--text-muted);">Locked</span>`;
                    }
                }

                rewardsHtml += `
                    <tr>
                        <td><strong>Level ${tgt.level}</strong></td>
                        <td>
                            <div style="font-weight: 600;">Target: ${tgt.strong_leg_target}</div>
                            <div style="width: 150px; background: rgba(255,255,255,0.1); height: 8px; border-radius: 4px; margin-top: 6px; overflow: hidden; position: relative;">
                                <div style="width: ${strongLegPct}%; height: 100%; background: var(--c-rewards); border-radius: 4px;"></div>
                            </div>
                            <small style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-top: 2px;">Progress: ${networkAndRewardsData.strong_leg} / ${tgt.strong_leg_target}</small>
                        </td>
                        <td>
                            <div style="font-weight: 600;">Target: ${tgt.other_legs_target}</div>
                            <div style="width: 150px; background: rgba(255,255,255,0.1); height: 8px; border-radius: 4px; margin-top: 6px; overflow: hidden; position: relative;">
                                <div style="width: ${otherLegsPct}%; height: 100%; background: var(--c-rewards); border-radius: 4px;"></div>
                            </div>
                            <small style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-top: 2px;">Progress: ${networkAndRewardsData.other_legs} / ${tgt.other_legs_target}</small>
                        </td>
                        <td class="amt-positive">$${parseFloat(tgt.reward_amount).toFixed(4)}</td>
                        <td>${statusBadge}</td>
                    </tr>
                `;
            });
            document.getElementById('rewardsBody').innerHTML = rewardsHtml;
        }
        
        loadStatement();
    </script>
</body>
</html>
