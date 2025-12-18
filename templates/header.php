<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';
$usuario = $_SESSION['usuario'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Gimnasio TFC</title>
  <link rel="stylesheet" href="<?=BASE_URL?>/public/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <a class="logo" href="<?=BASE_URL?>/public/index.php">GIMNASIO TFC</a>
    
    <nav class="main-nav">
      <ul id="navList" class="nav-list">
        <li><a href="<?=BASE_URL?>/public/index.php">Inicio</a></li>
        <li><a href="<?=BASE_URL?>/public/cuotas.php">Cuotas</a></li>
        <li><a href="<?=BASE_URL?>/public/contacto.php">Contacto</a></li>
      </ul>
    </nav>

    <div class="header-actions">
      <?php if(!$usuario): ?>
        <a class="btn-outline" href="<?=BASE_URL?>/public/login.php">Entrar / Registro</a>
      <?php else: ?>
        <div class="user-menu">
          <button id="userMenuBtn" class="btn-outline" type="button">
            <?=htmlspecialchars($usuario['nombre'])?> ▾
          </button>
          <div id="userDropdown" class="dropdown" aria-hidden="true">
            <a href="<?=BASE_URL?>/dashboard/<?=htmlspecialchars($usuario['rol'])?>.php">Mi Panel</a>
            <a href="<?=BASE_URL?>/public/logout.php">Cerrar sesión</a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</header>