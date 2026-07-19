<?php
require_once __DIR__ . '/../../libs/db.php';
require_once __DIR__ . '/../../libs/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'] ?? '';
$pack = isset($_GET['pack']) ? (int)$_GET['pack'] : 1;

$package_map = [
    1 => 'main_11',
    2 => 'main_30',
    3 => 'main_60',
    4 => 'main_120',
    5 => 'main_240',
    6 => 'main_480'
];
$package_type = $package_map[$pack] ?? 'main_11';

// Check if package is active
$stmt = $pdo->prepare("SELECT id FROM user_packages WHERE user_id = ? AND package_type = ? AND is_active = 1");
$stmt->execute([$user_id, $package_type]);
$isActive = (bool)$stmt->fetch();

if (!$isActive) {
    require_once __DIR__ . '/../../libs/config.php';
    global $PACKAGE_CONFIG;
    $config = $PACKAGE_CONFIG[$package_type] ?? [];
    $req_downlines = $config['req_downlines'] ?? 0;
    $prev_package = $config['prev_package'] ?? null;
    
    // Check previous package
    $isPrevActive = true;
    $prev_package_name = '';
    if ($prev_package) {
        $prevConfig = $PACKAGE_CONFIG[$prev_package] ?? [];
        $prev_package_name = ($prevConfig['name'] ?? '') . ' ($' . ($prevConfig['cost'] ?? '') . ')';
        
        $stmt = $pdo->prepare("SELECT id FROM user_packages WHERE user_id = ? AND package_type = ? AND is_active = 1");
        $stmt->execute([$user_id, $prev_package]);
        $isPrevActive = (bool)$stmt->fetch();
    }
    
    // Check downlines count
    $stmt = $pdo->prepare("SELECT direct_team_count FROM user_financial_summary WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $current_downlines = (int)($stmt->fetchColumn() ?: 0);
    
    $isEligible = ($isPrevActive && ($current_downlines >= $req_downlines));
    
    include __DIR__ . '/../../includes/header.php';
    ?>
    <div class="card" style="max-width: 600px; margin: 40px auto; text-align: center; border: 1px solid #ffb703; background: rgba(6, 17, 33, 0.75); backdrop-filter: blur(15px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5); padding: 35px 30px; border-radius: 12px;">
        <div style="font-size: 50px; color: #ff4d4d; margin-bottom: 20px;">
            <i class="fa-solid fa-circle-xmark"></i>
        </div>
        <h2 style="color: #ff4d4d; margin-bottom: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">Autopool Pack <?php echo $pack; ?> Not Active</h2>
        <p style="color: #a0aec0; font-size: 15px; margin-bottom: 30px;">You do not have this package active. Please review your eligibility status below to upgrade.</p>

        <div style="background: rgba(0,0,0,0.3); border-radius: 8px; padding: 20px; text-align: left; margin-bottom: 30px; border: 1px dashed rgba(255,183,3,0.3);">
            <h4 style="color: #ffb703; margin-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 8px; font-size: 16px; font-weight: bold;">ELIGIBILITY CHECKLIST</h4>
            
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <?php if ($prev_package_name): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px;">
                    <span style="color: #e2e8f0; font-weight: 500;">Previous Level Active (<?php echo htmlspecialchars($prev_package_name); ?>)</span>
                    <span>
                        <?php if ($isPrevActive): ?>
                            <span style="color: #2ecc71; font-weight: bold;"><i class="fa-solid fa-circle-check"></i> YES</span>
                        <?php else: ?>
                            <span style="color: #ff4d4d; font-weight: bold;"><i class="fa-solid fa-circle-xmark"></i> NO</span>
                        <?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>

                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px;">
                    <span style="color: #e2e8f0; font-weight: 500;">Direct Downlines (Required: <?php echo $req_downlines; ?>)</span>
                    <span>
                        <span style="color: <?php echo ($current_downlines >= $req_downlines) ? '#2ecc71' : '#ff4d4d'; ?>; font-weight: bold;">
                            <i class="fa-solid <?php echo ($current_downlines >= $req_downlines) ? 'fa-circle-check' : 'fa-circle-xmark'; ?>"></i> 
                            <?php echo $current_downlines; ?> / <?php echo $req_downlines; ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 16px; font-weight: bold; margin-bottom: 25px;">
            <span style="color: #a0aec0;">Overall Eligibility:</span>
            <?php if ($isEligible): ?>
                <span style="color: #2ecc71; text-transform: uppercase;"><i class="fa-solid fa-circle-check"></i> Eligible</span>
            <?php else: ?>
                <span style="color: #ff4d4d; text-transform: uppercase;"><i class="fa-solid fa-circle-xmark"></i> Not Eligible</span>
            <?php endif; ?>
        </div>

        <div style="display: flex; gap: 15px; justify-content: center;">
            <?php if ($isEligible): ?>
                <a href="autopoolPackage.php" class="btn btn-submit-gold" style="text-decoration: none; padding: 10px 24px; font-size: 14px; font-weight: bold; display: inline-flex; align-items: center; gap: 8px;"><i class="fa-solid fa-arrow-up-right-from-square"></i> Upgrade Now</a>
            <?php endif; ?>
            <a href="index.php" class="btn btn-reset" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;"><i class="fa-solid fa-house"></i> Go Dashboard</a>
        </div>
    </div>
    <?php
    include __DIR__ . '/../../includes/footer.php';
    exit;
}
?>
