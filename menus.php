<?php
session_start();

/* Configuración del módulo */
$titulo = 'Menús';
$paginaActiva = 'menus';
$usarTablas = true;

/* Datos de platillos ampliados y ordenados */
$platillos = [
    1 => ['nombre' => 'Arroz con pollo'],
    2 => ['nombre' => 'Ensalada César'],
    3 => ['nombre' => 'Sopa '],
    4 => ['nombre' => 'Pechuga de pollo a la plancha'],
    5 => ['nombre' => 'Pasta Alfredo'],
    6 => ['nombre' => 'Carne asada'],
    7 => ['nombre' => 'Tacos al pastor'],
    8 => ['nombre' => 'Ceviche de camarones'],
    9 => ['nombre' => 'Pizza margarita'],
    10 => ['nombre' => 'Hamburguesa clásica'],
    11 => ['nombre' => 'Pescado frito'],
    12 => ['nombre' => 'Lasaña de carne'],
    13 => ['nombre' => 'Empanada de papa'],
    14 => ['nombre' => 'Ensalada de frutas'],
    15 => ['nombre' => 'Empanada de queso'],
];

/* Funciones auxiliares */
function e($texto)
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

function fechaValida($fecha)
{
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) && strtotime($fecha) !== false;
}

function fechaCorta($fecha)
{
    return date('d/m/Y', strtotime($fecha));
}

/* Inicializar los menús temporales */
if (!isset($_SESSION['menus'])) {
    $_SESSION['menus'] = [];
}

$mensaje = '';

/* Procesar formularios */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $accion = $_POST['accion'] ?? '';

    /* Registrar o actualizar menú */
    if ($accion === 'guardar') {

        $id = filter_var(
            $_POST['id'] ?? null,
            FILTER_VALIDATE_INT
        );

        $nombre = trim($_POST['nombre'] ?? '');
        $fecha = trim($_POST['fecha'] ?? '');
        $seleccionados = $_POST['platillos'] ?? [];

        if (!is_array($seleccionados)) {
            $seleccionados = [];
        }

        // Validar los platillos seleccionados.
        $seleccionados = array_values(array_unique(array_filter(
            $seleccionados,
            function ($valor) use ($platillos) {
                $idPlatillo = filter_var($valor, FILTER_VALIDATE_INT);

                return $idPlatillo !== false &&
                    isset($platillos[$idPlatillo]);
            }
        )));

        if ($nombre === '' || !fechaValida($fecha)) {
            $mensaje = 'Debe ingresar un nombre y una fecha válida.';
        } elseif (count($seleccionados) === 0) {
            $mensaje = 'Debe seleccionar al menos un platillo.';
        } elseif (
            isset($_POST['id']) &&
            ($id === false || !isset($_SESSION['menus'][$id]))
        ) {
            $mensaje = 'No se encontró el menú que desea editar.';
        } else {

            if (isset($_POST['id'])) {
                $idMenu = $id;
            } else {
                $idMenu = 1;

                if (!empty($_SESSION['menus'])) {
                    $idMenu = max(array_keys($_SESSION['menus'])) + 1;
                }
            }

            $_SESSION['menus'][$idMenu] = [
                'id' => $idMenu,
                'nombre' => $nombre,
                'fecha' => $fecha,
                'platillos' => $seleccionados
            ];

            $_SESSION['mensaje_menus'] = isset($_POST['id'])
                ? 'Menú actualizado correctamente.'
                : 'Menú registrado correctamente.';

            header('Location: menus.php');
            exit;
        }
    }

    /* Eliminar menú */
    if ($accion === 'eliminar') {

        $id = filter_var(
            $_POST['id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($id !== false && isset($_SESSION['menus'][$id])) {
            unset($_SESSION['menus'][$id]);

            $_SESSION['mensaje_menus'] =
                'Menú eliminado correctamente.';

            header('Location: menus.php');
            exit;
        }

        $mensaje = 'No se encontró el menú seleccionado.';
    }
}

/* Recuperar mensaje */
if (isset($_SESSION['mensaje_menus'])) {
    $mensaje = $_SESSION['mensaje_menus'];
    unset($_SESSION['mensaje_menus']);
}

/* Preparar edición */
$idEditar = filter_var(
    $_GET['editar'] ?? null,
    FILTER_VALIDATE_INT
);

$editando = (
    $idEditar !== false &&
    $idEditar !== null &&
    isset($_SESSION['menus'][$idEditar])
);

$datosEditar = $editando
    ? $_SESSION['menus'][$idEditar]
    : [
        'nombre' => '',
        'fecha' => date('Y-m-d'),
        'platillos' => []
    ];

/* Encabezado simple para que el código esté completo */
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= e($titulo) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f9f9f9;
        }
        .tarjeta {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }
        h2, h3 {
            margin-top: 0;
        }
        label {
            display: block;
            margin-bottom: 8px;
            cursor: pointer;
        }
        input[type="text"], input[type="date"] {
            width: 100%;
            box-sizing: border-box;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #007BFF;
            border: none;
            padding: 10px 18px;
            color: white;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        a {
            margin-left: 15px;
            color: #007BFF;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .mensaje {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #d6e9c6;
            border-radius: 4px;
        }
        .tabla--datos {
            width: 100%;
            border-collapse: collapse;
        }
        .tabla--datos th, .tabla--datos td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        .tabla--datos th {
            background-color: #f2f2f2;
        }
        /* Estilos para la cuadrícula de platillos */
        .platillos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 10px;
            max-height: 250px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
            background: #fefefe;
            margin-bottom: 15px;
        }
        .platillo-item {
            background-color: #fafafa;
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .platillo-item input[type="checkbox"] {
            margin-right: 8px;
        }
        form.inline {
            display: inline;
        }
    </style>
</head>
<body>

<!-- FORMULARIO DE MENÚS -->
<div class="tarjeta">

    <h2><?= $editando ? 'Editar menú' : 'Registrar menú' ?></h2>

    <?php if ($mensaje !== ''): ?>
        <p class="mensaje"><?= e($mensaje) ?></p>
    <?php endif; ?>

    <form method="POST">

        <input type="hidden" name="accion" value="guardar">

        <?php if ($editando): ?>
            <input
                type="hidden"
                name="id"
                value="<?= (int) $idEditar ?>"
            >
        <?php endif; ?>

        <label for="nombre">Nombre del menú</label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            placeholder="Ejemplo: Almuerzo del lunes"
            value="<?= e($datosEditar['nombre']) ?>"
            required
        >

        <label for="fecha">Fecha del menú</label>
        <input
            type="date"
            id="fecha"
            name="fecha"
            value="<?= e($datosEditar['fecha']) ?>"
            required
        >

        <h3>Seleccionar platillos</h3>

        <div class="platillos-grid">
            <?php foreach ($platillos as $idPlatillo => $platillo): ?>
                <label class="platillo-item">
                    <input
                        type="checkbox"
                        name="platillos[]"
                        value="<?= (int) $idPlatillo ?>"
                        <?= in_array(
                            (int) $idPlatillo,
                            array_map('intval', $datosEditar['platillos']),
                            true
                        ) ? 'checked' : '' ?>
                    >
                    <?= e($platillo['nombre']) ?>
                </label>
            <?php endforeach; ?>
        </div>

        <button type="submit">
            <?= $editando ? 'Guardar cambios' : 'Registrar menú' ?>
        </button>

        <?php if ($editando): ?>
            <a href="menus.php">Cancelar edición</a>
        <?php endif; ?>

    </form>

</div>

<!-- LISTADO DE MENÚS -->
<div class="tarjeta">

    <h2>Menús registrados</h2>

    <table class="tabla--datos">

        <thead>
            <tr>
                <th>Nombre</th>
                <th>Fecha</th>
                <th>Platillos</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>

            <?php if (empty($_SESSION['menus'])): ?>

                <tr>
                    <td colspan="4">
                        Todavía no hay menús registrados.
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($_SESSION['menus'] as $menu): ?>

                    <tr>

                        <td><?= e($menu['nombre']) ?></td>

                        <td><?= e(fechaCorta($menu['fecha'])) ?></td>

                        <td>
                            <?php
                            $nombresPlatillos = [];

                            foreach ($menu['platillos'] as $idPlatillo) {
                                if (isset($platillos[$idPlatillo])) {
                                    $nombresPlatillos[] =
                                        $platillos[$idPlatillo]['nombre'];
                                }
                            }

                            echo e(implode(', ', $nombresPlatillos));
                            ?>
                        </td>

                        <td>
                            <a href="menus.php?editar=<?= (int) $menu['id'] ?>">
                                Editar
                            </a>

                            <form method="POST" class="inline"
                                  onsubmit="return confirm('¿Está seguro de eliminar este menú?');">

                                <input
                                    type="hidden"
                                    name="accion"
                                    value="eliminar"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $menu['id'] ?>"
                                >

                                <button type="submit">
                                    Eliminar
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

        </tbody>

    </table>

</div>

</body>
</html>


