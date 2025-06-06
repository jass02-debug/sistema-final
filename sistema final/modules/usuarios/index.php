<?php
require_once '../../includes/header.php';
require_once '../../config/db.php';
require_rol(1); // Solo administrador

// Obtener usuarios y roles
$stmt = $pdo->query('SELECT u.id_usuario, u.nombre, u.correo, r.nombre AS rol FROM usuarios u JOIN roles r ON u.id_rol = r.id_rol');
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<section class="section">
  <div class="container">
    <h1 class="title">Usuarios</h1>
    <a href="add.php" class="button is-primary mb-4">Agregar usuario</a>
    <table class="table is-fullwidth is-striped">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Correo</th>
          <th>Rol</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($usuarios as $usuario): ?>
        <tr>
          <td><?= htmlspecialchars($usuario['nombre']) ?></td>
          <td><?= htmlspecialchars($usuario['correo']) ?></td>
          <td><?= htmlspecialchars($usuario['rol']) ?></td>
          <td>
            <a href="edit.php?id=<?= $usuario['id_usuario'] ?>" class="button is-small is-info">Editar</a>
            <a href="delete.php?id=<?= $usuario['id_usuario'] ?>" class="button is-small is-danger" onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">Eliminar</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
