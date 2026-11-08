<?php
/*
 * Lógica del módulo de reportes.
 * Cada reporte se arma como una lista de SECCIONES (título, columnas, filas
 * y resumen). Con esa misma estructura se genera:
 *   - la vista en pantalla con tablas DataTables  -> reportes.php
 *   - el archivo de texto plano (.txt)             -> generar_reporte.php
 */
require_once __DIR__ . '/datos.php';

const ANCHO_REPORTE = 90;
const DIAS_ALERTA_VENCIMIENTO = 7; // igual que en assets/js/inventario.js

const TIPOS_REPORTE = [
    'consumo'       => 'Reporte de consumo',
    'existencias'   => 'Reporte de existencias',
    'compras'       => 'Reporte de compras',
    'desperdicio'   => 'Reporte de desperdicio',
    'beneficiarios' => 'Reporte de beneficiarios',
];

/* ---------------------------------------------------------------------
   Estructura de una sección
   --------------------------------------------------------------------- */

/*
 * $columnas = [['Título', ancho_en_txt, 'izq' | 'der'], ...]
 * $filas    = [[celda, celda, ...], ...]   (celda = texto o celdaNum())
 * $resumen  = ['Etiqueta' => 'valor', ...]
 */
function seccion(string $titulo, array $columnas, array $filas, array $resumen = [], ?string $nota = null): array
{
    return compact('titulo', 'columnas', 'filas', 'resumen', 'nota');
}

// Celda numérica: se muestra con formato, pero DataTables la ordena por su valor real
function celdaNum(string $texto, float $valor): array
{
    return ['texto' => $texto, 'orden' => $valor];
}

function celdaFecha(string $fechaIso): array
{
    return celdaNum(fechaCorta($fechaIso), strtotime($fechaIso));
}

function textoCelda($celda): string
{
    return is_array($celda) ? $celda['texto'] : (string) $celda;
}

function enRango(string $fecha, string $desde, string $hasta): bool
{
    return $fecha >= $desde && $fecha <= $hasta;
}

/* ---------------------------------------------------------------------
   Reportes
   --------------------------------------------------------------------- */

function reporteConsumo(string $desde, string $hasta): array
{
    $platillos = obtenerPlatillos();
    $productos = obtenerProductos();

    $filas = [];
    $consumo = [];
    $total = 0;
    $dias = [];
    foreach (obtenerRaciones() as $r) {
        if (!enRango($r['fecha'], $desde, $hasta)) {
            continue;
        }
        $nombres = array_map(fn ($id) => $platillos[$id]['nombre'], $r['platillos']);
        $filas[] = [celdaFecha($r['fecha']), $r['tiempo'], implode(', ', $nombres), celdaNum(numero($r['raciones'], 0), $r['raciones'])];
        $total += $r['raciones'];
        $dias[$r['fecha']] = true;

        // Productos usados = raciones x cantidad de la receta
        foreach ($r['platillos'] as $idPlatillo) {
            foreach ($platillos[$idPlatillo]['ingredientes'] as $idProducto => $porRacion) {
                $consumo[$idProducto] = ($consumo[$idProducto] ?? 0) + $r['raciones'] * $porRacion;
            }
        }
    }

    $filasProductos = [];
    foreach ($consumo as $idProducto => $cantidad) {
        $p = $productos[$idProducto];
        $filasProductos[] = [$p['nombre'], $p['unidad'], celdaNum(numero($cantidad), $cantidad)];
    }
    usort($filasProductos, fn ($a, $b) => strcmp($a[0], $b[0]));

    return [
        seccion('Raciones servidas',
            [['Fecha', 10, 'izq'], ['Tiempo', 10, 'izq'], ['Platillos', 52, 'izq'], ['Raciones', 10, 'der']],
            $filas,
            [
                'Total de raciones servidas'  => numero($total, 0),
                'Días con servicio'           => (string) count($dias),
                'Promedio diario de raciones' => count($dias) ? numero($total / count($dias), 1) : '0',
            ]
        ),
        seccion('Consumo de productos',
            [['Producto', 30, 'izq'], ['Unidad', 10, 'izq'], ['Cantidad consumida', 20, 'der']],
            $filasProductos,
            [],
            'Cantidad calculada con la receta de cada platillo multiplicada por las raciones servidas.'
        ),
    ];
}

function reporteExistencias(): array
{
    $productos = obtenerProductos();
    $hoy = date('Y-m-d');

    $filas = [];
    $alertas = 0;
    foreach (obtenerInventario() as $id => $item) {
        $p = $productos[$id];
        $estado = [];
        if ($item['cantidad'] <= $p['minimo']) {
            $estado[] = 'Stock bajo';
        }
        $dias = (int) round((strtotime($item['vence']) - strtotime($hoy)) / 86400);
        if ($dias < 0) {
            $estado[] = 'Vencido';
        } elseif ($dias <= DIAS_ALERTA_VENCIMIENTO) {
            $estado[] = "Por vencer ($dias días)";
        }
        $alertas += $estado ? 1 : 0;

        $filas[] = [$p['nombre'], celdaNum(numero($item['cantidad']) . ' ' . $p['unidad'], $item['cantidad']),
                    celdaNum(numero($p['minimo']) . ' ' . $p['unidad'], $p['minimo']), celdaFecha($item['vence']),
                    $estado ? '** ' . implode(' / ', $estado) : 'Normal'];
    }

    return [seccion('Existencias al ' . fechaCorta($hoy),
        [['Producto', 14, 'izq'], ['Cantidad actual', 16, 'der'], ['Stock mínimo', 14, 'der'],
         ['Vencimiento', 12, 'izq'], ['Estado', 29, 'izq']],
        $filas,
        ['Productos con alerta' => (string) $alertas, 'Productos en inventario' => (string) count($filas)],
        'Se marca "Por vencer" cuando faltan ' . DIAS_ALERTA_VENCIMIENTO . ' días o menos.'
    )];
}

function reporteCompras(string $desde, string $hasta): array
{
    $productos = obtenerProductos();
    $proveedores = obtenerProveedores();

    $filas = [];
    $detalle = [];
    $totalGeneral = 0;
    $porProveedor = [];
    foreach (obtenerCompras() as $c) {
        if (!enRango($c['fecha'], $desde, $hasta)) {
            continue;
        }
        $total = 0;
        foreach ($c['detalle'] as [$idProducto, $cantidad, $precio]) {
            $p = $productos[$idProducto];
            $total += $cantidad * $precio;
            $detalle[] = [celdaNum('#' . $c['id'], $c['id']), $p['nombre'],
                          celdaNum(numero($cantidad) . ' ' . $p['unidad'], $cantidad),
                          celdaNum(colones($precio), $precio), celdaNum(colones($cantidad * $precio), $cantidad * $precio)];
        }
        $nombreProveedor = $proveedores[$c['proveedor']]['nombre'];
        $totalGeneral += $total;
        $porProveedor[$nombreProveedor] = ($porProveedor[$nombreProveedor] ?? 0) + $total;
        $filas[] = [celdaNum('#' . $c['id'], $c['id']), celdaFecha($c['fecha']), $nombreProveedor,
                    celdaNum((string) count($c['detalle']), count($c['detalle'])), celdaNum(colones($total), $total)];
    }

    $filasProveedor = [];
    foreach ($porProveedor as $nombre => $monto) {
        $filasProveedor[] = [$nombre, celdaNum(colones($monto), $monto),
                             celdaNum(numero($monto / $totalGeneral * 100, 1) . ' %', $monto / $totalGeneral * 100)];
    }

    return [
        seccion('Compras registradas',
            [['Compra', 7, 'izq'], ['Fecha', 10, 'izq'], ['Proveedor', 42, 'izq'], ['Productos', 10, 'der'], ['Total', 15, 'der']],
            $filas,
            ['Cantidad de compras' => (string) count($filas), 'Total gastado' => colones($totalGeneral)]
        ),
        seccion('Gasto por proveedor',
            [['Proveedor', 42, 'izq'], ['Total', 15, 'der'], ['Porcentaje', 12, 'der']],
            $filasProveedor
        ),
        seccion('Detalle de productos comprados',
            [['Compra', 7, 'izq'], ['Producto', 24, 'izq'], ['Cantidad', 16, 'der'], ['Precio unit.', 14, 'der'], ['Subtotal', 15, 'der']],
            $detalle
        ),
    ];
}

function reporteDesperdicio(string $desde, string $hasta): array
{
    $productos = obtenerProductos();

    $filas = [];
    $porMotivo = [];
    $costoTotal = 0;
    foreach (obtenerDesperdicios() as $d) {
        if (!enRango($d['fecha'], $desde, $hasta)) {
            continue;
        }
        $p = $productos[$d['producto']];
        $costo = $d['cantidad'] * $p['precio'];
        $costoTotal += $costo;
        $porMotivo[$d['motivo']] = ($porMotivo[$d['motivo']] ?? 0) + $costo;
        $filas[] = [celdaFecha($d['fecha']), $p['nombre'], celdaNum(numero($d['cantidad']) . ' ' . $p['unidad'], $d['cantidad']),
                    $d['motivo'], celdaNum(colones($costo), $costo), $d['detalle']];
    }

    $resumen = [];
    foreach ($porMotivo as $motivo => $costo) {
        $porcentaje = $costoTotal > 0 ? $costo / $costoTotal * 100 : 0;
        $resumen[] = [$motivo, celdaNum(colones($costo), $costo), celdaNum(numero($porcentaje, 1) . ' %', $porcentaje)];
    }

    return [
        seccion('Alimentos desperdiciados',
            [['Fecha', 10, 'izq'], ['Producto', 12, 'izq'], ['Cantidad', 10, 'der'], ['Motivo', 11, 'izq'],
             ['Costo est.', 11, 'der'], ['Detalle', 31, 'izq']],
            $filas,
            ['Costo total estimado' => colones($costoTotal)],
            'El costo se estima con el precio de referencia de cada producto.'
        ),
        seccion('Costo por motivo',
            [['Motivo', 15, 'izq'], ['Costo', 15, 'der'], ['Porcentaje', 12, 'der']],
            $resumen
        ),
    ];
}

function reporteBeneficiarios(): array
{
    $lista = obtenerBeneficiarios();
    usort($lista, fn ($a, $b) => strcmp($a['nombre'], $b['nombre']));

    $filas = [];
    $activos = 0;
    $vacaciones = 0;
    foreach ($lista as $b) {
        $activos += $b['activo'] ? 1 : 0;
        $vacaciones += ($b['activo'] && $b['vacaciones']) ? 1 : 0;
        $filas[] = [$b['identificacion'], $b['nombre'], $b['nivel'], $b['vacaciones'] ? 'Sí' : 'No',
                    $b['activo'] ? 'Activo' : 'Dado de baja'];
    }

    return [seccion('Beneficiarios registrados',
        [['Identificación', 14, 'izq'], ['Nombre', 30, 'izq'], ['Nivel', 10, 'izq'], ['Vacaciones', 10, 'izq'], ['Estado', 12, 'izq']],
        $filas,
        [
            'Beneficiarios activos'      => (string) $activos,
            'Asisten durante vacaciones' => (string) $vacaciones,
            'Dados de baja'              => (string) (count($lista) - $activos),
        ]
    )];
}

/* ---------------------------------------------------------------------
   Punto de entrada
   --------------------------------------------------------------------- */

/*
 * Devuelve ['titulo', 'periodo', 'secciones', 'archivo'].
 * Lanza InvalidArgumentException si los filtros no son válidos.
 */
function generarReporte(string $tipo, string $desde, string $hasta): array
{
    if (!isset(TIPOS_REPORTE[$tipo])) {
        throw new InvalidArgumentException('Tipo de reporte no válido.');
    }
    $usaPeriodo = in_array($tipo, ['consumo', 'compras', 'desperdicio'], true);
    if ($usaPeriodo) {
        if (!fechaValida($desde) || !fechaValida($hasta)) {
            throw new InvalidArgumentException('Las fechas deben tener el formato AAAA-MM-DD.');
        }
        if ($desde > $hasta) {
            throw new InvalidArgumentException('La fecha "Desde" no puede ser mayor que la fecha "Hasta".');
        }
    }

    $periodo = $usaPeriodo ? 'Del ' . fechaCorta($desde) . ' al ' . fechaCorta($hasta) : null;

    switch ($tipo) {
        case 'consumo':
            $secciones = reporteConsumo($desde, $hasta);
            break;
        case 'existencias':
            $periodo = 'Existencias actuales al ' . date('d/m/Y');
            $secciones = reporteExistencias();
            break;
        case 'compras':
            $secciones = reporteCompras($desde, $hasta);
            break;
        case 'desperdicio':
            $secciones = reporteDesperdicio($desde, $hasta);
            break;
        default:
            $periodo = null;
            $secciones = reporteBeneficiarios();
    }

    return [
        'titulo'    => TIPOS_REPORTE[$tipo],
        'periodo'   => $periodo,
        'secciones' => $secciones,
        'archivo'   => 'reporte_' . $tipo . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.txt',
    ];
}

/* ---------------------------------------------------------------------
   Conversión a texto plano (.txt)
   --------------------------------------------------------------------- */

function lineaTxt(string $caracter = '-'): string
{
    return str_repeat($caracter, ANCHO_REPORTE);
}

function centrarTxt(string $texto): string
{
    return str_repeat(' ', max(intdiv(ANCHO_REPORTE - mb_strlen($texto), 2), 0)) . $texto;
}

function tablaTxt(array $columnas, array $filas): array
{
    $unir = function (array $valores) use ($columnas): string {
        $partes = [];
        foreach ($columnas as $i => [, $ancho, $alinear]) {
            $partes[] = rellenar(textoCelda($valores[$i]), $ancho, $alinear === 'der');
        }
        return rtrim(implode(' ', $partes));
    };

    $anchoTotal = array_sum(array_column($columnas, 1)) + count($columnas) - 1;
    $lineas = [$unir(array_column($columnas, 0)), str_repeat('-', $anchoTotal)];
    if (empty($filas)) {
        $lineas[] = '(Sin registros para el periodo seleccionado)';
    }
    foreach ($filas as $fila) {
        $lineas[] = $unir($fila);
    }
    return $lineas;
}

function reporteATexto(array $reporte, string $usuario): string
{
    $centro = obtenerCentro();
    $lineas = [
        lineaTxt('='),
        centrarTxt('PROGRAMA DE COMEDORES ESTUDIANTILES - PANEA'),
        centrarTxt(mb_strtoupper($reporte['titulo'])),
        lineaTxt('='),
        'Centro educativo  : ' . $centro['nombre'] . ' (código ' . $centro['codigo'] . ')',
        'Dirección regional: ' . $centro['regional'],
    ];
    if ($reporte['periodo'] !== null) {
        $lineas[] = 'Periodo           : ' . $reporte['periodo'];
    }
    $lineas[] = 'Generado por      : ' . $usuario;
    $lineas[] = 'Fecha de emisión  : ' . date('d/m/Y H:i');
    $lineas[] = lineaTxt('=');

    foreach ($reporte['secciones'] as $s) {
        $lineas[] = '';
        $lineas[] = mb_strtoupper($s['titulo']);
        $lineas[] = lineaTxt('-');
        $lineas = array_merge($lineas, tablaTxt($s['columnas'], $s['filas']));

        if ($s['resumen']) {
            $lineas[] = '';
            $anchoEtiqueta = max(array_map('mb_strlen', array_keys($s['resumen'])));
            foreach ($s['resumen'] as $etiqueta => $valor) {
                $lineas[] = rellenar($etiqueta, $anchoEtiqueta) . ' : ' . $valor;
            }
        }
        if ($s['nota']) {
            $lineas[] = 'Nota: ' . $s['nota'];
        }
    }

    $lineas[] = '';
    $lineas[] = lineaTxt('=');
    $lineas[] = centrarTxt('Fin del reporte');
    $lineas[] = lineaTxt('=');

    return implode(PHP_EOL, $lineas) . PHP_EOL;
}
