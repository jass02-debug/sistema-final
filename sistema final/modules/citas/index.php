<?php
require_once '../../includes/header.php';
require_once '../../config/db.php';

// Paginación
$registros_por_pagina = 10;
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina - 1) * $registros_por_pagina;

// Filtros de búsqueda
$busqueda = trim($_GET['busqueda'] ?? '');
$estado = $_GET['estado'] ?? '';
$sql = 'SELECT c.*, p.nombre AS nombre_paciente, u.nombre AS nombre_doctor FROM citas c JOIN pacientes p ON c.id_paciente = p.id_paciente JOIN usuarios u ON c.id_doctor = u.id_usuario';
$where = [];
$params = [];
if ($busqueda) {
    $where[] = '(p.nombre LIKE ? OR u.nombre LIKE ?)';
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}
if ($estado) {
    $where[] = 'c.estado = ?';
    $params[] = $estado;
}
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

// Consulta para obtener el total de registros
$sql_total = str_replace('SELECT c.*, p.nombre AS nombre_paciente, u.nombre AS nombre_doctor', 'SELECT COUNT(*)', $sql);
$stmt = $pdo->prepare($sql_total);
$stmt->execute($params);
$total_registros = $stmt->fetchColumn();
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Agregar ORDER BY y LIMIT a la consulta principal
$sql .= ' ORDER BY c.fecha_cita DESC LIMIT ' . $registros_por_pagina . ' OFFSET ' . $offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estados posibles de una cita
$estados = ['Programada', 'Cancelada', 'Realizada'];
?>
<section class="section">
  <div class="container">
    <h1 class="title">Citas médicas</h1>
    <form method="get" class="mb-4">
      <div class="field is-grouped">
        <div class="control">
          <input class="input" type="text" name="busqueda" placeholder="Buscar por paciente o doctor" value="<?= htmlspecialchars($busqueda) ?>">
        </div>
        <div class="control">
          <div class="select">
            <select name="estado">
              <option value="">Todos los estados</option>
              <?php foreach ($estados as $e): ?>
                <option value="<?= $e ?>" <?= $estado === $e ? 'selected' : '' ?>><?= $e ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="control">
          <button class="button is-link" type="submit">Buscar</button>
        </div>
        <div class="control">
          <a href="index.php" class="button is-light">Limpiar</a>
        </div>
      </div>
    </form>
    <a href="add.php" class="button is-primary mb-4">Programar cita</a>
    <table class="table is-fullwidth is-striped">
      <thead>
        <tr>
          <th>Paciente</th>
          <th>Doctor</th>
          <th>Fecha y hora</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($citas as $cita): ?>
        <tr>
          <td><?= htmlspecialchars($cita['nombre_paciente']) ?></td>
          <td><?= htmlspecialchars($cita['nombre_doctor']) ?></td>
          <td><?= htmlspecialchars($cita['fecha_cita']) ?></td>
          <td><?= htmlspecialchars($cita['estado']) ?></td>
          <td>
            <a href="edit.php?id=<?= $cita['id_cita'] ?>" class="button is-small is-warning">Editar</a>
            <a href="delete.php?id=<?= $cita['id_cita'] ?>" class="button is-small is-danger" onclick="return confirm('¿Seguro que deseas cancelar esta cita?');">Cancelar</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if (empty($citas)): ?>
      <p>No se encontraron citas.</p>
    <?php endif; ?>
    <!-- Paginación -->
    <?php if ($total_paginas > 1): ?>
      <nav class="pagination is-centered" role="navigation" aria-label="pagination">
        <?php $url = 'index.php?busqueda=' . urlencode($busqueda) . '&estado=' . urlencode($estado) . '&'; ?>
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
