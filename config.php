<?php
// config.php - Ajusta DB_USER y DB_PASS según tu entorno local
session_start();
date_default_timezone_set('Europe/Madrid');

define('DB_HOST','127.0.0.1');
define('DB_NAME','gimnasiodb');
define('DB_USER','root');
define('DB_PASS','');

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}

// CSRF token helper
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}
?>