<?php
require_once '../../includes/header.php';
require_once '../../config/db.php';
require_rol(1);

// Obtener roles para el select
$stmt = $pdo->query('SELECT id_rol, nombre FROM roles');
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

// Obtener datos del usuario
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id_usuario = ?');
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$usuario) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');
    $id_rol = intval($_POST['id_rol'] ?? 0);

    if ($nombre && $correo && $id_rol) {
        // Validar correo único (excepto el propio)
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE correo = ? AND id_usuario != ?');
        $stmt->execute([$correo, $id]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'El correo ya está registrado.';
        } else {
            if ($contrasena) {
                $stmt = $pdo->prepare('UPDATE usuarios SET nombre=?, correo=?, contraseña=?, id_rol=? WHERE id_usuario=?');
                $stmt->execute([$nombre, $correo, $contrasena, $id_rol, $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE usuarios SET nombre=?, correo=?, id_rol=? WHERE id_usuario=?');
                $stmt->execute([$nombre, $correo, $id_rol, $id]);
            }
            header('Location: index.php');
            exit;
        }
    } else {
        $error = 'Nombre, correo y rol son obligatorios.';
    }
}
?>
<section class="section">
  <div class="container">
    <h1 class="title">Editar usuario</h1>
    <?php if ($error): ?>
      <div class="notification is-danger">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    <form method="post" class="box">
      <div class="field">
        <label class="label">Nombre</label>
        <div class="control">
          <input class="input" type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
        </div>
      </div>
      <div class="field">
        <label class="label">Correo</label>
        <div class="control">
          <input class="input" type="email" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required>
        </div>
      </div>
      <div class="field">
        <label class="label">Contraseña (dejar en blanco para no cambiar)</label>
        <div class="control">
          <input class="input" type="password" name="contrasena">
        </div>
      </div>
      <div class="field">
        <label class="label">Rol</label>
        <div class="control">
          <div class="select">
            <select name="id_rol" required>
              <option value="">Seleccione un rol</option>
              <?php foreach ($roles as $rol): ?>
                <option value="<?= $rol['id_rol'] ?>" <?= $usuario['id_rol'] == $rol['id_rol'] ? 'selected' : '' ?>><?= htmlspecialchars($rol['nombre']) ?></option>
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
