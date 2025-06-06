<?php
require_once '../../includes/header.php';
require_once '../../config/db.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}
// Obtener datos del paciente y padre/tutor
$stmt = $pdo->prepare('SELECT p.*, pa.nombre AS nombre_padre, pa.cedula, pa.id_padre FROM pacientes p JOIN padres pa ON p.id_padre = pa.id_padre WHERE p.id_paciente = ?');
$stmt->execute([$id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$paciente) {
    header('Location: index.php');
    exit;
}
// Obtener teléfonos adicionales
$stmt = $pdo->prepare('SELECT telefono FROM telefonos_padre WHERE id_padre = ?');
$stmt->execute([$paciente['id_padre']]);
$telefonos = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<section class="section">
  <div class="container">
    <h1 class="title">Detalles del paciente</h1>
    <div class="box">
      <h2 class="subtitle">Datos del paciente</h2>
      <p><strong>Nombre:</strong> <?= htmlspecialchars($paciente['nombre']) ?></p>
      <p><strong>Fecha de nacimiento:</strong> <?= htmlspecialchars($paciente['fecha_nacimiento']) ?></p>
      <p><strong>Antecedentes médicos:</strong> <?= nl2br(htmlspecialchars($paciente['antecedentes_medicos'])) ?></p>
      <hr>
      <h2 class="subtitle">Padre/Tutor</h2>
      <p><strong>Nombre:</strong> <?= htmlspecialchars($paciente['nombre_padre']) ?></p>
      <p><strong>Cédula:</strong> <?= htmlspecialchars($paciente['cedula']) ?></p>
      <p><strong>Teléfonos:</strong> <?= $telefonos ? implode(', ', array_map('htmlspecialchars', $telefonos)) : 'No registrados' ?></p>
    </div>
    <a href="../historial/index.php?id_paciente=<?= $paciente['id_paciente'] ?>" class="button is-link">Ver historial médico</a>
    <a href="index.php" class="button is-light">Volver</a>
  </div>
</section>
