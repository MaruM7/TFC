<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../templates/header.php';

// Obtener próximas clases
try {
    $stmt = $pdo->prepare("SELECT cl.*, d.nombre as disciplina, COUNT(i.id) AS inscritos_actuales 
                           FROM clases cl 
                           JOIN disciplinas d ON cl.disciplina_id = d.id 
                           LEFT JOIN inscripciones i ON cl.id = i.clase_id 
                           WHERE cl.fecha_hora >= NOW() 
                           GROUP BY cl.id 
                           ORDER BY cl.fecha_hora ASC LIMIT 6");
    $stmt->execute();
    $clases = $stmt->fetchAll();
} catch (Exception $e) { $clases = []; }
?>

<style>
  /* Efecto de fondo para la sección Hero */
  .hero {
    position: relative;
    padding: 120px 0;
    text-align: center;
    overflow: hidden;
    background: linear-gradient(135deg, #0a1128 0%, #1c2a48 100%); /* Fondo base azul oscuro */
    color: white;
  }

  /* Capa de imagen difuminada opcional */
  .hero::before {
    content: "";
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background-image: url('<?=BASE_URL?>/public/assets/img/bg-dojo.jpg'); /* Asegúrate de tener una imagen aquí */
    background-size: cover;
    background-position: center;
    opacity: 0.2; /* Muy suave para no molestar al texto */
    filter: blur(5px);
    z-index: 1;
  }

  .hero-inner {
    position: relative;
    z-index: 2; /* Por encima del fondo difuminado */
  }

  .hero h1 {
    font-size: 3.8rem;
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 20px;
    letter-spacing: -1px;
  }

  .hero p {
    font-size: 1.25rem;
    color: rgba(255,255,255,0.8);
    max-width: 700px;
    margin: 0 auto 40px;
    line-height: 1.6;
  }

  /* Ajuste de las tarjetas para que no se vean sosas */
  .card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  .card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.3);
  }
</style>

<main>
  <section class="hero">
    <div class="hero-inner container">
      <h1>Cruza el umbral.<br>Domina el arte.</h1>
      <p>
        Tu evolución en artes marciales medida punto a punto. Únete a <strong>Umbral Academy</strong> 
        y lidera el ranking global de nuestras disciplinas.
      </p>
      
      <div class="cta-group">
        <?php if(!isset($_SESSION['usuario'])): ?>
          <a class="btn-primary" href="<?=BASE_URL?>/public/login.php" style="padding: 15px 35px; font-size: 1.1rem;">Únete Ahora</a>
        <?php else: ?>
          <a class="btn-primary" href="<?=BASE_URL?>/dashboard/<?=htmlspecialchars($_SESSION['usuario']['rol'])?>.php" style="padding: 15px 35px; font-size: 1.1rem;">Ir a mi panel</a>
        <?php endif; ?>
        <a class="btn-outline" href="<?=BASE_URL?>/public/cuotas.php" style="padding: 15px 35px; font-size: 1.1rem; margin-left: 10px;">Ver Precios</a>
      </div>
    </div>
  </section>

  <section class="cards-section container" style="padding: 80px 0;">
    <h2 style="margin-bottom: 40px; text-align:center;">Próximas Clases Disponibles</h2>
    <div class="cards-grid">
      <?php if(count($clases) > 0): foreach($clases as $c): 
          $cupo = $c['cupo'] - $c['inscritos_actuales']; ?>
          <article class="card">
            <div style="font-size: 0.8rem; color: var(--accent); font-weight: bold; margin-bottom: 10px; text-transform: uppercase;">
                🥋 <?=htmlspecialchars($c['disciplina'])?>
            </div>
            <h3 style="margin-top:0;"><?=date('H:i', strtotime($c['fecha_hora']))?> - <?=date('d/m/Y', strtotime($c['fecha_hora']))?></h3>
            <p style="color: var(--muted); font-size: 0.9rem;">
                Cupo: <strong style="color: var(--white);"><?=htmlspecialchars($cupo)?></strong> plazas libres
            </p>
            
            <div style="margin-top: 20px;">
                <?php if(isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'alumno'): ?>
                  <?php if ($cupo > 0): ?>
                      <form method="POST" action="<?=BASE_URL?>/public/inscribir.php">
                        <input type="hidden" name="clase_id" value="<?=$c['id']?>">
                        <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
                        <button class="btn-outline" type="submit" style="width:100%;">Reservar Plaza</button>
                      </form>
                  <?php else: ?>
                      <button class="btn-primary" disabled style="background:#ff3838; width:100%; border:none;">Completo</button>
                  <?php endif; ?>
                <?php elseif(!isset($_SESSION['usuario'])): ?>
                   <a href="<?=BASE_URL?>/public/login.php" class="btn-outline" style="display:block; text-align:center; font-size:0.8rem; text-decoration:none;">Acceder para reservar</a>
                <?php endif; ?>
            </div>
          </article>
      <?php endforeach; else: ?>
          <p style="grid-column: 1/-1; text-align: center; color: var(--muted);">Actualmente no hay clases programadas para los próximos días.</p>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>