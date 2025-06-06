<?php
require_once '../../includes/header.php';
require_once '../../config/db.php';
require_rol(1); // Solo administrador

// Obtener roles para el select
$stmt = $pdo->query('SELECT id_rol, nombre FROM roles');
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');
    $id_rol = intval($_POST['id_rol'] ?? 0);

    if ($nombre && $correo && $contrasena && $id_rol) {
        // Validar correo único
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE correo = ?');
        $stmt->execute([$correo]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'El correo ya está registrado.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, correo, contraseña, id_rol) VALUES (?, ?, ?, ?)');
            $stmt->execute([$nombre, $correo, $contrasena, $id_rol]);
            header('Location: index.php');
            exit;
        }
    } else {
        $error = 'Todos los campos son obligatorios.';
    }
}
?>
<section class="section">
  <div class="container">
    <h1 class="title">Agregar usuario</h1>
    <?php if ($error): ?>
      <div class="notification is-danger">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    <form method="post" class="box">
      <div class="field">
        <label class="label">Nombre</label>
        <div class="control">
          <input class="input" type="text" name="nombre" required>
        </div>
      </div>
      <div class="field">
        <label class="label">Correo</label>
        <div class="control">
          <input class="input" type="email" name="correo" required>
        </div>
      </div>
      <div class="field">
        <label class="label">Contraseña</label>
        <div class="control">
          <input class="input" type="password" name="contrasena" required>
        </div>
      </div>
      <div class="field">
        <label class="label">Rol</label>
        <div class="control">
          <div class="select">
            <select name="id_rol" required>
              <option value="">Seleccione un rol</option>
              <?php foreach ($roles as $rol): ?>
                <option value="<?= $rol['id_rol'] ?>"><?= htmlspecialchars($rol['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      <div class="field is-grouped is-grouped-centered">
        <div class="control">
          <button class="button is-link" type="submit">Guardar</button>
        </div>
        <div class="control">
          <a class="button is-light" href="index.php">Cancelar</a>
        </div>
      </div>
    </form>
  </div>
</section>
