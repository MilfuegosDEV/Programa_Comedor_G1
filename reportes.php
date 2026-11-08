<?php
/*
 * Módulo de Reportes: el usuario elige el tipo y el periodo, ve el reporte
 * en tablas (jQuery DataTables) y lo descarga en .txt.
 */
require_once __DIR__ . '/includes/reportes.php';

$titulo       = 'Reportes';
$paginaActiva = 'reportes';
$usarTablas   = true;
$usuario      = 'Encargado del comedor'; // luego: $_SESSION['nombre']

// Filtros (por defecto: últimos 30 días)
$tipo  = $_GET['tipo']  ?? 'consumo';
$desde = $_GET['desde'] ?? fechaEnDias(-30);
$hasta = $_GET['hasta'] ?? date('Y-m-d');
$error = $_GET['error'] ?? '';

$reporte = null;
if (($_GET['accion'] ?? '') === 'ver') {
    try {
        $reporte = generarReporte($tipo, $desde, $hasta);
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    }
}

$parametros = http_build_query(['tipo' => $tipo, 'desde' => $desde, 'hasta' => $hasta]);
$guardados  = glob(__DIR__ . '/reportes_generados/*.txt') ?: [];

require __DIR__ . '/includes/encabezado.php';
?>

<!-- Filtros -->
<div class="tarjeta tarjeta-ancha">
    <h2>Generar reporte</h2>

    <?php if ($error !== ''): ?>
        <p class="mensaje" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <form id="form-reporte" class="filtros" method="get" action="reportes.php">
        <div>
            <label for="tipo">Tipo de reporte</label>
            <select id="tipo" name="tipo" required>
                <?php foreach (TIPOS_REPORTE as $clave => $nombre): ?>
                    <option value="<?= e($clave) ?>"<?= $clave === $tipo ? ' selected' : '' ?>><?= e($nombre) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="desde">Desde</label>
            <input type="date" id="desde" name="desde" value="<?= e($desde) ?>" required>
        </div>

        <div>
            <label for="hasta">Hasta</label>
            <input type="date" id="hasta" name="hasta" value="<?= e($hasta) ?>" required>
        </div>

        <div class="filtros__botones">
            <button type="submit" name="accion" value="ver" class="boton-secundario">Ver reporte</button>
            <button type="submit" formaction="generar_reporte.php">Descargar .txt</button>
        </div>
    </form>
    <p class="ayuda">Existencias muestra el inventario actual y Beneficiarios no depende de las fechas.</p>
</div>

<!-- Resultado -->
<?php if ($reporte !== null): ?>
    <div class="tarjeta tarjeta-ancha">
        <div class="tarjeta__encabezado">
            <div>
                <h2><?= e($reporte['titulo']) ?></h2>
                <?php if ($reporte['periodo']): ?>
                    <p class="ayuda"><?= e($reporte['periodo']) ?></p>
                <?php endif; ?>
            </div>
            <div>
                <button type="button" id="btn-ver-texto" class="boton-secundario"
                        aria-expanded="false" aria-controls="reporte-texto">Ver como texto (.txt)</button>
                <a class="boton" href="generar_reporte.php?<?= e($parametros) ?>">Descargar .txt</a>
            </div>
        </div>

        <pre id="reporte-texto" class="reporte-texto" hidden><?= e(reporteATexto($reporte, $usuario)) ?></pre>

        <?php foreach ($reporte['secciones'] as $s): ?>
            <h3><?= e($s['titulo']) ?></h3>

            <?php if ($s['resumen']): ?>
                <dl class="resumen">
                    <?php foreach ($s['resumen'] as $etiqueta => $valor): ?>
                        <div>
                            <dt><?= e($etiqueta) ?></dt>
                            <dd><?= e($valor) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            <?php endif; ?>

            <div class="tabla-contenedor">
                <table class="tabla--datos" data-sin-orden="true">
                    <thead>
                        <tr>
                            <?php foreach ($s['columnas'] as [$columna, , $alinear]): ?>
                                <th<?= $alinear === 'der' ? ' class="num"' : '' ?>><?= e($columna) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($s['filas'] as $fila): ?>
                            <tr>
                                <?php foreach ($fila as $j => $celda):
                                    $texto  = textoCelda($celda);
                                    $clases = [];
                                    if ($s['columnas'][$j][2] === 'der') {
                                        $clases[] = 'num';
                                    }
                                    if (str_starts_with($texto, '**')) { // marca de alerta del .txt
                                        $clases[] = 'estado-alerta';
                                        $texto = trim($texto, '* ');
                                    } elseif ($texto === 'Normal') {
                                        $clases[] = 'estado-ok';
                                    }
                                ?>
                                    <td<?= $clases ? ' class="' . implode(' ', $clases) . '"' : '' ?><?= is_array($celda) ? ' data-order="' . e($celda['orden']) . '"' : '' ?>><?= e($texto) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($s['nota']): ?>
                <p class="ayuda"><?= e($s['nota']) ?></p>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Reportes guardados en el servidor -->
<div class="tarjeta tarjeta-ancha">
    <h2>Reportes generados</h2>
    <div class="tabla-contenedor">
        <table class="tabla--datos" data-orden-columna="1" data-orden="desc" data-por-pagina="5">
            <thead>
                <tr>
                    <th>Archivo</th>
                    <th>Fecha</th>
                    <th class="num">Tamaño</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($guardados as $ruta): ?>
                    <tr>
                        <td><a href="reportes_generados/<?= e(rawurlencode(basename($ruta))) ?>" download><?= e(basename($ruta)) ?></a></td>
                        <td data-order="<?= filemtime($ruta) ?>"><?= date('d/m/Y H:i', filemtime($ruta)) ?></td>
                        <td class="num" data-order="<?= filesize($ruta) ?>"><?= numero(filesize($ruta) / 1024, 1) ?> KB</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/pie.php'; ?>
