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

if (!function_exists('getSiteUrl')) {
    function getSiteUrl() {
        $envPath = __DIR__ . '/../.env';
        $envVars = file_exists($envPath) ? parse_ini_file($envPath) : [];
        $envSiteUrl = rtrim($envVars['SITE_URL'] ?? '', '/');
        
        if (isset($_SERVER['HTTP_HOST'])) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'];
            
            $scriptDir = $_SERVER['SCRIPT_NAME'] ?? '';
            $baseDir = preg_replace('#/(UserPanel|AdminPanel|libs|tests|api).*$#i', '', $scriptDir);
            $baseDir = rtrim($baseDir, '/');

            $dynamicSiteUrl = $protocol . $host . $baseDir;

            if (!empty($envSiteUrl)) {
                $envHost = parse_url($envSiteUrl, PHP_URL_HOST);
                if ($envHost === $host) {
                    return $envSiteUrl;
                }
            }
            return $dynamicSiteUrl;
        }
        
        return !empty($envSiteUrl) ? $envSiteUrl : 'http://localhost/autopool';
    }
}
?>
