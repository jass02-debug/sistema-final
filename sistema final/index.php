<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/db.php';

$rol = $_SESSION['usuario']['id_rol'];
$nombre = htmlspecialchars($_SESSION['usuario']['nombre']);

// Tarjetas de resumen
$total_pacientes = 0;
$total_usuarios = 0;
$citas_hoy = 0;
$citas_proximas = [];
$ultimos_pacientes = [];
$citas_urgentes = [];
$cumples_proximos = [];
$ultimos_usuarios = [];

// Estadísticas mensuales (últimos 6 meses)
$estadisticas = [];
$meses = [];
for ($i = 5; $i >= 0; $i--) {
    $mes = date('Y-m', strtotime("-{$i} months"));
    $meses[] = $mes;
    // Pacientes nuevos
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pacientes WHERE DATE_FORMAT(fecha_nacimiento, '%Y-%m') = ?");
    $stmt->execute([$mes]);
    $pacientes_mes = $stmt->fetchColumn();
    // Citas realizadas
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM citas WHERE estado = 'Realizada' AND DATE_FORMAT(fecha_cita, '%Y-%m') = ?");
    $stmt->execute([$mes]);
    $citas_mes = $stmt->fetchColumn();
    // Consultas médicas
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM historial_medico WHERE DATE_FORMAT(fecha_registro, '%Y-%m') = ?");
    $stmt->execute([$mes]);
    $consultas_mes = $stmt->fetchColumn();
    $estadisticas[] = [
        'mes' => $mes,
        'pacientes' => $pacientes_mes,
        'citas' => $citas_mes,
        'consultas' => $consultas_mes
    ];
}

if ($rol == 1) { // Administrador
    $total_pacientes = $pdo->query('SELECT COUNT(*) FROM pacientes')->fetchColumn();
    $total_usuarios = $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    $citas_hoy = $pdo->query("SELECT COUNT(*) FROM citas WHERE DATE(fecha_cita) = CURDATE() AND estado = 'Programada'")->fetchColumn();
    $stmt = $pdo->prepare("SELECT c.fecha_cita, p.nombre AS paciente, u.nombre AS doctor FROM citas c JOIN pacientes p ON c.id_paciente = p.id_paciente JOIN usuarios u ON c.id_doctor = u.id_usuario WHERE c.estado = 'Programada' AND c.fecha_cita >= NOW() ORDER BY c.fecha_cita ASC LIMIT 5");
    $stmt->execute();
    $citas_proximas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Últimos pacientes
    $stmt = $pdo->query("SELECT nombre, fecha_nacimiento FROM pacientes ORDER BY id_paciente DESC LIMIT 5");
    $ultimos_pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Citas próximas en menos de 1 hora
    $stmt = $pdo->prepare("SELECT c.fecha_cita, p.nombre AS paciente, u.nombre AS doctor FROM citas c JOIN pacientes p ON c.id_paciente = p.id_paciente JOIN usuarios u ON c.id_doctor = u.id_usuario WHERE c.estado = 'Programada' AND c.fecha_cita >= NOW() AND c.fecha_cita <= DATE_ADD(NOW(), INTERVAL 1 HOUR) ORDER BY c.fecha_cita ASC");
    $stmt->execute();
    $citas_urgentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Últimos usuarios
    $stmt = $pdo->query("SELECT nombre, correo FROM usuarios ORDER BY id_usuario DESC LIMIT 5");
    $ultimos_usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($rol == 2) { // Doctor
    $id_doctor = $_SESSION['usuario']['id_usuario'];
    $citas_hoy = $pdo->prepare("SELECT COUNT(*) FROM citas WHERE id_doctor = ? AND DATE(fecha_cita) = CURDATE() AND estado = 'Programada'");
    $citas_hoy->execute([$id_doctor]);
    $citas_hoy = $citas_hoy->fetchColumn();
    $stmt = $pdo->prepare("SELECT c.fecha_cita, p.nombre AS paciente FROM citas c JOIN pacientes p ON c.id_paciente = p.id_paciente WHERE c.id_doctor = ? AND c.estado = 'Programada' AND c.fecha_cita >= NOW() ORDER BY c.fecha_cita ASC LIMIT 5");
    $stmt->execute([$id_doctor]);
    $citas_proximas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_pacientes = $pdo->prepare('SELECT COUNT(DISTINCT id_paciente) FROM citas WHERE id_doctor = ?');
    $total_pacientes->execute([$id_doctor]);
    $total_pacientes = $total_pacientes->fetchColumn();
    // Últimos pacientes
    $stmt = $pdo->query("SELECT nombre, fecha_nacimiento FROM pacientes ORDER BY id_paciente DESC LIMIT 5");
    $ultimos_pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Citas próximas en menos de 1 hora
    $stmt = $pdo->prepare("SELECT c.fecha_cita, p.nombre AS paciente FROM citas c JOIN pacientes p ON c.id_paciente = p.id_paciente WHERE c.id_doctor = ? AND c.estado = 'Programada' AND c.fecha_cita >= NOW() AND c.fecha_cita <= DATE_ADD(NOW(), INTERVAL 1 HOUR) ORDER BY c.fecha_cita ASC");
    $stmt->execute([$id_doctor]);
    $citas_urgentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Cumpleaños próximos (próximos 7 días)
    $stmt = $pdo->query("SELECT nombre, fecha_nacimiento FROM pacientes WHERE DATE_FORMAT(fecha_nacimiento, '%m-%d') BETWEEN DATE_FORMAT(CURDATE(), '%m-%d') AND DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 7 DAY), '%m-%d') ORDER BY fecha_nacimiento");
    $cumples_proximos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($rol == 3) { // Recepcionista
    $total_pacientes = $pdo->query('SELECT COUNT(*) FROM pacientes')->fetchColumn();
    $citas_hoy = $pdo->query("SELECT COUNT(*) FROM citas WHERE DATE(fecha_cita) = CURDATE() AND estado = 'Programada'")->fetchColumn();
    $stmt = $pdo->prepare("SELECT c.fecha_cita, p.nombre AS paciente, u.nombre AS doctor FROM citas c JOIN pacientes p ON c.id_paciente = p.id_paciente JOIN usuarios u ON c.id_doctor = u.id_usuario WHERE c.estado = 'Programada' AND c.fecha_cita >= NOW() ORDER BY c.fecha_cita ASC LIMIT 5");
    $stmt->execute();
    $citas_proximas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Últimos pacientes
    $stmt = $pdo->query("SELECT nombre, fecha_nacimiento FROM pacientes ORDER BY id_paciente DESC LIMIT 5");
    $ultimos_pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Citas próximas en menos de 1 hora
    $stmt = $pdo->prepare("SELECT c.fecha_cita, p.nombre AS paciente, u.nombre AS doctor FROM citas c JOIN pacientes p ON c.id_paciente = p.id_paciente JOIN usuarios u ON c.id_doctor = u.id_usuario WHERE c.estado = 'Programada' AND c.fecha_cita >= NOW() AND c.fecha_cita <= DATE_ADD(NOW(), INTERVAL 1 HOUR) ORDER BY c.fecha_cita ASC");
    $stmt->execute();
    $citas_urgentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Cumpleaños próximos (próximos 7 días)
    $stmt = $pdo->query("SELECT nombre, fecha_nacimiento FROM pacientes WHERE DATE_FORMAT(fecha_nacimiento, '%m-%d') BETWEEN DATE_FORMAT(CURDATE(), '%m-%d') AND DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 7 DAY), '%m-%d') ORDER BY fecha_nacimiento");
    $cumples_proximos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel principal | Sistema Pediátrico</title>
    <link rel="stylesheet" href="/assets/css/bulma.min.css">
</head>
<body>
    <section class="section">
        <div class="container">
            <h1 class="title">Bienvenido, <?= $nombre ?>!</h1>
            <div class="columns">
                <div class="column">
                    <div class="box has-text-centered">
                        <p class="heading">Pacientes</p>
                        <p class="title"><?= $total_pacientes ?></p>
                    </div>
                </div>
                <?php if ($rol == 1): ?>
                <div class="column">
                    <div class="box has-text-centered">
                        <p class="heading">Usuarios</p>
                        <p class="title"><?= $total_usuarios ?></p>
                    </div>
                </div>
                <?php endif; ?>
                <div class="column">
                    <div class="box has-text-centered">
                        <p class="heading">Citas hoy</p>
                        <p class="title"><?= $citas_hoy ?></p>
                    </div>
                </div>
            </div>

            <!-- Estadísticas mensuales -->
            <div class="box">
                <h2 class="subtitle">Estadísticas mensuales (últimos 6 meses)</h2>
                <div class="table-container">
                    <table class="table is-fullwidth is-striped is-hoverable is-responsive">
                        <thead>
                            <tr>
                                <th>Mes</th>
                                <th>Pacientes nuevos</th>
                                <th>Citas realizadas</th>
                                <th>Consultas médicas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estadisticas as $e): ?>
                                <tr>
                                    <td><?= htmlspecialchars($e['mes']) ?></td>
                                    <td><?= $e['pacientes'] ?></td>
                                    <td><?= $e['citas'] ?></td>
                                    <td><?= $e['consultas'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Accesos directos -->
            <div class="buttons mb-4 is-grouped is-grouped-multiline">
                <a href="/modules/pacientes/add.php" class="button is-link is-light">Registrar paciente</a>
                <a href="/modules/citas/add.php" class="button is-link is-light">Programar cita</a>
                <a href="/modules/pacientes/index.php" class="button is-link is-light">Buscar paciente</a>
                <a href="/modules/citas/index.php" class="button is-link is-light">Ver agenda</a>
                <a href="/modules/vacunas/index.php" class="button is-link is-light">Ver vacunas</a>
            </div>

            <!-- Alertas de citas próximas en menos de 1 hora -->
            <?php if (!empty($citas_urgentes)): ?>
                <div class="notification is-warning">
                    <strong>¡Atención!</strong> Tienes citas programadas en menos de 1 hora:
                    <ul>
                        <?php foreach ($citas_urgentes as $cita): ?>
                            <li>
                                <?= htmlspecialchars($cita['fecha_cita']) ?> - Paciente: <?= htmlspecialchars($cita['paciente']) ?><?php if (isset($cita['doctor'])): ?>, Doctor: <?= htmlspecialchars($cita['doctor']) ?><?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="columns">
                <div class="column is-half">
                    <div class="box">
                        <h2 class="subtitle">Últimos pacientes registrados</h2>
                        <?php if (empty($ultimos_pacientes)): ?>
                            <p>No hay pacientes recientes.</p>
                        <?php else: ?>
                            <ul class="is-size-7">
                                <?php foreach ($ultimos_pacientes as $p): ?>
                                    <li><?= htmlspecialchars($p['nombre']) ?> (<?= date('d/m/Y', strtotime($p['fecha_nacimiento'])) ?>)</li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($rol == 1): ?>
                <div class="column is-half">
                    <div class="box">
                        <h2 class="subtitle">Últimos usuarios creados</h2>
                        <?php if (empty($ultimos_usuarios)): ?>
                            <p>No hay usuarios recientes.</p>
                        <?php else: ?>
                            <ul class="is-size-7">
                                <?php foreach ($ultimos_usuarios as $u): ?>
                                    <li><?= htmlspecialchars($u['nombre']) ?> (<?= htmlspecialchars($u['correo']) ?>)</li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($rol == 2 || $rol == 3): ?>
                <div class="column is-half">
                    <div class="box">
                        <h2 class="subtitle">Cumpleaños próximos (7 días)</h2>
                        <?php if (empty($cumples_proximos)): ?>
                            <p>No hay cumpleaños próximos.</p>
                        <?php else: ?>
                            <ul class="is-size-7">
                                <?php foreach ($cumples_proximos as $c): ?>
                                    <li><?= htmlspecialchars($c['nombre']) ?> (<?= date('d/m', strtotime($c['fecha_nacimiento'])) ?>)</li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="box">
                <h2 class="subtitle">Próximas citas</h2>
                <?php if (empty($citas_proximas)): ?>
                    <p>No hay citas próximas.</p>
                <?php else: ?>
                    <table class="table is-fullwidth is-responsive">
                        <thead>
                            <tr>
                                <th>Fecha y hora</th>
                                <th>Paciente</th>
                                <?php if ($rol != 2): ?><th>Doctor</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($citas_proximas as $cita): ?>
                                <tr>
                                    <td><?= htmlspecialchars($cita['fecha_cita']) ?></td>
                                    <td><?= htmlspecialchars($cita['paciente']) ?></td>
                                    <?php if ($rol != 2): ?><td><?= htmlspecialchars($cita['doctor']) ?></td><?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </section>
</body>
</html>
