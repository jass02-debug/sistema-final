<?php
require_once '../../includes/header.php';
require_once '../../config/db.php';

// Paginación
$registros_por_pagina = 10;
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina - 1) * $registros_por_pagina;

// Filtros de búsqueda
$id_paciente = isset($_GET['id_paciente']) ? intval($_GET['id_paciente']) : null;
$sql = 'SELECT v.*, p.nombre AS nombre_paciente, tv.nombre AS nombre_tipo_vacuna FROM vacunas v JOIN pacientes p ON v.id_paciente = p.id_paciente JOIN tipos_vacunas tv ON v.id_tipo_vacuna = tv.id_tipo_vacuna';
$where = [];
$params = [];
if ($id_paciente) {
    $where[] = 'v.id_paciente = ?';
    $params[] = $id_paciente;
}
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql_total = str_replace('SELECT v.*, p.nombre AS nombre_paciente, tv.nombre AS nombre_tipo_vacuna', 'SELECT COUNT(*)', $sql);
$stmt = $pdo->prepare($sql_total);
$stmt->execute($params);
$total_registros = $stmt->fetchColumn();
$total_paginas = ceil($total_registros / $registros_por_pagina);
$sql .= ' ORDER BY v.fecha_aplicacion DESC LIMIT ' . intval($registros_por_pagina) . ' OFFSET ' . intval($offset);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vacunas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener pacientes para autocompletado
$stmt = $pdo->query('SELECT id_paciente, nombre FROM pacientes ORDER BY nombre');
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<section class="section">
  <div class="container">
    <h1 class="title">Vacunas</h1>
    <div class="mb-4">
      <form method="get" id="searchForm">
        <div class="field">
          <label class="label">Buscar paciente</label>
          <div class="control">
            <input class="input" type="text" name="paciente_busqueda" id="paciente_busqueda" placeholder="Buscar por nombre de paciente">
            <input type="hidden" name="id_paciente" id="id_paciente">
            <ul id="lista-pacientes"></ul>
          </div>
        </div>
      </form>
    </div>
    <a href="add.php" class="button is-primary">Agregar vacuna</a>
    <table class="table is-fullwidth is-striped">
      <thead>
        <tr>
          <th>Paciente</th>
          <th>Tipo de vacuna</th>
          <th>Fecha de aplicación</th>
          <th>Dosis</th>
          <th>Lote</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($vacunas as $vacuna): ?>
        <tr>
          <td><?= htmlspecialchars($vacuna['nombre_paciente']) ?></td>
          <td><?= htmlspecialchars($vacuna['nombre_tipo_vacuna']) ?></td>
          <td><?= htmlspecialchars($vacuna['fecha_aplicacion']) ?></td>
          <td><?= htmlspecialchars($vacuna['dosis']) ?></td>
          <td><?= htmlspecialchars($vacuna['lote']) ?></td>
          <td><?= htmlspecialchars($vacuna['estado']) ?></td>
          <td>
            <a href="edit.php?id=<?= $vacuna['id_vacuna'] ?>" class="button is-small is-warning">Editar</a>
            <a href="delete.php?id=<?= $vacuna['id_vacuna'] ?>" class="button is-small is-danger" onclick="return confirm('¿Seguro que deseas eliminar esta vacuna?');">Eliminar</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if (empty($vacunas)): ?>
      <p>No se encontraron vacunas.</p>
    <?php endif; ?>
    <!-- Paginación -->
    <?php if ($total_paginas > 1): ?>
      <nav class="pagination is-centered" role="navigation" aria-label="pagination">
        <?php $url = 'index.php?id_paciente=' . urlencode($id_paciente) . '&'; ?>
        <a class="pagination-previous" href=<?php echo $pagina > 1 ? $url . 'pagina=' . ($pagina - 1) : '#' ?> <?php if ($pagina == 1): ?>disabled<?php endif; ?>>Anterior</a>
        <a class="pagination-next" href=<?php echo $pagina < $total_paginas ? $url . 'pagina=' . ($pagina + 1) : '#' ?> <?php if ($pagina == $total_paginas): ?>disabled<?php endif; ?>>Siguiente</a>
        <ul class="pagination-list">
          <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <li><a class="pagination-link <?php echo $i == $pagina ? 'is-current' : '' ?>" href=<?php echo $url . 'pagina=' . $i ?>><?= $i ?></a></li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
  </div>
</section>
<script>
  const pacienteBusqueda = document.getElementById('paciente_busqueda');
  const idPacienteInput = document.getElementById('id_paciente');
  const listaPacientes = document.getElementById('lista-pacientes');
  const pacientes = <?php echo json_encode($pacientes); ?>;
  const form = document.getElementById('searchForm');

  pacienteBusqueda.addEventListener('focus', () => {
    const filtro = pacienteBusqueda.value.toLowerCase();
    const opcionesFiltradas = pacientes.filter(paciente => paciente.nombre.toLowerCase().includes(filtro)).slice(0, 5);
    mostrarOpciones(opcionesFiltradas);
  });

  pacienteBusqueda.addEventListener('input', () => {
    const filtro = pacienteBusqueda.value.toLowerCase();
    const opcionesFiltradas = pacientes.filter(paciente => paciente.nombre.toLowerCase().includes(filtro)).slice(0, 5);
    mostrarOpciones(opcionesFiltradas);
  });

  function mostrarOpciones(opciones) {
    listaPacientes.innerHTML = '';
    opciones.forEach(paciente => {
      const elemento = document.createElement('li');
      elemento.textContent = paciente.nombre;
      elemento.addEventListener('click', (event) => {
        event.preventDefault();
        idPacienteInput.value = paciente.id_paciente;
        pacienteBusqueda.value = paciente.nombre;
        listaPacientes.innerHTML = '';
        form.submit();
      });
      listaPacientes.appendChild(elemento);
    });
  }

  form.addEventListener('submit', function(event) {
    if (pacienteBusqueda.value === '') {
      event.preventDefault();
      alert('Por favor, ingrese un nombre de paciente.');
    }
  });
</script>
