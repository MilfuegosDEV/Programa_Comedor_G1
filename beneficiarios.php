<?php
session_start();

/* Configuración del módulo */
$titulo = 'Beneficiarios';
$paginaActiva = 'beneficiarios';
$usarTablas = true;

/* Cargar los datos de ejemplo */
require_once __DIR__ . '/includes/datos.php';

/* Inicializar los beneficiarios */
if (!isset($_SESSION['beneficiarios'])) {
    $_SESSION['beneficiarios'] = obtenerBeneficiarios();
}

$mensaje = '';

/* Procesar las acciones */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $accion = $_POST['accion'] ?? '';

    /* Registrar beneficiario */
    if ($accion === 'registrar') {

        $nombre = trim($_POST['nombre'] ?? '');
        $identificacion = trim($_POST['identificacion'] ?? '');
        $nivel = trim($_POST['nivel'] ?? '');

        if ($nombre === '' || $identificacion === '' || $nivel === '') {
            $mensaje = 'Debe completar todos los campos.';
        } else {

            $duplicado = false;

            foreach ($_SESSION['beneficiarios'] as $beneficiario) {
                if ($beneficiario['identificacion'] === $identificacion) {
                    $duplicado = true;
                    break;
                }
            }

            if ($duplicado) {
                $mensaje = 'La identificación ya está registrada.';
            } else {
                $_SESSION['beneficiarios'][] = [
                    'nombre' => $nombre,
                    'identificacion' => $identificacion,
                    'nivel' => $nivel,
                    'vacaciones' => false,
                    'activo' => true
                ];

                $_SESSION['mensaje_beneficiarios'] =
                    'Beneficiario registrado correctamente.';

                header('Location: beneficiarios.php');
                exit;
            }
        }
    }

    /* Editar beneficiario */
    if ($accion === 'editar') {

        $indice = filter_var(
            $_POST['indice'] ?? null,
            FILTER_VALIDATE_INT
        );

        $nombre = trim($_POST['nombre'] ?? '');
        $identificacion = trim($_POST['identificacion'] ?? '');
        $nivel = trim($_POST['nivel'] ?? '');

        if (
            $indice === false ||
            !isset($_SESSION['beneficiarios'][$indice])
        ) {
            $mensaje = 'Beneficiario no encontrado.';
        } elseif ($nombre === '' || $identificacion === '' || $nivel === '') {
            $mensaje = 'Debe completar todos los campos.';
        } else {

            $duplicado = false;

            foreach ($_SESSION['beneficiarios'] as $i => $beneficiario) {
                if (
                    $i !== $indice &&
                    $beneficiario['identificacion'] === $identificacion
                ) {
                    $duplicado = true;
                    break;
                }
            }

            if ($duplicado) {
                $mensaje = 'Esa identificación pertenece a otro estudiante.';
            } else {
                $_SESSION['beneficiarios'][$indice]['nombre'] = $nombre;
                $_SESSION['beneficiarios'][$indice]['identificacion'] = $identificacion;
                $_SESSION['beneficiarios'][$indice]['nivel'] = $nivel;

                $_SESSION['mensaje_beneficiarios'] =
                    'Beneficiario actualizado correctamente.';

                header('Location: beneficiarios.php');
                exit;
            }
        }
    }

    /* Activar o desactivar */
    if ($accion === 'estado') {

        $indice = filter_var(
            $_POST['indice'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (
            $indice !== false &&
            isset($_SESSION['beneficiarios'][$indice])
        ) {
            $_SESSION['beneficiarios'][$indice]['activo'] =
                !$_SESSION['beneficiarios'][$indice]['activo'];

            $_SESSION['mensaje_beneficiarios'] =
                'Estado del beneficiario actualizado.';

            header('Location: beneficiarios.php');
            exit;
        }
    }

    /* Cambiar estado de vacaciones */
    if ($accion === 'vacaciones') {

        $indice = filter_var(
            $_POST['indice'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (
            $indice !== false &&
            isset($_SESSION['beneficiarios'][$indice])
        ) {
            $_SESSION['beneficiarios'][$indice]['vacaciones'] =
                !$_SESSION['beneficiarios'][$indice]['vacaciones'];

            $_SESSION['mensaje_beneficiarios'] =
                'Estado de vacaciones actualizado.';

            header('Location: beneficiarios.php');
            exit;
        }
    }
}

/* Obtener mensaje de confirmación */
if (isset($_SESSION['mensaje_beneficiarios'])) {
    $mensaje = $_SESSION['mensaje_beneficiarios'];
    unset($_SESSION['mensaje_beneficiarios']);
}

/* Preparar edición */
$indiceEditar = filter_var(
    $_GET['editar'] ?? null,
    FILTER_VALIDATE_INT
);

$editando = (
    $indiceEditar !== false &&
    $indiceEditar !== null &&
    isset($_SESSION['beneficiarios'][$indiceEditar])
);

$datosEditar = $editando
    ? $_SESSION['beneficiarios'][$indiceEditar]
    : [
        'nombre' => '',
        'identificacion' => '',
        'nivel' => ''
    ];

/* Buscar beneficiarios */
$busqueda = trim($_GET['buscar'] ?? '');

require __DIR__ . '/includes/encabezado.php';
?>

<div class="tarjeta tarjeta-ancha">

    <h2><?= $editando ? 'Editar beneficiario' : 'Registrar beneficiario' ?></h2>

    <?php if ($mensaje !== ''): ?>
        <p class="mensaje"><?= e($mensaje) ?></p>
    <?php endif; ?>

    <form method="POST">

        <input
            type="hidden"
            name="accion"
            value="<?= $editando ? 'editar' : 'registrar' ?>"
        >

        <?php if ($editando): ?>
            <input
                type="hidden"
                name="indice"
                value="<?= (int) $indiceEditar ?>"
            >
        <?php endif; ?>

        <label for="nombre">Nombre completo</label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            value="<?= e($datosEditar['nombre']) ?>"
            required
        >

        <label for="identificacion">Identificación</label>
        <input
            type="text"
            id="identificacion"
            name="identificacion"
            value="<?= e($datosEditar['identificacion']) ?>"
            required
        >

        <label for="nivel">Nivel educativo</label>
        <select id="nivel" name="nivel" required>
            <option value="">Seleccione un nivel</option>

            <?php for ($i = 1; $i <= 6; $i++): ?>
                <?php $opcion = $i . '° grado'; ?>
                <option
                    value="<?= e($opcion) ?>"
                    <?= $datosEditar['nivel'] === $opcion ? 'selected' : '' ?>
                >
                    <?= e($opcion) ?>
                </option>
            <?php endfor; ?>
        </select>

        <button type="submit">
            <?= $editando ? 'Guardar cambios' : 'Registrar beneficiario' ?>
        </button>

        <?php if ($editando): ?>
            <a href="beneficiarios.php">Cancelar edición</a>
        <?php endif; ?>

    </form>
</div>

<div class="tarjeta tarjeta-ancha">

    <h2>Consultar beneficiarios</h2>

    <form method="GET">
        <label for="buscar">Buscar por nombre o identificación</label>

        <input
            type="text"
            id="buscar"
            name="buscar"
            value="<?= e($busqueda) ?>"
            placeholder="Escriba el nombre o identificación"
        >

        <button type="submit">Buscar</button>
        <a href="beneficiarios.php">Limpiar búsqueda</a>
    </form>

    <table class="tabla--datos">

        <thead>
            <tr>
                <th>Nombre</th>
                <th>Identificación</th>
                <th>Nivel</th>
                <th>Vacaciones</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>

            <?php
            $encontrados = 0;

            foreach ($_SESSION['beneficiarios'] as $indice => $beneficiario):

                $coincide =
                    $busqueda === '' ||
                    stripos($beneficiario['nombre'], $busqueda) !== false ||
                    stripos($beneficiario['identificacion'], $busqueda) !== false;

                if (!$coincide) {
                    continue;
                }

                $encontrados++;
            ?>

                <tr>
                    <td><?= e($beneficiario['nombre']) ?></td>

                    <td><?= e($beneficiario['identificacion']) ?></td>

                    <td><?= e($beneficiario['nivel']) ?></td>

                    <td>
                        <?= $beneficiario['vacaciones'] ? 'Sí' : 'No' ?>
                    </td>

                    <td>
                        <?= $beneficiario['activo'] ? 'Activo' : 'Inactivo' ?>
                    </td>

                    <td>
                        <a href="beneficiarios.php?editar=<?= (int) $indice ?>">
                            Editar
                        </a>

                        <form method="POST">
                            <input type="hidden" name="accion" value="estado">
                            <input
                                type="hidden"
                                name="indice"
                                value="<?= (int) $indice ?>"
                            >
                            <button type="submit">
                                <?= $beneficiario['activo'] ? 'Desactivar' : 'Activar' ?>
                            </button>
                        </form>

                        <form method="POST">
                            <input type="hidden" name="accion" value="vacaciones">
                            <input
                                type="hidden"
                                name="indice"
                                value="<?= (int) $indice ?>"
                            >
                            <button type="submit">
                                Cambiar vacaciones
                            </button>
                        </form>
                    </td>
                </tr>

            <?php endforeach; ?>

            <?php if ($encontrados === 0): ?>
                <tr>
                    <td colspan="6">No se encontraron beneficiarios.</td>
                </tr>
            <?php endif; ?>

        </tbody>
    </table>

</div>

<?php require __DIR__ . '/includes/pie.php'; ?>
