<?php
require_once __DIR__ . '/../../libs/db.php';
require_once __DIR__ . '/../../libs/helpers.php';
require_once __DIR__ . '/../../libs/mailer.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'lookup_sponsor') {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        echo json_encode(['success' => false, 'message' => 'Invalid method']);
        exit;
    }
    $sponsorId = trim($_GET['id'] ?? '');
    if (!$sponsorId) {
        echo json_encode(['success' => false, 'message' => 'Sponsor ID is required']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("SELECT name FROM users WHERE user_id = ?");
        $stmt->execute([$sponsorId]);
        $user = $stmt->fetch();
        if ($user) {
            echo json_encode(['success' => true, 'name' => $user['name']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid Sponsor ID']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$sponsorId = trim($_POST['sponsor_id'] ?? '');
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';

if (!$sponsorId || !$name || !$email || !$phone || !$password) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Check sponsor
    $stmt = $pdo->prepare("SELECT id FROM users WHERE user_id = ?");
    $stmt->execute([$sponsorId]);
    if (!$stmt->fetch()) {
        throw new Exception("Invalid Sponsor ID");
    }

    $userId = generateUserId($pdo);
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("
        INSERT INTO users (user_id, sponsor_id, name, email, phone, password, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'Inactive')
    ");
    $stmt->execute([$userId, $sponsorId, $name, $email, $phone, $hashedPassword]);

    $stmt = $pdo->prepare("INSERT INTO user_financial_summary (user_id) VALUES (?)");
    $stmt->execute([$userId]);

    $stmt = $pdo->prepare("
        UPDATE user_financial_summary 
        SET direct_team_count = direct_team_count + 1, 
            total_inactive_team_count = total_inactive_team_count + 1 
        WHERE user_id = ?
    ");
    $stmt->execute([$sponsorId]);

    // Send email (ignore errors for email)
    try {
        if (function_exists('sendMail')) {
            $body = "<h1>Welcome to SAPG!</h1><p>Your Username: $userId</p><p>Your Password: $password</p>";
            sendMail($email, $name, "Welcome to SAPG - Registration Successful", $body);
        }
    } catch (Exception $e) {}

    $pdo->commit();
    echo json_encode([
        'success' => true, 
        'message' => 'Registration successful', 
        'data' => ['user_id' => $userId, 'password' => $password]
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
