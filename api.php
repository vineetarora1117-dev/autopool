<?php
header('Content-Type: application/json');

// Load environment variables
$env = parse_ini_file(__DIR__ . '/.env');

$host = $env['DB_HOST'] ?? '127.0.0.1';
$db   = $env['DB_NAME'] ?? 'autopool_db';
$user = $env['DB_USER'] ?? 'root';
$pass = $env['DB_PASS'] ?? '';


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
    try {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE transactions; TRUNCATE TABLE users; TRUNCATE TABLE user_rewards; SET FOREIGN_KEY_CHECKS = 1;");
    } catch (PDOException $e) {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE transactions; TRUNCATE TABLE users; SET FOREIGN_KEY_CHECKS = 1;");
    }
    
    try {
        $pdo->exec("TRUNCATE TABLE company_wallet;");
    } catch (PDOException $e) { }

    // Automatically add the status and reward_level columns if they are missing
    try {
        $pdo->exec("ALTER TABLE transactions ADD COLUMN status ENUM('completed', 'pending') DEFAULT 'completed'");
        $pdo->exec("ALTER TABLE transactions ADD COLUMN blocked_by_user_id INT DEFAULT NULL");
    } catch (PDOException $e) { }

    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN reward_level INT DEFAULT 0");
    } catch (PDOException $e) { }

    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN sponsor_team_size INT DEFAULT 0");
    } catch (PDOException $e) { }

    // Create company wallet table if missing
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS company_wallet (
            id INT AUTO_INCREMENT PRIMARY KEY,
            from_user_id INT NOT NULL,
            amount DECIMAL(10, 4) NOT NULL,
            type ENUM('level', 'reward', 'other') NOT NULL,
            level INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (from_user_id) REFERENCES users(id)
        )
    ");

    // Create reward_targets table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS reward_targets (
            level INT PRIMARY KEY,
            strong_leg_target INT NOT NULL,
            other_legs_target INT NOT NULL,
            reward_amount DECIMAL(10, 4) NOT NULL
        )
    ");

    // Seed reward_targets table
    $targets = [
        [1, 15, 15, 15.0000],
        [2, 18, 18, 3.0000],
        [3, 24, 24, 6.0000],
        [4, 36, 36, 12.0000],
        [5, 60, 60, 24.0000],
        [6, 108, 108, 48.0000],
        [7, 204, 204, 96.0000],
        [8, 396, 396, 192.0000],
        [9, 780, 780, 384.0000],
        [10, 1548, 1548, 768.0000]
    ];
    $stmt = $pdo->prepare("INSERT INTO reward_targets (level, strong_leg_target, other_legs_target, reward_amount) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE strong_leg_target = VALUES(strong_leg_target), other_legs_target = VALUES(other_legs_target), reward_amount = VALUES(reward_amount)");
    foreach ($targets as $target) {
        $stmt->execute($target);
    }

    // Create user_rewards table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_rewards (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            level INT NOT NULL,
            amount DECIMAL(10, 4) NOT NULL,
            achieved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            UNIQUE KEY unique_user_level (user_id, level)
        )
    ");

    $name = getRandomName();

    $stmt = $pdo->prepare("INSERT INTO users (name, sponsor_id, upline_id, position, total_earnings) VALUES (?, NULL, NULL, NULL, 0.00)");
    $stmt->execute([$name]);

    echo json_encode(['success' => true, 'message' => 'Simulation started. Root user created.']);
    exit;
}

if ($action === 'clear_db') {
    try {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE transactions; TRUNCATE TABLE users; TRUNCATE TABLE user_rewards; SET FOREIGN_KEY_CHECKS = 1;");
    } catch (PDOException $e) {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE transactions; TRUNCATE TABLE users; SET FOREIGN_KEY_CHECKS = 1;");
    }
    try { $pdo->exec("TRUNCATE TABLE company_wallet;"); } catch (PDOException $e) { }
    try {
        $pdo->exec("ALTER TABLE transactions ADD COLUMN status ENUM('completed', 'pending') DEFAULT 'completed'");
        $pdo->exec("ALTER TABLE transactions ADD COLUMN blocked_by_user_id INT DEFAULT NULL");
    } catch (PDOException $e) { }
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN reward_level INT DEFAULT 0");
    } catch (PDOException $e) { }
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN sponsor_team_size INT DEFAULT 0");
    } catch (PDOException $e) { }

    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS db_auth (
                id INT AUTO_INCREMENT PRIMARY KEY,
                passcode VARCHAR(50) NOT NULL
            )
        ");
        $stmt = $pdo->query("SELECT COUNT(*) FROM db_auth");
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO db_auth (passcode) VALUES ('000')");
        }
    } catch (PDOException $e) { }
    
    echo json_encode(['success' => true]);
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

        // 3. Sponsor Distribution ($5) & Reward Level Update
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, from_user_id, amount, type, level) VALUES (?, ?, ?, 'sponsor', 0)");
        $stmt->execute([$sponsor_id, $new_user_id, 5.0000]);

        $pdo->prepare("UPDATE users SET total_earnings = total_earnings + 5.0000 WHERE id = ?")->execute([$sponsor_id]);

        // Update sponsor's reward level
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE sponsor_id = ?");
        $stmt->execute([$sponsor_id]);
        $sponsor_directs = $stmt->fetchColumn();
        
        $new_reward_level = min(10, floor($sponsor_directs / 2));
        $pdo->prepare("UPDATE users SET reward_level = ? WHERE id = ? AND reward_level < ?")->execute([$new_reward_level, $sponsor_id, $new_reward_level]);

        // Increment sponsor_team_size recursively for all sponsors up the chain and check rewards
        $temp_sponsor = $sponsor_id;
        while ($temp_sponsor) {
            $pdo->prepare("UPDATE users SET sponsor_team_size = sponsor_team_size + 1 WHERE id = ?")->execute([$temp_sponsor]);
            
            // Check reward targets for this sponsor
            $stmt_refs = $pdo->prepare("SELECT (sponsor_team_size + 1) as leg_size FROM users WHERE sponsor_id = ?");
            $stmt_refs->execute([$temp_sponsor]);
            $leg_sizes = $stmt_refs->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($leg_sizes) > 0) {
                rsort($leg_sizes);
                $strong_leg = (int) $leg_sizes[0];
                $other_legs = (int) array_sum(array_slice($leg_sizes, 1));
                
                // Fetch unachieved reward targets
                $stmt_targets = $pdo->prepare("
                    SELECT rt.* 
                    FROM reward_targets rt
                    LEFT JOIN user_rewards ur ON ur.user_id = ? AND ur.level = rt.level
                    WHERE ur.id IS NULL
                    ORDER BY rt.level ASC
                ");
                $stmt_targets->execute([$temp_sponsor]);
                $pending_rewards = $stmt_targets->fetchAll();
                
                foreach ($pending_rewards as $target) {
                    if ($strong_leg >= $target['strong_leg_target'] && $other_legs >= $target['other_legs_target']) {
                        // Qualify for the reward!
                        $stmt_insert_reward = $pdo->prepare("INSERT IGNORE INTO user_rewards (user_id, level, amount) VALUES (?, ?, ?)");
                        $stmt_insert_reward->execute([$temp_sponsor, $target['level'], $target['reward_amount']]);
                        
                        if ($stmt_insert_reward->rowCount() > 0) {
                            // Insert completed transaction of type 'reward'
                            $stmt_tx = $pdo->prepare("INSERT INTO transactions (user_id, from_user_id, amount, type, level, status) VALUES (?, ?, ?, 'reward', ?, 'completed')");
                            $stmt_tx->execute([$temp_sponsor, $new_user_id, $target['reward_amount'], $target['level']]);
                            
                            // Update total earnings
                            $pdo->prepare("UPDATE users SET total_earnings = total_earnings + ? WHERE id = ?")->execute([$target['reward_amount'], $temp_sponsor]);
                        }
                    } else {
                        break; // Sorted by level, so if they don't qualify here, they won't qualify for higher levels
                    }
                }
            }
            
            // Go to parent sponsor
            $stmt_parent = $pdo->prepare("SELECT sponsor_id FROM users WHERE id = ?");
            $stmt_parent->execute([$temp_sponsor]);
            $temp_sponsor = $stmt_parent->fetchColumn();
        }

        // Check if the upline just completed a pair (downlines count became 2)
        if ($downlines_count == 1) {
            // Release pending transactions blocked by this upline
            $stmt = $pdo->prepare("SELECT id, amount, user_id FROM transactions WHERE blocked_by_user_id = ? AND status = 'pending'");
            $stmt->execute([$upline_id]);
            $pending_txs = $stmt->fetchAll();

            foreach ($pending_txs as $tx) {
                // Update transaction status
                $pdo->prepare("UPDATE transactions SET status = 'completed' WHERE id = ?")->execute([$tx['id']]);
                // Add earnings
                $pdo->prepare("UPDATE users SET total_earnings = total_earnings + ? WHERE id = ?")->execute([$tx['amount'], $tx['user_id']]);
            }
        }

        // 4. Autopool Distribution ($4 over 8 levels)
        $current_upline = $upline_id;
        $current_blocker = null;
        
        for ($level = 1; $level <= 8; $level++) {
            if (!$current_upline)
                break;

            $amount = ($level <= 4) ? 0.1250 : 0.8750;

            // Check if the current upline has a complete pair in the binary tree
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE upline_id = ?");
            $stmt->execute([$current_upline]);
            $children_count = $stmt->fetchColumn();

            if ($children_count < 2) {
                $current_blocker = $current_upline;
            }

            if ($current_blocker !== null) {
                // Insert pending transaction
                $stmt = $pdo->prepare("INSERT INTO transactions (user_id, from_user_id, amount, type, level, status, blocked_by_user_id) VALUES (?, ?, ?, 'autopool', ?, 'pending', ?)");
                $stmt->execute([$current_upline, $new_user_id, $amount, $level, $current_blocker]);
            } else {
                // Insert completed transaction
                $stmt = $pdo->prepare("INSERT INTO transactions (user_id, from_user_id, amount, type, level, status) VALUES (?, ?, ?, 'autopool', ?, 'completed')");
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

        // 5. Level Income Distribution ($1 over 10 levels = $0.10 per level)
        $current_sponsor = $sponsor_id;
        $level_amount = 0.1000;
        $current_level_blocker = null;
        
        for ($lvl = 1; $lvl <= 10; $lvl++) {
            $qualifying_user = null; // null means flushed to company wallet

            if ($current_sponsor) {
                // Check if they have a complete binary pair (Gateway logic)
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE upline_id = ?");
                $stmt->execute([$current_sponsor]);
                $children_count = $stmt->fetchColumn();
                
                if ($children_count < 2) {
                    $current_level_blocker = $current_sponsor;
                }

                // Check if sponsor's reward level qualifies them for this level
                $stmt = $pdo->prepare("SELECT reward_level FROM users WHERE id = ?");
                $stmt->execute([$current_sponsor]);
                $reward_level = $stmt->fetchColumn();

                if ($reward_level >= $lvl) {
                    $qualifying_user = $current_sponsor;
                }
            }

            if ($qualifying_user) {
                if ($current_level_blocker !== null) {
                    // Insert pending transaction for the qualified sponsor
                    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, from_user_id, amount, type, level, status, blocked_by_user_id) VALUES (?, ?, ?, 'level', ?, 'pending', ?)");
                    $stmt->execute([$qualifying_user, $new_user_id, $level_amount, $lvl, $current_level_blocker]);
                } else {
                    // Insert completed transaction
                    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, from_user_id, amount, type, level, status) VALUES (?, ?, ?, 'level', ?, 'completed')");
                    $stmt->execute([$qualifying_user, $new_user_id, $level_amount, $lvl]);

                    // Update earnings for the receiving user
                    $pdo->prepare("UPDATE users SET total_earnings = total_earnings + ? WHERE id = ?")->execute([$level_amount, $qualifying_user]);
                }
            } else {
                // Flush to company wallet
                $stmt = $pdo->prepare("INSERT INTO company_wallet (from_user_id, amount, type, level) VALUES (?, ?, 'level', ?)");
                $stmt->execute([$new_user_id, $level_amount, $lvl]);
            }

            // Fetch next sponsor
            if ($current_sponsor) {
                $stmt = $pdo->prepare("SELECT sponsor_id FROM users WHERE id = ?");
                $stmt->execute([$current_sponsor]);
                $parent = $stmt->fetch();
                $current_sponsor = $parent ? $parent['sponsor_id'] : null;
            }
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

function getDownlinesRecursive($pdo, $upline_id, $current_level = 1) {
    $stmt = $pdo->prepare("SELECT id, name, upline_id, position, total_earnings, created_at FROM users WHERE upline_id = ?");
    $stmt->execute([$upline_id]);
    $children = $stmt->fetchAll();
    
    $all_downlines = [];
    foreach ($children as $child) {
        $child['level'] = $current_level;
        $all_downlines[] = $child;
        $child_downlines = getDownlinesRecursive($pdo, $child['id'], $current_level + 1);
        $all_downlines = array_merge($all_downlines, $child_downlines);
    }
    return $all_downlines;
}

function getSponsorDownlinesRecursive($pdo, $sponsor_id, $current_level = 1) {
    $stmt = $pdo->prepare("SELECT id, name, sponsor_id, sponsor_team_size, reward_level, created_at FROM users WHERE sponsor_id = ?");
    $stmt->execute([$sponsor_id]);
    $referrals = $stmt->fetchAll();
    
    $all_downlines = [];
    foreach ($referrals as $ref) {
        $ref['level'] = $current_level;
        $all_downlines[] = $ref;
        $child_downlines = getSponsorDownlinesRecursive($pdo, $ref['id'], $current_level + 1);
        $all_downlines = array_merge($all_downlines, $child_downlines);
    }
    return $all_downlines;
}

if ($action === 'get_network_and_rewards') {
    $user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;

    if (!$user_id) {
        echo json_encode(['error' => 'User ID required.']);
        exit;
    }

    // 1. Get user name
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_name = $stmt->fetchColumn() ?: "Unknown User";

    // 2. Get recursive sponsor referrals (sponsor downlines)
    $referrals = getSponsorDownlinesRecursive($pdo, $user_id);

    // 3. Get reward targets
    $stmt = $pdo->query("SELECT * FROM reward_targets ORDER BY level ASC");
    $reward_targets = $stmt->fetchAll();

    // 4. Get achieved rewards
    $stmt = $pdo->prepare("SELECT level, achieved_at FROM user_rewards WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $achieved_rewards_raw = $stmt->fetchAll();
    $achieved_levels = [];
    foreach ($achieved_rewards_raw as $ar) {
        $achieved_levels[(int)$ar['level']] = $ar['achieved_at'];
    }

    // 5. Calculate strong leg and other legs sizes
    $stmt_refs = $pdo->prepare("SELECT id, name, (sponsor_team_size + 1) as leg_size FROM users WHERE sponsor_id = ?");
    $stmt_refs->execute([$user_id]);
    $legs = $stmt_refs->fetchAll();
    
    $strong_leg = 0;
    $strong_leg_id = null;
    $strong_leg_name = "N/A";
    $other_legs = 0;
    
    if (count($legs) > 0) {
        usort($legs, function($a, $b) {
            return $b['leg_size'] - $a['leg_size'];
        });
        
        $strong_leg = (int) $legs[0]['leg_size'];
        $strong_leg_id = (int) $legs[0]['id'];
        $strong_leg_name = $legs[0]['name'];
        
        for ($i = 1; $i < count($legs); $i++) {
            $other_legs += (int)$legs[$i]['leg_size'];
        }
    }

    echo json_encode([
        'success' => true,
        'user_name' => $user_name,
        'referrals' => $referrals,
        'strong_leg' => $strong_leg,
        'strong_leg_id' => $strong_leg_id,
        'strong_leg_name' => $strong_leg_name,
        'other_legs' => $other_legs,
        'reward_targets' => $reward_targets,
        'achieved_levels' => (object)$achieved_levels
    ]);
    exit;
}

echo json_encode(['error' => 'Invalid action']);
