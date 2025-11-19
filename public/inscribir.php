<?php
require_once __DIR__ . '/../config.php';
// CORREGIDO: Redirecciones con BASE_URL
if($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . '/public/index.php'); exit; }
if(!isset($_SESSION['usuario'])) { header('Location: ' . BASE_URL . '/public/login.php'); exit; }
if(!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) { die('CSRF'); }

$clase_id = (int)($_POST['clase_id'] ?? 0);
$uid = (int)$_SESSION['usuario']['id'];

$stmt = $pdo->prepare('SELECT cupo FROM clases WHERE id = :id');
$stmt->execute(['id'=>$clase_id]);
$cl = $stmt->fetch();
if(!$cl){ header('Location: ' . BASE_URL . '/public/index.php?error=no_clase'); exit; }

// comprobar si ya inscrito
$stmt = $pdo->prepare('SELECT id FROM inscripciones WHERE usuario_id = :uid AND clase_id = :cid');
$stmt->execute(['uid'=>$uid,'cid'=>$clase_id]);
if($stmt->fetch()){ header('Location: ' . BASE_URL . '/public/index.php?msg=ya_inscrito'); exit; }

$pdo->prepare('INSERT INTO inscripciones (usuario_id, clase_id, estado) VALUES (:uid, :cid, "pendiente")')
    ->execute(['uid'=>$uid,'cid'=>$clase_id]);

header('Location: ' . BASE_URL . '/dashboard/alumno.php?msg=inscrito');
exit;
?>