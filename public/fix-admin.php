<?php
require_once __DIR__ . '/../config.php';

// Definimos la nueva contraseña
$nueva_pass = 'admin1234';
$hash = password_hash($nueva_pass, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = :hash WHERE email = 'admin@gimnasio.com'");
    $stmt->execute(['hash' => $hash]);
    
    echo "<h1>✅ Contraseña actualizada con éxito</h1>";
    echo "<p>Email: <b>admin@gimnasio.com</b></p>";
    echo "<p>Nueva contraseña: <b>admin1234</b></p>";
    echo "<br><a href='login.php'>Ir al Login</a>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>