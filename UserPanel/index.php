<?php
require_once '../libs/db.php';
require_once '../libs/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'] ?? '';
$env = parse_ini_file(__DIR__ . '/../.env');
$site_url = rtrim($env['SITE_URL'] ?? 'http://localhost', '/');

// Fetch user data
$stmt = $pdo->prepare("SELECT name, status FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['name' => 'User', 'status' => 'Inactive'];

// Fetch financial summary
$stmt = $pdo->prepare("SELECT * FROM user_financial_summary WHERE user_id = ?");
$stmt->execute([$user_id]);
$summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$current_package = $summary['my_package'] ?? 0;
$direct_team = $summary['direct_team_count'] ?? 0;
$total_active_team = $summary['total_active_team_count'] ?? 0;
$total_inactive_team = $summary['total_inactive_team_count'] ?? 0;
$direct_income = $summary['total_direct_referral_income'] ?? 0;
$team_income = $summary['total_team_level_income'] ?? 0;
$autopool_income = $summary['total_global_autopool_income'] ?? 0;
$booster_income = $summary['total_booster_income'] ?? 0;
$total_withdrawal = $summary['total_withdrawal_amount'] ?? 0;

$total_income = $direct_income + $team_income + $autopool_income + $booster_income;
$net_income = $total_income - $total_withdrawal;

// Fetch Announcements
$stmt = $pdo->query("SELECT message FROM announcements WHERE is_active = 1 ORDER BY id DESC LIMIT 5");
$announcements = $stmt->fetchAll(PDO::FETCH_COLUMN);
$marquee_text = !empty($announcements) ? implode(' ★ ', $announcements) . ' ★' : 'Welcome to ' . ($env['SITE_NAME'] ?? 'SAPG') . '!';

?>
<?php include '../includes/header.php'; ?>

<div id="dashboardSection" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">Dashboard</div>
        <div class="profile-breadcrumb"><a href="#">Home</a> &raquo; Dashboard</div>
    </div>
    
    <!-- Market Ticker: Announcement Marquee + Live Price Row -->
    <div class="market-ticker-wrap">
        <div class="ticker-announce">
            <span class="ticker-announce-track"><?php echo htmlspecialchars($marquee_text); ?></span>
        </div>
        <div class="ticker-price-row">
            <div class="ticker-price-track">
                <div class="ticker-price-item">
                    <div class="ticker-price-name">US 100 Cash CFD</div>
                    <div class="ticker-price-value">29,257.2 <span class="up">+0.86%</span></div>
                </div>
                <div class="ticker-price-item">
                    <div class="ticker-price-name">EUR to USD</div>
                    <div class="ticker-price-value">1.146 <span class="down">-0.07%</span></div>
                </div>
                <div class="ticker-price-item">
                    <div class="ticker-price-name">Bitcoin</div>
                    <div class="ticker-price-value">64,176 <span class="down">-1.78%</span></div>
                </div>
                <div class="ticker-price-item">
                    <div class="ticker-price-name">Ethereum</div>
                    <div class="ticker-price-value">1,884.4 <span class="down">-1.72%</span></div>
                </div>
                <div class="ticker-price-item">
                    <div class="ticker-price-name">S&amp;P 500</div>
                    <div class="ticker-price-value">7,354.3 <span class="down">-0.24%</span></div>
                </div>
                <!-- Duplicate set for seamless infinite scroll -->
                <div class="ticker-price-item" aria-hidden="true">
                    <div class="ticker-price-name">US 100 Cash CFD</div>
                    <div class="ticker-price-value">29,257.2 <span class="up">+0.86%</span></div>
                </div>
                <div class="ticker-price-item" aria-hidden="true">
                    <div class="ticker-price-name">EUR to USD</div>
                    <div class="ticker-price-value">1.146 <span class="down">-0.07%</span></div>
                </div>
                <div class="ticker-price-item" aria-hidden="true">
                    <div class="ticker-price-name">Bitcoin</div>
                    <div class="ticker-price-value">64,176 <span class="down">-1.78%</span></div>
                </div>
                <div class="ticker-price-item" aria-hidden="true">
                    <div class="ticker-price-name">Ethereum</div>
                    <div class="ticker-price-value">1,884.4 <span class="down">-1.72%</span></div>
                </div>
                <div class="ticker-price-item" aria-hidden="true">
                    <div class="ticker-price-name">S&amp;P 500</div>
                    <div class="ticker-price-value">7,354.3 <span class="down">-0.24%</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Welcome Greetings Header & Member ID Info -->
    <div class="db-welcome-title">Welcome, <?php echo htmlspecialchars($user['name']); ?></div>
    <div class="db-member-id">Member Id: <?php echo htmlspecialchars($user_id); ?></div>

    <!-- Referral Link Interface Layer -->
    <div class="db-referral-wrapper">
        <input type="text" class="db-referral-input" value="<?php echo $site_url; ?>/register.php?ref=<?php echo htmlspecialchars($user_id); ?>" readonly id="refLinkInput">
        <button class="db-referral-btn" onclick="navigator.clipboard.writeText(document.getElementById('refLinkInput').value); Swal.fire({icon:'success', title:'Copied!', text:'Referral link copied to clipboard', timer:1500, showConfirmButton:false, background:'#1a1a2e', color:'#fff'});">Referral Link</button>
    </div>

    <!-- Quick Action Button Bars Matrix -->
    <div class="db-actions-row">
        <div class="db-action-btn" onclick="window.location.href='deposit.php'">Deposit</div>
        <div class="db-action-btn" onclick="window.location.href='withdrawal.php'">Withdrawal</div>
        <div class="db-action-btn" onclick="window.location.href='autopoolPackage.php'">Buy Package</div>
    </div>

    <!-- Primary Row Statistics Cards Block -->
    <div class="db-stats-grid">
        <div class="db-gold-card">
            <div class="db-card-label">Account Status</div>
            <div class="db-card-value"><?php echo htmlspecialchars($user['status']); ?></div>
        </div>
        <div class="db-gold-card">
            <div class="db-card-label">My Package</div>
            <div class="db-card-value">$<?php echo number_format($current_package, 2); ?></div>
        </div>
        <div class="db-gold-card">
            <div class="db-card-label">Direct Team</div>
            <div class="db-card-value"><?php echo number_format($direct_team); ?></div>
        </div>
        <div class="db-gold-card">
            <div class="db-card-label">Total Active Team</div>
            <div class="db-card-value"><?php echo number_format($total_active_team); ?></div>
        </div>
        <div class="db-gold-card">
            <div class="db-card-label">Total Inactive Team</div>
            <div class="db-card-value"><?php echo number_format($total_inactive_team); ?></div>
        </div>
    </div>

    <!-- Secondary Income Matrix Cluster Cards Block -->
    <div class="db-stats-grid-4">
        <div class="db-gold-card">
            <div class="db-card-label">Direct Referral Income</div>
            <div class="db-card-value">$<?php echo number_format($direct_income, 2); ?></div>
        </div>
        <div class="db-gold-card">
            <div class="db-card-label">Team Level Income</div>
            <div class="db-card-value">$<?php echo number_format($team_income, 2); ?></div>
        </div>
        <div class="db-gold-card">
            <div class="db-card-label">Global Autopool Income</div>
            <div class="db-card-value">$<?php echo number_format($autopool_income, 2); ?></div>
        </div>
        <div class="db-gold-card">
            <div class="db-card-label">Booster Income</div>
            <div class="db-card-value">$<?php echo number_format($booster_income, 2); ?></div>
        </div>
        <div class="db-gold-card">
            <div class="db-card-label">Total Income</div>
            <div class="db-card-value">$<?php echo number_format($total_income, 2); ?></div>
        </div>
        <div class="db-gold-card">
            <div class="db-card-label">Total Withdrawal Income</div>
            <div class="db-card-value">$<?php echo number_format($total_withdrawal, 2); ?></div>
        </div>
        <div class="db-gold-card">
            <div class="db-card-label">Net Income</div>
            <div class="db-card-value">$<?php echo number_format($net_income, 2); ?></div>
        </div>
        <div class="db-gold-card">
            <div class="db-card-label">Wallet Address (Auto Generated ID)</div>
            <div class="db-card-value" style="font-size: 16px;"><?php echo htmlspecialchars($user_id); ?></div>
        </div>
    </div>

    <!-- Wallet Balances Grid -->
    <h3 style="color:#ffb703; margin-top:30px; margin-bottom:15px; font-size:18px;"><i class="fa-solid fa-wallet"></i> My Wallet Balances</h3>
    <div class="db-stats-grid">
        <div class="db-gold-card" style="border-color:#ffb703; background: linear-gradient(135deg, rgba(6, 17, 33, 0.9), rgba(191, 161, 0, 0.05)); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="db-card-label">Main Deposit Wallet</div>
                <div class="db-card-value" style="color:#ffb703;">$<?php echo number_format($summary['main_deposit_balance'] ?? 0.00, 2); ?></div>
            </div>
            <a href="myWallet.php" style="color: #ffb703; font-size: 13px; text-decoration: none; display: inline-block; margin-top: 10px; font-weight: bold;"><i class="fa-solid fa-eye"></i> View Wallet</a>
        </div>
        <div class="db-gold-card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="db-card-label">$11 Earning Wallet</div>
                <div class="db-card-value">$<?php echo number_format($summary['earnings_11_wallet'] ?? 0.00, 2); ?></div>
            </div>
            <a href="autopoolPackWallet.php?pack=1" style="color: #ffb703; font-size: 13px; text-decoration: none; display: inline-block; margin-top: 10px; font-weight: bold;"><i class="fa-solid fa-eye"></i> View Wallet</a>
        </div>
        <div class="db-gold-card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="db-card-label">$30 Earning Wallet</div>
                <div class="db-card-value">$<?php echo number_format($summary['earnings_30_wallet'] ?? 0.00, 2); ?></div>
            </div>
            <a href="autopoolPackWallet.php?pack=2" style="color: #ffb703; font-size: 13px; text-decoration: none; display: inline-block; margin-top: 10px; font-weight: bold;"><i class="fa-solid fa-eye"></i> View Wallet</a>
        </div>
        <div class="db-gold-card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="db-card-label">$60 Earning Wallet</div>
                <div class="db-card-value">$<?php echo number_format($summary['earnings_60_wallet'] ?? 0.00, 2); ?></div>
            </div>
            <a href="autopoolPackWallet.php?pack=3" style="color: #ffb703; font-size: 13px; text-decoration: none; display: inline-block; margin-top: 10px; font-weight: bold;"><i class="fa-solid fa-eye"></i> View Wallet</a>
        </div>
        <div class="db-gold-card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="db-card-label">$120 Earning Wallet</div>
                <div class="db-card-value">$<?php echo number_format($summary['earnings_120_wallet'] ?? 0.00, 2); ?></div>
            </div>
            <a href="autopoolPackWallet.php?pack=4" style="color: #ffb703; font-size: 13px; text-decoration: none; display: inline-block; margin-top: 10px; font-weight: bold;"><i class="fa-solid fa-eye"></i> View Wallet</a>
        </div>
        <div class="db-gold-card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="db-card-label">$240 Earning Wallet</div>
                <div class="db-card-value">$<?php echo number_format($summary['earnings_240_wallet'] ?? 0.00, 2); ?></div>
            </div>
            <a href="autopoolPackWallet.php?pack=5" style="color: #ffb703; font-size: 13px; text-decoration: none; display: inline-block; margin-top: 10px; font-weight: bold;"><i class="fa-solid fa-eye"></i> View Wallet</a>
        </div>
        <div class="db-gold-card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="db-card-label">$480 Earning Wallet</div>
                <div class="db-card-value">$<?php echo number_format($summary['earnings_480_wallet'] ?? 0.00, 2); ?></div>
            </div>
            <a href="autopoolPackWallet.php?pack=6" style="color: #ffb703; font-size: 13px; text-decoration: none; display: inline-block; margin-top: 10px; font-weight: bold;"><i class="fa-solid fa-eye"></i> View Wallet</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>