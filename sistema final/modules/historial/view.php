<?php
require_once '../../includes/header.php';
require_once '../../config/db.php';

$id = intval($_GET['id'] ?? 0);
$id_paciente = intval($_GET['id_paciente'] ?? 0);
if (!$id || !$id_paciente) {
    header('Location: index.php?id_paciente=' . $id_paciente);
    exit;
}
// Obtener datos de la consulta
$stmt = $pdo->prepare('SELECT h.*, u.nombre AS creado_por_nombre FROM historial_medico h LEFT JOIN usuarios u ON h.creado_por = u.id_usuario WHERE h.id_historial = ?');
$stmt->execute([$id]);
$consulta = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$consulta) {
    header('Location: index.php?id_paciente=' . $id_paciente);
    exit;
}
$puede_editar = in_array($_SESSION['usuario']['id_rol'], [1,2]);
?>
<section class="section">
  <div class="container">
    <h1 class="title">Detalle de la consulta médica</h1>
    <div class="box">
      <p><strong>Fecha de registro:</strong> <?= htmlspecialchars($consulta['fecha_registro']) ?></p>
      <p><strong>Diagnóstico:</strong><br><?= nl2br(htmlspecialchars($consulta['diagnostico'])) ?></p>
      <p><strong>Tratamiento:</strong><br><?= nl2br(htmlspecialchars($consulta['tratamiento'])) ?></p>
      <p><strong>Seguimiento / Observaciones:</strong><br><?= nl2br(htmlspecialchars($consulta['seguimiento'])) ?></p>
      <p><strong>Creado por:</strong> <?= htmlspecialchars($consulta['creado_por_nombre']) ?></p>
    </div>
    <a href="index.php?id_paciente=<?= $id_paciente ?>" class="button is-light">Volver al historial</a>
    <?php if ($puede_editar): ?>
      <a href="edit.php?id=<?= $id ?>&id_paciente=<?= $id_paciente ?>" class="button is-warning">Editar</a>
    <?php endif; ?>
  </div>
</section>
