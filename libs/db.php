<?php
$envPath = __DIR__ . '/../.env';
$envVars = file_exists($envPath) ? parse_ini_file($envPath) : [];

$host = $envVars['DB_HOST'] ?? 'localhost';
$db   = $envVars['DB_NAME'] ?? 'SAPG';
$user = $envVars['DB_USER'] ?? 'root';
$pass = $envVars['DB_PASS'] ?? '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
