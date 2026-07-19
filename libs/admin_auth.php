<?php
require_once __DIR__ . '/auth.php';

function isAdminLoggedIn() {
    startSession();
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        $env = getEnvConfig();
        $siteUrl = rtrim($env['SITE_URL'] ?? '', '/');
        header("Location: $siteUrl/AdminPanel/login.php");
        exit;
    }
}

function loginAdmin($pdo, $username, $password) {
    startSession();
    $stmt = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        return ['success' => true];
    }
    return ['success' => false, 'message' => 'Invalid username or password.'];
}

function logoutAdmin() {
    startSession();
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_id']);
    if (!isset($_SESSION['user_id'])) {
        session_destroy();
    }
}

function loginAsUser($pdo, $targetUserId) {
    startSession();
    if (!isAdminLoggedIn()) {
        return ['success' => false, 'message' => 'Unauthorized'];
    }
    
    $stmt = $pdo->prepare("SELECT id, user_id FROM users WHERE user_id = ?");
    $stmt->execute([$targetUserId]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_db_id'] = $user['id'];
        $_SESSION['impersonating'] = true;
        return ['success' => true];
    }
    return ['success' => false, 'message' => 'User not found.'];
}
?>
