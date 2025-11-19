<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../templates/header.php';

// obtener próximas clases
$stmt = $pdo->prepare("SELECT cl.*, d.nombre as disciplina FROM clases cl JOIN disciplinas d ON cl.disciplina_id = d.id WHERE cl.fecha_hora >= NOW() ORDER BY cl.fecha_hora ASC LIMIT 12");
$stmt->execute();
$clases = $stmt->fetchAll();

?>
<main>
  <section class="hero">
    <div class="hero-inner container">
      <h1>Entrena. Compite. Mejora.</h1>
      <p>Gestiona tus clases, sigue tu ranking y participa en eventos — paneles según tu rol.</p>
      <div class="cta-group">
        <?php if(!isset($_SESSION['usuario'])): ?>
          <a class="btn-primary" href="/public/login.php">Iniciar sesión / Registrarse</a>
        <?php else: ?>
          <a class="btn-primary" href="/dashboard/" >Ir al panel</a>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section id="clases" class="cards-section container">
    <h2>Próximas clases</h2>
    <div class="cards-grid">
      <?php foreach($clases as $c): ?>
        <article class="card">
          <h3><?=htmlspecialchars($c['disciplina'])?></h3>
          <p><?=htmlspecialchars($c['descripcion'] ?? '')?></p>
          <p><strong><?=date('d/m/Y H:i', strtotime($c['fecha_hora']))?></strong> · <?=htmlspecialchars($c['duracion'])?> min</p>
          <p>Cupo: <?=htmlspecialchars($c['cupo'])?></p>
          <?php if(isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'alumno'): ?>
            <form method="POST" action="inscribir.php">
              <input type="hidden" name="clase_id" value="<?=htmlspecialchars($c['id'])?>">
              <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'])?>">
              <button class="btn-outline" type="submit">Inscribirme</button>
            </form>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <?php require_once __DIR__ . '/../templates/footer.php'; ?>
</main>
