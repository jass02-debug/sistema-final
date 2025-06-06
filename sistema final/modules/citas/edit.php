<?php
require_once '../../includes/header.php';
require_once '../../config/db.php';

// Solo doctor, recepcionista o admin pueden editar
if (!in_array($_SESSION['usuario']['id_rol'], [1,2,3])) {
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}
// Obtener datos de la cita
$stmt = $pdo->prepare('SELECT * FROM citas WHERE id_cita = ?');
$stmt->execute([$id]);
$cita = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cita) {
    header('Location: index.php');
    exit;
}
// Obtener pacientes
$stmt = $pdo->query('SELECT id_paciente, nombre FROM pacientes ORDER BY nombre');
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Obtener doctores
$stmt = $pdo->query('SELECT id_usuario, nombre FROM usuarios WHERE id_rol = 2 ORDER BY nombre');
$doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);

$estados = ['Programada', 'Cancelada', 'Realizada'];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_paciente = intval($_POST['id_paciente'] ?? 0);
    $id_doctor = intval($_POST['id_doctor'] ?? 0);
    $fecha_cita = trim($_POST['fecha_cita'] ?? '');
    $estado = $_POST['estado'] ?? 'Programada';

    if ($id_paciente && $id_doctor && $fecha_cita && in_array($estado, $estados)) {
        $stmt = $pdo->prepare('UPDATE citas SET id_paciente=?, id_doctor=?, fecha_cita=?, estado=? WHERE id_cita=?');
        $stmt->execute([$id_paciente, $id_doctor, $fecha_cita, $estado, $id]);
        header('Location: index.php');
        exit;
    } else {
        $error = 'Todos los campos son obligatorios.';
    }
}
?>
<section class="section">
  <div class="container">
    <h1 class="title">Editar cita</h1>
    <?php if ($error): ?>
      <div class="notification is-danger">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    <form method="post" class="box">
      <div class="field">
        <label class="label">Paciente</label>
        <div class="control">
          <div class="select is-fullwidth">
            <select name="id_paciente" required>
              <option value="">Seleccione un paciente</option>
              <?php foreach ($pacientes as $p): ?>
                <option value="<?= $p['id_paciente'] ?>" <?= $cita['id_paciente'] == $p['id_paciente'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      <div class="field">
        <label class="label">Doctor</label>
        <div class="control">
          <div class="select is-fullwidth">
            <select name="id_doctor" required>
              <option value="">Seleccione un doctor</option>
              <?php foreach ($doctores as $d): ?>
                <option value="<?= $d['id_usuario'] ?>" <?= $cita['id_doctor'] == $d['id_usuario'] ? 'selected' : '' ?>><?= htmlspecialchars($d['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      <div class="field">
        <label class="label">Fecha y hora</label>
        <div class="control">
          <input class="input" type="datetime-local" name="fecha_cita" value="<?= date('Y-m-d\TH:i', strtotime($cita['fecha_cita'])) ?>" required>
        </div>
      </div>
      <div class="field">
        <label class="label">Estado</label>
        <div class="control">
          <div class="select">
            <select name="estado" required>
              <?php foreach ($estados as $e): ?>
                <option value="<?= $e ?>" <?= $cita['estado'] === $e ? 'selected' : '' ?>><?= $e ?></option>
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
