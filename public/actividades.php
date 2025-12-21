<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../templates/header.php';

// Consultamos todas las disciplinas registradas
$stmt = $pdo->query("SELECT * FROM disciplinas ORDER BY nombre ASC");
$actividades = $stmt->fetchAll();
?>

<main class="container">
    <div style="text-align:center; margin-bottom:50px;">
        <h1 style="font-size: clamp(32px, 6vw, 56px); margin-bottom:10px;">Nuestras Actividades</h1>
        <p style="color:var(--muted); font-size:18px;">Descubre nuestras disciplinas y empieza a entrenar con los mejores.</p>
    </div>

    <div class="cards-grid">
        <?php foreach ($actividades as $act): 
            // Ruta de la imagen o placeholder si no existe
            $imgSrc = !empty($act['imagen']) ? BASE_URL . '/public/img/' . $act['imagen'] : 'https://via.placeholder.com/500x300?text=Proximamente';
        ?>
            <div class="card" style="padding:0; overflow:hidden; display:flex; flex-direction:column;">
                <div style="height: 220px; overflow:hidden; background: #000;">
                    <img src="<?= htmlspecialchars($imgSrc) ?>" 
                         alt="<?= htmlspecialchars($act['nombre']) ?>" 
                         style="width:100%; height:100%; object-fit:cover; transition: transform 0.4s ease;">
                </div>

                <div style="padding:25px; flex-grow:1; display:flex; flex-direction:column;">
                    <h2 style="margin:0 0 10px 0; color:var(--accent-2); font-size:1.5rem;">
                        <?= htmlspecialchars($act['nombre']) ?>
                    </h2>
                    
                    <p style="color:var(--muted); line-height:1.6; margin-bottom:20px; flex-grow:1;">
                        <?= nl2br(htmlspecialchars($act['descripcion'] ?? 'Entrenamiento profesional diseñado para mejorar tu técnica y condición física.')) ?>
                    </p>

                    <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border); padding-top:15px;">
                        <span style="font-size:0.8rem; font-weight:bold; color:var(--accent-2); text-transform:uppercase;">Activo</span>
                        <a href="detalle_actividad.php?id=<?= $act['id'] ?>" class="btn-outline" style="font-size:0.85rem;">Más info</a>                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<style>
    /* Efecto de zoom en la imagen al pasar el ratón */
    .card:hover img {
        transform: scale(1.08);
        opacity: 0.85;
    }
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>