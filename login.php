<?php
/* Inicio de sesión (sin menú) */
$titulo      = 'Iniciar sesión';
$mostrarMenu = false;
$scripts     = ['assets/js/login.js'];

require __DIR__ . '/includes/encabezado.php';
?>

<div class="tarjeta">
    <h2>Iniciar sesión</h2>

    <label for="correo">Correo electrónico</label>
    <br>
    <input type="email" id="correo">
    <br>

    <label for="contrasena">Contraseña</label>
    <br>
    <input type="password" id="contrasena">
    <br>

    <button onclick="iniciarSesion()">Ingresar</button>

    <p id="mensaje"></p>
</div>

<?php require __DIR__ . '/includes/pie.php'; ?>
