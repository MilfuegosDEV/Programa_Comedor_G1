<?php
/*
 * ENCABEZADO COMÚN (se incluye al inicio de cada página)
 *
 * Variables que puede definir la página ANTES de incluir este archivo:
 *   $titulo       Texto de la pestaña del navegador.            Ej: 'Inventario'
 *   $paginaActiva Enlace del menú que se marca como activo.     Ej: 'inventario'
 *   $mostrarMenu  false para páginas sin menú (login).          Por defecto: true
 *   $usarTablas   true si la página usa jQuery + DataTables.    Por defecto: false
 */
require_once __DIR__ . '/funciones.php';

$titulo       = $titulo ?? 'Inicio';
$paginaActiva = $paginaActiva ?? '';
$mostrarMenu  = $mostrarMenu ?? true;
$usarTablas   = $usarTablas ?? false;

// Menú: clave => [texto, archivo]
$menu = [
    'beneficiarios' => ['Beneficiarios',     'beneficiarios.php'],
    'menus'         => ['Menús',             'menus.php'],
    'inventario'    => ['Inventario',        'inventario.php'],
    'compras'       => ['Compras',           'compras.php'],
    'raciones'      => ['Raciones servidas', 'raciones.php'],
    'reportes'      => ['Reportes',          'reportes.php'],
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?> | Comedores Estudiantiles</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">

    <?php if ($usarTablas): ?>
        <link rel="stylesheet" href="assets/vendor/datatables/dataTables.dataTables.min.css">
    <?php endif; ?>
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>

<body>
    <h1>Comedores Estudiantiles</h1>

    <?php if ($mostrarMenu): ?>
        <nav id="menu" aria-label="Módulos del sistema">
            <?php foreach ($menu as $clave => [$texto, $archivo]): ?>
                <a href="<?= e($archivo) ?>"<?= $clave === $paginaActiva ? ' class="activo" aria-current="page"' : '' ?>><?= e($texto) ?></a>
            <?php endforeach; ?>
            <a href="login.php">Cerrar sesión</a>
        </nav>
    <?php endif; ?>

    <main>
