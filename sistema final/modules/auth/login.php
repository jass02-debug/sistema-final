<?php
session_start(); // Inicia la sesión del usuario.

require_once '../../config/db.php'; // Incluye el archivo de configuración de la base de datos.

// Procesar formulario de inicio de sesión.
$error = ''; // Variable para almacenar mensajes de error.
if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verificar si se ha enviado el formulario.
    $correo = trim($_POST['correo'] ?? ''); // Obtener el correo electrónico del formulario, eliminando espacios en blanco.
    $contrasena = trim($_POST['contrasena'] ?? ''); // Obtener la contraseña del formulario, eliminando espacios en blanco.

    if ($correo && $contrasena) { // Verificar si se han ingresado el correo electrónico y la contraseña.
        // Preparar la consulta SQL para obtener el usuario de la base de datos.
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE correo = ? AND contraseña = ?'); 
        $stmt->execute([$correo, $contrasena]); // Ejecutar la consulta con los valores del correo electrónico y la contraseña.
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC); // Obtener los datos del usuario como un array asociativo.
        if ($usuario) { // Verificar si se encontró un usuario que coincida con el correo electrónico y la contraseña.
            // Almacenar la información del usuario en la sesión.
            $_SESSION['usuario'] = [
                'id_usuario' => $usuario['id_usuario'],
                'nombre' => $usuario['nombre'],
                'correo' => $usuario['correo'],
                'id_rol' => $usuario['id_rol']
            ];
            header('Location: ../../index.php'); // Redirigir al usuario a la página principal.
            exit; // Salir del script.
        } else {
            $error = 'Correo o contraseña incorrectos.'; // Establecer un mensaje de error si las credenciales son incorrectas.
        }
    } else {
        $error = 'Por favor, completa todos los campos.'; // Establecer un mensaje de error si no se han ingresado todos los campos.
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="../../assets/css/bulma.min.css">
</head>
<body>
<section class="section">
    <div class="container">
        <div class="columns is-centered">
            <div class="column is-half">
                <div class="box">
                    <div class="has-text-centered mb-6">
                        <img src="/assets/img/logo.png" alt="Logo Sistema Pediátrico Integral" style="max-width: 200px;">
                        <h1 class="title">Sistema Pediátrico Integral</h1>
                    </div>
                    <?php if ($error): ?>
                        <div class="notification is-danger">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    <form method="post">
                        <div class="field">
                            <label class="label">Correo electrónico</label>
                            <div class="control">
                                <input class="input" type="email" name="correo" required autofocus>
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">Contraseña</label>
                            <div class="control">
                                <input class="input" type="password" name="contrasena" required>
                            </div>
                        </div>
                        <div class="field is-grouped is-grouped-centered">
                            <div class="control">
                                <button class="button is-link" type="submit">Entrar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
</body>
</html>
