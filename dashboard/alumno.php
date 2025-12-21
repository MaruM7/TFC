<?php
require_once __DIR__ . '/../config.php';
if(!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'alumno') { header('Location: ../public/login.php'); exit; }

$uid = $_SESSION['usuario']['id'];

// 1. CONSULTAR ESTADO DE SUSCRIPCIÓN PARA ALERTAS
$stmtS = $pdo->prepare("SELECT fecha_vencimiento, estado FROM suscripciones WHERE usuario_id = :uid ORDER BY fecha_inicio DESC LIMIT 1");
$stmtS->execute(['uid' => $uid]);
$sub = $stmtS->fetch();

$alerta = null;
if (!$sub) {
    $alerta = ['color' => '#ff3b30', 'msg' => '⚠️ No tienes una suscripción activa. Elige un plan en la sección de Cuotas para poder entrenar.'];
} else {
    $diasRestantes = (int)((strtotime($sub['fecha_vencimiento']) - time()) / 86400);
    if ($diasRestantes <= 0) {
        $alerta = ['color' => '#ff3b30', 'msg' => '🚫 Tu acceso ha vencido. Por favor, renueva tu suscripción para seguir asistiendo a clase.'];
    } elseif ($diasRestantes <= 5) {
        $alerta = ['color' => '#f1c40f', 'msg' => "⏳ Aviso: Tu suscripción vence en $diasRestantes días. ¡No olvides renovar!"];
    }
}

// 2. POSICIÓN GLOBAL Y PUNTOS
$stmtPos = $pdo->prepare("
    SELECT (COUNT(*) + 1) as mi_posicion, 
           (SELECT SUM(puntos) FROM ranking WHERE usuario_id = :uid) as mis_puntos 
    FROM (SELECT SUM(puntos) as total FROM ranking GROUP BY usuario_id) as totales
    WHERE totales.total > (SELECT IFNULL(SUM(puntos),0) FROM ranking WHERE usuario_id = :uid2)
");
$stmtPos->execute(['uid'=>$uid, 'uid2'=>$uid]);
$stats = $stmtPos->fetch();

// 3. MIS RANKINGS POR DISCIPLINA
$stmtRank = $pdo->prepare('
    SELECT r.puntos, d.nombre as disciplina, c.nombre as cinturon, c.color 
    FROM ranking r 
    JOIN disciplinas d ON r.disciplina_id = d.id 
    LEFT JOIN cinturones c ON r.cinturon_actual = c.id 
    WHERE r.usuario_id = :uid
');
$stmtRank->execute(['uid'=>$uid]);
$rankings = $stmtRank->fetchAll();

// 4. MIS INSCRIPCIONES
$stmtIns = $pdo->prepare('
    SELECT i.*, cl.fecha_hora, d.nombre as disciplina 
    FROM inscripciones i 
    LEFT JOIN clases cl ON i.clase_id = cl.id 
    LEFT JOIN disciplinas d ON cl.disciplina_id = d.id 
    WHERE i.usuario_id = :uid 
    ORDER BY i.fecha_inscripcion DESC
');
$stmtIns->execute(['uid'=>$uid]);
$inscripciones = $stmtIns->fetchAll();

// 5. RESPUESTAS DE ADMIN
$stmtResp = $pdo->prepare("
    SELECT r.respuesta, r.fecha_respuesta, m.mensaje as mi_duda 
    FROM respuestas_admin r 
    JOIN mensajes_contacto m ON r.mensaje_id = m.id 
    WHERE m.usuario_id = ? 
    ORDER BY r.fecha_respuesta DESC
");
$stmtResp->execute([$uid]);
$notificaciones = $stmtResp->fetchAll();

require_once __DIR__ . '/../templates/header.php';
?>

<main class="container">
    <?php if($alerta): ?>
        <div style="background:rgba(255,255,255,0.05); border-left:5px solid <?=$alerta['color']?>; padding:20px; margin-bottom:30px; border-radius:8px;">
            <p style="margin:0; font-weight:bold; color:white;"><?=$alerta['msg']?></p>
        </div>
    <?php endif; ?>

    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:40px;">
        <div>
            <h1>Hola, <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></h1>
            <p style="color:var(--muted)">Vencimiento: <strong><?= $sub ? date('d/m/Y', strtotime($sub['fecha_vencimiento'])) : 'Sin suscripción' ?></strong></p>
        </div>
        <div style="background:var(--accent); padding:15px 25px; border-radius:12px; text-align:center;">
            <small>Posición Global</small>
            <div style="font-size:1.8rem; font-weight:bold;">#<?= $stats['mi_posicion'] ?></div>
            <a href="certificado.php" target="_blank" style="color:white; font-size:0.7rem; text-decoration:underline;">Descargar Certificado</a>
        </div>
    </div>

    <section style="margin-bottom:50px;">
        <h2 style="text-align:center;">🏆 TOP 3 GLOBAL 🏆</h2>
        <?php include __DIR__ . '/../templates/podium.php'; ?>
    </section>

    <div class="cards-grid">
        <section class="card">
            <h2>Mis Disciplinas</h2>
            <?php if($rankings): foreach($rankings as $r): ?>
                <div style="padding:15px 0; border-bottom:1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong><?=htmlspecialchars($r['disciplina'])?></strong><br>
                        <small style="color:var(--muted)"><?=htmlspecialchars($r['puntos'])?> pts acumulados</small>
                    </div>
                    <span style="background:var(--glass); border: 1px solid <?= $r['color'] ?? 'var(--border)' ?>; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold;">
                        🥋 <?=htmlspecialchars($r['cinturon'] ?? 'Blanco')?>
                    </span>
                </div>
            <?php endforeach; else: ?>
                <p>No tienes actividad registrada.</p>
            <?php endif; ?>
        </section>

        <section class="card">
            <h2>Próximas Clases</h2>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead><tr><th>Clase</th><th>Fecha</th><th>Acción</th></tr></thead>
                    <tbody>
                    <?php foreach($inscripciones as $ins): if($ins['clase_id']): ?>
                        <tr>
                            <td><?= htmlspecialchars($ins['disciplina']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($ins['fecha_hora'])) ?></td>
                            <td>
                                <form method="POST" action="<?=BASE_URL?>/public/cancelar_inscripcion.php">
                                    <input type="hidden" name="id" value="<?=$ins['id']?>">
                                    <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token'] ?? ''?>">
                                    <button type="submit" class="btn-danger" style="padding:4px 8px; font-size:0.7rem;">Cancelar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endif; endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <?php if($notificaciones): ?>
        <section class="card" style="margin-top:40px; border-top: 4px solid #3498db;">
            <h2 style="margin-bottom:20px;">💬 Respuestas de Administración</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <?php foreach($notificaciones as $n): ?>
                    <div style="background:rgba(255,255,255,0.03); padding:15px; border-radius:10px; border: 1px solid var(--border); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <p style="font-size:0.8rem; color:var(--muted); margin-bottom:10px;">
                                Sobre tu consulta: "<i><?= htmlspecialchars($n['mi_duda']) ?></i>"
                            </p>
                            <p style="margin:0;"><strong>Admin respondió:</strong><br>
                            <?= nl2br(htmlspecialchars($n['respuesta'])) ?></p>
                        </div>
                        <p style="text-align:right; font-size:0.7rem; color:var(--muted); margin:0; margin-top:15px;">
                            <?= date('d/m/Y H:i', strtotime($n['fecha_respuesta'])) ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>