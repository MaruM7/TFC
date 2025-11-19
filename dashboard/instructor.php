<?php
require_once __DIR__ . '/../config.php';
// CORREGIDO: Redirecciones con BASE_URL
if(!isset($_SESSION['usuario'])){ header('Location: ' . BASE_URL . '/public/login.php'); exit; }
if($_SESSION['usuario']['rol'] !== 'instructor'){ header('Location: ' . BASE_URL . '/public/index.php'); exit; }
$uid = $_SESSION['usuario']['id'];

$stmt = $pdo->prepare('SELECT cl.*, d.nombre as disciplina FROM clases cl JOIN disciplinas d ON cl.disciplina_id = d.id WHERE cl.instructor_id = :uid ORDER BY cl.fecha_hora DESC');
$stmt->execute(['uid'=>$uid]);
$clases = $stmt->fetchAll();

require_once __DIR__ . '/../templates/header.php';
?>
<main class="container">
  <h1>Panel Instructor</h1>
  <section>
    <h2>Mis clases</h2>
    <div class="cards-grid">
      <?php foreach($clases as $c): ?>
        <div class="card">
          <h3><?=htmlspecialchars($c['disciplina'])?></h3>
          <p><?=htmlspecialchars($c['descripcion'])?></p>
          <p><?=htmlspecialchars(date('d/m/Y H:i', strtotime($c['fecha_hora'])))?></p>
          <p>Cupo: <?=htmlspecialchars($c['cupo'])?></p>
          <a class="btn-outline" href="<?=BASE_URL?>/admin/clases.php?clase_id=<?=htmlspecialchars($c['id'])?>">Ver inscritos</a>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>