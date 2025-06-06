<?php
require_once '../../includes/header.php';
require_once '../../config/db.php';

// Solo doctor, recepcionista o admin pueden cancelar
if (!in_array($_SESSION['usuario']['id_rol'], [1,2,3])) {
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare('UPDATE citas SET estado = ? WHERE id_cita = ?');
    $stmt->execute(['Cancelada', $id]);
}
header('Location: index.php');
exit;
