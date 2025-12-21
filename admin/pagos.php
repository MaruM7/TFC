<?php
require_once __DIR__ . '/../config.php';

// Seguridad: Solo el administrador puede acceder
if(!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../public/index.php');
    exit;
}

// 1. Obtener filtros de búsqueda y plan
$search = $_GET['search'] ?? '';
$plan_filter = $_GET['plan'] ?? '';

// 2. Preparar la consulta con filtros dinámicos
$sql = "SELECT p.*, u.nombre, u.apellidos, u.email 
        FROM pagos p 
        JOIN usuarios u ON p.usuario_id = u.id 
        WHERE (u.nombre LIKE ? OR u.apellidos LIKE ? OR u.email LIKE ?)";

$params = ["%$search%", "%$search%", "%$search%"];

if ($plan_filter) {
    $sql .= " AND p.plan_nombre = ?";
    $params[] = $plan_filter;
}

$sql .= " ORDER BY p.fecha_pago DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$historial = $stmt->fetchAll();

// 3. Calcular la recaudación total histórica
$totalHistorico = $pdo->query("SELECT SUM(monto) FROM pagos")->fetchColumn() ?: 0;

require_once __DIR__ . '/../templates/header.php';
?>

<main class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:40px; flex-wrap: wrap; gap: 20px;">
        <div>
            <h1>Historial de Pagos</h1>
            <p style="color:var(--muted)">Consulta y filtra las transacciones de los alumnos.</p>
            <a href="exportar_pagos.php" class="btn-outline" style="display:inline-flex; align-items:center; gap:8px; margin-top:10px; border-color:#2ecc71; color:#2ecc71;">
                📊 Descargar CSV
            </a>
        </div>
        
        <div class="card" style="padding:15px 25px; border-left:4px solid #2ecc71; min-width: 200px;">
            <small style="color:var(--muted); text-transform:uppercase; font-size:11px; font-weight: bold;">Recaudación Total</small>
            <div style="font-size:1.8rem; font-weight:bold; color:#2ecc71;"><?= number_format($totalHistorico, 2) ?>€</div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 25px; padding: 15px;">
        <form method="GET" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                   placeholder="Nombre o email..." 
                   style="flex: 2; min-width: 200px; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--input-bg); color: var(--white);">
            
            <select name="plan" style="flex: 1; min-width: 150px; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--input-bg); color: var(--white);">
                <option value="">Todos los planes</option>
                <option value="Mensual" <?= $plan_filter == 'Mensual' ? 'selected' : '' ?>>Mensual</option>
                <option value="Trimestral" <?= $plan_filter == 'Trimestral' ? 'selected' : '' ?>>Trimestral</option>
                <option value="Anual" <?= $plan_filter == 'Anual' ? 'selected' : '' ?>>Anual</option>
            </select>
            
            <button type="submit" class="btn-primary" style="padding: 10px 20px;">Filtrar</button>
            
            <?php if($search || $plan_filter): ?>
                <a href="pagos.php" class="btn-outline" style="display: flex; align-items: center; padding: 10px 15px;">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card" style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Alumno</th>
                    <th>Plan</th>
                    <th>Monto</th>
                    <th>PayPal ID</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($historial)): ?>
                    <tr><td colspan="5" style="text-align:center; padding:40px; color:var(--muted);">No hay registros.</td></tr>
                <?php else: ?>
                    <?php foreach($historial as $p): ?>
                        <tr>
                            <td style="font-size:0.9rem;"><strong><?= date('d/m/Y', strtotime($p['fecha_pago'])) ?></strong><br><small><?= date('H:i', strtotime($p['fecha_pago'])) ?></small></td>
                            <td><strong><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) ?></strong><br><small style="color:var(--muted);"><?= htmlspecialchars($p['email']) ?></small></td>
                            <td><span style="background:var(--glass); padding:4px 10px; border-radius:6px; font-size:0.75rem;"><?= htmlspecialchars($p['plan_nombre']) ?></span></td>
                            <td style="font-weight:bold; color:var(--accent-2);"><?= number_format($p['monto'], 2) ?>€</td>
                            <td style="font-family:monospace; font-size:0.8rem; color:var(--muted);"><?= htmlspecialchars($p['paypal_order_id']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>