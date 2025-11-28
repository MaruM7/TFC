<?php
require_once __DIR__ . '/../config.php';
if(!isset($_SESSION['usuario'])){ header('Location: ' . BASE_URL . '/public/login.php'); exit; }
if($_SESSION['usuario']['rol'] !== 'alumno'){ header('Location: ' . BASE_URL . '/public/index.php'); exit; }

$uid = $_SESSION['usuario']['id'];

// ranking
$stmt = $pdo->prepare('SELECT r.puntos, d.nombre as disciplina, c.nombre as cinturon FROM ranking r JOIN disciplinas d ON r.disciplina_id = d.id LEFT JOIN cinturones c ON r.cinturon_actual = c.id WHERE r.usuario_id = :uid');
$stmt->execute(['uid'=>$uid]);
$rankings = $stmt->fetchAll();

// inscripciones
// La consulta trae inscripciones, aunque la clase_id sea NULL (si la clase fue eliminada)
$stmt = $pdo->prepare('SELECT i.*, cl.fecha_hora, d.nombre as disciplina FROM inscripciones i LEFT JOIN clases cl ON i.clase_id = cl.id LEFT JOIN disciplinas d ON cl.disciplina_id = d.id WHERE i.usuario_id = :uid ORDER BY i.fecha_inscripcion DESC');
$stmt->execute(['uid'=>$uid]);
$inscripciones = $stmt->fetchAll();

require_once __DIR__ . '/../templates/header.php';
?>
<main class="container">
  <h1>Hola, <?=htmlspecialchars($_SESSION['usuario']['nombre'])?></h1>
  <section>
    <h2>Ranking</h2>
    <?php if($rankings): ?>
      <div class="cards-grid">
        <?php foreach($rankings as $r): ?>
          <div class="card">
            <h3><?=htmlspecialchars($r['disciplina'])?></h3>
            <p>Puntos: <?=htmlspecialchars($r['puntos'])?></p>
            <p>Cinturón: <?=htmlspecialchars($r['cinturon'] ?? '—')?></p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p>No tienes ranking aún.</p>
    <?php endif; ?>
  </section>

  <section style="margin-top:30px">
    <h2>Mis inscripciones</h2>
    <?php if($inscripciones): ?>
      <table class="table">
        <thead><tr><th>Clase</th><th>Fecha de Clase</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach($inscripciones as $ins): ?>
          <tr>
            <td><?= $ins['disciplina'] ? htmlspecialchars($ins['disciplina']) : 'Clase Eliminada' ?></td>
            <td><?= $ins['fecha_hora'] ? htmlspecialchars(date('d/m/Y H:i', strtotime($ins['fecha_hora']))) : 'Indisponible' ?></td>
            <td><?=htmlspecialchars($ins['estado'])?></td>
            <td>
              <?php if($ins['clase_id']): ?>
                <form method="POST" action="<?=BASE_URL?>/public/cancelar_inscripcion.php" style="display:inline">
                  <input type="hidden" name="id" value="<?=htmlspecialchars($ins['id'])?>" />
                  <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'])?>" />
                  <button class="btn-outline" type="submit">Cancelar</button>
                </form>
              <?php else: ?>
                <span style="color:#666;">Eliminada</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No estás apuntado a ninguna clase.</p>
    <?php endif; ?>
  </section>
</main>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>