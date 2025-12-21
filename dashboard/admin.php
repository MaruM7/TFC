<?php
require_once __DIR__ . '/../config.php';

// Seguridad: Solo admin o instructor
if(!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['admin', 'instructor'])) {
    header('Location: ../public/index.php');
    exit;
}

// 1. ESTADÍSTICAS RÁPIDAS
$totalAlumnos = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'alumno' AND activo = 1")->fetchColumn();
$totalActividades = $pdo->query("SELECT COUNT(*) FROM disciplinas")->fetchColumn();

// Consulta de mensajes pendientes
$mensajesPendientes = $pdo->query("SELECT COUNT(*) FROM mensajes_contacto WHERE leido = 0")->fetchColumn();

$ingresosMes = $pdo->query("
    SELECT SUM(CASE 
        WHEN plan_nombre = 'Mensual' THEN monto 
        WHEN plan_nombre = 'Trimestral' THEN monto / 3 
        WHEN plan_nombre = 'Anual' THEN monto / 12 
        ELSE 0 END) 
    FROM suscripciones WHERE estado = 'activo'
")->fetchColumn() ?: 0;

// 2. DATOS PARA EL GRÁFICO (Inscritos por Disciplina)
$stmtGrafico = $pdo->query("
    SELECT d.nombre, COUNT(i.id) as total 
    FROM disciplinas d 
    LEFT JOIN clases c ON d.id = c.disciplina_id 
    LEFT JOIN inscripciones i ON c.id = i.clase_id 
    GROUP BY d.id 
    ORDER BY total DESC
");
$datosGrafico = $stmtGrafico->fetchAll();
$maxInscritos = !empty($datosGrafico) ? max(array_column($datosGrafico, 'total')) : 1;

require_once __DIR__ . '/../templates/header.php';
?>

<main class="container">
    <div style="margin-bottom: 40px;">
        <h1>Panel de Administración</h1>
        <p style="color:var(--muted)">Gestión integral del gimnasio y niveles.</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div class="card" style="border-left: 4px solid var(--accent-2); padding: 15px;">
            <small style="color:var(--muted); text-transform: uppercase; font-size: 11px; font-weight: bold;">Alumnos Activos</small>
            <div style="font-size: 24px; font-weight: bold; margin-top: 5px;"><?= $totalAlumnos ?></div>
        </div>
        <div class="card" style="border-left: 4px solid #9b59b6; padding: 15px;">
            <small style="color:var(--muted); text-transform: uppercase; font-size: 11px; font-weight: bold;">Ingresos Est./Mes</small>
            <div style="font-size: 24px; font-weight: bold; color:#9b59b6; margin-top: 5px;"><?= number_format($ingresosMes, 2) ?>€</div>
        </div>
        <div class="card" style="border-left: 4px solid #2ecc71; padding: 15px;">
            <small style="color:var(--muted); text-transform: uppercase; font-size: 11px; font-weight: bold;">Disciplinas</small>
            <div style="font-size: 24px; font-weight: bold; margin-top: 5px;"><?= $totalActividades ?></div>
        </div>
    </div>

    <h2 style="margin-bottom: 20px;">Gestión de la Academia</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 40px;">
        
        <a href="<?= BASE_URL ?>/admin/config_cinturones.php" style="text-decoration: none; color: inherit;">
            <div class="card" style="text-align: center; border-bottom: 3px solid #673ab7;">
                <div style="font-size: 30px; margin-bottom: 10px;">⚙️</div>
                <h4 style="margin:0;">Config. Cinturones</h4>
                <small style="color:var(--muted)">Crear colores y niveles</small>
            </div>
        </a>

        <a href="<?= BASE_URL ?>/admin/asignar_cinturon.php" style="text-decoration: none; color: inherit;">
            <div class="card" style="text-align: center; border-bottom: 3px solid #9c27b0;">
                <div style="font-size: 30px; margin-bottom: 10px;">🎓</div>
                <h4 style="margin:0;">Graduaciones</h4>
                <small style="color:var(--muted)">Asignar rangos</small>
            </div>
        </a>

        <a href="<?= BASE_URL ?>/admin/actividades.php" style="text-decoration: none; color: inherit;">
            <div class="card" style="text-align: center; border-bottom: 3px solid var(--accent-2);">
                <div style="font-size: 30px; margin-bottom: 10px;">🥋</div>
                <h4 style="margin:0;">Actividades</h4>
                <small style="color:var(--muted)">Editar clases</small>
            </div>
        </a>

        <a href="<?= BASE_URL ?>/admin/clases.php" style="text-decoration: none; color: inherit;">
            <div class="card" style="text-align: center; border-bottom: 3px solid #f1c40f;">
                <div style="font-size: 30px; margin-bottom: 10px;">📅</div>
                <h4 style="margin:0;">Horarios</h4>
                <small style="color:var(--muted)">Programar sesiones</small>
            </div>
        </a>

        <a href="<?= BASE_URL ?>/admin/mensajes.php" style="text-decoration: none; color: inherit;">
            <div class="card" style="text-align: center; border-bottom: 3px solid #f39c12;">
                <div style="font-size: 30px; margin-bottom: 10px;">📩</div>
                <h4 style="margin:0;">Mensajes</h4>
                <?php if($mensajesPendientes > 0): ?>
                    <span style="background:#f39c12; color:white; padding:2px 8px; border-radius:10px; font-size:0.75rem; font-weight:bold;">
                        <?= $mensajesPendientes ?> nuevos
                    </span>
                <?php else: ?>
                    <small style="color:var(--muted)">Buzón al día</small>
                <?php endif; ?>
            </div>
        </a>

        <a href="<?= BASE_URL ?>/admin/users.php" style="text-decoration: none; color: inherit;">
            <div class="card" style="text-align: center; border-bottom: 3px solid #e74c3c;">
                <div style="font-size: 30px; margin-bottom: 10px;">👥</div>
                <h4 style="margin:0;">Usuarios</h4>
                <small style="color:var(--muted)">Alumnos y roles</small>
            </div>
        </a>

    </div>

    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px;">
        <section class="card">
            <h3>Inscritos por Disciplina</h3>
            <div style="display: flex; flex-direction: column; gap: 15px; margin-top: 20px;">
                <?php foreach($datosGrafico as $dato): 
                    $porcentaje = ($maxInscritos > 0) ? ($dato['total'] / $maxInscritos) * 100 : 0;
                ?>
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 5px;">
                            <span><?= htmlspecialchars($dato['nombre']) ?></span>
                            <span style="font-weight: bold;"><?= $dato['total'] ?></span>
                        </div>
                        <div style="background: var(--glass); height: 8px; border-radius: 4px; overflow: hidden;">
                            <div style="width: <?= $porcentaje ?>%; background: var(--accent-2); height: 100%; border-radius: 4px; transition: width 0.5s;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="card">
            <h3>Accesos Rápidos</h3>
            <a href="<?= BASE_URL ?>/admin/pagos.php" class="btn-outline" style="display:block; text-align:center; margin-bottom:10px;">Ver Historial de Pagos</a>
            <p style="font-size: 0.8rem; color: var(--muted); text-align: center;">Recuerda revisar los pagos de PayPal semanalmente.</p>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../templates/header.php'; ?>