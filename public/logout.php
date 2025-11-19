<?php
require_once __DIR__ . '/../config.php';
session_unset();
session_destroy();
// Redirigir usando BASE_URL
header('Location: ' . BASE_URL . '/public/index.php');
exit;
?>