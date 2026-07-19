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

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$snapFile = __DIR__ . '/empty_state.sql';
$passArg = $pass ? "-p" . escapeshellarg($pass) : "";

if ($action === 'status') {
    if (file_exists($snapFile)) {
        echo json_encode([
            'success' => true,
            'exists' => true,
            'size' => filesize($snapFile),
            'formatted_size' => round(filesize($snapFile) / 1024, 2) . ' KB',
            'modified' => date("Y-m-d H:i:s", filemtime($snapFile))
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'exists' => false
        ]);
    }
    exit;
}

if ($action === 'store') {
    $cmd = "c:\\xampp\\mysql\\bin\\mysqldump.exe -h " . escapeshellarg($host) . " -u " . escapeshellarg($user) . " $passArg " . escapeshellarg($db) . " > " . escapeshellarg($snapFile);
    exec($cmd, $output, $return_var);
    
    if ($return_var === 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Empty State stored successfully.',
            'size' => round(filesize($snapFile) / 1024, 2) . ' KB',
            'modified' => date("Y-m-d H:i:s", filemtime($snapFile))
        ]);
    } else {
        echo json_encode(['error' => 'Store failed. Return code: ' . $return_var]);
    }
    exit;
}

if ($action === 'restore') {
    if (!file_exists($snapFile)) {
        echo json_encode(['error' => 'No Empty State file found. Please store a state first.']);
        exit;
    }

    $cmd = "c:\\xampp\\mysql\\bin\\mysql.exe -h " . escapeshellarg($host) . " -u " . escapeshellarg($user) . " $passArg " . escapeshellarg($db) . " < " . escapeshellarg($snapFile);
    exec($cmd, $output, $return_var);
    
    if ($return_var === 0) {
        echo json_encode(['success' => true, 'message' => 'Database restored to Empty State successfully.']);
    } else {
        echo json_encode(['error' => 'Restore failed. Return code: ' . $return_var]);
    }
    exit;
}

echo json_encode(['error' => 'Invalid action']);
