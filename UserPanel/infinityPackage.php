<?php
require_once '../libs/db.php';
require_once '../libs/auth.php';
require_once '../libs/config.php';

requireLogin();
$user_id = $_SESSION['user_id'] ?? '';

$target_user_id = $_GET['target'] ?? 'self';
if ($target_user_id === 'self' || empty($target_user_id)) {
    $target_user_id = $user_id;
}

// Verify target user status
$stmt = $pdo->prepare("SELECT status FROM users WHERE user_id = ?");
$stmt->execute([$target_user_id]);
$targetStatus = $stmt->fetchColumn();
if (!$targetStatus || $targetStatus === 'Blocked') {
    $target_user_id = $user_id; // Fallback to self
}

// Fetch active booster packages of target user
$stmtActivePkgs = $pdo->prepare("SELECT package_type FROM user_packages WHERE user_id = ? AND is_active = 1");
$stmtActivePkgs->execute([$target_user_id]);
$active_packages = $stmtActivePkgs->fetchAll(PDO::FETCH_COLUMN);

// Fetch logged in user's main wallet balance for display
$stmtUserBalance = $pdo->prepare("SELECT main_deposit_balance FROM user_financial_summary WHERE user_id = ?");
$stmtUserBalance->execute([$user_id]);
$loggedInUserBalance = $stmtUserBalance->fetchColumn() ?: 0.00;

$boosters = [];
foreach ($BOOSTER_CONFIG as $key => $conf) {
    $boosters[] = [
        'id' => $conf['id'],
        'key' => $key,
        'name' => $conf['name'],
        'price' => $conf['cost'],
        'user_earnings' => '$' . number_format($conf['user_earnings'], 2),
        'sponsor_income' => '$' . number_format($conf['sponsor_income'], 2),
        'upgrade_reserve' => '$' . number_format($conf['upgrade_reserve'], 2),
        'total_generation' => '$' . number_format($conf['total_generation'], 2)
    ];
}

include '../includes/header.php';
?>
<style>
.package-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}
.pkg-card {
    background: rgba(6, 17, 33, 0.75);
    border: 1px solid #ffb703;
    border-radius: 12px;
    padding: 20px;
    position: relative;
    overflow: hidden;
}
.pkg-card.locked {
    opacity: 0.6;
    border-color: #555;
}
.pkg-card.activated {
    border-color: #2ecc71;
}
.pkg-title {
    color: #ffb703;
    font-size: 20px;
    font-weight: bold;
    text-align: center;
    margin-bottom: 10px;
}
.pkg-price {
    font-size: 28px;
    font-weight: bold;
    text-align: center;
    margin-bottom: 15px;
    color: #fff;
}
.pkg-details {
    margin-bottom: 20px;
}
.pkg-detail-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 14px;
    border-bottom: 1px dashed rgba(255,255,255,0.1);
    padding-bottom: 4px;
}
.pkg-status-badge {
    text-align: center;
    padding: 8px;
    border-radius: 6px;
    font-weight: bold;
    margin-bottom: 15px;
}
.badge-activated { background: rgba(46, 204, 113, 0.2); color: #2ecc71; }
.badge-locked { background: rgba(255, 77, 77, 0.2); color: #ff4d4d; }
</style>

<div id="infinityPackageSection" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">Buy Infinity Booster Package</div>
        <div class="profile-breadcrumb"><a href="index.php">Home</a> &raquo; Buy Infinity Package</div>
    </div>

    <!-- Main Wallet Balance Card -->
    <div style="background: rgba(6, 17, 33, 0.75); border: 1px solid rgba(255, 183, 3, 0.3); border-radius: 12px; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; margin-top: 20px; margin-bottom: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="background: rgba(255, 183, 3, 0.1); width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(255, 183, 3, 0.3);">
                <i class="fa-solid fa-wallet" style="color: #ffb703; font-size: 20px;"></i>
            </div>
            <div>
                <div style="color: #a0aec0; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Main Wallet Balance</div>
                <div style="color: #fff; font-size: 24px; font-weight: bold; margin-top: 2px;">$<?php echo number_format($loggedInUserBalance, 2); ?></div>
            </div>
        </div>
        <a href="deposit.php" class="btn btn-gold" style="padding: 8px 16px; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-weight: bold; background: #ffb703; color: #000; border-radius: 6px; border: none; transition: 0.2s;">
            <i class="fa-solid fa-circle-plus"></i> Deposit
        </a>
    </div>

    <div class="package-grid">
        <?php foreach ($boosters as $index => $pkg): 
            $is_activated = in_array($pkg['key'], $active_packages);
            
            // Locked check: previous booster must be active
            $is_locked = !$is_activated && ($index > 0 && !in_array($boosters[$index-1]['key'], $active_packages));
            $lock_reason = "";
            if ($is_locked) {
                $lock_reason = "Unlock previous booster first";
            }
        ?>
        <div class="pkg-card <?php echo $is_activated ? 'activated' : ($is_locked ? 'locked' : ''); ?>">
            <div class="pkg-title"><?php echo htmlspecialchars($pkg['name']); ?></div>
            <div class="pkg-price">$<?php echo $pkg['price']; ?></div>
            
            <div class="pkg-details">
                <div class="pkg-detail-row"><span>Board Value:</span> <span style="color:#ffb703"><?php echo $pkg['total_generation']; ?></span></div>
                <div class="pkg-detail-row"><span>User Earnings:</span> <span style="color:#2ecc71; font-weight:bold;"><?php echo $pkg['user_earnings']; ?></span></div>
                <div class="pkg-detail-row"><span>Sponsor Income:</span> <span style="color:#ffb703"><?php echo $pkg['sponsor_income']; ?></span></div>
                <div class="pkg-detail-row"><span>Upgrade Reserve:</span> <span style="color:#ffb703"><?php echo $pkg['upgrade_reserve']; ?></span></div>
            </div>

            <?php if ($is_activated): ?>
                <div class="pkg-status-badge badge-activated">
                    <i class="fa-solid fa-circle-check"></i> Activated
                </div>
            <?php elseif ($is_locked): ?>
                <div class="pkg-status-badge badge-locked">
                    <i class="fa-solid fa-lock"></i> Locked<br>
                    <small style="font-size:11px; opacity:0.8"><?php echo $lock_reason; ?></small>
                </div>
            <?php else: ?>
                <div class="buy-section">
                    <button class="btn-submit-gold" style="width:100%" onclick="purchaseBooster('<?php echo htmlspecialchars($pkg['key']); ?>', <?php echo $pkg['price']; ?>)">
                        <i class="fa-solid fa-cart-shopping"></i> Buy Package
                    </button>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
$envConfig = parse_ini_file(__DIR__ . '/../.env');
$siteUrl = rtrim($envConfig['SITE_URL'] ?? 'http://localhost/autopool', '/');
?>
<script>
function purchaseBooster(boosterKey, price) {
    const targetUser = '<?php echo htmlspecialchars($target_user_id); ?>';
    const apiURL = '<?php echo $siteUrl; ?>/UserPanel/api/packages.php';
    
    Swal.fire({
        title: 'Confirm Purchase',
        text: `Are you sure you want to buy the $${price} booster package for ${targetUser}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ffb703',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Buy it!',
        background: '#1a1a2e',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({title:'Processing...', allowOutsideClick:false, background:'#1a1a2e', color:'#fff', didOpen:()=>{Swal.showLoading()}});
            
            const formData = new FormData();
            formData.append('action', 'purchase');
            formData.append('package_type', boosterKey);
            formData.append('target_user_id', targetUser);
            
            fetch(apiURL, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({icon:'success', title:'Success', text:data.message || 'Booster purchased successfully!', background:'#1a1a2e', color:'#fff'})
                    .then(() => location.reload());
                } else {
                    Swal.fire({icon:'error', title:'Error', text:data.message || 'Failed to purchase booster.', background:'#1a1a2e', color:'#fff'});
                }
            })
            .catch(err => {
                Swal.fire({icon:'error', title:'Error', text:'An error occurred.', background:'#1a1a2e', color:'#fff'});
            });
        }
    });
}
</script>
<?php include '../includes/footer.php'; ?>