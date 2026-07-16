<?php
$host = '127.0.0.1';
$db = 'u983618620_test';
$user = 'u983618620_test';
$pass = 'VineetArora@1117';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("ALTER TABLE transactions ADD COLUMN IF NOT EXISTS status ENUM('completed', 'pending') DEFAULT 'completed'");
    echo "Column added successfully";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
