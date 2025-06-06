<?php
require_once '../../includes/header.php';
require_once '../../config/db.php';

// Solo doctor o admin pueden editar
if (!in_array($_SESSION['usuario']['id_rol'], [1,2])) {
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
$id_paciente = intval($_GET['id_paciente'] ?? 0);
if (!$id || !$id_paciente) {
    header('Location: index.php?id_paciente=' . $id_paciente);
    exit;
}
// Obtener datos de la consulta
$stmt = $pdo->prepare('SELECT * FROM historial_medico WHERE id_historial = ?');
$stmt->execute([$id]);
$consulta = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$consulta) {
    header('Location: index.php?id_paciente=' . $id_paciente);
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diagnostico = trim($_POST['diagnostico'] ?? '');
    $tratamiento = trim($_POST['tratamiento'] ?? '');
    $seguimiento = trim($_POST['seguimiento'] ?? '');
    $actualizado_por = $_SESSION['usuario']['id_usuario'];
    $fecha_actualizacion = date('Y-m-d H:i:s');

    if ($diagnostico && $tratamiento) {
        $stmt = $pdo->prepare('UPDATE historial_medico SET diagnostico=?, tratamiento=?, seguimiento=?, actualizado_por=?, fecha_actualizacion=? WHERE id_historial=?');
        $stmt->execute([$diagnostico, $tratamiento, $seguimiento, $actualizado_por, $fecha_actualizacion, $id]);
        header('Location: view.php?id=' . $id . '&id_paciente=' . $id_paciente);
        exit;
    } else {
        $error = 'Diagnóstico y tratamiento son obligatorios.';
    }
}
?>
<section class="section">
  <div class="container">
    <h1 class="title">Editar consulta médica</h1>
    <?php if ($error): ?>
      <div class="notification is-danger">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    <form method="post" class="box">
      <div class="field">
        <label class="label">Diagnóstico</label>
        <div class="control">
          <textarea class="textarea" name="diagnostico" required><?= htmlspecialchars($consulta['diagnostico']) ?></textarea>
        </div>
      </div>
      <div class="field">
        <label class="label">Tratamiento</label>
        <div class="control">
          <textarea class="textarea" name="tratamiento" required><?= htmlspecialchars($consulta['tratamiento']) ?></textarea>
        </div>
      </div>
      <div class="field">
        <label class="label">Seguimiento / Observaciones</label>
        <div class="control">
          <textarea class="textarea" name="seguimiento"><?= htmlspecialchars($consulta['seguimiento']) ?></textarea>
        </div>
      </div>
      <div class="field is-grouped is-grouped-centered">
        <div class="control">
          <button class="button is-link" type="submit">Guardar</button>
        </div>
        <div class="control">
          <a class="button is-light" href="view.php?id=<?= $id ?>&id_paciente=<?= $id_paciente ?>">Cancelar</a>
        </div>
      </div>
    </form>
  </div>
</section>
