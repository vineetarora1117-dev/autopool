<?php
header('Content-Type: application/json');

$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    echo json_encode(['error' => '.env file not found']);
    exit;
}

$env = parse_ini_file($envPath);
$host = $env['DB_HOST'] ?? '127.0.0.1';
$db   = $env['DB_NAME'] ?? 'autopool_db';
$user = $env['DB_USER'] ?? 'root';
$pass = $env['DB_PASS'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$action = $_POST['action'] ?? '';
$passcode = $_POST['passcode'] ?? '';

if (!$action || !$passcode) {
    echo json_encode(['error' => 'Missing action or passcode']);
    exit;
}

// Verify passcode
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM db_auth WHERE passcode = ?");
    $stmt->execute([$passcode]);
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['error' => 'Invalid passcode']);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Auth table missing or error: ' . $e->getMessage()]);
    exit;
}

$snapFile = __DIR__ . '/snapshot.sql';

// Escape pass if exists
$passArg = $pass ? "-p" . escapeshellarg($pass) : "";

if ($action === 'snap') {
    $cmd = "c:\\xampp\\mysql\\bin\\mysqldump.exe -h " . escapeshellarg($host) . " -u " . escapeshellarg($user) . " $passArg " . escapeshellarg($db) . " > " . escapeshellarg($snapFile);
    
    exec($cmd, $output, $return_var);
    
    if ($return_var === 0) {
        echo json_encode(['success' => true, 'message' => 'Snapshot saved successfully.']);
    } else {
        echo json_encode(['error' => 'Snapshot failed. Return code: ' . $return_var]);
    }
    exit;
}

if ($action === 'restore') {
    if (!file_exists($snapFile)) {
        echo json_encode(['error' => 'No snapshot file found to restore from.']);
        exit;
    }

    $cmd = "c:\\xampp\\mysql\\bin\\mysql.exe -h " . escapeshellarg($host) . " -u " . escapeshellarg($user) . " $passArg " . escapeshellarg($db) . " < " . escapeshellarg($snapFile);
    
    exec($cmd, $output, $return_var);
    
    if ($return_var === 0) {
        echo json_encode(['success' => true, 'message' => 'Database restored successfully!']);
    } else {
        echo json_encode(['error' => 'Restore failed. Return code: ' . $return_var]);
    }
    exit;
}

echo json_encode(['error' => 'Invalid action']);
