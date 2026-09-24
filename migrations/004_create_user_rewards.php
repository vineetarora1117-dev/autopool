<?php
require_once __DIR__ . '/../libs/db.php';

echo "Running Migration 004: Create user_rewards table...\n";

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `user_rewards` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` varchar(20) NOT NULL,
            `level` int(11) NOT NULL,
            `amount` decimal(10,4) NOT NULL DEFAULT 0.0000,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_user_level` (`user_id`, `level`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    echo "Migration 004 completed successfully.\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
