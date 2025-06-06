<?php
require_once '../../includes/header.php';
require_once '../../config/db.php';

// Verificar permisos (asumiendo que solo doctores o administradores pueden editar vacunas)
if (!in_array($_SESSION['usuario']['id_rol'], [1, 2])) {
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT v.*, p.nombre AS nombre_paciente, tv.nombre AS nombre_tipo_vacuna, hm.diagnostico, hm.tratamiento FROM vacunas v JOIN pacientes p ON v.id_paciente = p.id_paciente JOIN tipos_vacunas tv ON v.id_tipo_vacuna = tv.id_tipo_vacuna LEFT JOIN vacuna_historial vh ON v.id_vacuna = vh.id_vacuna LEFT JOIN historial_medico hm ON vh.id_historial = hm.id_historial WHERE v.id_vacuna = ?');
$stmt->execute([$id]);
$vacuna = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vacuna) {
    header('Location: index.php');
    exit;
}

// Obtener pacientes
$stmt = $pdo->query('SELECT id_paciente, nombre FROM pacientes ORDER BY nombre');
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener tipos de vacunas
$stmt = $pdo->query('SELECT id_tipo_vacuna, nombre FROM tipos_vacunas ORDER BY nombre');
$tiposVacunas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_paciente = intval($_POST['id_paciente'] ?? 0);
    $id_tipo_vacuna = intval($_POST['id_tipo_vacuna'] ?? 0);
    $fecha_aplicacion = trim($_POST['fecha_aplicacion'] ?? '');
    $dosis = intval($_POST['dosis'] ?? 0);
    $lote = trim($_POST['lote'] ?? '');
    $estado = $_POST['estado'] ?? 'Aplicada';
    $diagnostico = trim($_POST['diagnostico'] ?? '');
    $tratamiento = trim($_POST['tratamiento'] ?? '');

    if ($id_paciente && $id_tipo_vacuna && $fecha_aplicacion && $dosis && $lote) {
        $stmt = $pdo->prepare('UPDATE vacunas SET id_paciente=?, id_tipo_vacuna=?, fecha_aplicacion=?, dosis=?, lote=?, estado=? WHERE id_vacuna=?');
        $stmt->execute([$id_paciente, $id_tipo_vacuna, $fecha_aplicacion, $dosis, $lote, $estado, $id]);
        $stmt = $pdo->prepare('SELECT id_historial FROM vacuna_historial WHERE id_vacuna = ?');
        $stmt->execute([$id]);
        $id_historial = $stmt->fetchColumn();
        if ($id_historial) {
            $stmt = $pdo->prepare('UPDATE historial_medico SET diagnostico = ?, tratamiento = ?, actualizado_por = ? WHERE id_historial = ?');
            $stmt->execute([$diagnostico, $tratamiento, $_SESSION['usuario']['id_usuario'], $id_historial]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO historial_medico (id_paciente, diagnostico, tratamiento, creado_por) VALUES (?, ?, ?, ?)');
            $stmt->execute([$id_paciente, $diagnostico, $tratamiento, $_SESSION['usuario']['id_usuario']]);
            $id_historial = $pdo->lastInsertId();
            $stmt = $pdo->prepare('INSERT INTO vacuna_historial (id_vacuna, id_historial) VALUES (?, ?)');
            $stmt->execute([$id, $id_historial]);
        }
        header('Location: index.php');
        exit;
    } else {
        $error = 'Todos los campos son obligatorios.';
    }
}
?>
<section class="section">
  <div class="container">
    <h1 class="title">Editar vacuna</h1>
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
                <option value=<?= $p['id_paciente'] ?> <?php if ($vacuna['id_paciente'] == $p['id_paciente']): ?>selected<?php endif; ?>><?= htmlspecialchars($p['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      <div class="field">
        <label class="label">Tipo de vacuna</label>
        <div class="control">
          <div class="select is-fullwidth">
            <select name="id_tipo_vacuna" required>
              <option value="">Seleccione un tipo de vacuna</option>
              <?php foreach ($tiposVacunas as $tv): ?>
                <option value=<?= $tv['id_tipo_vacuna'] ?> <?php if ($vacuna['id_tipo_vacuna'] == $tv['id_tipo_vacuna']): ?>selected<?php endif; ?>><?= htmlspecialchars($tv['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      <div class="field">
        <label class="label">Fecha de aplicación</label>
        <div class="control">
          <input class="input" type="date" name="fecha_aplicacion" value=<?= date('Y-m-d', strtotime($vacuna['fecha_aplicacion'])) ?> required>
        </div>
      </div>
      <div class="field">
        <label class="label">Dosis</label>
        <div class="control">
          <input class="input" type="number" name="dosis" value=<?= $vacuna['dosis'] ?> required>
        </div>
      </div>
      <div class="field">
        <label class="label">Lote</label>
        <div class="control">
          <input class="input" type="text" name="lote" value=<?= $vacuna['lote'] ?> required>
        </div>
      </div>
      <div class="field">
        <label class="label">Diagnóstico</label>
        <div class="control">
          <textarea class="textarea" name="diagnostico" required><?= htmlspecialchars($vacuna['diagnostico']) ?></textarea>
        </div>
      </div>
      <div class="field">
        <label class="label">Tratamiento</label>
        <div class="control">
          <textarea class="textarea" name="tratamiento" required><?= htmlspecialchars($vacuna['tratamiento']) ?></textarea>
        </div>
      </div>
      <div class="field">
        <label class="label">Estado</label>
        <div class="control">
          <div class="select">
            <select name="estado">
              <option value="Aplicada" <?php if ($vacuna['estado'] == 'Aplicada'): ?>selected<?php endif; ?>>Aplicada</option>
              <option value="No Aplicada" <?php if ($vacuna['estado'] == 'No Aplicada'): ?>selected<?php endif; ?>>No Aplicada</option>
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
