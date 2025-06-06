<?php
require_once '../../includes/header.php';
require_once '../../config/db.php';

$id = intval($_GET['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare('DELETE FROM pacientes WHERE id_paciente = ?');
    $stmt->execute([$id]);
}
header('Location: index.php');
exit;
