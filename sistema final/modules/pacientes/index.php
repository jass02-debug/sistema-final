<?php
require_once '../../includes/header.php';
require_once '../../config/db.php';

// Paginación
$registros_por_pagina = 10;
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina - 1) * $registros_por_pagina;

// Búsqueda
$busqueda = trim($_GET['busqueda'] ?? '');
$sql = 'SELECT p.id_paciente, p.nombre AS nombre_paciente, p.fecha_nacimiento, pa.nombre AS nombre_padre, pa.cedula
        FROM pacientes p
        JOIN padres pa ON p.id_padre = pa.id_padre';
$params = [];
$where = [];
if ($busqueda) {
    $where[] = '(p.nombre LIKE ? OR pa.cedula LIKE ?)';
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql_total = str_replace('SELECT p.id_paciente, p.nombre AS nombre_paciente, p.fecha_nacimiento, pa.nombre AS nombre_padre, pa.cedula', 'SELECT COUNT(*)', $sql);
$stmt = $pdo->prepare($sql_total);
$stmt->execute($params);
$total_registros = $stmt->fetchColumn();
$total_paginas = ceil($total_registros / $registros_por_pagina);

$sql .= ' ORDER BY p.nombre LIMIT ' . intval($registros_por_pagina) . ' OFFSET ' . intval($offset);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<section class="section">
  <div class="container">
    <h1 class="title">Pacientes</h1>
    <form method="get" id="searchForm" class="mb-4">
      <div class="field">
        <label class="label">Buscar paciente</label>
        <div class="control">
          <input class="input" type="text" name="busqueda" id="paciente_busqueda" placeholder="Buscar por nombre del niño o cédula del padre/tutor" value="<?= htmlspecialchars($busqueda) ?>">
          <input type="hidden" name="id_paciente" id="id_paciente">
          <ul id="lista-pacientes"></ul>
        </div>
      </div>
    </form>
    <script>
      let pacientes = <?php echo json_encode($pacientes); ?>;
    </script>
    <a href="add.php" class="button is-primary mb-4">Agregar paciente</a>
    <table class="table is-fullwidth is-striped is-responsive">
      <thead>
        <tr>
          <th>Nombre del paciente</th>
          <th>Fecha de nacimiento</th>
          <th>Padre/Tutor</th>
          <th>Cédula</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pacientes as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['nombre_paciente']) ?></td>
          <td><?= htmlspecialchars($p['fecha_nacimiento']) ?></td>
          <td><?= htmlspecialchars($p['nombre_padre']) ?></td>
          <td><?= htmlspecialchars($p['cedula']) ?></td>
          <td>
            <a href="view.php?id=<?= $p['id_paciente'] ?>" class="button is-small is-info">Ver</a>
            <a href="edit.php?id=<?= $p['id_paciente'] ?>" class="button is-small is-warning">Editar</a>
            <a href="delete.php?id=<?= $p['id_paciente'] ?>" class="button is-small is-danger" onclick="return confirm('¿Seguro que deseas eliminar este paciente?');">Eliminar</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if (empty($pacientes)): ?>
      <p>No se encontraron pacientes.</p>
    <?php endif; ?>
    <!-- Paginación -->
    <?php if ($total_paginas > 1): ?>
      <nav class="pagination is-centered" role="navigation" aria-label="pagination">
        <?php $url = 'index.php?busqueda=' . urlencode($busqueda) . '&'; ?>
        <a class="pagination-previous" href="<?= $pagina > 1 ? $url . 'pagina=' . ($pagina - 1) : '#' ?>" <?= $pagina == 1 ? 'disabled' : '' ?>>Anterior</a>
        <a class="pagination-next" href="<?= $pagina < $total_paginas ? $url . 'pagina=' . ($pagina + 1) : '#' ?>" <?= $pagina == $total_paginas ? 'disabled' : '' ?>>Siguiente</a>
        <ul class="pagination-list">
          <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <li><a class="pagination-link <?= $i == $pagina ? 'is-current' : '' ?>" href="<?= $url . 'pagina=' . $i ?>"><?= $i ?></a></li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
  </div>
</section>
