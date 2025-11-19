<?php
require_once __DIR__ . '/../config.php';
if($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /dashboard/alumno.php'); exit; }
if(!isset($_SESSION['usuario'])) { header('Location: /public/login.php'); exit; }
if(!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) { die('CSRF'); }

$id = (int)$_POST['id'];
$uid = (int)$_SESSION['usuario']['id'];
$pdo->prepare('DELETE FROM inscripciones WHERE id = :id AND usuario_id = :uid')->execute(['id'=>$id,'uid'=>$uid]);
header('Location: /dashboard/alumno.php?msg=cancelado');
exit;
?>