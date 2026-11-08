<?php
/*
 * ============================================================
 *  PLANTILLA BASE - copiar este archivo para crear un módulo
 * ============================================================
 */

$titulo       = 'Título del módulo';  // texto de la pestaña del navegador
$paginaActiva = '';                   // beneficiarios, menus, inventario, compras, raciones o reportes
$usarTablas   = true;                 // true = jQuery + DataTables (búsqueda, orden y paginación)
$scripts      = [];                   // ej. ['assets/js/datos-ejemplo.js', 'assets/js/beneficiarios.js']

require __DIR__ . '/includes/encabezado.php';
?>

<!-- ===== Tarjeta con formulario ===== -->
<div class="tarjeta">
    <h2>Registrar algo</h2>

    <label for="campo-texto">Campo de texto</label>
    <br>
    <input type="text" id="campo-texto">
    <br>

    <label for="campo-lista">Lista de opciones</label>
    <br>
    <select id="campo-lista">
        <option value="">Seleccione una opción</option>
        <option value="1">Opción 1</option>
    </select>
    <br>

    <label for="campo-fecha">Fecha</label>
    <br>
    <input type="date" id="campo-fecha">
    <br>

    <button>Guardar</button>

    <!-- class="mensaje" = rojo (error) / class="mensaje exito" = verde -->
    <p id="mensaje-ejemplo" class="mensaje exito">Mensaje de ejemplo.</p>
</div>

<!-- ===== Tarjeta ancha con tabla =====
     La clase "tabla--datos" la convierte en DataTable (requiere $usarTablas = true).
     Quítela si quiere una tabla normal. -->
<div class="tarjeta tarjeta-ancha">
    <h2>Lista de registros</h2>
    <div class="tabla-contenedor">
        <table class="tabla--datos">
            <thead>
                <tr>
                    <th>Columna 1</th>
                    <th>Columna 2</th>
                    <th class="num">Monto</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Valor</td>
                    <td>Valor</td>
                    <!-- data-order: valor real para que DataTables ordene bien los montos -->
                    <td class="num" data-order="75000">₡75 000.00</td>
                    <td class="estado-ok">Normal</td>
                </tr>
                <tr>
                    <td>Valor</td>
                    <td>Valor</td>
                    <td class="num" data-order="9500">₡9 500.00</td>
                    <td class="estado-alerta">Stock bajo</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/pie.php'; ?>
