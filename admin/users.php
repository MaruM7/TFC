<?php
require_once __DIR__ . '/../config.php';
if(!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin'){ header('Location: /public/index.php'); exit; }

$q = $_GET['q'] ?? '';
if($q){
  $stmt = $pdo->prepare("SELECT id,nombre,apellidos,email,rol,activo,fecha_registro FROM usuarios WHERE nombre LIKE :q OR apellidos LIKE :q OR email LIKE :q ORDER BY fecha_registro DESC LIMIT 500");
  $stmt->execute([':q'=>"%$q%"]);
} else {
  $stmt = $pdo->query("SELECT id,nombre,apellidos,email,rol,activo,fecha_registro FROM usuarios ORDER BY fecha_registro DESC LIMIT 200");
}
$users = $stmt->fetchAll();

// borrar usuario (POST)
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete'){
  if(!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) { die('CSRF'); }
  $id = (int)$_POST['id'];
  if($id === (int)$_SESSION['usuario']['id']) { $msg = "No puedes borrarte a ti mismo."; }
  else {
    $pdo->prepare("DELETE FROM usuarios WHERE id = :id")->execute([':id'=>$id]);
    header('Location: users.php?deleted=1'); exit;
  }
}

require_once __DIR__ . '/../templates/header.php';
?>
<main class="container">
  <h1>Gestionar usuarios</h1>
  <form method="GET" style="margin-bottom:16px">
    <input name="q" placeholder="Buscar por nombre o email" value="<?=htmlspecialchars($q)?>" />
    <button class="btn-outline" type="submit">Buscar</button>
  </form>

  <table class="table">
    <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Activo</th><th>Registro</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach($users as $u): ?>
      <tr>
        <td><?=htmlspecialchars($u['nombre']).' '.htmlspecialchars($u['apellidos'])?></td>
        <td><?=htmlspecialchars($u['email'])?></td>
        <td><?=htmlspecialchars($u['rol'])?></td>
        <td><?=htmlspecialchars($u['activo'])?></td>
        <td><?=htmlspecialchars($u['fecha_registro'])?></td>
        <td>
          <a class="btn-outline" href="edit_user.php?id=<?=htmlspecialchars($u['id'])?>">Editar</a>
          <form method="POST" style="display:inline" onsubmit="return confirm('Borrar usuario?');">
            <input type="hidden" name="id" value="<?=htmlspecialchars($u['id'])?>">
            <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'])?>">
            <input type="hidden" name="action" value="delete">
            <button class="btn-outline" type="submit">Borrar</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</main>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
