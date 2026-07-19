<?php
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        $env = getEnvConfig();
        $siteUrl = rtrim($env['SITE_URL'] ?? '', '/');
        header("Location: $siteUrl/UserPanel/login.php");
        exit;
    }
}

function loginUser($pdo, $userId, $password) {
    startSession();
    $stmt = $pdo->prepare("SELECT id, user_id, password, status FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] !== 'active' && $user['status'] !== 'inactive') {
            return ['success' => false, 'message' => 'Account is blocked or suspended.'];
        }
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_db_id'] = $user['id'];
        return ['success' => true];
    }
    return ['success' => false, 'message' => 'Invalid User ID or Password.'];
}

function logoutUser() {
    startSession();
    unset($_SESSION['user_id']);
    unset($_SESSION['user_db_id']);
    if (!isset($_SESSION['admin_logged_in'])) {
        session_destroy();
    }
}

function getCurrentUserId() {
    startSession();
    return $_SESSION['user_id'] ?? null;
}

function getEnvConfig() {
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath)) {
        return parse_ini_file($envPath);
    }
    return [];
}
?>
