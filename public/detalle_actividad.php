<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../templates/header.php';

$id = (int)($_GET['id'] ?? 0);

// 1. Obtener los detalles de la actividad
$stmt = $pdo->prepare("SELECT * FROM disciplinas WHERE id = ?");
$stmt->execute([$id]);
$actividad = $stmt->fetch();

if (!$actividad) {
    header("Location: actividades.php");
    exit;
}

// 2. Obtener las próximas clases de esta disciplina
$stmtClases = $pdo->prepare("SELECT * FROM clases WHERE disciplina_id = ? AND fecha_hora >= NOW() ORDER BY fecha_hora ASC LIMIT 4");
$stmtClases->execute([$id]);
$proximasClases = $stmtClases->fetchAll();
?>

<main class="container">
    <a href="actividades.php" style="color:var(--muted); text-decoration:none; display:inline-flex; align-items:center; gap:8px; margin-bottom:20px; transition: color 0.2s;">
        ← Volver a Actividades
    </a>

    <div style="display: grid; grid-template-columns: 1fr 380px; gap: 40px; align-items: start;" class="detalle-grid">
        
        <section>
            <div style="border-radius: 16px; overflow: hidden; margin-bottom: 30px; border: 1px solid var(--border); height: 350px; background: #000;">
                <?php $img = !empty($actividad['imagen']) ? BASE_URL . '/public/img/' . $actividad['imagen'] : 'https://via.placeholder.com/800x450?text=Sin+Imagen'; ?>
                <img src="<?= htmlspecialchars($img) ?>" 
                     alt="<?= htmlspecialchars($actividad['nombre']) ?>" 
                     style="width: 100%; height: 100%; display: block; object-fit: cover; object-position: center;">
            </div>

            <h1 style="font-size: 2.5rem; margin-bottom: 20px; color: var(--accent-2);"><?= htmlspecialchars($actividad['nombre']) ?></h1>
            
            <div class="card" style="margin-bottom: 30px;">
                <h3>Sobre esta disciplina</h3>
                <p style="line-height: 1.8; color: var(--muted); white-space: pre-wrap;">
                    <?= htmlspecialchars($actividad['descripcion']) ?>
                </p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;" class="info-grid">
                <div class="card">
                    <h4 style="margin-top:0; color:var(--accent-2);">🥋 Equipamiento</h4>
                    <ul style="color:var(--muted); padding-left:20px; font-size:0.9rem; line-height:1.6; margin-bottom:0;">
                        <li>Ropa cómoda / Deportiva</li>
                        <li>Toalla individual</li>
                        <li>Botella de agua</li>
                        <li>Protecciones (según nivel)</li>
                    </ul>
                </div>
                <div class="card">
                    <h4 style="margin-top:0; color:var(--accent-2);">💡 Beneficios</h4>
                    <ul style="color:var(--muted); padding-left:20px; font-size:0.9rem; line-height:1.6; margin-bottom:0;">
                        <li>Mejora de la disciplina mental</li>
                        <li>Quema de calorías intensiva</li>
                        <li>Aumento de la coordinación</li>
                        <li>Defensa personal básica</li>
                    </ul>
                </div>
            </div>
        </section>

        <aside>
            <div class="card" style="position: sticky; top: 110px; border: 1px solid var(--accent-2);">
                <h3 style="margin-top: 0; padding-bottom: 15px; border-bottom: 1px solid var(--border);">Próximos Horarios</h3>
                
                <?php if (empty($proximasClases)): ?>
                    <p style="color: var(--muted); padding: 20px 0; text-align: center;">No hay clases programadas esta semana.</p>
                <?php else: ?>
                    <div style="margin: 20px 0;">
                        <?php foreach ($proximasClases as $clase): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--glass);">
                                <div>
                                    <span style="display: block; font-weight: bold;"><?= date('d/m/Y', strtotime($clase['fecha_hora'])) ?></span>
                                    <small style="color: var(--muted);"><?= date('H:i', strtotime($clase['fecha_hora'])) ?> h</small>
                                </div>
                                <div style="text-align: right;">
                                    <span style="font-size: 0.75rem; background: var(--glass); padding: 2px 8px; border-radius: 10px; border:1px solid var(--border);">
                                        👥 <?= $clase['cupo'] ?> plazas
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div style="margin-top: 30px;">
                    <a href="cuotas.php" class="btn-primary" style="width: 100%; text-align: center; margin-bottom: 10px; font-weight: bold;">¡Quiero apuntarme!</a>
                    <p style="font-size: 0.8rem; color: var(--muted); text-align: center;">Acceso ilimitado con cualquier suscripción.</p>

                    <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--border); text-align: center;">
                        <p style="font-size: 0.9rem; margin-bottom: 5px; color: var(--white);">¿Aún no te decides?</p>
                        <a href="contacto.php" style="color: var(--accent-2); text-decoration: none; font-weight: bold; font-size: 0.9rem; transition: 0.2s; border-bottom: 1px dotted var(--accent-2);">
                            Solicita una Clase de Prueba Gratis →
                        </a>
                    </div>
                </div>
            </div>
        </aside>

    </div>
</main>

<style>
    @media (max-width: 900px) {
        .detalle-grid { grid-template-columns: 1fr !important; }
        .info-grid { grid-template-columns: 1fr !important; }
        aside .card { position: static !important; }
    }
    a[href="contacto.php"]:hover { opacity: 0.8; }
    a[href="actividades.php"]:hover { color: var(--accent-2) !important; }
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>