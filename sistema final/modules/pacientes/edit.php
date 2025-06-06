<?php
require_once '../../includes/header.php';
require_once '../../config/db.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}
// Obtener datos del paciente y padre/tutor
$stmt = $pdo->prepare('SELECT p.*, pa.nombre AS nombre_padre, pa.telefono, pa.cedula, pa.id_padre FROM pacientes p JOIN padres pa ON p.id_padre = pa.id_padre WHERE p.id_paciente = ?');
$stmt->execute([$id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$paciente) {
    header('Location: index.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_paciente = trim($_POST['nombre_paciente'] ?? '');
    $fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
    $antecedentes = trim($_POST['antecedentes'] ?? '');
    $nombre_padre = trim($_POST['nombre_padre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $id_padre = $paciente['id_padre'];

    if ($nombre_paciente && $fecha_nacimiento && $nombre_padre && $cedula) {
        // Validar cédula única (excepto el propio padre)
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM padres WHERE cedula = ? AND id_padre != ?');
        $stmt->execute([$cedula, $id_padre]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'La cédula ya está registrada para otro padre/tutor.';
        } else {
            // Actualizar padre/tutor
            $stmt = $pdo->prepare('UPDATE padres SET nombre=?, telefono=?, cedula=? WHERE id_padre=?');
            $stmt->execute([$nombre_padre, $telefono, $cedula, $id_padre]);
            // Insertar teléfono adicional si no existe y si se ingresó
            if ($telefono) {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM telefonos_padre WHERE id_padre = ? AND telefono = ?');
                $stmt->execute([$id_padre, $telefono]);
                if ($stmt->fetchColumn() == 0) {
                    $stmt = $pdo->prepare('INSERT INTO telefonos_padre (id_padre, telefono) VALUES (?, ?)');
                    $stmt->execute([$id_padre, $telefono]);
                }
            }
            // Actualizar paciente
            $stmt = $pdo->prepare('UPDATE pacientes SET nombre=?, fecha_nacimiento=?, antecedentes_medicos=? WHERE id_paciente=?');
            $stmt->execute([$nombre_paciente, $fecha_nacimiento, $antecedentes, $id]);
            header('Location: index.php');
            exit;
        }
    } else {
        $error = 'Todos los campos obligatorios deben ser completados.';
    }
}
?>
<section class="section">
  <div class="container">
    <h1 class="title">Editar paciente</h1>
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
          <input class="input" type="text" name="nombre_paciente" value="<?= htmlspecialchars($paciente['nombre']) ?>" required>
        </div>
      </div>
      <div class="field">
        <label class="label">Fecha de nacimiento</label>
        <div class="control">
          <input class="input" type="date" name="fecha_nacimiento" value="<?= htmlspecialchars($paciente['fecha_nacimiento']) ?>" required>
        </div>
      </div>
      <div class="field">
        <label class="label">Antecedentes médicos</label>
        <div class="control">
          <textarea class="textarea" name="antecedentes"><?= htmlspecialchars($paciente['antecedentes_medicos']) ?></textarea>
        </div>
      </div>
      <hr>
      <h2 class="subtitle">Datos del padre/tutor</h2>
      <div class="field">
        <label class="label">Nombre completo</label>
        <div class="control">
          <input class="input" type="text" name="nombre_padre" value="<?= htmlspecialchars($paciente['nombre_padre']) ?>" required>
        </div>
      </div>
      <div class="field">
        <label class="label">Teléfono</label>
        <div class="control">
          <input class="input" type="text" name="telefono" value="<?= htmlspecialchars($paciente['telefono']) ?>">
        </div>
      </div>
      <div class="field">
        <label class="label">Cédula</label>
        <div class="control">
          <input class="input" type="text" name="cedula" value="<?= htmlspecialchars($paciente['cedula']) ?>" required>
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
