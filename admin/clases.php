<?php
require_once __DIR__ . '/../config.php';
if(!isset($_SESSION['usuario']) || ($_SESSION['usuario']['rol'] !== 'admin' && $_SESSION['usuario']['rol'] !== 'instructor')){ header('Location: /public/index.php'); exit; }

$clase_id = (int)($_GET['clase_id'] ?? 0);

if(!$clase_id){
  // lista de clases
  $stmt = $pdo->query('SELECT cl.*, d.nombre as disciplina FROM clases cl JOIN disciplinas d ON cl.disciplina_id = d.id ORDER BY fecha_hora DESC');
  $clases = $stmt->fetchAll();
} else {
  // inscritos
  $stmt = $pdo->prepare('SELECT i.*, u.nombre, u.apellidos, u.email, cl.fecha_hora FROM inscripciones i JOIN usuarios u ON i.usuario_id = u.id JOIN clases cl ON i.clase_id = cl.id WHERE i.clase_id = :cid');
  $stmt->execute(['cid'=>$clase_id]);
  $ins = $stmt->fetchAll();
  $clInfo = $pdo->prepare('SELECT cl.*, d.nombre as disciplina FROM clases cl JOIN disciplinas d ON cl.disciplina_id = d.id WHERE cl.id = :id');
  $clInfo->execute(['id'=>$clase_id]);
  $cldata = $clInfo->fetch();
}

// marcar asistencia (POST)
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'marcar'){
  if(!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) die('CSRF');
  $uid = (int)$_POST['usuario_id'];
  $cid = (int)$_POST['clase_id'];
  // insertar asistencia si no existe
  $insCheck = $pdo->prepare('SELECT id FROM asistencia WHERE usuario_id = :uid AND clase_id = :cid');
  $insCheck->execute(['uid'=>$uid,'cid'=>$cid]);
  if(!$insCheck->fetch()){
    // puntos por asistencia desde configuracion
    $cfg = $pdo->prepare('SELECT valor FROM configuracion WHERE clave = :k LIMIT 1');
    $cfg->execute(['k'=>'puntos_por_asistencia']);
    $p = (int)$cfg->fetchColumn();
    $pdo->prepare('INSERT INTO asistencia (clase_id, usuario_id, presente, puntos_otorgados) VALUES (:cid, :uid, 1, :pts)')->execute(['cid'=>$cid,'uid'=>$uid,'pts'=>$p]);
    // actualizar ranking (sumar puntos)
    // comprobar si hay entrada en ranking
    $rk = $pdo->prepare('SELECT id FROM ranking WHERE usuario_id = :uid AND disciplina_id = (SELECT disciplina_id FROM clases WHERE id = :cid) LIMIT 1');
    $rk->execute(['uid'=>$uid,'cid'=>$cid]);
    $rkRow = $rk->fetch();
    if($rkRow){
      $pdo->prepare('UPDATE ranking SET puntos = puntos + :pts WHERE id = :rid')->execute(['pts'=>$p,'rid'=>$rkRow['id']]);
    } else {
      // crear ranking si no existe
      $disc = $pdo->prepare('SELECT disciplina_id FROM clases WHERE id = :cid LIMIT 1'); $disc->execute(['cid'=>$cid]); $did = $disc->fetchColumn();
      $pdo->prepare('INSERT INTO ranking (usuario_id, disciplina_id, puntos) VALUES (:uid, :did, :pts)')->execute(['uid'=>$uid,'did'=>$did,'pts'=>$p]);
    }
  }
  header('Location: clases.php?clase_id=' . $cid);
  exit;
}

require_once __DIR__ . '/../templates/header.php';
?>
<main class="container">
  <h1>Gestión de Clases</h1>
  <?php if(!$clase_id): ?>
    <h2>Clases</h2>
    <div class="cards-grid">
      <?php foreach($clases as $c): ?>
        <div class="card">
          <h3><?=htmlspecialchars($c['disciplina'])?></h3>
          <p><?=htmlspecialchars($c['descripcion'])?></p>
          <p><?=htmlspecialchars(date('d/m/Y H:i', strtotime($c['fecha_hora'])))?></p>
          <a class="btn-outline" href="clases.php?clase_id=<?=htmlspecialchars($c['id'])?>">Ver inscritos</a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <h2>Inscritos en <?=htmlspecialchars($cldata['disciplina'])?> (<?=htmlspecialchars(date('d/m/Y H:i', strtotime($cldata['fecha_hora'])))?>)</h2>
    <?php if($ins): ?>
      <table class="table">
        <thead><tr><th>Nombre</th><th>Email</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach($ins as $i): ?>
          <tr>
            <td><?=htmlspecialchars($i['nombre']).' '.htmlspecialchars($i['apellidos'])?></td>
            <td><?=htmlspecialchars($i['email'])?></td>
            <td><?=htmlspecialchars($i['estado'])?></td>
            <td>
              <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="marcar" />
                <input type="hidden" name="usuario_id" value="<?=htmlspecialchars($i['usuario_id'])?>" />
                <input type="hidden" name="clase_id" value="<?=htmlspecialchars($clase_id)?>" />
                <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'])?>" />
                <button class="btn-primary" type="submit">Marcar presente</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No hay inscritos.</p>
    <?php endif; ?>
  <?php endif; ?>
</main>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
