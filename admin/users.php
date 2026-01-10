<?php
// 1. IMPORTANTE: Esto debe ser siempre la primera línea para cargar la sesión
require_once __DIR__ . '/../config.php';

// Seguridad: Solo el administrador puede gestionar usuarios
if(!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin'){ 
    header('Location: ' . BASE_URL . '/public/index.php'); 
    exit; 
}

$msg = $_SESSION['msg'] ?? null;
unset($_SESSION['msg']);

// --- LÓGICA DE ACCIONES (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Verificación de seguridad CSRF común para todas las acciones
    if(!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) { die('Error de validación CSRF'); }
    
    $uid = (int)$_POST['usuario_id'];

    // ACCIÓN 1: ELIMINAR USUARIO
    if ($_POST['action'] === 'delete_user') {
        if ($uid == $_SESSION['usuario']['id']) {
            $_SESSION['msg'] = "❌ No puedes eliminar tu propia cuenta.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$uid]);
            $_SESSION['msg'] = "🗑️ Usuario eliminado permanentemente.";
        }
    }
    
    // ACCIÓN 2: CAMBIAR ESTADO (SUSPENDER/ACTIVAR)
    elseif ($_POST['action'] === 'toggle_status') {
        $nuevoEstado = (int)$_POST['nuevo_estado'];
        
        if ($uid == $_SESSION['usuario']['id']) {
            $_SESSION['msg'] = "❌ No puedes desactivar tu propia cuenta de administrador.";
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET activo = :estado WHERE id = :id");
            $stmt->execute(['estado' => $nuevoEstado, 'id' => $uid]);
            $texto = $nuevoEstado ? 'activado' : 'suspendido';
            $_SESSION['msg'] = "✅ Usuario $texto correctamente.";
        }
    }

    // Redirección limpia para evitar reenvío de formularios
    header('Location: users.php' . (isset($_GET['search']) ? '?search='.$_GET['search'] : ''));
    exit;
}

// --- LÓGICA DE BÚSQUEDA Y FILTRADO (GET) ---
$search = $_GET['search'] ?? '';
$query = "SELECT * FROM usuarios WHERE 1=1";
$params = [];

if ($search !== '') {
    $query .= " AND (nombre LIKE :s OR apellidos LIKE :s OR email LIKE :s)";
    $params['s'] = "%$search%";
}

$query .= " ORDER BY rol ASC, nombre ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$usuarios = $stmt->fetchAll();

require_once __DIR__ . '/../templates/header.php';
?>

<main class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h1>Gestión de Usuarios</h1>
        <a href="<?= BASE_URL ?>/dashboard/admin.php" style="color:var(--accent-2); text-decoration:none;">← Volver al Panel</a>
    </div>

    <?php if($msg): ?>
        <div style="background:rgba(46, 204, 113, 0.2); color:#2ecc71; padding:15px; border-radius:8px; margin-bottom:20px;">
            <?= $msg ?>
        </div>
    <?php endif; ?>

    <div class="card" style="margin-bottom: 30px; padding: 15px;">
        <form method="GET" style="display:flex; gap:10px;">
            <input type="text" name="search" placeholder="Buscar por nombre, email..." 
                   value="<?= htmlspecialchars($search) ?>" 
                   style="flex:1; padding:10px; background:rgba(0,0,0,0.2); border:1px solid #444; color:white; border-radius:8px;">
            <button type="submit" class="btn-primary">Buscar</button>
            <?php if($search): ?>
                <a href="users.php" class="btn-outline" style="line-height:2.4;">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card" style="padding:0; overflow-x:auto;">
        <table class="table" style="margin-top:0;">
            <thead>
                <tr>
                    <th>Nombre y Apellidos</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th style="text-align:center;">Estado</th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($usuarios as $u): ?>
                    <tr style="<?= !$u['activo'] ? 'opacity:0.5; background:rgba(255,0,0,0.02);' : '' ?>">
                        <td>
                            <strong><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']) ?></strong>
                            <?php if($u['id'] == $_SESSION['usuario']['id']): ?>
                                <span style="font-size:0.7rem; background:var(--accent); padding:2px 6px; border-radius:4px; margin-left:5px;">TÚ</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <span style="font-size:0.8rem; text-transform:uppercase; color:<?= $u['rol'] === 'admin' ? '#f1c40f' : ($u['rol'] === 'instructor' ? '#2ecc71' : 'var(--muted)') ?>">
                                <?= $u['rol'] ?>
                            </span>
                        </td>
                        <td style="text-align:center;">
                            <?php if($u['activo']): ?>
                                <span style="color:#2ecc71; font-size:0.8rem;">● Activo</span>
                            <?php else: ?>
                                <span style="color:#e74c3c; font-size:0.8rem;">● Suspendido</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right; white-space: nowrap;">
                            <?php if($u['id'] != $_SESSION['usuario']['id']): ?>
                                
                                <form method="POST" style="display:inline-block; margin-right:5px;">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="usuario_id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="nuevo_estado" value="<?= $u['activo'] ? 0 : 1 ?>">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    
                                    <button type="submit" class="<?= $u['activo'] ? 'btn-danger' : 'btn-primary' ?>" style="font-size:0.8rem; padding:5px 10px;">
                                        <?= $u['activo'] ? 'Suspender' : 'Activar' ?>
                                    </button>
                                </form>

                                <form method="POST" style="display:inline-block;" onsubmit="return confirm('¿Estás seguro de eliminar a este usuario permanentemente? Esta acción no se puede deshacer.');">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="usuario_id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <button type="submit" class="btn-danger" style="font-size:0.8rem; padding:5px 10px; background:#e74c3c; border-color:#c0392b;">Eliminar</button>
                                </form>

                            <?php else: ?>
                                <small style="color:var(--muted)">Sin acciones</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if(empty($usuarios)): ?>
                    <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--muted);">No se encontraron usuarios.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>