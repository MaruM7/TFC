<?php
require_once __DIR__ . '/../config.php';

// Seguridad: Solo admin
if(!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../public/index.php'); exit;
}

// 1. Guardar nuevo cinturón
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear'])) {
    $d_id = $_POST['disciplina_id'];
    $nombre = htmlspecialchars($_POST['nombre']);
    $color = $_POST['color']; // Ej: 'white', 'yellow', '#FF5733'
    $nivel = (int)$_POST['nivel']; // El orden (1, 2, 3...)

    $stmt = $pdo->prepare("INSERT INTO cinturones (disciplina_id, nombre, color, nivel) VALUES (?, ?, ?, ?)");
    $stmt->execute([$d_id, $nombre, $color, $nivel]);
}

// 2. Borrar cinturón
if (isset($_GET['borrar'])) {
    $stmt = $pdo->prepare("DELETE FROM cinturones WHERE id = ?");
    $stmt->execute([(int)$_GET['borrar']]);
    header("Location: config_cinturones.php"); exit;
}

$disciplinas = $pdo->query("SELECT * FROM disciplinas ORDER BY nombre ASC")->fetchAll();
$cinturones = $pdo->query("SELECT c.*, d.nombre as disciplina_nom FROM cinturones c JOIN disciplinas d ON c.disciplina_id = d.id ORDER BY d.nombre, c.nivel ASC")->fetchAll();

require_once __DIR__ . '/../templates/header.php';
?>

<main class="container">
    <h1>Configuración de Niveles y Cinturones</h1>
    <p style="color:var(--muted); margin-bottom:30px;">Define los rangos para cada disciplina. El nivel 1 es el más bajo.</p>

    <div style="display: grid; grid-template-columns: 350px 1fr; gap: 30px;">
        <section class="card">
            <h3>Añadir Cinturón</h3>
            <form method="POST">
                <label>Disciplina:</label>
                <select name="disciplina_id" required>
                    <?php foreach($disciplinas as $dis): ?>
                        <option value="<?= $dis['id'] ?>"><?= htmlspecialchars($dis['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Nombre (Ej: Azul, 1º DAN):</label>
                <input type="text" name="nombre" placeholder="Nombre del rango" required>

                <label>Color (Nombre o Hexadecimal):</label>
                <input type="text" name="color" placeholder="Ej: blue o #0000FF" required>

                <label>Nivel Numérico (Orden):</label>
                <input type="number" name="nivel" placeholder="1" required>
                <small style="color:var(--muted); display:block; margin-bottom:15px;">Define el orden: 1 es principiante.</small>

                <button type="submit" name="crear" class="btn-primary" style="width:100%;">Registrar Cinturón</button>
            </form>
        </section>

        <section class="card">
            <h3>Librería de Cinturones</h3>
            <table class="table">
                <thead>
                    <tr><th>Disciplina</th><th>Nombre</th><th>Color</th><th>Nivel</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    <?php foreach($cinturones as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['disciplina_nom']) ?></td>
                        <td><strong><?= htmlspecialchars($c['nombre']) ?></strong></td>
                        <td>
                            <span style="display:inline-block; width:40px; height:15px; background:<?= $c['color'] ?>; border:1px solid var(--border); border-radius:4px;"></span>
                        </td>
                        <td><?= $c['nivel'] ?></td>
                        <td>
                            <a href="?borrar=<?= $c['id'] ?>" onclick="return confirm('¿Borrar este nivel?')" style="color:#ff3b30; text-decoration:none;">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>
</main>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>