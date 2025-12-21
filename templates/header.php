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
  <title>Umbral Academy | Artes Marciales</title>
  <link rel="stylesheet" href="<?=BASE_URL?>/public/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="<?=BASE_URL?>/img/favicon.png">
  <style>
    /* Ajuste para que el logo y texto estén alineados */
    .logo {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        font-weight: 700;
        color: var(--white);
    }
    
    .logo img {
        height: 45px; /* Altura ideal para la cabecera */
        width: auto;
        transition: transform 0.3s ease;
    }

    .logo:hover img {
        transform: scale(1.05);
    }

    /* Invertir colores del logo en modo claro si es necesario */
    [data-theme="light"] .logo img {
        filter: brightness(0.8); /* Ajusta esto según cómo se vea en tu fondo claro */
    }
  </style>

  <script>
    (function() {
      const savedTheme = localStorage.getItem('theme') || 'dark';
      document.documentElement.setAttribute('data-theme', savedTheme);
    })();
  </script>
</head>
<body>

<header class="site-header">
  <div class="container header-inner">
    <a class="logo" href="<?=BASE_URL?>/public/index.php">
<img src="<?=BASE_URL?>/img/logo.png" 
     alt="Logo" 
     style="height: 40px; filter: brightness(0) invert(1);">      <span>UMBRAL ACADEMY</span>
    </a>
    
    <nav class="main-nav">
      <ul class="nav-list">
        <li><a href="<?= BASE_URL ?>/public/index.php">Inicio</a></li>
        <li><a href="<?= BASE_URL ?>/public/actividades.php">Actividades</a></li>
        <li><a href="<?= BASE_URL ?>/public/cuotas.php">Cuotas</a></li>
        <li><a href="<?= BASE_URL ?>/public/contacto.php">Contacto</a></li>
      </ul>
    </nav>

    <div class="header-actions" style="display: flex; align-items: center; gap: 15px;">
      <button id="themeToggle" class="btn-outline" title="Cambiar tema">
        <span id="themeIcon">🌙</span>
      </button>

      <?php if(!$usuario): ?>
        <a class="btn-outline" href="<?=BASE_URL?>/public/login.php">Entrar / Registro</a>
      <?php else: ?>
        <div class="user-menu" style="position: relative;">
          <button id="userMenuBtn" class="btn-outline" type="button">
            <?=htmlspecialchars($usuario['nombre'])?> ▾
          </button>
          <div id="userDropdown" class="dropdown" style="display:none; position: absolute; right: 0; top: 100%; background: var(--card); border: 1px solid var(--border); border-radius: 8px; min-width: 150px; z-index: 1000; margin-top: 10px;">
            <a href="<?=BASE_URL?>/dashboard/<?=htmlspecialchars($usuario['rol'])?>.php" style="display: block; padding: 10px; text-decoration: none; color: var(--white); border-bottom: 1px solid var(--border);">Mi Panel</a>
            <a href="<?=BASE_URL?>/public/logout.php" style="display: block; padding: 10px; text-decoration: none; color: #e74c3c;">Cerrar sesión</a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</header>

<script>
/* Lógica del Cambio de Tema */
const themeBtn = document.getElementById('themeToggle');
const themeIcon = document.getElementById('themeIcon');

function updateThemeUI(theme) {
    themeIcon.innerText = theme === 'light' ? '☀️' : '🌙';
}

themeBtn.addEventListener('click', () => {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeUI(newTheme);
});

updateThemeUI(localStorage.getItem('theme') || 'dark');

/* Lógica del Dropdown de Usuario */
const menuBtn = document.getElementById('userMenuBtn');
const dropdown = document.getElementById('userDropdown');

if(menuBtn) {
    menuBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', () => {
        dropdown.style.display = 'none';
    });
}
</script>