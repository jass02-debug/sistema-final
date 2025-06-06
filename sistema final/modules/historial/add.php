<?php
require_once '../../includes/header.php';
require_once '../../config/db.php';

// Solo doctor o admin pueden agregar
if (!in_array($_SESSION['usuario']['id_rol'], [1,2])) {
    header('Location: index.php');
    exit;
}

$id_paciente = intval($_GET['id_paciente'] ?? 0);
if (!$id_paciente) {
    header('Location: ../pacientes/index.php');
    exit;
}
// Obtener datos del paciente
$stmt = $pdo->prepare('SELECT nombre FROM pacientes WHERE id_paciente = ?');
$stmt->execute([$id_paciente]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$paciente) {
    header('Location: ../pacientes/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diagnostico = trim($_POST['diagnostico'] ?? '');
    $tratamiento = trim($_POST['tratamiento'] ?? '');
    $seguimiento = trim($_POST['seguimiento'] ?? '');
    $creado_por = $_SESSION['usuario']['id_usuario'];

    if ($diagnostico && $tratamiento) {
        $stmt = $pdo->prepare('INSERT INTO historial_medico (id_paciente, diagnostico, tratamiento, seguimiento, creado_por) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$id_paciente, $diagnostico, $tratamiento, $seguimiento, $creado_por]);
        header('Location: index.php?id_paciente=' . $id_paciente);
        exit;
    } else {
        $error = 'Diagnóstico y tratamiento son obligatorios.';
    }
}
?>
<section class="section">
  <div class="container">
    <h1 class="title">Agregar consulta a <?= htmlspecialchars($paciente['nombre']) ?></h1>
    <?php if ($error): ?>
      <div class="notification is-danger">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    <form method="post" class="box">
      <div class="field">
        <label class="label">Diagnóstico</label>
        <div class="control">
          <textarea class="textarea" name="diagnostico" required></textarea>
        </div>
      </div>
      <div class="field">
        <label class="label">Tratamiento</label>
        <div class="control">
          <textarea class="textarea" name="tratamiento" required></textarea>
        </div>
      </div>
      <div class="field">
        <label class="label">Seguimiento / Observaciones</label>
        <div class="control">
          <textarea class="textarea" name="seguimiento"></textarea>
        </div>
      </div>
      <div class="field is-grouped is-grouped-centered">
        <div class="control">
          <button class="button is-link" type="submit">Guardar</button>
        </div>
        <div class="control">
          <a class="button is-light" href="index.php?id_paciente=<?= $id_paciente ?>">Cancelar</a>
        </div>
      </div>
    </form>
  </div>
</section>
