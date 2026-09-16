<?php
session_start();
require_once __DIR__ . '/../libs/db.php';
require_once __DIR__ . '/../libs/auth.php';

$env = getEnvConfig();
$site_name = $env['SITE_NAME'] ?? 'SAPG';
$site_secret = $env['SITE_SECRET'] ?? 'default_secret';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$isValid = false;
$errorMsg = '';
$userId = '';
$userPassHash = '';

if (empty($token)) {
    $errorMsg = "Invalid request. Token is missing.";
} else {
    $parts = explode('.', $token);
    if (count($parts) !== 2) {
        $errorMsg = "Invalid token format.";
    } else {
        $payload = $parts[0];
        $signature = $parts[1];

        $decoded = base64_decode($payload);
        if ($decoded === false || strpos($decoded, ':') === false) {
            $errorMsg = "Failed to decode token payload.";
        } else {
            list($userId, $expiry) = explode(':', $decoded);

            if (time() > intval($expiry)) {
                $errorMsg = "This password reset link has expired.";
            } else {
                try {
                    $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ? AND status != 'Blocked'");
                    $stmt->execute([$userId]);
                    $userPassHash = $stmt->fetchColumn();

                    if (!$userPassHash) {
                        $errorMsg = "User account not found or suspended.";
                    } else {
                        // Validate token signature
                        $expectedSignature = hash_hmac('sha256', $payload . ':' . $userPassHash, $site_secret);
                        if (hash_equals($expectedSignature, $signature)) {
                            $isValid = true;
                        } else {
                            $errorMsg = "Invalid or expired password reset link.";
                        }
                    }
                } catch (Exception $e) {
                    $errorMsg = "Database error occurred.";
                }
            }
        }
    }
}

$message = '';
$message_type = '';

if ($isValid && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($newPassword) || strlen($newPassword) < 6) {
        $message = "Password must be at least 6 characters long.";
        $message_type = "error";
    } elseif ($newPassword !== $confirmPassword) {
        $message = "Passwords do not match.";
        $message_type = "error";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->execute([$newPassword, $userId]);
            
            // Password successfully reset
            $message = "Password reset successful! Redirecting to login...";
            $message_type = "success";
            
            // The signature is now automatically invalidated since $userPassHash is updated.
            $isValid = false;
        } catch (Exception $e) {
            $message = "Failed to update password. Please try again.";
            $message_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_name); ?> - Reset Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #030b14; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; overflow: hidden; }
        .login-card {
            background: rgba(6, 17, 33, 0.75);
            backdrop-filter: blur(15px);
            border: 1px solid #ffb703;
            border-radius: 12px;
            padding: 40px 30px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        .login-card h2 { color: #ffb703; margin-bottom: 10px; font-size: 28px; }
        .login-card p { font-size: 14px; color: #a0aec0; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 14px; color: #a0aec0; }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 183, 3, 0.4);
            color: #fff;
            border-radius: 6px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.3s;
        }
        .form-control:focus { border-color: #ffb703; }
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #ffcf00 0%, #bfa100 100%);
            border: none;
            padding: 12px;
            color: #000;
            font-weight: bold;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255, 183, 3, 0.3); }
        .back-link { margin-top: 20px; font-size: 14px; color: #a0aec0; }
        .back-link a { color: #ffb703; text-decoration: none; font-weight: bold; }
        .back-link a:hover { text-decoration: underline; }
        .error-container { color: #fc5c65; margin-bottom: 20px; font-size: 15px; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Reset Password</h2>
        
        <?php if ($isValid): ?>
            <p>Setup a new secure password for your account.</p>
            <form method="POST" action="resetPassword.php">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control" placeholder="Minimum 6 characters" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
                </div>
                <button type="submit" class="btn-login">Save Password</button>
            </form>
        <?php else: ?>
            <div class="error-container">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 40px; margin-bottom: 15px; color: #fc5c65;"></i>
                <p style="color: #fc5c65;"><?php echo htmlspecialchars($errorMsg ?: "Invalid access."); ?></p>
            </div>
            <div class="back-link">
                <a href="forgotPassword.php">Request New Reset Link</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($message)): ?>
    <script>
        Swal.fire({
            icon: '<?php echo $message_type; ?>',
            title: '<?php echo ($message_type === "success") ? "Success" : "Error"; ?>',
            text: '<?php echo htmlspecialchars($message); ?>',
            background: '#1a1a2e',
            color: '#fff',
            confirmButtonColor: '#ffb703'
        }).then(() => {
            <?php if ($message_type === 'success'): ?>
                window.location.href = 'login.php';
            <?php endif; ?>
        });
    </script>
    <?php endif; ?>
</body>
</html>
