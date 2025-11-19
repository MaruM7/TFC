<?php
require_once __DIR__ . '/../config.php';
if(!isset($_SESSION['usuario'])){ header('Location: /public/login.php'); exit; }
if($_SESSION['usuario']['rol'] !== 'admin'){ header('Location: /public/index.php'); exit; }

// resumen
$u = $pdo->query("SELECT COUNT(*) as total FROM usuarios")->fetch();
$cl = $pdo->query("SELECT COUNT(*) as total FROM clases")->fetch();
$top = $pdo->query("SELECT r.usuario_id, u.nombre, SUM(r.puntos) as puntos_total FROM ranking r JOIN usuarios u ON r.usuario_id = u.id GROUP BY r.usuario_id ORDER BY puntos_total DESC LIMIT 10")->fetchAll();

require_once __DIR__ . '/../templates/header.php';
?>
<main class="container">
  <h1>Panel Admin</h1>
  <section>
    <div style="display:flex;gap:20px">
      <div class="card card-small"><h3>Usuarios</h3><p><?=htmlspecialchars($u['total'])?></p><a class="btn-outline" href="/admin/users.php">Gestionar</a></div>
      <div class="card card-small"><h3>Clases</h3><p><?=htmlspecialchars($cl['total'])?></p><a class="btn-outline" href="/admin/clases.php">Gestionar</a></div>
    </div>
  </section>

  <section style="margin-top:30px">
    <h2>Top usuarios (por puntos)</h2>
    <?php if($top): ?>
      <ul>
        <?php foreach($top as $t): ?>
          <li><?=htmlspecialchars($t['nombre'])?> — <?=htmlspecialchars($t['puntos_total'])?> pts</li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No hay datos de ranking.</p>
    <?php endif; ?>
  </section>
</main>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
