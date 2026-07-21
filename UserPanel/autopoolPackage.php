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

// Fetch target user package summary
$stmt = $pdo->prepare("SELECT * FROM user_financial_summary WHERE user_id = ?");
$stmt->execute([$target_user_id]);
$summary = $stmt->fetch(PDO::FETCH_ASSOC);
$current_package = $summary['my_package'] ?? 0;
$direct_team_count = $summary['direct_team_count'] ?? 0;

// Fetch target user's active direct referral count (referrals with status='Active' and package >= 11)
$stmtActiveDirects = $pdo->prepare("
    SELECT COUNT(*) 
    FROM users u 
    INNER JOIN user_financial_summary ufs ON u.user_id = ufs.user_id 
    WHERE u.sponsor_id = ? AND u.status = 'Active' AND ufs.my_package >= 11
");
$stmtActiveDirects->execute([$target_user_id]);
$active_direct_team_count = (int)$stmtActiveDirects->fetchColumn();

// Fetch logged in user's main wallet balance for display
$stmtUserBalance = $pdo->prepare("SELECT main_deposit_balance FROM user_financial_summary WHERE user_id = ?");
$stmtUserBalance->execute([$user_id]);
$loggedInUserBalance = $stmtUserBalance->fetchColumn() ?: 0.00;

$packages = [];
foreach ($PACKAGE_CONFIG as $key => $conf) {
    $packages[] = [
        'id' => $conf['id'],
        'key' => $key,
        'name' => $conf['name'],
        'price' => $conf['cost'],
        'autopool' => '$' . number_format((30 * $conf['autopool_l1_4']) + (480 * $conf['autopool_l5_8']), 2),
        'sponsor' => '$' . number_format($conf['sponsor_amount'], 2),
        'level' => '$' . number_format($conf['level_levels'] * $conf['level_amount'], 2),
        'req_direct' => $conf['req_downlines']
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

.buy-tabs {
    display: flex;
    margin-bottom: 15px;
    border-radius: 6px;
    overflow: hidden;
    border: 1px solid #ffb703;
}
.buy-tab {
    flex: 1;
    text-align: center;
    padding: 8px;
    cursor: pointer;
    background: rgba(0,0,0,0.5);
    color: #a0aec0;
    font-size: 13px;
    transition: 0.3s;
}
.buy-tab.active {
    background: #ffb703;
    color: #000;
    font-weight: bold;
}
.other-user-input {
    width: 100%;
    padding: 10px;
    background: rgba(0,0,0,0.5);
    border: 1px solid #ffb703;
    color: #fff;
    border-radius: 6px;
    margin-bottom: 10px;
    display: none;
}
</style>

<div id="autopoolPackageSection" class="content-section active-view">
    <div class="profile-header-bar">
        <div class="profile-header-title">Autopool Packages</div>
        <div class="profile-breadcrumb"><a href="index.php">Home</a> &raquo; Buy Package</div>
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
        <?php foreach ($packages as $index => $pkg): 
            $is_activated = ($current_package >= $pkg['price']);
            
            // To unlock a package, user might need previous package activated and required directs
            // For simplicity, we just check required directs here. 
            $is_locked = !$is_activated && ($active_direct_team_count < $pkg['req_direct'] || ($index > 0 && $current_package < $packages[$index-1]['price']));
            $lock_reason = "";
            if ($is_locked) {
                if ($index > 0 && $current_package < $packages[$index-1]['price']) {
                    $lock_reason = "Unlock previous package first";
                } elseif ($active_direct_team_count < $pkg['req_direct']) {
                    $needed = $pkg['req_direct'] - $active_direct_team_count;
                    $lock_reason = "Need $needed more active direct referral(s)";
                }
            }
        ?>
        <div class="pkg-card <?php echo $is_activated ? 'activated' : ($is_locked ? 'locked' : ''); ?>">
            <div class="pkg-title"><?php echo htmlspecialchars($pkg['name']); ?></div>
            <div class="pkg-price">$<?php echo $pkg['price']; ?></div>
            
            <div class="pkg-details">
                <div class="pkg-detail-row"><span>Autopool Dist:</span> <span style="color:#ffb703"><?php echo $pkg['autopool']; ?></span></div>
                <div class="pkg-detail-row"><span>Sponsor Dist:</span> <span style="color:#ffb703"><?php echo $pkg['sponsor']; ?></span></div>
                <div class="pkg-detail-row"><span>Level Dist:</span> <span style="color:#ffb703"><?php echo $pkg['level']; ?></span></div>
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
                <div class="buy-section" id="buy_section_<?php echo $pkg['id']; ?>">
                    <button class="btn-submit-gold" style="width:100%" onclick="purchasePackage('<?php echo htmlspecialchars($pkg['key']); ?>', <?php echo $pkg['price']; ?>)">
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
function purchasePackage(pkgKey, price) {
    const targetUser = '<?php echo htmlspecialchars($target_user_id); ?>';
    const buyerUser = '<?php echo htmlspecialchars($user_id); ?>';
    const apiURL = '<?php echo $siteUrl; ?>/UserPanel/api/packages.php';
    
    Swal.fire({
        title: 'Confirm Purchase',
        text: `Are you sure you want to buy the $${price} package for ${targetUser}?`,
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
            formData.append('package_type', pkgKey);
            formData.append('target_user_id', targetUser);
            
            fetch(apiURL, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({icon:'success', title:'Success', text:data.message || 'Package purchased successfully!', background:'#1a1a2e', color:'#fff'})
                    .then(() => location.reload());
                } else {
                    Swal.fire({icon:'error', title:'Error', text:data.message || 'Failed to purchase package.', background:'#1a1a2e', color:'#fff'});
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