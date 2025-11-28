<?php
require_once __DIR__ . '/../config.php';
// Aseguramos que todas las redirecciones usen BASE_URL
if($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . '/public/index.php'); exit; }
if(!isset($_SESSION['usuario'])) { header('Location: ' . BASE_URL . '/public/login.php'); exit; }
if(!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) { die('CSRF'); }

$clase_id = (int)($_POST['clase_id'] ?? 0);
$uid = (int)$_SESSION['usuario']['id'];

// 1. OBTENER INFORMACIÓN DE LA CLASE (incluye el cupo máximo)
$stmt = $pdo->prepare('SELECT cupo FROM clases WHERE id = :id');
$stmt->execute(['id'=>$clase_id]);
$cl = $stmt->fetch();
if(!$cl){ header('Location: ' . BASE_URL . '/public/index.php?error=no_clase'); exit; }

// 2. COMPROBAR INSCRIPCIONES EXISTENTES
$stmt_count = $pdo->prepare('SELECT COUNT(id) FROM inscripciones WHERE clase_id = :cid');
$stmt_count->execute(['cid'=>$clase_id]);
$inscritos_actuales = $stmt_count->fetchColumn();

// 3. LOGICA CRÍTICA: COMPROBAR SI HAY CUPOS DISPONIBLES
if ($inscritos_actuales >= $cl['cupo']) {
    header('Location: ' . BASE_URL . '/public/index.php?error=clase_llena');
    exit;
}

// 4. COMPROBAR SI EL ALUMNO YA ESTÁ INSCRITO
$stmt_check = $pdo->prepare('SELECT id FROM inscripciones WHERE usuario_id = :uid AND clase_id = :cid');
$stmt_check->execute(['uid'=>$uid,'cid'=>$clase_id]);
if($stmt_check->fetch()){ header('Location: ' . BASE_URL . '/public/index.php?msg=ya_inscrito'); exit; }

// 5. INSERTAR INSCRIPCIÓN
$pdo->prepare('INSERT INTO inscripciones (usuario_id, clase_id, estado) VALUES (:uid, :cid, "pendiente")')
    ->execute(['uid'=>$uid,'cid'=>$clase_id]);

header('Location: ' . BASE_URL . '/dashboard/alumno.php?msg=inscrito');
exit;
?>