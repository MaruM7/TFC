<?php
require_once __DIR__ . '/../config.php';
if(!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['admin', 'instructor'])) { header('Location: ../public/index.php'); exit; }

$msg = $_SESSION['msg'] ?? null; unset($_SESSION['msg']);

// --- LÓGICA CRUD CLASES ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) die('CSRF Error');
    
    $action = $_POST['action'];
    $id = (int)($_POST['clase_id'] ?? 0);
    $did = (int)($_POST['disciplina_id'] ?? 0);
    $fecha = $_POST['fecha_hora'] ?? '';
    $cupo = (int)($_POST['cupo'] ?? 20);

    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO clases (disciplina_id, fecha_hora, cupo) VALUES (?, ?, ?)");
        $stmt->execute([$did, $fecha, $cupo]);
    } elseif ($action === 'edit') {
        $stmt = $pdo->prepare("UPDATE clases SET disciplina_id = ?, fecha_hora = ?, cupo = ? WHERE id = ?");
        $stmt->execute([$did, $fecha, $cupo, $id]);
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM clases WHERE id = ?")->execute([$id]);
    }
    header("Location: clases.php"); exit;
}

$clase_id_view = (int)($_GET['clase_id'] ?? 0);
$disciplinas = $pdo->query("SELECT id, nombre FROM disciplinas ORDER BY nombre ASC")->fetchAll();
require_once __DIR__ . '/../templates/header.php';
?>

<main class="container">
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
                        <form method="POST" onsubmit="return confirm('¿Borrar?')">
                            <input type="hidden" name="action" value="delete"><input type="hidden" name="clase_id" value="<?=$c['id']?>">
                            <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>"><button type="submit" class="btn-danger">🗑️</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <?php endif; ?>
</main>

<script>
function abrirModalClase() {
    document.getElementById('modalClase').style.display = 'block';
    document.getElementById('actionClase').value = 'create';
    document.getElementById('modalTitle').innerText = 'Programar Nueva Clase';
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