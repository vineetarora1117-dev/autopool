<?php
require_once '../libs/db.php';
require_once '../libs/auth.php';
require_once '../libs/config.php';
require_once '../includes/BoosterEngine.php';

requireLogin();
$user_id = $_SESSION['user_id'];

// Generate CSRF token for form re-submission prevention
if (empty($_SESSION['booster_csrf'])) {
    $_SESSION['booster_csrf'] = bin2hex(random_bytes(16));
}

$alert_message = '';
$alert_type = '';

// Handle POST request (Purchase action)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'buy_booster') {
    $token = $_POST['csrf_token'] ?? '';
    
    if (empty($token) || $token !== $_SESSION['booster_csrf']) {
        // Invalid or stale token -> redirect to prevent duplicate purchase on refresh
        header("Location: buyBooster.php?msg=stale");
        exit;
    }

    // Refresh token immediately so a browser F5 refresh cannot reuse this token
    $_SESSION['booster_csrf'] = bin2hex(random_bytes(16));

    // Call BoosterEngine purchase function
    $result = purchaseBooster($pdo, $user_id);

    if ($result['success']) {
        header("Location: buyBooster.php?msg=success&id=" . urlencode($result['booster_id']));
        exit;
    } else {
        header("Location: buyBooster.php?msg=error&err=" . urlencode($result['message']));
        exit;
    }
}

// Fetch user financial summary
$stmtSummary = $pdo->prepare("SELECT main_deposit_balance, booster_wallet FROM user_financial_summary WHERE user_id = ?");
$stmtSummary->execute([$user_id]);
$summary = $stmtSummary->fetch(PDO::FETCH_ASSOC);

$mainBalance = floatval($summary['main_deposit_balance'] ?? 0);
$boosterWallet = floatval($summary['booster_wallet'] ?? 0);

// Calculate cooldown seconds remaining
$cooldownRemaining = getBoosterCooldownSecondsRemaining($pdo, $user_id);

include '../includes/header.php';
?>

<div class="content-section active-view" style="padding: 20px; max-width: 900px; margin: 0 auto;">
    <div class="profile-header-bar">
        <div class="profile-header-title">
            <i class="fa-solid fa-bolt"></i> Purchase 1x3 Growth Engine
        </div>
        <div class="profile-breadcrumb">
            <a href="index.php">Home</a> &raquo; Buy Growth Engine
        </div>
    </div>

    <!-- Balance & Information Row -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
        <div class="db-gold-card">
            <div class="db-card-label">Main Deposit Wallet</div>
            <div class="db-card-value">$<?php echo number_format($mainBalance, 2); ?></div>
            <div class="db-card-watermark"><i class="fa-solid fa-wallet fa-2x"></i></div>
        </div>

        <div class="db-gold-card">
            <div class="db-card-label">Growth Engine Cost</div>
            <div class="db-card-value">$10.00</div>
            <div class="db-card-watermark"><i class="fa-solid fa-bolt fa-2x"></i></div>
        </div>
    </div>

    <!-- Main Purchase Card -->
    <div class="form-container" style="background: rgba(6, 17, 33, 0.75); border: 1px solid #ffb703; border-radius: 12px; padding: 30px;">
        <h3 style="color: #ffb703; margin-bottom: 15px; font-size: 20px;">
            <i class="fa-solid fa-cart-shopping"></i> 1x3 Auto-Cycling Growth Engine
        </h3>

        <div style="background: rgba(255, 183, 3, 0.1); border: 1px solid rgba(255, 183, 3, 0.3); border-radius: 8px; padding: 15px; margin-bottom: 25px; line-height: 1.6; font-size: 14px; color: #e2e8f0;">
            <i class="fa-solid fa-circle-info" style="color: #ffb703;"></i> 
            <strong>How it works:</strong> Each Growth Engine costs <strong>$10.00</strong>. When 3 downlines join under your Growth Engine in the 1x3 matrix, you earn <strong>$10.00</strong> into your Growth Engine Wallet, <strong>$10.00</strong> goes to Company Revenue, and <strong>$10.00</strong> automatically creates a new <strong>Auto Re-entry Growth Engine</strong> to start another cycle!
        </div>

        <?php if ($cooldownRemaining > 0): ?>
            <!-- Cooldown Lock Display -->
            <div style="background: rgba(231, 76, 60, 0.15); border: 1px solid #e74c3c; border-radius: 10px; padding: 20px; text-align: center; margin-bottom: 20px;">
                <i class="fa-solid fa-clock" style="color: #e74c3c; font-size: 32px; margin-bottom: 10px;"></i>
                <h4 style="color: #e74c3c; margin-bottom: 8px;">6-Hour Cooldown Lock Active</h4>
                <p style="color: #e2e8f0; font-size: 14px; margin-bottom: 15px;">You must wait 6 hours between manual Growth Engine purchases. Next purchase unlocks in:</p>
                <div id="countdownTimer" style="font-size: 28px; font-weight: bold; color: #ffb703; font-family: monospace; letter-spacing: 2px;">
                    --:--:--
                </div>
            </div>

            <button type="button" disabled class="btn-submit-gold" style="width: 100%; padding: 15px; font-size: 16px; opacity: 0.5; cursor: not-allowed;">
                <i class="fa-solid fa-lock"></i> Purchase Locked (Cooldown Active)
            </button>

        <?php elseif ($mainBalance < 10.00): ?>
            <!-- Insufficient Funds Display -->
            <div style="background: rgba(241, 196, 15, 0.15); border: 1px solid #f1c40f; border-radius: 10px; padding: 20px; text-align: center; margin-bottom: 20px;">
                <i class="fa-solid fa-triangle-exclamation" style="color: #f1c40f; font-size: 32px; margin-bottom: 10px;"></i>
                <h4 style="color: #f1c40f; margin-bottom: 8px;">Insufficient Balance</h4>
                <p style="color: #e2e8f0; font-size: 14px;">Your Main Deposit Wallet has $<?php echo number_format($mainBalance, 2); ?>. You need at least $10.00 to buy a Growth Engine.</p>
            </div>

            <a href="deposit.php" class="btn-submit-gold" style="display: block; text-align: center; text-decoration: none; padding: 15px; font-size: 16px;">
                <i class="fa-solid fa-plus-circle"></i> Deposit Funds Now
            </a>

        <?php else: ?>
            <!-- Active Purchase Form -->
            <form method="POST" action="buyBooster.php" id="boosterPurchaseForm">
                <input type="hidden" name="action" value="buy_booster">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['booster_csrf']; ?>">

                <button type="submit" id="btnBuyBooster" class="btn-submit-gold" style="width: 100%; padding: 16px; font-size: 18px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 15px rgba(255, 183, 3, 0.4);">
                    <i class="fa-solid fa-bolt"></i> Buy $10.00 Growth Engine Now
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
<?php if ($cooldownRemaining > 0): ?>
let remainingSeconds = <?php echo $cooldownRemaining; ?>;

function updateCountdown() {
    if (remainingSeconds <= 0) {
        window.location.reload();
        return;
    }
    let h = Math.floor(remainingSeconds / 3600);
    let m = Math.floor((remainingSeconds % 3600) / 60);
    let s = remainingSeconds % 60;

    let hStr = h < 10 ? '0' + h : h;
    let mStr = m < 10 ? '0' + m : m;
    let sStr = s < 10 ? '0' + s : s;

    document.getElementById('countdownTimer').innerText = hStr + ':' + mStr + ':' + sStr;
    remainingSeconds--;
}

updateCountdown();
setInterval(updateCountdown, 1000);
<?php endif; ?>

// SweetAlert Notification handling on redirect
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('msg') === 'success') {
        let bId = urlParams.get('id') || '';
        Swal.fire({
            icon: 'success',
            title: 'Booster Activated!',
            text: 'Your Booster #' + bId + ' has been placed in the global matrix.',
            confirmButtonColor: '#ffb703',
            background: '#061121',
            color: '#fff'
        });
    } else if (urlParams.get('msg') === 'error') {
        let errStr = urlParams.get('err') || 'Purchase failed.';
        Swal.fire({
            icon: 'error',
            title: 'Purchase Failed',
            text: decodeURIComponent(errStr),
            confirmButtonColor: '#e74c3c',
            background: '#061121',
            color: '#fff'
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>
