<?php
require_once '../../includes/header.php';
require_once '../../config/db.php';
require_rol(1);

$id = intval($_GET['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id_usuario = ?');
    $stmt->execute([$id]);
}
header('Location: index.php');
exit;
