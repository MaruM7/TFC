<?php
// templates/header.php
if (session_status() === PHP_SESSION_NONE) session_start();
$usuario = $_SESSION['usuario'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Gimnasio TFC</title>
<link rel="stylesheet" href="/gimnasio-tfc/public/style.css">
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <a class="logo" href="/public/index.php">GIMNASIO TFC</a>
    <nav class="main-nav" aria-label="Menú principal">
      <button id="navToggle" class="nav-toggle" aria-expanded="false">☰</button>
      <ul id="navList" class="nav-list">
        <li><a href="/public/index.php#clases">Clases</a></li>
        <li><a href="/public/index.php#eventos">Eventos</a></li>
        <li><a href="/public/index.php#contacto">Contacto</a></li>
      </ul>
    </nav>

    <div class="header-actions">
      <?php if(!$usuario): ?>
        <a class="btn-outline" href="/gimnasio-tfc/public/login.php">Iniciar sesión / Registrarse</a>
      <?php else: ?>
        <div class="user-menu">
          <button id="userMenuBtn" class="btn-outline"><?=htmlspecialchars($usuario['nombre'])?> ▾</button>
          <div id="userDropdown" class="dropdown" aria-hidden="true">
            <a href="/dashboard/<?=htmlspecialchars($usuario['rol'])?>.php">Mi panel</a>
            <a href="/public/logout.php">Cerrar sesión</a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</header>
