<?php
require_once __DIR__ . '/../config.php';
// CORREGIDO: Redirecciones con BASE_URL
if($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . '/dashboard/alumno.php'); exit; }
if(!isset($_SESSION['usuario'])) { header('Location: ' . BASE_URL . '/public/login.php'); exit; }
if(!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) { die('CSRF'); }

$id = (int)$_POST['id'];
$uid = (int)$_SESSION['usuario']['id'];
$pdo->prepare('DELETE FROM inscripciones WHERE id = :id AND usuario_id = :uid')->execute(['id'=>$id,'uid'=>$uid]);

header('Location: ' . BASE_URL . '/dashboard/alumno.php?msg=cancelado');
exit;
?>