<?php
session_start();
require_once __DIR__ . '/../libs/db.php';
require_once __DIR__ . '/../libs/auth.php';
require_once __DIR__ . '/../libs/mailer.php';

$env = getEnvConfig();
$site_name = $env['SITE_NAME'] ?? 'SAPG';
$site_secret = $env['SITE_SECRET'] ?? 'default_secret';
$site_url = rtrim($env['SITE_URL'] ?? 'http://localhost/autopool', '/');

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = trim($_POST['user_id'] ?? '');

    if (empty($userId)) {
        $message = "Please enter your User ID.";
        $message_type = "error";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT user_id, name, email, password, status FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user) {
                // Security practice: don't reveal if user doesn't exist
                header("Location: forgotPasswordStatus.php");
                exit;
            } elseif ($user['status'] === 'Blocked') {
                $message = "Your account is suspended. Please contact support.";
                $message_type = "error";
            } else {
                $email = $user['email'];
                $name = $user['name'];

                // Generate signed token
                $expiry = time() + 3600; // 1 hour expiration
                $payload = base64_encode($user['user_id'] . ':' . $expiry);
                $signature = hash_hmac('sha256', $payload . ':' . $user['password'], $site_secret);
                $token = $payload . '.' . $signature;

                $resetLink = $site_url . "/UserPanel/resetPassword.php?token=" . urlencode($token);

                // Prepare Email Body
                $body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ffb703; border-radius: 8px; background-color: #030b14; color: #fff;'>
                    <h2 style='color: #ffb703; text-align: center;'>$site_name - Password Reset Request</h2>
                    <p>Hello " . htmlspecialchars($name) . ",</p>
                    <p>We received a request to reset your password. You can reset it by clicking the link below:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='" . htmlspecialchars($resetLink) . "' style='background: linear-gradient(135deg, #ffcf00 0%, #bfa100 100%); color: #000; padding: 12px 25px; text-decoration: none; font-weight: bold; border-radius: 5px;'>Reset Password</a>
                    </div>
                    <p>Or copy and paste this URL into your browser:</p>
                    <p style='word-break: break-all; color: #ffb703;'>" . htmlspecialchars($resetLink) . "</p>
                    <p style='font-size: 12px; color: #a0aec0; margin-top: 30px;'>This link is valid for 1 hour. If you did not request this, you can ignore this email safely.</p>
                </div>";

                $subject = "$site_name - Password Reset Request";
                $sent = sendEmail($email, $subject, $body);

                if ($sent) {
                    $maskedEmail = substr($email, 0, 3) . "••••@" . explode('@', $email)[1];
                    header("Location: forgotPasswordStatus.php?email=" . urlencode($maskedEmail));
                    exit;
                } else {
                    $message = "Failed to send reset email. Please try again later or contact support.";
                    $message_type = "error";
                }
            }
        } catch (Exception $e) {
            $message = "An error occurred. Please try again.";
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
    <title><?php echo htmlspecialchars($site_name); ?> - Forgot Password</title>
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
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Forgot Password</h2>
        <p>Enter your User ID to receive a password reset link.</p>
        
        <form method="POST" action="forgotPassword.php">
            <div class="form-group">
                <label>User ID</label>
                <input type="text" name="user_id" class="form-control" placeholder="e.g. SA123456" required>
            </div>
            <button type="submit" class="btn-login">Send Reset Link</button>
        </form>
        
        <div class="back-link">
            <a href="login.php"><i class="fa-solid fa-arrow-left"></i> Back to Login</a>
        </div>
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
        });
    </script>
    <?php endif; ?>
</body>
</html>
