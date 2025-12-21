<?php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje_id = $_POST['mensaje_id'];
    $respuesta = htmlspecialchars($_POST['respuesta']);
    $admin_id = $_SESSION['usuario']['id'];

    // Guardamos la respuesta en la tabla que acabas de crear
    $stmt = $pdo->prepare("INSERT INTO respuestas_admin (mensaje_id, admin_id, respuesta) VALUES (?, ?, ?)");
    $stmt->execute([$mensaje_id, $admin_id, $respuesta]);

    // Marcamos el mensaje original como leído
    $pdo->prepare("UPDATE mensajes_contacto SET leido = 1 WHERE id = ?")->execute([$mensaje_id]);

    header("Location: mensajes.php?enviado=ok");
}