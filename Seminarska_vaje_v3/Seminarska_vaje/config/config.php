<?php
// Database configuration
$db_host = 'localhost';
$db_name = 'eprodajalna';
$db_user = 'root';
$db_pass = '';

try {
    // First, create the database if it doesn't exist
    $temp_pdo = new PDO(
        "mysql:host=$db_host;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    $temp_pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Now connect to the specific database
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
} catch (PDOException $e) {
    die("Could not connect to the database $db_name :" . $e->getMessage());
}

// Application configuration
define('BASE_URL', '/Seminarska_vaje/public');
define('ROOT_PATH', dirname(__DIR__));

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
