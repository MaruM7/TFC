<?php
require_once __DIR__ . '/../config.php';

// Seguridad: Solo administradores
if(!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../public/index.php');
    exit;
}

// Procesar el formulario (Crear, Editar, Eliminar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) die('Error de seguridad CSRF');

    $action = $_POST['action'];
    $id = (int)($_POST['id'] ?? 0);
    $nombre = htmlspecialchars($_POST['nombre'] ?? '');
    $desc = htmlspecialchars($_POST['descripcion'] ?? '');
    $img = htmlspecialchars($_POST['imagen'] ?? '');

    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO disciplinas (nombre, descripcion, imagen) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $desc, $img]);
        $_SESSION['msg'] = "✅ Actividad creada.";
    } elseif ($action === 'edit') {
        $stmt = $pdo->prepare("UPDATE disciplinas SET nombre = ?, descripcion = ?, imagen = ? WHERE id = ?");
        $stmt->execute([$nombre, $desc, $img, $id]);
        $_SESSION['msg'] = "✅ Actividad actualizada.";
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM disciplinas WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['msg'] = "🗑️ Actividad eliminada.";
    }
    header("Location: actividades.php");
    exit;
}

$actividades = $pdo->query("SELECT * FROM disciplinas ORDER BY nombre ASC")->fetchAll();
require_once __DIR__ . '/../templates/header.php';
?>

<main class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h1>Gestión de Actividades</h1>
        <button onclick="abrirForm()" class="btn-primary">+ Nueva Actividad</button>
    </div>

    <div id="panelForm" class="card" style="display:none; margin-bottom:40px; border:1px solid var(--accent-2);">
        <h2 id="tituloForm">Crear Actividad</h2>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action" id="inputAction" value="create">
            <input type="hidden" name="id" id="inputId" value="">

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:15px;">
                <div>
                    <label style="display:block; margin-bottom:5px;">Nombre de la actividad:</label>
                    <input type="text" name="nombre" id="inputNombre" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--glass); color:inherit;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:5px;">Nombre de imagen (ej: mma.jpg):</label>
                    <input type="text" name="imagen" id="inputImagen" style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--glass); color:inherit;">
                </div>
            </div>

            <label style="display:block; margin-bottom:5px;">Descripción:</label>
            <textarea name="descripcion" id="inputDesc" rows="4" style="width:100%; margin-bottom:20px; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--glass); color:inherit;"></textarea>

            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn-primary">Guardar</button>
                <button type="button" onclick="cerrarForm()" class="btn-outline">Cancelar</button>
            </div>
        </form>
    </div>

    <div class="cards-grid">
        <?php foreach($actividades as $a): ?>
            <div class="card">
                <h3><?= htmlspecialchars($a['nombre']) ?></h3>
                <p style="font-size: 0.9rem; color: var(--muted); margin-bottom:20px;"><?= htmlspecialchars($a['descripcion']) ?></p>
                <div style="display:flex; gap:10px;">
                    <button onclick='cargarEdicion(<?= json_encode($a) ?>)' class="btn-outline" style="flex:1;">Editar</button>
                    <form method="POST" onsubmit="return confirm('¿Seguro?')" style="flex:1;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button type="submit" class="btn-danger" style="width:100%;">Borrar</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<script>
function abrirForm() {
    document.getElementById('panelForm').style.display = 'block';
    document.getElementById('tituloForm').innerText = 'Crear Actividad';
    document.getElementById('inputAction').value = 'create';
    document.getElementById('inputId').value = '';
    document.getElementById('inputNombre').value = '';
    document.getElementById('inputDesc').value = '';
    document.getElementById('inputImagen').value = '';
}
function cerrarForm() { document.getElementById('panelForm').style.display = 'none'; }
function cargarEdicion(a) {
    abrirForm();
    document.getElementById('tituloForm').innerText = 'Editar: ' + a.nombre;
    document.getElementById('inputAction').value = 'edit';
    document.getElementById('inputId').value = a.id;
    document.getElementById('inputNombre').value = a.nombre;
    document.getElementById('inputDesc').value = a.descripcion;
    document.getElementById('inputImagen').value = a.imagen;
}
</script>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>