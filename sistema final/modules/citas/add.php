<?php
require_once '../../includes/header.php';
require_once '../../config/db.php';

// Solo doctor, recepcionista o admin pueden programar
if (!in_array($_SESSION['usuario']['id_rol'], [1,2,3])) {
    header('Location: index.php');
    exit;
}

// Obtener pacientes
$stmt = $pdo->query('SELECT id_paciente, nombre FROM pacientes ORDER BY nombre');
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Obtener doctores
$stmt = $pdo->query('SELECT id_usuario, nombre FROM usuarios WHERE id_rol = 2 ORDER BY nombre');
$doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_paciente = intval($_POST['id_paciente'] ?? 0);
    $id_doctor = intval($_POST['id_doctor'] ?? 0);
    $fecha_cita = trim($_POST['fecha_cita'] ?? '');
    $creado_por = $_SESSION['usuario']['id_usuario'];

    if ($id_paciente && $id_doctor && $fecha_cita) {
        $stmt = $pdo->prepare('INSERT INTO citas (id_paciente, id_doctor, fecha_cita, creado_por) VALUES (?, ?, ?, ?)');
        $stmt->execute([$id_paciente, $id_doctor, $fecha_cita, $creado_por]);
        header('Location: index.php');
        exit;
    } else {
        $error = 'Todos los campos son obligatorios.';
    }
}
?>
<section class="section">
  <div class="container">
    <h1 class="title">Programar nueva cita</h1>
    <?php if ($error): ?>
      <div class="notification is-danger">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    <form method="post" class="box">
      <div class="field">
        <label class="label">Paciente</label>
        <div class="control">
          <input class="input is-fullwidth" type="text" name="paciente_busqueda" id="paciente_busqueda" placeholder="Buscar paciente" required>
          <input type="hidden" name="id_paciente" id="id_paciente">
          <ul id="lista-pacientes"></ul>
        </div>
      </div>
      <div class="field">
        <label class="label">Doctor</label>
        <div class="control">
          <div class="select is-fullwidth">
            <select name="id_doctor" required>
              <option value="">Seleccione un doctor</option>
              <?php foreach ($doctores as $d): ?>
                <option value="<?= $d['id_usuario'] ?>"><?= htmlspecialchars($d['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      <div class="field">
        <label class="label">Fecha y hora</label>
        <div class="control">
          <input class="input" type="datetime-local" name="fecha_cita" required>
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
<script>
  const pacienteBusqueda = document.getElementById('paciente_busqueda');
  const idPacienteInput = document.getElementById('id_paciente');
  const pacientes = <?php echo json_encode($pacientes); ?>;

  pacienteBusqueda.addEventListener('input', () => {
    const filtro = pacienteBusqueda.value.toLowerCase();
    const opcionesFiltradas = pacientes.filter(paciente => paciente.nombre.toLowerCase().includes(filtro)).slice(0,5);
    mostrarOpciones(opcionesFiltradas);
  });

  // Mostrar opciones al cargar la página
  mostrarOpciones(pacientes.slice(0,5));

  // Mostrar opciones al cargar la página
  mostrarOpciones(pacientes);

  function mostrarOpciones(opciones) {
    const listaPacientes = document.getElementById('lista-pacientes');
    listaPacientes.innerHTML = '';
    opciones.forEach(paciente => {
      const elemento = document.createElement('li');
      elemento.textContent = paciente.nombre;
      elemento.addEventListener('click', () => {
        idPacienteInput.value = paciente.id_paciente;
        pacienteBusqueda.value = paciente.nombre;
        listaPacientes.innerHTML = '';
      });
      listaPacientes.appendChild(elemento);
    });
  }
</script>
