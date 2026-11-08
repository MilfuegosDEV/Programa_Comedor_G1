<?php
session_start();

// Arreglo de transacciones
if (!isset($_SESSION['transacciones'])) {
    $_SESSION['transacciones'] = [];
}

$transacciones = &$_SESSION['transacciones'];

// Funcion para registrar una transaccion
function registrarTransaccion($id, $descripcion, $monto) {
    global $transacciones;

    $transaccion = [
        "id" => $id,
        "descripcion" => $descripcion,
        "monto" => $monto
    ];

    array_push($transacciones, $transaccion);
}

// Funcion para generar el estado de cuenta
function generarEstadoDeCuenta() {
    global $transacciones;

    $totalContado = 0;

    $contenido = "========== ESTADO DE CUENTA ==========\n\n";

    $contenido .= "DETALLE DE TRANSACCIONES:\n";

    foreach ($transacciones as $transaccion) {
        $contenido .= "ID: " . $transaccion["id"] . "\n";
        $contenido .= "Descripcion: " . $transaccion["descripcion"] . "\n";
        $contenido .= "Monto: ₡" . number_format($transaccion["monto"], 2) . "\n";
        $contenido .= "----------------------------------\n";

        $totalContado += $transaccion["monto"];
    }

    // Calculos del estado de cuenta
    $interes = $totalContado * 0.026;
    $totalConInteres = $totalContado + $interes;
    $cashback = $totalContado * 0.001;
    $totalPagar = $totalConInteres - $cashback;

    // Agregar los totales al estado de cuenta
    $contenido .= "\n========== RESUMEN ==========\n";
    $contenido .= "Total de contado: ₡" . number_format($totalContado, 2) . "\n";
    $contenido .= "Interes (2.6%): ₡" . number_format($interes, 2) . "\n";
    $contenido .= "Total con interes: ₡" . number_format($totalConInteres, 2) . "\n";
    $contenido .= "Cashback (0.1%): ₡" . number_format($cashback, 2) . "\n";
    $contenido .= "Monto final a pagar: ₡" . number_format($totalPagar, 2) . "\n";

    // Generar archivo de texto
    file_put_contents("estado_cuenta.txt", $contenido);

    return $contenido;
}

// Registrar una nueva transaccion
if (isset($_POST['registrar'])) {

    $descripcion = trim($_POST['descripcion']);
    $monto = $_POST['monto'];

    if ($descripcion != "" && is_numeric($monto) && $monto > 0) {

        $id = count($transacciones) + 1;

        registrarTransaccion($id, $descripcion, $monto);

        $mensaje = "Transaccion registrada correctamente.";

    } else {
        $mensaje = "Ingrese una descripcion y un monto valido.";
    }
}

// Eliminar todas las transacciones
if (isset($_POST['limpiar'])) {
    $transacciones = [];
}

// Generar el estado de cuenta
$estadoCuenta = "";

if (isset($_POST['generar']) || isset($_POST['registrar'])) {
    $estadoCuenta = generarEstadoDeCuenta();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Estado de Cuenta</title>
</head>

<body>

    <h1>Sistema de Tarjeta de Credito</h1>

    <h2>Registrar Transaccion</h2>

    <?php if (isset($mensaje)) { ?>
        <p><?php echo htmlspecialchars($mensaje); ?></p>
    <?php } ?>

    <form method="POST">

        <label>Descripcion:</label><br>
        <input type="text" name="descripcion" required>
        <br><br>

        <label>Monto:</label><br>
        <input type="number" name="monto" step="0.01" min="0.01" required>
        <br><br>

        <button type="submit" name="registrar">
            Registrar Transaccion
        </button>

    </form>

    <hr>

    <h2>Transacciones Registradas</h2>

    <table border="1" cellpadding="8">

        <tr>
            <th>ID</th>
            <th>Descripcion</th>
            <th>Monto</th>
        </tr>

        <?php foreach ($transacciones as $transaccion) { ?>

            <tr>
                <td><?php echo $transaccion['id']; ?></td>

                <td>
                    <?php echo htmlspecialchars($transaccion['descripcion']); ?>
                </td>

                <td>
                    ₡<?php echo number_format($transaccion['monto'], 2); ?>
                </td>
            </tr>

        <?php } ?>

    </table>

    <br>

    <form method="POST">
        <button type="submit" name="generar">
            Generar Estado de Cuenta
        </button>

        <button type="submit" name="limpiar">
            Limpiar Transacciones
        </button>
    </form>

    <?php if ($estadoCuenta != "") { ?>

        <hr>

        <h2>Estado de Cuenta</h2>

        <pre><?php echo htmlspecialchars($estadoCuenta); ?></pre>

        <a href="estado_cuenta.txt" download>
            Descargar estado_cuenta.txt
        </a>

    <?php } ?>

</body>

</html>
