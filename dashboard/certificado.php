<?php
require_once __DIR__ . '/../config.php';
if(!isset($_SESSION['usuario'])) die('Acceso denegado');

$u = $_SESSION['usuario'];
$uid = $u['id'];

// Obtener puntos totales y posición
$stmt = $pdo->prepare("
    SELECT (SELECT SUM(puntos) FROM ranking WHERE usuario_id = :uid) as puntos,
    (SELECT COUNT(*) + 1 FROM (SELECT SUM(puntos) as t FROM ranking GROUP BY usuario_id) as s WHERE t > (SELECT IFNULL(SUM(puntos),0) FROM ranking WHERE usuario_id = :uid2)) as posicion
");
$stmt->execute(['uid' => $uid, 'uid2' => $uid]);
$data = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado - <?= $u['nombre'] ?></title>
    <style>
        body { font-family: 'serif'; background: white; color: black; text-align: center; padding: 50px; }
        .border { border: 10px double #1428A0; padding: 50px; outline: 2px solid #1428A0; outline-offset: -20px; }
        h1 { font-size: 50px; margin-bottom: 0; }
        .sub { font-style: italic; font-size: 20px; margin-bottom: 50px; }
        .name { font-size: 40px; font-weight: bold; text-decoration: underline; margin: 20px 0; }
        .stats { margin-top: 50px; font-size: 22px; }
        .footer { margin-top: 100px; display: flex; justify-content: space-around; }
        .seal { width: 100px; height: 100px; border-radius: 50%; border: 4px solid #1428A0; display: inline-block; line-height: 100px; font-weight: bold; color: #1428A0; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()">Descargar / Imprimir Certificado</button>
        <a href="alumno.php">Volver al panel</a>
    </div>

    <div class="border">
        <p class="sub">Certificado de Honor y Trayectoria</p>
        <h1>GIMNASIO TFC</h1>
        <p>Se otorga con orgullo a:</p>
        <div class="name"><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']) ?></div>
        <p>Por su dedicación, disciplina y esfuerzo constante en las artes marciales.</p>
        
        <div class="stats">
            Puntos Acumulados: <strong><?= $data['puntos'] ?? 0 ?></strong><br>
            Posición en el Ranking Global: <strong>#<?= $data['posicion'] ?></strong>
        </div>

        <div class="footer">
            <div>
                <br>_______________________<br>
                Firma del Instructor
            </div>
            <div class="seal">TFC</div>
            <div>
                Fecha: <?= date('d/m/Y') ?><br>_______________________<br>
                Sello del Gimnasio
            </div>
        </div>
    </div>
</body>
</html>