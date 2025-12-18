<?php
require_once __DIR__ . '/../config.php';
if(!isset($_SESSION['usuario']) || ($_SESSION['usuario']['rol'] !== 'admin' && $_SESSION['usuario']['rol'] !== 'instructor')){ header('Location: ../public/index.php'); exit; }

$msg = $_SESSION['msg'] ?? null; unset($_SESSION['msg']);

// 1. PROCESAR ACCIONES (ELIMINAR / ASISTENCIA)
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) die('CSRF Error');
    
    if($_POST['action'] === 'delete' && $_SESSION['usuario']['rol'] === 'admin'){
        $pdo->prepare("DELETE FROM clases WHERE id = ?")->execute([$_POST['clase_id']]);
        $_SESSION['msg'] = "✅ Clase eliminada con éxito.";
        header('Location: clases.php'); exit;
    }

    if($_POST['action'] === 'marcar'){
        $pts = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'puntos_por_asistencia'")->fetchColumn() ?: 5;
        $pdo->prepare("INSERT IGNORE INTO asistencia (clase_id, usuario_id, puntos_otorgados) VALUES (?, ?, ?)")->execute([$_POST['clase_id'], $_POST['usuario_id'], $pts]);
        
        $did = $pdo->prepare("SELECT disciplina_id FROM clases WHERE id = ?"); $did->execute([$_POST['clase_id']]);
        $disc = $did->fetchColumn();

        $pdo->prepare("INSERT INTO ranking (usuario_id, disciplina_id, puntos) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE puntos = puntos + ?")
            ->execute([$_POST['usuario_id'], $disc, $pts, $pts]);
        
        $_SESSION['msg'] = "✅ Asistencia marcada y puntos otorgados.";
        header("Location: clases.php?clase_id=".$_POST['clase_id']); exit;
    }
}

// 2. CONSULTAS ESTADÍSTICAS
$alumnosActivos = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'alumno' AND activo = 1")->fetchColumn();
$ingresosMes = $pdo->query("SELECT SUM(CASE WHEN plan_nombre='Mensual' THEN monto WHEN plan_nombre='Trimestral' THEN monto/3 WHEN plan_nombre='Anual' THEN monto/12 ELSE 0 END) FROM suscripciones WHERE estado='activo'")->fetchColumn() ?: 0;
$proxima = $pdo->query("SELECT d.nombre FROM clases c JOIN disciplinas d ON c.disciplina_id=d.id WHERE fecha_hora >= NOW() ORDER BY fecha_hora ASC LIMIT 1")->fetchColumn() ?: 'Ninguna';

$clase_id = (int)($_GET['clase_id'] ?? 0);
require_once __DIR__ . '/../templates/header.php';
?>

<main class="container">
    <h1>Panel de Administración</h1>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div class="card" style="border-left: 4px solid var(--accent-2);">
            <small>Alumnos Activos</small>
            <div style="font-size: 1.8rem; font-weight: bold;"><?= $alumnosActivos ?></div>
        </div>
        <div class="card" style="border-left: 4px solid #9b59b6;">
            <small>Ingresos Est./Mes</small>
            <div style="font-size: 1.8rem; font-weight: bold; color:#9b59b6;"><?= number_format($ingresosMes, 2) ?>€</div>
        </div>
        <div class="card" style="border-left: 4px solid #f1c40f;">
            <small>Siguiente Sesión</small>
            <div style="font-size: 1.8rem; font-weight: bold;"><?= htmlspecialchars($proxima) ?></div>
        </div>
    </div>

    <?php if($msg): ?><div style="background:rgba(46, 204, 113, 0.2); color:#2ecc71; padding:15px; border-radius:8px; margin-bottom:20px;"><?=$msg?></div><?php endif; ?>

    <section style="margin-bottom:50px;">
        <h2 style="text-align:center;">🏆 Ranking Top 3 🏆</h2>
        <?php include __DIR__ . '/../templates/podium.php'; ?>
    </section>

    <?php if(!$clase_id): ?>
        <h2>Gestión de Clases</h2>
        <div class="cards-grid">
            <?php 
            $stmt = $pdo->query("SELECT c.*, d.nombre as disciplina, (SELECT COUNT(*) FROM inscripciones WHERE clase_id = c.id) as inscritos FROM clases c JOIN disciplinas d ON c.disciplina_id = d.id ORDER BY fecha_hora DESC");
            foreach($stmt->fetchAll() as $c): ?>
                <div class="card">
                    <h3><?= htmlspecialchars($c['disciplina']) ?></h3>
                    <p><?= date('d/m/Y H:i', strtotime($c['fecha_hora'])) ?></p>
                    <p>Inscritos: <?= $c['inscritos'] ?>/<?= $c['cupo'] ?></p>
                    <div style="display:flex; gap:10px; margin-top:15px;">
                        <a href="clases.php?clase_id=<?=$c['id']?>" class="btn-outline" style="flex:1; text-align:center;">Ver Alumnos</a>
                        <?php if($_SESSION['usuario']['rol'] === 'admin'): ?>
                            <form method="POST" onsubmit="return confirm('¿Borrar clase?');">
                                <input type="hidden" name="action" value="delete"><input type="hidden" name="clase_id" value="<?=$c['id']?>">
                                <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>"><button type="submit" class="btn-danger">🗑️</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <a href="clases.php" style="color:var(--accent-2);">← Volver</a>
        <h2 style="margin-top:20px;">Lista de Asistencia</h2>
        <table class="table">
            <thead><tr><th>Alumno</th><th>Email</th><th>Acción</th></tr></thead>
            <tbody>
            <?php 
            $stmt = $pdo->prepare("SELECT u.id, u.nombre, u.apellidos, u.email FROM inscripciones i JOIN usuarios u ON i.usuario_id = u.id WHERE i.clase_id = ?");
            $stmt->execute([$clase_id]);
            foreach($stmt->fetchAll() as $u): 
                $check = $pdo->prepare("SELECT id FROM asistencia WHERE usuario_id = ? AND clase_id = ?");
                $check->execute([$u['id'], $clase_id]);
                $asistio = $check->fetch();
            ?>
                <tr>
                    <td><?=htmlspecialchars($u['nombre'].' '.$u['apellidos'])?></td>
                    <td><?=htmlspecialchars($u['email'])?></td>
                    <td>
                        <?php if(!$asistio): ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="marcar"><input type="hidden" name="clase_id" value="<?=$clase_id?>"><input type="hidden" name="usuario_id" value="<?=$u['id']?>">
                                <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>"><button type="submit" class="btn-primary">Presente</button>
                            </form>
                        <?php else: ?><span style="color:#2ecc71;">✅ Presente</span><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>