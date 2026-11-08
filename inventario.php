<?php
/* Módulo de Inventario: alertas, registro de movimientos y existencias */
$titulo       = 'Inventario';
$paginaActiva = 'inventario';
$scripts      = ['assets/js/datos-ejemplo.js', 'assets/js/inventario.js'];

require __DIR__ . '/includes/encabezado.php';
?>

<!--Alertas de stock bajo y productos por vencer -->
<div class="tarjeta tarjeta-ancha">
    <h2>Alertas de inventario</h2>
    <ul id="lista-alertas"></ul>
</div>

<!-- Registro de entradas y salidas -->
<div class="tarjeta">
    <h2>Registrar movimiento</h2>

    <label>Producto</label>
    <br>
    <select id="producto"></select>
    <br>

    <label>Tipo de movimiento</label>
    <br>
    <select id="tipo">
        <option value="">Seleccione una opción</option>
        <option value="Entrada">Entrada</option>
        <option value="Salida">Salida</option>
    </select>
    <br>

    <label>Cantidad</label>
    <br>
    <input type="number" id="cantidad" min="0" step="0.01">
    <br>

    <label>Fecha de vencimiento </label>
    <br>
    <input type="date" id="vencimiento">
    <br>

    <button onclick="registrarMovimiento()">Registrar</button>

    <p id="mensaje" class="mensaje"></p>
</div>

<!--Existencias -->
<div class="tarjeta tarjeta-ancha">
    <h2>Existencias</h2>
    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad actual</th>
                    <th>Stock mínimo</th>
                    <th>Vencimiento</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody id="cuerpo-inventario"></tbody>
        </table>
    </div>
</div>

<!-- Ultimos movimientos -->
<div class="tarjeta tarjeta-ancha">
    <h2>Últimos movimientos</h2>
    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody id="cuerpo-movimientos"></tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/pie.php'; ?>
