<?php
session_start();
require_once __DIR__ . '/../libs/db.php';
$env = parse_ini_file(__DIR__ . '/../.env');
$site_name = $env['SITE_NAME'] ?? 'SAPG';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_name); ?> - Login</title>
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
        .login-card h2 { color: #ffb703; margin-bottom: 25px; font-size: 28px; }
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
        .register-link { margin-top: 20px; font-size: 14px; color: #a0aec0; }
        .register-link a { color: #ffb703; text-decoration: none; font-weight: bold; }
        .register-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2><?php echo htmlspecialchars($site_name); ?></h2>
        <form id="loginForm">
            <div class="form-group">
                <label>User ID</label>
                <input type="text" name="user_id" class="form-control" placeholder="e.g. SA123456" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter Password" required>
            </div>
            <button type="submit" class="btn-login">Login</button>
        </form>
        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'login');
            
            fetch('<?php echo rtrim($env['SITE_URL'] ?? 'http://localhost/autopool', '/'); ?>/UserPanel/api/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({icon: 'success', title: 'Success', text: 'Login successful!', background: '#1a1a2e', color: '#fff', timer: 1500, showConfirmButton: false}).then(() => { window.location.href = 'index.php'; });
                } else {
                    Swal.fire({icon: 'error', title: 'Error', text: data.message || 'Invalid User ID or Password.', background: '#1a1a2e', color: '#fff'});
                }
            })
            .catch(err => {
                Swal.fire({icon: 'error', title: 'Error', text: 'An error occurred during login.', background: '#1a1a2e', color: '#fff'});
            });
        });
    </script>
</body>
</html>
