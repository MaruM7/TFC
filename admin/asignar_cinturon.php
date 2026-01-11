<?php
require_once __DIR__ . '/../config.php';

// Seguridad: Solo admin
if(!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../public/index.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_POST['usuario_id'];
    $disciplina_id = $_POST['disciplina_id'];
    $cinturon_id = $_POST['cinturon_id'];

    $stmt = $pdo->prepare("
        INSERT INTO ranking (usuario_id, disciplina_id, cinturon_actual) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE cinturon_actual = VALUES(cinturon_actual)
    ");
    $stmt->execute([$usuario_id, $disciplina_id, $cinturon_id]);
    
    header("Location: asignar_cinturon.php?success=1"); exit;
}

$alumnos = $pdo->query("SELECT id, nombre, apellidos FROM usuarios WHERE rol = 'alumno'")->fetchAll();
$disciplinas = $pdo->query("SELECT id, nombre FROM disciplinas")->fetchAll();
$cinturones = $pdo->query("SELECT * FROM cinturones ORDER BY nivel ASC")->fetchAll();

require_once __DIR__ . '/../templates/header.php';
?>

<main class="container">
    <div class="card" style="max-width: 500px; margin: 0 auto;">
        <h2>🎓 Graduación de Alumno</h2>
        <form method="POST">
            <label>1. Seleccionar Alumno</label>
            <select name="usuario_id" required>
                <?php foreach($alumnos as $al): ?>
                    <option value="<?= $al['id'] ?>"><?= htmlspecialchars($al['nombre'].' '.$al['apellidos']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>2. Disciplina</label>
            <select name="disciplina_id" id="select_disciplina" required onchange="actualizarCinturones()">
                <option value="">-- Elige Disciplina --</option>
                <?php foreach($disciplinas as $dis): ?>
                    <option value="<?= $dis['id'] ?>"><?= htmlspecialchars($dis['nombre']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>3. Nuevo Cinturón</label>
            <select name="cinturon_id" id="select_cinturon" required disabled>
                <option value="">-- Selecciona disciplina primero --</option>
            </select>

            <button type="submit" class="btn-primary" style="width:100%; margin-top:20px;">Actualizar Rango</button>
        </form>
    </div>
</main>

<script>
// Lista de cinturones en formato JSON para el filtro
const listaCinturones = <?= json_encode($cinturones) ?>;

function actualizarCinturones() {
    const disciplinaId = document.getElementById('select_disciplina').value;
    const comboCinturones = document.getElementById('select_cinturon');
    
    comboCinturones.innerHTML = '<option value="">-- Selecciona Cinturón --</option>';
    
    if(!disciplinaId) {
        comboCinturones.disabled = true;
        return;
    }

    const filtrados = listaCinturones.filter(c => c.disciplina_id == disciplinaId);
    
    if(filtrados.length > 0) {
        filtrados.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.nombre + ' (Nivel ' + c.nivel + ')';
            comboCinturones.appendChild(opt);
        });
        comboCinturones.disabled = false;
    } else {
        comboCinturones.innerHTML = '<option value="">Sin cinturones creados</option>';
        comboCinturones.disabled = true;
    }
}
</script>