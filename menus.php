<?php
session_start();

/* Configuración del módulo */
$titulo = 'Menús';
$paginaActiva = 'menus';
$usarTablas = true;

/* Cargar datos de ejemplo */
require_once __DIR__ . '/includes/datos.php';

$platillos = obtenerPlatillos();

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

require __DIR__ . '/includes/encabezado.php';
?>

<!-- FORMULARIO DE MENÚS -->
<div class="tarjeta tarjeta-ancha">

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

        <?php foreach ($platillos as $idPlatillo => $platillo): ?>

            <label>
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

            <br>

        <?php endforeach; ?>

        <br>

        <button type="submit">
            <?= $editando ? 'Guardar cambios' : 'Registrar menú' ?>
        </button>

        <?php if ($editando): ?>
            <a href="menus.php">Cancelar edición</a>
        <?php endif; ?>

    </form>

</div>

<!-- LISTADO DE MENÚS -->
<div class="tarjeta tarjeta-ancha">

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

                            <form method="POST"
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

<?php require __DIR__ . '/includes/pie.php'; ?>
