<?php
session_start();
require_once __DIR__ . '/../libs/auth.php';

$env = getEnvConfig();
$site_name = $env['SITE_NAME'] ?? 'SAPG';
$email = $_GET['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_name); ?> - Check Email</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            max-width: 450px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        .login-card h2 { color: #ffb703; margin-bottom: 20px; font-size: 28px; }
        .email-icon { font-size: 60px; color: #ffb703; margin-bottom: 20px; }
        .info-text { font-size: 15px; color: #e2e8f0; line-height: 1.6; margin-bottom: 20px; text-align: left; }
        .alert-box { background: rgba(255, 183, 3, 0.1); border: 1px dashed #ffb703; border-radius: 6px; padding: 15px; text-align: left; margin-bottom: 25px; }
        .alert-box h4 { color: #ffb703; font-size: 14px; margin-bottom: 5px; font-weight: 600; }
        .alert-box p { font-size: 13px; color: #cbd5e0; line-height: 1.4; }
        .btn-login {
            display: block;
            width: 100%;
            background: linear-gradient(135deg, #ffcf00 0%, #bfa100 100%);
            border: none;
            padding: 12px;
            color: #000;
            font-weight: bold;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255, 183, 3, 0.3); }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="email-icon">
            <i class="fa-solid fa-envelope-circle-check"></i>
        </div>
        <h2>Reset Link Sent!</h2>
        
        <div class="info-text">
            <p>A password reset link has been successfully dispatched to your registered email address<?php if (!empty($email)) echo " (<strong>" . htmlspecialchars($email) . "</strong>)"; ?>.</p>
            <br>
            <p>Please check your inbox and click the reset link to choose a new password. The link will remain active for 1 hour.</p>
        </div>

        <div class="alert-box">
            <h4><i class="fa-solid fa-circle-exclamation"></i> Didn't receive the email?</h4>
            <p>Please check your <strong>Junk</strong> or <strong>Spam</strong> folder, as the email might sometimes be routed there by your mail provider.</p>
        </div>
        
        <a href="login.php" class="btn-login">Back to Login</a>
    </div>
</body>
</html>
