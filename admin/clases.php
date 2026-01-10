<?php
require_once __DIR__ . '/../config.php';
// Seguridad: Solo admin e instructores
if(!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['admin', 'instructor'])) { 
    header('Location: ../public/index.php'); 
    exit; 
}

$msg = $_SESSION['msg'] ?? null; 
unset($_SESSION['msg']);

// --- LÓGICA UNIFICADA (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) die('Error de seguridad CSRF');
    
    $action = $_POST['action'] ?? '';
    // Capturamos el ID de la clase para saber a dónde volver después
    $id = (int)($_POST['clase_id'] ?? 0); 
    
    // 1. Acciones del Calendario (Crear, Editar, Borrar Clase)
    if (in_array($action, ['create', 'edit', 'delete'])) {
        $did = (int)($_POST['disciplina_id'] ?? 0);
        $fecha = $_POST['fecha_hora'] ?? '';
        $cupo = (int)($_POST['cupo'] ?? 20);

        if ($action === 'create') {
            $stmt = $pdo->prepare("INSERT INTO clases (disciplina_id, fecha_hora, cupo) VALUES (?, ?, ?)");
            $stmt->execute([$did, $fecha, $cupo]);
            $_SESSION['msg'] = "✅ Clase creada correctamente.";
        } elseif ($action === 'edit') {
            $stmt = $pdo->prepare("UPDATE clases SET disciplina_id = ?, fecha_hora = ?, cupo = ? WHERE id = ?");
            $stmt->execute([$did, $fecha, $cupo, $id]);
            $_SESSION['msg'] = "✅ Clase actualizada.";
        } elseif ($action === 'delete') {
            $pdo->prepare("DELETE FROM clases WHERE id = ?")->execute([$id]);
            $_SESSION['msg'] = "🗑️ Clase eliminada.";
        }
        header("Location: clases.php"); exit;
    }
    
    // 2. Acción de Asistencia (Dar Puntos - ÚNICO USO)
    elseif ($action === 'dar_puntos') {
        $uid = (int)$_POST['usuario_id'];
        $did = (int)$_POST['disciplina_id'];

        // PASO CLAVE: Verificamos si ya está "asistido" antes de sumar nada
        $stmtCheck = $pdo->prepare("SELECT estado FROM inscripciones WHERE usuario_id = ? AND clase_id = ?");
        $stmtCheck->execute([$uid, $id]);
        $estadoActual = $stmtCheck->fetchColumn();

        if ($estadoActual !== 'asistido') {
            // A) Sumamos los puntos en el ranking
            $stmt = $pdo->prepare("INSERT INTO ranking (usuario_id, disciplina_id, puntos) VALUES (?, ?, 10) ON DUPLICATE KEY UPDATE puntos = puntos + 10");
            $stmt->execute([$uid, $did]);
            
            // B) Marcamos la inscripción como 'asistido' para bloquear el botón
            $stmtUpd = $pdo->prepare("UPDATE inscripciones SET estado = 'asistido' WHERE usuario_id = ? AND clase_id = ?");
            $stmtUpd->execute([$uid, $id]);

            $_SESSION['msg'] = "✅ Asistencia confirmada (+10 puntos).";
        } else {
            $_SESSION['msg'] = "⚠️ Este alumno ya tenía la asistencia registrada.";
        }
        
        // Volvemos a la misma lista de asistencia
        header("Location: clases.php?clase_id=" . $id); exit;
    }
}

// Vista: ¿Estamos viendo el calendario o una lista de asistencia?
$clase_id_view = (int)($_GET['clase_id'] ?? 0);
$disciplinas = $pdo->query("SELECT id, nombre FROM disciplinas ORDER BY nombre ASC")->fetchAll();

require_once __DIR__ . '/../templates/header.php';
?>

<main class="container">
    <?php if($msg): ?>
        <div style="background:rgba(46, 204, 113, 0.2); color:#2ecc71; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #2ecc71;">
            <?= $msg ?>
        </div>
    <?php endif; ?>

    <?php if(!$clase_id_view): ?>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <h1>Calendario de Clases</h1>
            <button onclick="abrirModalClase()" class="btn-primary">+ Nueva Clase</button>
        </div>

        <div id="modalClase" class="card" style="display:none; margin-bottom:30px; border: 1px solid var(--accent-2);">
            <h2 id="modalTitle">Programar Clase</h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="action" id="actionClase" value="create">
                <input type="hidden" name="clase_id" id="idClase" value="">

                <div style="display:grid; grid-template-columns: 1fr 1fr 100px; gap:15px; margin-bottom:20px;">
                    <div>
                        <label>Disciplina:</label>
                        <select name="disciplina_id" id="discClase" required style="width:100%; padding:10px; border-radius:8px; background:var(--input-bg); color:var(--white);">
                            <?php foreach($disciplinas as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Fecha y Hora:</label>
                        <input type="datetime-local" name="fecha_hora" id="fechaClase" required style="width:100%; padding:8px; border-radius:8px; background:var(--input-bg); color:var(--white);">
                    </div>
                    <div>
                        <label>Cupo:</label>
                        <input type="number" name="cupo" id="cupoClase" value="20" style="width:100%; padding:8px; border-radius:8px; background:var(--input-bg); color:var(--white);">
                    </div>
                </div>
                <button type="submit" class="btn-primary">Guardar Clase</button>
                <button type="button" onclick="this.parentElement.parentElement.style.display='none'" class="btn-outline">Cancelar</button>
            </form>
        </div>

        <div class="cards-grid">
            <?php 
            $stmt = $pdo->query("SELECT c.*, d.nombre as disc FROM clases c JOIN disciplinas d ON c.disciplina_id = d.id ORDER BY fecha_hora DESC");
            foreach($stmt->fetchAll() as $c): ?>
                <div class="card">
                    <h3><?= htmlspecialchars($c['disc']) ?></h3>
                    <p>📅 <?= date('d/m/Y H:i', strtotime($c['fecha_hora'])) ?></p>
                    <p>👥 Cupo: <?= $c['cupo'] ?></p>
                    <div style="display:flex; gap:5px; margin-top:15px;">
                        <a href="clases.php?clase_id=<?=$c['id']?>" class="btn-outline" style="flex:1; text-align:center;">Asistencia</a>
                        <button onclick='editarClase(<?= json_encode($c) ?>)' class="btn-outline">✏️</button>
                        <form method="POST" onsubmit="return confirm('¿Seguro que quieres borrar esta clase?')" style="margin:0;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="clase_id" value="<?=$c['id']?>">
                            <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
                            <button type="submit" class="btn-danger">🗑️</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: 
        // ================= VISTA 2: LISTA DE ALUMNOS (ASISTENCIA) =================
        $stmtC = $pdo->prepare("SELECT c.*, d.nombre as disc FROM clases c JOIN disciplinas d ON c.disciplina_id = d.id WHERE c.id = ?");
        $stmtC->execute([$clase_id_view]);
        $clase_info = $stmtC->fetch();
        
        if(!$clase_info) { echo "<script>window.location='clases.php';</script>"; exit; }

        // Obtenemos los alumnos y su ESTADO actual (pendiente/asistido)
        $stmtA = $pdo->prepare("SELECT u.id, u.nombre, u.apellidos, u.email, i.estado FROM inscripciones i JOIN usuarios u ON i.usuario_id = u.id WHERE i.clase_id = ?");
        $stmtA->execute([$clase_id_view]);
        $alumnos = $stmtA->fetchAll();
    ?>
        <div style="margin-bottom:20px;">
            <a href="clases.php" class="btn-outline">← Volver al Calendario</a>
        </div>
        <h1>Asistencia: <?= htmlspecialchars($clase_info['disc']) ?></h1>
        <p style="color:var(--muted)">Fecha: <?= date('d/m/Y H:i', strtotime($clase_info['fecha_hora'])) ?></p>

        <div class="card" style="margin-top:20px;">
            <table class="table">
                <thead>
                    <tr><th>Alumno</th><th>Email</th><th>Estado</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    <?php if(empty($alumnos)): ?>
                        <tr><td colspan="4" style="text-align:center; padding:30px; color:var(--muted);">No hay alumnos inscritos en esta clase.</td></tr>
                    <?php else: foreach($alumnos as $al): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($al['nombre'].' '.$al['apellidos']) ?></strong></td>
                            <td><?= htmlspecialchars($al['email']) ?></td>
                            <td>
                                <?php if($al['estado'] === 'asistido'): ?>
                                    <span style="color:#2ecc71; font-weight:bold; font-size:0.9rem;">Asistido</span>
                                <?php else: ?>
                                    <span style="color:var(--muted); font-size:0.9rem;">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($al['estado'] === 'asistido'): ?>
                                    <button disabled class="btn-outline" style="border-color:#2ecc71; color:#2ecc71; cursor:default; opacity:1;">
                                        ✅ Completado
                                    </button>
                                <?php else: ?>
                                    <form method="POST" style="margin:0;">
                                        <input type="hidden" name="action" value="dar_puntos">
                                        <input type="hidden" name="usuario_id" value="<?= $al['id'] ?>">
                                        <input type="hidden" name="disciplina_id" value="<?= $clase_info['disciplina_id'] ?>">
                                        <input type="hidden" name="clase_id" value="<?= $clase_id_view ?>">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <button type="submit" class="btn-primary" style="font-size:0.8rem; padding:5px 10px;">Confirmar Asistencia</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<script>
function abrirModalClase() {
    document.getElementById('modalClase').style.display = 'block';
    document.getElementById('actionClase').value = 'create';
    document.getElementById('modalTitle').innerText = 'Programar Nueva Clase';
    document.getElementById('idClase').value = '';
    document.getElementById('fechaClase').value = '';
    document.getElementById('cupoClase').value = '20';
}
function editarClase(c) {
    abrirModalClase();
    document.getElementById('modalTitle').innerText = 'Editar Clase';
    document.getElementById('actionClase').value = 'edit';
    document.getElementById('idClase').value = c.id;
    document.getElementById('discClase').value = c.disciplina_id;
    document.getElementById('fechaClase').value = c.fecha_hora.replace(' ', 'T').substring(0, 16);
    document.getElementById('cupoClase').value = c.cupo;
}
</script>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>