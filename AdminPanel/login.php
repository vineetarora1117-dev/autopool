<?php
require_once 'includes/header.php';
?>
<style>
    .login-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        width: 100vw;
    }
    .login-box {
        background: #061121;
        padding: 40px;
        border-radius: 8px;
        border: 1px solid #ffb703;
        width: 100%;
        max-width: 400px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    }
    .login-box h2 {
        text-align: center;
        color: #ffb703;
        margin-bottom: 20px;
    }
    .btn-block { width: 100%; margin-top: 20px; padding: 12px; font-size: 16px; }
</style>
<div class="login-container">
    <div class="login-box">
        <h2>Admin Login</h2>
        <form id="loginForm">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-gold btn-block">Login</button>
        </form>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'login');
    
    fetch('api/auth.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            window.location.href = 'index.php';
        } else {
            Swal.fire({icon: 'error', title: 'Error', text: data.message, background: '#061121', color: '#fff'});
        }
    });
});
</script>
<?php require_once 'includes/footer.php'; ?>
