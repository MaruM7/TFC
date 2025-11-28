<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../templates/header.php';

// obtener próximas clases
// Usamos LEFT JOIN y GROUP BY para contar las inscripciones (i.id) 
// y calcular el cupo_restante.
try {
    $stmt = $pdo->prepare("
        SELECT 
            cl.*, 
            d.nombre as disciplina,
            COUNT(i.id) AS inscritos_actuales
        FROM clases cl 
        JOIN disciplinas d ON cl.disciplina_id = d.id 
        LEFT JOIN inscripciones i ON cl.id = i.clase_id 
        WHERE cl.fecha_hora >= NOW() 
        GROUP BY cl.id 
        ORDER BY cl.fecha_hora ASC 
        LIMIT 12
    ");
    $stmt->execute();
    $clases = $stmt->fetchAll();
} catch (Exception $e) {
    $clases = [];
}
?>
<main>
  <section class="hero">
    <div class="hero-inner container">
      <h1>Entrena. Compite. Mejora.</h1>
      <p>Gestiona tus clases, sigue tu ranking y participa en eventos.</p>
      <div class="cta-group">
        <?php if(!isset($_SESSION['usuario'])): ?>
          <a class="btn-primary" href="<?=BASE_URL?>/public/login.php">Iniciar sesión</a>
        <?php else: ?>
          <a class="btn-primary" href="<?=BASE_URL?>/dashboard/<?=htmlspecialchars($_SESSION['usuario']['rol'])?>.php" >Ir a mi panel</a>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section id="clases" class="cards-section container">
    <h2>Próximas clases</h2>
    <div class="cards-grid">
      <?php if(count($clases) > 0): ?>
          <?php foreach($clases as $c): ?>
            <?php 
                // Calculamos el cupo restante
                $cupo_disponible = $c['cupo'] - $c['inscritos_actuales'];
            ?>
            <article class="card">
              <h3><?=htmlspecialchars($c['disciplina'])?></h3>
              <p><?=htmlspecialchars($c['descripcion'] ?? '')?></p>
              <p><strong><?=date('d/m/Y H:i', strtotime($c['fecha_hora']))?></strong> · <?=htmlspecialchars($c['duracion'])?> min</p>
              
              <p>Cupo: <strong><?=htmlspecialchars($cupo_disponible)?></strong> disponibles</p>
              
              <?php if(isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'alumno'): ?>
                <?php if ($cupo_disponible > 0): ?>
                    <form method="POST" action="<?=BASE_URL?>/public/inscribir.php">
                      <input type="hidden" name="clase_id" value="<?=htmlspecialchars($c['id'])?>">
                      <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'])?>">
                      <button class="btn-outline" type="submit">Inscribirme</button>
                    </form>
                <?php else: ?>
                    <button class="btn-primary" disabled style="background:#ff3838;">Completo</button>
                <?php endif; ?>
              <?php elseif(!isset($_SESSION['usuario'])): ?>
                 <a href="<?=BASE_URL?>/public/login.php" style="font-size:0.9em; text-decoration:underline; color:#888;">Inicia sesión para inscribirte</a>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
      <?php else: ?>
          <p>No hay clases programadas próximamente.</p>
      <?php endif; ?>
    </div>
  </section>

  <?php require_once __DIR__ . '/../templates/footer.php'; ?>
</main>