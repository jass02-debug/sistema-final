<?php
require_once '../../includes/header.php';
require_once '../../config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_paciente = trim($_POST['nombre_paciente'] ?? '');
    $fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
    $antecedentes = trim($_POST['antecedentes'] ?? '');
    $nombre_padre = trim($_POST['nombre_padre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');

    if ($nombre_paciente && $fecha_nacimiento && $nombre_padre && $cedula) {
        // Buscar si el padre/tutor ya existe por cédula
        $stmt = $pdo->prepare('SELECT id_padre FROM padres WHERE cedula = ?');
        $stmt->execute([$cedula]);
        $padre = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($padre) {
            $id_padre = $padre['id_padre'];
        } else {
            // Insertar nuevo padre/tutor
            $stmt = $pdo->prepare('INSERT INTO padres (nombre, telefono, cedula) VALUES (?, ?, ?)');
            $stmt->execute([$nombre_padre, $telefono, $cedula]);
            $id_padre = $pdo->lastInsertId();
        }
        // Insertar teléfono adicional si no existe y si se ingresó
        if ($telefono) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM telefonos_padre WHERE id_padre = ? AND telefono = ?');
            $stmt->execute([$id_padre, $telefono]);
            if ($stmt->fetchColumn() == 0) {
                $stmt = $pdo->prepare('INSERT INTO telefonos_padre (id_padre, telefono) VALUES (?, ?)');
                $stmt->execute([$id_padre, $telefono]);
            }
        }
        // Insertar paciente
        $stmt = $pdo->prepare('INSERT INTO pacientes (id_padre, nombre, fecha_nacimiento, antecedentes_medicos) VALUES (?, ?, ?, ?)');
        $stmt->execute([$id_padre, $nombre_paciente, $fecha_nacimiento, $antecedentes]);
        header('Location: index.php');
        exit;
    } else {
        $error = 'Todos los campos obligatorios deben ser completados.';
    }
}
?>
<section class="section">
  <div class="container">
    <h1 class="title">Agregar paciente</h1>
    <?php if ($error): ?>
      <div class="notification is-danger">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    <form method="post" class="box">
      <h2 class="subtitle">Datos del paciente</h2>
      <div class="field">
        <label class="label">Nombre del paciente</label>
        <div class="control">
          <input class="input" type="text" name="nombre_paciente" required>
        </div>
      </div>
      <div class="field">
        <label class="label">Fecha de nacimiento</label>
        <div class="control">
          <input class="input" type="date" name="fecha_nacimiento" required>
        </div>
      </div>
      <div class="field">
        <label class="label">Antecedentes médicos</label>
        <div class="control">
          <textarea class="textarea" name="antecedentes"></textarea>
        </div>
      </div>
      <hr>
      <h2 class="subtitle">Datos del padre/tutor</h2>
      <div class="field">
        <label class="label">Nombre completo</label>
        <div class="control">
          <input class="input" type="text" name="nombre_padre" required>
        </div>
      </div>
      <div class="field">
        <label class="label">Teléfono</label>
        <div class="control">
          <input class="input" type="text" name="telefono">
        </div>
      </div>
      <div class="field">
        <label class="label">Cédula</label>
        <div class="control">
          <input class="input" type="text" name="cedula" required>
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
