<?php
require_once __DIR__ . '/../config.php';

if(isset($_SESSION['usuario'])){
  header('Location: /dashboard/' . $_SESSION['usuario']['rol'] . '.php');
  exit;
}

$msg = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $email = filter_input(INPUT_POST,'email', FILTER_VALIDATE_EMAIL);
  $password = $_POST['password'] ?? '';
  $nombre = trim($_POST['nombre'] ?? '');

  if(!$email || !$password){
    $msg = 'Email y contraseña obligatorios.';
  } else {
    // buscar por email
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = :email LIMIT 1');
    $stmt->execute(['email'=>$email]);
    $u = $stmt->fetch();

    if($u){
      // existe -> verificar
      if(password_verify($password, $u['password_hash'])){
        session_regenerate_id(true);
        $_SESSION['usuario'] = $u;
        header('Location: /dashboard/' . $u['rol'] . '.php');
        exit;
      } else {
        $msg = 'Contraseña incorrecta.';
      }
    } else {
      // registrar nuevo usuario como alumno
      $hash = password_hash($password, PASSWORD_BCRYPT);
      if(!$nombre){
        $parts = explode('@',$email);
        $nombre = $parts[0];
      }
      $ins = $pdo->prepare('INSERT INTO usuarios (nombre, apellidos, email, password_hash, rol) VALUES (:n, :ap, :e, :p, "alumno")');
      $ins->execute(['n'=>$nombre, 'ap'=>'', 'e'=>$email, 'p'=>$hash]);
      $id = $pdo->lastInsertId();
      $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
      $stmt->execute(['id'=>$id]);
      $nuevo = $stmt->fetch();
      session_regenerate_id(true);
      $_SESSION['usuario'] = $nuevo;
      header('Location: /dashboard/alumno.php');
      exit;
    }
  }
}
require_once __DIR__ . '/../templates/header.php';
?>
<main class="container">
  <div class="login-box">
    <h2>Iniciar sesión o registrarse</h2>
    <?php if($msg): ?><p style="color:#ff9b9b"><?=$msg?></p><?php endif; ?>
    <form method="POST">
      <label>Nombre (opcional para registro)</label>
      <input name="nombre" type="text" placeholder="Tu nombre" />
      <label>Email</label>
      <input name="email" type="email" required />
      <label>Contraseña</label>
      <input name="password" type="password" required />
      <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'])?>" />
      <button class="btn-primary" type="submit">Entrar / Registrar</button>
    </form>
  </div>
</main>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
