<?php
/*
 * Genera el reporte en .txt:
 *   1. guarda una copia en la carpeta reportes_generados/
 *   2. lo envía al navegador para descargarlo
 * Recibe los filtros por GET desde el formulario de reportes.php.
 */
require_once __DIR__ . '/includes/reportes.php';

$tipo    = $_GET['tipo']  ?? '';
$desde   = $_GET['desde'] ?? '';
$hasta   = $_GET['hasta'] ?? '';
$usuario = 'Encargado del comedor'; 

try {
    $reporte = generarReporte($tipo, $desde, $hasta);
} catch (InvalidArgumentException $error) {
    // Si los filtros vienen mal, se regresa al formulario con el mensaje
    header('Location: reportes.php?' . http_build_query([
        'tipo' => $tipo, 'desde' => $desde, 'hasta' => $hasta, 'error' => $error->getMessage(),
    ]));
    exit;
}

$contenido = reporteATexto($reporte, $usuario);

$carpeta = __DIR__ . '/reportes_generados';
if (!is_dir($carpeta)) {
    mkdir($carpeta, 0775, true);
}
file_put_contents($carpeta . '/' . $reporte['archivo'], $contenido);

header('Content-Type: text/plain; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $reporte['archivo'] . '"');
header('Content-Length: ' . strlen($contenido));
echo $contenido;
