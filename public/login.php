<?php
require_once __DIR__ . '/../config.php';

if(isset($_SESSION['usuario'])){
  header('Location: ' . BASE_URL . '/dashboard/' . $_SESSION['usuario']['rol'] . '.php');
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
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = :email LIMIT 1');
    $stmt->execute(['email'=>$email]);
    $u = $stmt->fetch();

    if($u){
      if(password_verify($password, $u['password_hash'])){
        session_regenerate_id(true);
        $_SESSION['usuario'] = $u;
        // REDIRECCION CORREGIDA
        header('Location: ' . BASE_URL . '/dashboard/' . $u['rol'] . '.php');
        exit;
      } else {
        $msg = 'Contraseña incorrecta.';
      }
    } else {
      // REGISTRO AUTOMATICO
      $hash = password_hash($password, PASSWORD_BCRYPT);
      if(!$nombre){
        $parts = explode('@',$email);
        $nombre = $parts[0];
      }
      $ins = $pdo->prepare('INSERT INTO usuarios (nombre, apellidos, email, password_hash, rol, fecha_registro) VALUES (:n, :ap, :e, :p, "alumno", NOW())');
      // Añadí fecha_registro NOW() para evitar errores si la columna no tiene default
      $ins->execute(['n'=>$nombre, 'ap'=>'', 'e'=>$email, 'p'=>$hash]);
      $id = $pdo->lastInsertId();
      
      $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
      $stmt->execute(['id'=>$id]);
      $nuevo = $stmt->fetch();
      
      session_regenerate_id(true);
      $_SESSION['usuario'] = $nuevo;
      // REDIRECCION CORREGIDA
      header('Location: ' . BASE_URL . '/dashboard/alumno.php');
      exit;
    }
  }
}
require_once __DIR__ . '/../templates/header.php';
?>
<main class="container">
  <div class="login-box">
    <h2>Iniciar sesión / Registrarse</h2>
    <?php if($msg): ?><p style="color:#ff7b7b; background:rgba(255,0,0,0.1); padding:10px; border-radius:5px;"><?=$msg?></p><?php endif; ?>
    <form method="POST">
      <label style="display:block; margin-bottom:5px; color:#ccc;">Nombre (Solo si eres nuevo)</label>
      <input name="nombre" type="text" placeholder="Tu nombre" />
      
      <label style="display:block; margin-bottom:5px; color:#ccc;">Email</label>
      <input name="email" type="email" required placeholder="ejemplo@correo.com"/>
      
      <label style="display:block; margin-bottom:5px; color:#ccc;">Contraseña</label>
      <input name="password" type="password" required />
      
      <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'])?>" />
      <button class="btn-primary" style="width:100%; margin-top:10px;" type="submit">Entrar / Registrar</button>
    </form>
  </div>
</main>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>