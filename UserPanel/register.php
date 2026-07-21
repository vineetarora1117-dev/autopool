<?php
session_start();
require_once __DIR__ . '/../libs/db.php';
$env = parse_ini_file(__DIR__ . '/../.env');
$site_name = $env['SITE_NAME'] ?? 'SAPG';
$ref = $_GET['ref'] ?? '';
$site_url = getSiteUrl();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_name); ?> - Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #030b14; color: #fff; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .register-card {
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
        .register-card h2 { color: #ffb703; margin-bottom: 25px; font-size: 26px; }
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; margin-bottom: 6px; font-size: 14px; color: #a0aec0; }
        .form-control {
            width: 100%;
            padding: 10px 15px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 183, 3, 0.4);
            color: #fff;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
        }
        .form-control:focus { border-color: #ffb703; }
        .btn-register {
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
            margin-top: 15px;
        }
        .btn-register:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255, 183, 3, 0.3); }
        .error-msg { color: #ff4d4d; font-size: 16px; margin-top: 20px; }
        .sponsor-name { font-size: 13px; color: #00bfa5; margin-top: 5px; font-weight: bold; }
        
        .success-box { background: rgba(0,0,0,0.4); border: 1px dashed #ffb703; padding: 20px; border-radius: 8px; margin-top: 20px; display: none; }
        .success-box h3 { color: #2ecc71; margin-bottom: 15px; }
        .user-detail { font-size: 18px; margin-bottom: 10px; font-weight: bold; }
        .user-detail span { color: #ffb703; }
        .action-btns { display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap; justify-content: center; }
        .btn-action { padding: 8px 12px; border-radius: 4px; font-size: 13px; font-weight: bold; cursor: pointer; border: none; text-decoration: none; color: #fff; }
        .btn-copy { background: #4a5568; }
        .btn-sms { background: #3182ce; }
        .btn-wa { background: #25D366; }
    </style>
</head>
<body>
    <div class="register-card">
        <h2><?php echo htmlspecialchars($site_name); ?> - Register</h2>
        
        <?php if (empty($ref)): ?>
            <div class="error-msg">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 40px; margin-bottom: 15px; display: block;"></i>
                Direct registrations are not allowed.<br>Please use a valid referral link.
            </div>
            <div style="margin-top: 20px;">
                <a href="login.php" style="color: #ffb703; text-decoration: none;">Go to Login</a>
            </div>
        <?php else: ?>
            <div id="successBox" class="success-box">
                <h3><i class="fa-solid fa-circle-check"></i> Registration Successful!</h3>
                <div class="user-detail">User ID: <span id="newUserId"></span></div>
                <div class="user-detail">Password: <span id="newUserPass"></span></div>
                
                <div class="action-btns" id="actionBtnsContainer"></div>
            </div>
            <div id="loginProceed" style="margin-top: 20px; display: none;">
                <a href="login.php" style="color: #ffb703; text-decoration: none;">Proceed to Login</a>
            </div>

            <form id="registerForm">
                <div class="form-group">
                    <label>Sponsor Referral ID</label>
                    <input type="text" name="sponsor_id" id="sponsor_id" class="form-control" value="<?php echo htmlspecialchars($ref); ?>" readonly style="background: rgba(255,255,255,0.1);">
                    <div class="sponsor-name" id="sponsorNameDisplay">Loading Sponsor...</div>
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="Enter your full name">
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" required placeholder="Enter your email">
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" class="form-control" required placeholder="Enter your phone number">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" id="reg_password" class="form-control" required placeholder="Create a password">
                </div>
                <button type="submit" class="btn-register">Register Now</button>
            </form>
            <div id="registerFooter" style="margin-top: 15px; font-size: 14px; color: #a0aec0;">
                Already have an account? <a href="login.php" style="color: #ffb703; text-decoration: none; font-weight: bold;">Login</a>
            </div>
            
            <script>
                const apiURL = "<?php echo $site_url; ?>/UserPanel/api/register.php";

                document.addEventListener('DOMContentLoaded', function() {
                    const sponsorId = document.getElementById('sponsor_id').value;
                    if (sponsorId) {
                        fetch(apiURL + '?action=lookup_sponsor&id=' + encodeURIComponent(sponsorId))
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('sponsorNameDisplay').innerText = data.name;
                            } else {
                                document.getElementById('sponsorNameDisplay').innerText = "Invalid Sponsor";
                                document.getElementById('sponsorNameDisplay').style.color = "#ff4d4d";
                            }
                        })
                        .catch(() => {
                            document.getElementById('sponsorNameDisplay').innerText = "Error checking sponsor";
                        });
                    }
                });

                document.getElementById('registerForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    
                    fetch(apiURL, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('registerForm').style.display = 'none';
                            document.getElementById('registerFooter').style.display = 'none';
                            document.getElementById('successBox').style.display = 'block';
                            document.getElementById('loginProceed').style.display = 'block';
                            
                            const pass = document.getElementById('reg_password').value;
                            document.getElementById('newUserId').innerText = data.user_id;
                            document.getElementById('newUserPass').innerText = pass;
                            
                            const siteUrl = "<?php echo $site_url; ?>/login.php";
                            const msg = `Welcome to <?php echo htmlspecialchars($site_name); ?>! Your User ID is ${data.user_id} and Password is ${pass}. Login at ${siteUrl}`;
                            
                            document.getElementById('actionBtnsContainer').innerHTML = `
                                <button class="btn-action btn-copy" onclick="navigator.clipboard.writeText('User ID: ${data.user_id} | Password: ${pass}'); Swal.fire({icon:'success', title:'Copied', text:'Credentials copied to clipboard!', timer:1500, showConfirmButton:false, background:'#1a1a2e', color:'#fff'});"><i class="fa-solid fa-copy"></i> Copy ID</button>
                                <a href="sms:?body=${encodeURIComponent(msg)}" class="btn-action btn-sms"><i class="fa-solid fa-comment-sms"></i> Share on SMS</a>
                                <a href="https://wa.me/?text=${encodeURIComponent(msg)}" target="_blank" class="btn-action btn-wa"><i class="brands fa-whatsapp"></i> Share on WhatsApp</a>
                            `;
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Registration failed', background: '#1a1a2e', color: '#fff' });
                        }
                    })
                    .catch(err => {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred', background: '#1a1a2e', color: '#fff' });
                    });
                });
            </script>
        <?php endif; ?>
    </div>
</body>
</html>
