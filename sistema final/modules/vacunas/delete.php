<?php
require_once '../../includes/header.php';
require_once '../../config/db.php';

// Verificar permisos (asumiendo que solo doctores o administradores pueden eliminar vacunas)
if (!in_array($_SESSION['usuario']['id_rol'], [1, 2])) {
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare('DELETE FROM vacunas WHERE id_vacuna = ?');
    $stmt->execute([$id]);
}
header('Location: index.php');
exit;
?>
