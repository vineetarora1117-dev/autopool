<?php
header('Content-Type: application/json');

// $host = '127.0.0.1';
// $db   = 'autopool_db';
// $user = 'root';
// $pass = '';


$host = '127.0.0.1';
$db = 'u983618620_test';
$user = 'u983618620_test';
$pass = 'VineetArora@1117';


try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

function getRandomName()
{
    $namesJson = file_get_contents('names.json');
    $data = json_decode($namesJson, true);

    $first = $data['first_names'][array_rand($data['first_names'])];
    $last = $data['surnames'][array_rand($data['surnames'])];

    return "$first $last";
}

if ($action === 'start_simulation') {
    // Reset the database
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE transactions; TRUNCATE TABLE users; SET FOREIGN_KEY_CHECKS = 1;");

    $name = getRandomName();

    $stmt = $pdo->prepare("INSERT INTO users (name, sponsor_id, upline_id, position, total_earnings) VALUES (?, NULL, NULL, NULL, 0.00)");
    $stmt->execute([$name]);

    echo json_encode(['success' => true, 'message' => 'Simulation started. Root user created.']);
    exit;
}

if ($action === 'add_user') {
    $sponsor_id = isset($_POST['sponsor_id']) ? (int) $_POST['sponsor_id'] : 0;

    if (!$sponsor_id) {
        echo json_encode(['error' => 'Sponsor ID required.']);
        exit;
    }

    // 1. Find the lowest available ID with < 2 downlines
    $stmt = $pdo->query("
        SELECT u.id, 
               (SELECT COUNT(*) FROM users WHERE upline_id = u.id) as downlines_count
        FROM users u 
        HAVING downlines_count < 2 
        ORDER BY u.id ASC 
        LIMIT 1
    ");
    $upline = $stmt->fetch();

    if (!$upline) {
        echo json_encode(['error' => 'Could not find a valid upline.']);
        exit;
    }

    $upline_id = $upline['id'];
    $downlines_count = $upline['downlines_count'];
    $position = ($downlines_count == 0) ? 'left' : 'right';

    // 2. Create the new user
    $name = getRandomName();

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO users (name, sponsor_id, upline_id, position, total_earnings) VALUES (?, ?, ?, ?, 0.00)");
        $stmt->execute([$name, $sponsor_id, $upline_id, $position]);
        $new_user_id = $pdo->lastInsertId();

        // 3. Sponsor Distribution ($5)
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, from_user_id, amount, type, level) VALUES (?, ?, ?, 'sponsor', 0)");
        $stmt->execute([$sponsor_id, $new_user_id, 5.0000]);

        $pdo->prepare("UPDATE users SET total_earnings = total_earnings + 5.0000 WHERE id = ?")->execute([$sponsor_id]);

        // 4. Autopool Distribution ($4 over 8 levels)
        $current_upline = $upline_id;
        for ($level = 1; $level <= 8; $level++) {
            if (!$current_upline)
                break;

            $amount = ($level <= 4) ? 0.1250 : 0.8750;

            // Check if the current upline has a complete pair in the binary tree
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE upline_id = ?");
            $stmt->execute([$current_upline]);
            $children_count = $stmt->fetchColumn();

            if ($children_count >= 2) {
                // Insert transaction
                $stmt = $pdo->prepare("INSERT INTO transactions (user_id, from_user_id, amount, type, level) VALUES (?, ?, ?, 'autopool', ?)");
                $stmt->execute([$current_upline, $new_user_id, $amount, $level]);

                // Update earnings
                $pdo->prepare("UPDATE users SET total_earnings = total_earnings + ? WHERE id = ?")->execute([$amount, $current_upline]);
            }

            // Fetch next upline
            $stmt = $pdo->prepare("SELECT upline_id FROM users WHERE id = ?");
            $stmt->execute([$current_upline]);
            $parent = $stmt->fetch();
            $current_upline = $parent ? $parent['upline_id'] : null;
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'new_user_id' => $new_user_id, 'name' => $name, 'upline_id' => $upline_id]);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['error' => 'Transaction failed: ' . $e->getMessage()]);
    }

    exit;
}

if ($action === 'get_tree') {
    $stmt = $pdo->query("SELECT id, name, sponsor_id, upline_id, position, total_earnings FROM users ORDER BY id ASC");
    $users = $stmt->fetchAll();

    echo json_encode(['success' => true, 'users' => $users]);
    exit;
}

if ($action === 'get_statement') {
    $user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;

    if (!$user_id) {
        echo json_encode(['error' => 'User ID required.']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT t.*, u.name as from_name 
        FROM transactions t
        LEFT JOIN users u ON t.from_user_id = u.id
        WHERE t.user_id = ?
        ORDER BY t.created_at DESC, t.id DESC
    ");
    $stmt->execute([$user_id]);
    $transactions = $stmt->fetchAll();

    echo json_encode(['success' => true, 'transactions' => $transactions]);
    exit;
}

echo json_encode(['error' => 'Invalid action']);
