<?php
require_once __DIR__ . '/../config.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aquí iría la lógica para enviar email (PHPMailer, etc.)
    // Como es un PFC, simulamos el envío.
    $nombre = htmlspecialchars($_POST['nombre']);
    $msg = "¡Gracias $nombre! Hemos recibido tu mensaje. Te contactaremos pronto.";
}

require_once __DIR__ . '/../templates/header.php';
?>
<main class="container">
  <div style="text-align:center; margin-bottom:40px;">
    <h1>Contacto</h1>
    <p style="color:var(--muted)">¿Tienes dudas? Escríbenos.</p>
  </div>

  <?php if($msg): ?>
    <div style="background:rgba(46, 204, 113, 0.2); color:#2ecc71; padding:15px; border-radius:8px; text-align:center; margin-bottom:20px;">
        <?=$msg?>
    </div>
  <?php endif; ?>

  <form method="POST" class="contact-form">
    <label>Nombre</label>
    <input type="text" name="nombre" class="form-control" style="width:100%; padding:10px; margin-bottom:15px; background:rgba(0,0,0,0.2); border:1px solid #444; color:white; border-radius:8px;" required>
    
    <label>Email</label>
    <input type="email" name="email" style="width:100%; padding:10px; margin-bottom:15px; background:rgba(0,0,0,0.2); border:1px solid #444; color:white; border-radius:8px;" required>
    
    <label>Mensaje</label>
    <textarea name="mensaje" required></textarea>
    
    <button type="submit" class="btn-primary" style="width:100%">Enviar Mensaje</button>
  </form>
</main>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>