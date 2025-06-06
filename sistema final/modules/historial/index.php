<?php
require_once '../../includes/header.php';
require_once '../../config/db.php';

// Paginación
$registros_por_pagina = 10;
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina - 1) * $registros_por_pagina;

// Obtener id del paciente
$id_paciente = intval($_GET['id_paciente'] ?? 0);
if (!$id_paciente) {
    header('Location: ../pacientes/index.php');
    exit;
}
// Obtener datos del paciente
$stmt = $pdo->prepare('SELECT p.nombre, pa.nombre AS nombre_padre FROM pacientes p JOIN padres pa ON p.id_padre = pa.id_padre WHERE p.id_paciente = ?');
$stmt->execute([$id_paciente]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$paciente) {
    header('Location: ../pacientes/index.php');
    exit;
}
// Total de registros para paginación
$stmt = $pdo->prepare('SELECT COUNT(*) FROM historial_medico WHERE id_paciente = ?');
$stmt->execute([$id_paciente]);
$total_registros = $stmt->fetchColumn();
$total_paginas = ceil($total_registros / $registros_por_pagina);
// Obtener historial médico paginado
$sql = 'SELECT h.*, u.nombre AS creado_por_nombre FROM historial_medico h LEFT JOIN usuarios u ON h.creado_por = u.id_usuario WHERE h.id_paciente = ? ORDER BY h.fecha_registro DESC LIMIT ' . intval($registros_por_pagina) . ' OFFSET ' . intval($offset);
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_paciente]);
$historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Permisos: solo doctor (2) o admin (1) pueden agregar/editar
$puede_editar = in_array($_SESSION['usuario']['id_rol'], [1,2]);
?>
<section class="section">
  <div class="container">
    <h1 class="title">Historial médico de <?= htmlspecialchars($paciente['nombre']) ?></h1>
    <p class="subtitle">Padre/Tutor: <?= htmlspecialchars($paciente['nombre_padre']) ?></p>
    <?php if ($puede_editar): ?>
      <a href="add.php?id_paciente=<?= $id_paciente ?>" class="button is-primary mb-4">Agregar consulta</a>
    <?php endif; ?>
    <table class="table is-fullwidth is-striped">
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Diagnóstico</th>
          <th>Tratamiento</th>
          <th>Seguimiento</th>
          <th>Creado por</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($historial as $h): ?>
        <tr>
          <td><?= htmlspecialchars($h['fecha_registro']) ?></td>
          <td><?= htmlspecialchars($h['diagnostico']) ?></td>
          <td><?= htmlspecialchars($h['tratamiento']) ?></td>
          <td><?= htmlspecialchars($h['seguimiento']) ?></td>
          <td><?= htmlspecialchars($h['creado_por_nombre']) ?></td>
          <td>
            <a href="view.php?id=<?= $h['id_historial'] ?>&id_paciente=<?= $id_paciente ?>" class="button is-small is-info">Ver</a>
            <?php if ($puede_editar): ?>
              <a href="edit.php?id=<?= $h['id_historial'] ?>&id_paciente=<?= $id_paciente ?>" class="button is-small is-warning">Editar</a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if (empty($historial)): ?>
      <p>No hay consultas registradas.</p>
    <?php endif; ?>
    <!-- Paginación -->
    <?php if ($total_paginas > 1): ?>
      <nav class="pagination is-centered" role="navigation" aria-label="pagination">
        <?php $url = 'index.php?id_paciente=' . $id_paciente . '&'; ?>
        <a class="pagination-previous" href="<?= $pagina > 1 ? $url . 'pagina=' . ($pagina - 1) : '#' ?>" <?= $pagina == 1 ? 'disabled' : '' ?>>Anterior</a>
        <a class="pagination-next" href="<?= $pagina < $total_paginas ? $url . 'pagina=' . ($pagina + 1) : '#' ?>" <?= $pagina == $total_paginas ? 'disabled' : '' ?>>Siguiente</a>
        <ul class="pagination-list">
          <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <li><a class="pagination-link <?= $i == $pagina ? 'is-current' : '' ?>" href="<?= $url . 'pagina=' . $i ?>"><?= $i ?></a></li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
    <a href="../pacientes/view.php?id=<?= $id_paciente ?>" class="button is-light">Volver al paciente</a>
  </div>
</section>
