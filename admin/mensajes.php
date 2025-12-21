<?php
require_once __DIR__ . '/../config.php';
if(!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') { header('Location: ../public/index.php'); exit; }

// Consultamos los mensajes uniendo con la tabla de usuarios para saber si son registrados
$mensajes = $pdo->query("SELECT * FROM mensajes_contacto ORDER BY fecha DESC")->fetchAll();

require_once __DIR__ . '/../templates/header.php';
?>

<main class="container">
    <h1 style="margin-bottom:30px;">Buzón de Consultas</h1>
    <div class="cards-grid">
        <?php foreach($mensajes as $m): 
            $tel_clean = preg_replace('/[^0-9]/', '', $m['telefono']); 
        ?>
            <div class="card" style="border-top: 5px solid <?= $m['usuario_id'] ? '#3498db' : '#2ecc71' ?>; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                        <span style="font-size:0.7rem; background:var(--glass); padding:2px 8px; border-radius:10px; font-weight:bold;">
                            <?= $m['usuario_id'] ? '👤 ALUMNO' : '✨ EXTERNO' ?>
                        </span>
                        <small style="color:var(--muted)"><?= date('d/m/y H:i', strtotime($m['fecha'])) ?></small>
                    </div>
                    <p><strong><?= htmlspecialchars($m['nombre']) ?></strong></p>
                    <p style="font-size:0.85rem; color:var(--muted); margin-bottom:15px;"><?= htmlspecialchars($m['email']) ?></p>
                    <div style="background:rgba(255,255,255,0.05); padding:12px; border-radius:8px; font-size:0.9rem; border: 1px solid var(--border);">
                        "<?= nl2br(htmlspecialchars($m['mensaje'])) ?>"
                    </div>
                </div>

                <div style="margin-top:20px;">
                    <?php if($m['usuario_id']): ?>
                        <form method="POST" action="responder_interno.php">
                            <input type="hidden" name="mensaje_id" value="<?= $m['id'] ?>">
                            <textarea name="respuesta" placeholder="Escribe tu respuesta para su panel..." required style="height:70px; resize:none; margin-bottom:10px;"></textarea>
                            <button type="submit" class="btn-primary" style="width:100%;">Enviar al Dashboard</button>
                        </form>
                    <?php else: ?>
                        <a href="https://wa.me/34<?= $tel_clean ?>?text=Hola%20<?= urlencode($m['nombre']) ?>,%20te%20contacto%20desde%20el%20gimnasio..." 
                           target="_blank" class="btn-primary" style="background:#25d366; text-align:center; display:block; text-decoration:none;">
                           Responder por WhatsApp
                        </a>
                        <p style="text-align:center; font-size:0.7rem; color:var(--muted); margin-top:8px;">Tel: <?= $m['telefono'] ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>