<?php
session_start();

function usuario_autenticado() {
    return isset($_SESSION['usuario']);
}

function usuario_rol($rol_id) {
    return usuario_autenticado() && $_SESSION['usuario']['id_rol'] == $rol_id;
}

function require_login() {
    if (!usuario_autenticado()) {
        header('Location: /modules/auth/login.php');
        exit;
    }
}

function require_rol($rol_id) {
    if (!usuario_rol($rol_id)) {
        header('Location: /index.php?error=permiso');
        exit;
    }
}
