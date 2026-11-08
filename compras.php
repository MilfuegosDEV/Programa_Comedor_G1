<?php
/* Módulo de Compras: proveedores, registro de compras e historial */
$titulo       = 'Compras y proveedores';
$paginaActiva = 'compras';
$scripts      = ['assets/js/datos-ejemplo.js', 'assets/js/compras.js'];

require __DIR__ . '/includes/encabezado.php';
?>

<!-- Proveedores -->
<div class="tarjeta">
    <h2>Registrar proveedor</h2>

    <label>Nombre del proveedor</label>
    <br>
    <input type="text" id="nombre-proveedor">
    <br>

    <label>Teléfono máximo 8 dígitos</label>
    <br>
    <input type="text" id="telefono-proveedor" maxlength="8">
    <br>

    <button onclick="registrarProveedor()">Guardar proveedor</button>

    <p id="mensaje-proveedor" class="mensaje"></p>
</div>

<div class="tarjeta tarjeta-ancha">
    <h2>Proveedores registrados</h2>
    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                </tr>
            </thead>
            <tbody id="cuerpo-proveedores"></tbody>
        </table>
    </div>
</div>

<!-- Registro de compras -->
<div class="tarjeta tarjeta-ancha">
    <h2>Registrar compra</h2>

    <label>Proveedor</label>
    <br>
    <select id="proveedor"></select>
    <br>

    <label>Fecha de la compra</label>
    <br>
    <input type="date" id="fecha-compra">
    <br>

    <h2>Productos de la compra</h2>

    <label>Producto</label>
    <br>
    <select id="producto-compra"></select>
    <br>

    <label>Cantidad</label>
    <br>
    <input type="number" id="cantidad-compra" min="0" step="0.01">
    <br>

    <label>Costo unitario (₡)</label>
    <br>
    <input type="number" id="precio-compra" min="0" step="0.01">
    <br>

    <button onclick="agregarDetalle()">Agregar producto</button>

    <p id="mensaje-detalle" class="mensaje"></p>

    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Costo unitario</th>
                    <th>Subtotal</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody id="cuerpo-detalle"></tbody>
        </table>
    </div>

    <p><strong>Total de la compra: <span id="total-compra">₡0,00</span></strong></p>

    <button onclick="guardarCompra()">Guardar compra</button>

    <p id="mensaje-compra" class="mensaje"></p>
</div>

<!-- Historial -->
<div class="tarjeta tarjeta-ancha">
    <h2>Compras registradas</h2>
    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Proveedor</th>
                    <th>Productos</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody id="cuerpo-compras"></tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/pie.php'; ?>
