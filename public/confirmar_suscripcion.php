<?php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $uid = $_SESSION['usuario']['id'] ?? null;

    if ($uid && isset($data['orderID'])) {
        $meses = (int)$data['meses'];
        $monto = (float)$data['monto'];
        $plan = htmlspecialchars($data['plan']);
        $vencimiento = date('Y-m-d H:i:s', strtotime("+$meses months"));

        // 1. Insertamos en el historial de PAGOS
        $stmtP = $pdo->prepare("INSERT INTO pagos (usuario_id, monto, paypal_order_id, plan_nombre) VALUES (?, ?, ?, ?)");
        $stmtP->execute([$uid, $monto, $data['orderID'], $plan]);

        // 2. Actualizamos la suscripción actual (cancelamos las anteriores)
        $pdo->prepare("UPDATE suscripciones SET estado = 'cancelado' WHERE usuario_id = ?")->execute([$uid]);
        
        $stmtS = $pdo->prepare("INSERT INTO suscripciones (usuario_id, paypal_id, plan_nombre, monto, fecha_vencimiento) 
                               VALUES (:uid, :pid, :plan, :monto, :venc)");
        $stmtS->execute([
            'uid'  => $uid,
            'pid'  => $data['orderID'],
            'plan' => $plan,
            'monto' => $monto,
            'venc' => $vencimiento
        ]);

        // 3. El usuario pasa a ser ACTIVO en la tabla 'usuarios'
        $pdo->prepare("UPDATE usuarios SET activo = 1 WHERE id = ?")->execute([$uid]);

        echo json_encode(['status' => 'ok']);
    }
}