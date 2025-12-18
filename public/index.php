<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../templates/header.php';

// Obtener próximas clases
try {
    $stmt = $pdo->prepare("SELECT cl.*, d.nombre as disciplina, COUNT(i.id) AS inscritos_actuales FROM clases cl JOIN disciplinas d ON cl.disciplina_id = d.id LEFT JOIN inscripciones i ON cl.id = i.clase_id WHERE cl.fecha_hora >= NOW() GROUP BY cl.id ORDER BY cl.fecha_hora ASC LIMIT 6");
    $stmt->execute();
    $clases = $stmt->fetchAll();
} catch (Exception $e) { $clases = []; }
?>
<main>
  <section class="hero">
    <div class="hero-inner container">
      <h1>Entrena. Compite. Mejora.</h1>
      <p>El camino del guerrero comienza aquí. Gestión de clases y ranking en tiempo real.</p>
      <div class="cta-group">
        <?php if(!isset($_SESSION['usuario'])): ?>
          <a class="btn-primary" href="<?=BASE_URL?>/public/login.php">Únete Ahora</a>
        <?php else: ?>
          <a class="btn-primary" href="<?=BASE_URL?>/dashboard/<?=htmlspecialchars($_SESSION['usuario']['rol'])?>.php">Ir a mi panel</a>
        <?php endif; ?>
        <a class="btn-outline" href="<?=BASE_URL?>/public/cuotas.php">Ver Precios</a>
      </div>
    </div>
  </section>

  <section class="cards-section container">
    <h2>Próximas Clases</h2>
    <div class="cards-grid">
      <?php if(count($clases) > 0): foreach($clases as $c): 
          $cupo = $c['cupo'] - $c['inscritos_actuales']; ?>
          <article class="card">
            <h3><?=htmlspecialchars($c['disciplina'])?></h3>
            <p><strong><?=date('d/m/Y H:i', strtotime($c['fecha_hora']))?></strong></p>
            <p>Cupo: <strong><?=htmlspecialchars($cupo)?></strong> disponibles</p>
            
            <?php if(isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'alumno'): ?>
              <?php if ($cupo > 0): ?>
                  <form method="POST" action="<?=BASE_URL?>/public/inscribir.php">
                    <input type="hidden" name="clase_id" value="<?=$c['id']?>">
                    <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
                    <button class="btn-outline" type="submit">Inscribirme</button>
                  </form>
              <?php else: ?><button class="btn-primary" disabled style="background:#ff3838;">Completo</button><?php endif; ?>
            <?php elseif(!isset($_SESSION['usuario'])): ?>
               <a href="<?=BASE_URL?>/public/login.php" style="color:#aaa; font-size:0.9rem;">Inicia sesión para inscribirte</a>
            <?php endif; ?>
          </article>
      <?php endforeach; else: ?><p>No hay clases programadas.</p><?php endif; ?>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>