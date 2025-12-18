<?php
// templates/podium.php
// Obtener TOP 3 usuarios globales
$stmt = $pdo->query("SELECT u.nombre, u.apellidos, SUM(r.puntos) as total 
                     FROM ranking r 
                     JOIN usuarios u ON r.usuario_id = u.id 
                     GROUP BY r.usuario_id 
                     ORDER BY total DESC 
                     LIMIT 3");
$top3 = $stmt->fetchAll();

// Rellenar huecos si hay menos de 3 usuarios
while(count($top3) < 3) { $top3[] = ['nombre'=>'Vacante', 'apellidos'=>'', 'total'=>0]; }

// Organizar para el podio: [Segunda, Primera, Tercera]
$ordenPodio = [$top3[1], $top3[0], $top3[2]];
$clases = ['second', 'first', 'third'];
$numeros = [2, 1, 3];
?>

<div class="podium-container">
  <?php foreach($ordenPodio as $k => $u): ?>
    <div class="podium-place <?= $clases[$k] ?>">
      <div class="avatar-circle"><?= $numeros[$k] ?></div>
      <div class="podium-bar"></div>
      <div class="podium-info text-center">
        <p><strong><?= htmlspecialchars($u['nombre']) ?></strong></p>
        <p class="podium-points"><?= (int)$u['total'] ?> pts</p>
      </div>
    </div>
  <?php endforeach; ?>
</div>