<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../templates/header.php';

$mensaje_exito = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = htmlspecialchars($_POST['nombre'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $telefono = htmlspecialchars($_POST['telefono'] ?? '');
    $msg = htmlspecialchars($_POST['mensaje'] ?? '');
    
    // Capturar ID si el usuario está logueado
    $usuario_id = isset($_SESSION['usuario']) ? $_SESSION['usuario']['id'] : null;

    if (!empty($nombre) && !empty($email) && !empty($msg)) {
        $stmt = $pdo->prepare("INSERT INTO mensajes_contacto (usuario_id, nombre, email, telefono, mensaje) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$usuario_id, $nombre, $email, $telefono, $msg]);
        $mensaje_exito = "✅ ¡Solicitud enviada! Nos pondremos en contacto contigo pronto.";
    }
}
?>

<style>
    /* Evita que el usuario pueda deformar el formulario arrastrando la esquina */
    .contact-form textarea {
        resize: none;
    }
    
    /* Estilo visual para campos inválidos mientras se escribe */
    .contact-form input:invalid:focus {
        border-color: #ff3b30;
        box-shadow: 0 0 5px rgba(255, 59, 48, 0.5);
    }
</style>

<main class="container">
    <div style="max-width: 600px; margin: 0 auto;">
        <h1 style="text-align:center;">Contacto</h1>
        
        <?php if($mensaje_exito): ?>
            <div class="card" style="border-color: #2ecc71; color: #2ecc71; margin-bottom: 20px; text-align: center;">
                <?= $mensaje_exito ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="contact-form card">
            <label>Nombre completo</label>
            <input type="text" name="nombre" value="<?= $_SESSION['usuario']['nombre'] ?? '' ?>" required>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label>Email de contacto</label>
                    <input type="email" name="email" 
                           value="<?= $_SESSION['usuario']['email'] ?? '' ?>" 
                           placeholder="ejemplo@correo.com" required>
                </div>
                <div>
                    <label>Teléfono (9 dígitos)</label>
                    <input type="tel" name="telefono" 
                           pattern="[0-9]{9}" 
                           title="Por favor, introduce un número de teléfono válido de 9 dígitos" 
                           placeholder="600123456" required>
                </div>
            </div>
            
            <label>¿En qué podemos ayudarte?</label>
            <textarea name="mensaje" rows="5" required 
                      placeholder="Escribe aquí tu consulta..."></textarea>
            
            <button type="submit" class="btn-primary" style="width:100%;">Enviar Consulta</button>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>