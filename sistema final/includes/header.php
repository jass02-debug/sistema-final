<?php
require_once __DIR__ . '/auth.php';
require_login();

// Opciones de menú según el rol
$rol = $_SESSION['usuario']['id_rol'];
$nombre = htmlspecialchars($_SESSION['usuario']['nombre']);

// Definir nombres de roles
$roles = [1 => 'Administrador', 2 => 'Doctor', 3 => 'Recepcionista'];
$nombre_rol = $roles[$rol] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Pediátrico</title>
    <link rel="stylesheet" href="/assets/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
<nav class="navbar is-link" role="navigation" aria-label="main navigation">
  <div class="navbar-brand">
    <a class="navbar-item" href="/index.php">
      <strong>Sistema Pediátrico</strong>
    </a>
    <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasic">
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
    </a>
  </div>
  <div id="navbarBasic" class="navbar-menu">
    <div class="navbar-start">
      <a class="navbar-item" href="/index.php">Inicio</a>
      <?php if ($rol == 1): // Administrador ?>
        <a class="navbar-item" href="/modules/usuarios/index.php">Usuarios</a>
      <?php endif; ?>
      <a class="navbar-item" href="/modules/pacientes/index.php">Pacientes</a>
      <a class="navbar-item" href="/modules/citas/index.php">Citas</a>
      <?php if ($rol == 2): // Doctor ?>
        <a class="navbar-item" href="/modules/historial/index.php">Historial Médico</a>
      <?php endif; ?>
      <?php if ($rol == 1 || $rol == 2): ?>
        <a class="navbar-item" href="/modules/vacunas/index.php">Vacunas</a>
      <?php endif; ?>
    </div>
    <div class="navbar-end">
      <div class="navbar-item">
        <div class="buttons">
          <span class="icon"><i class="fas fa-user"></i></span>
          <span class="button is-light is-static">
            <?= $nombre ?> (<?= $nombre_rol ?>)
          </span>
          <a class="button is-danger" href="/modules/auth/logout.php">Cerrar sesión</a>
        </div>
      </div>
    </div>
  </div>
</nav>
<script>
// Script para el menú burger en móvil
  document.addEventListener('DOMContentLoaded', () => {
    const $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);
    if ($navbarBurgers.length > 0) {
      $navbarBurgers.forEach( el => {
        el.addEventListener('click', () => {
          const target = el.dataset.target;
          const $target = document.getElementById(target);
          el.classList.toggle('is-active');
          $target.classList.toggle('is-active');
        });
      });
    }
  });
</script>
