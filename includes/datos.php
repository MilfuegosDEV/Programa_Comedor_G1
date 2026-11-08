<?php
/*
 * Datos de ejemplo en PHP (sin base de datos).
 */
require_once __DIR__ . '/funciones.php';

function obtenerCentro(): array
{
    return ['codigo' => '0101', 'nombre' => 'Escuela República de Chile', 'regional' => 'San José Central'];
}

// precio = precio de referencia por unidad (para estimar el costo del desperdicio)
function obtenerProductos(): array
{
    return [
        1 => ['nombre' => 'Arroz',     'unidad' => 'kg',     'minimo' => 20, 'precio' => 1100],
        2 => ['nombre' => 'Frijoles',  'unidad' => 'kg',     'minimo' => 15, 'precio' => 1000],
        3 => ['nombre' => 'Aceite',    'unidad' => 'L',      'minimo' => 5, 'precio' => 1900],
        4 => ['nombre' => 'Leche',     'unidad' => 'L',      'minimo' => 20, 'precio' => 780],
        5 => ['nombre' => 'Huevos',    'unidad' => 'unidad', 'minimo' => 60, 'precio' => 110],
        6 => ['nombre' => 'Pollo',     'unidad' => 'kg',     'minimo' => 15, 'precio' => 2800],
        7 => ['nombre' => 'Zanahoria', 'unidad' => 'kg',     'minimo' => 8, 'precio' => 650],
    ];
}

// id_producto => cantidad actual y fecha de vencimiento
function obtenerInventario(): array
{
    return [
        1 => ['cantidad' => 80,  'vence' => fechaEnDias(200)],
        2 => ['cantidad' => 12,  'vence' => fechaEnDias(150)],
        3 => ['cantidad' => 10,  'vence' => fechaEnDias(120)],
        4 => ['cantidad' => 30,  'vence' => fechaEnDias(3)],
        5 => ['cantidad' => 120, 'vence' => fechaEnDias(20)],
        6 => ['cantidad' => 25,  'vence' => fechaEnDias(5)],
        7 => ['cantidad' => 6,   'vence' => fechaEnDias(-1)],
    ];
}

function obtenerProveedores(): array
{
    return [
        1 => ['nombre' => 'Consejo Nacional de Producción (CNP)', 'telefono' => '22567890'],
        2 => ['nombre' => 'Distribuidora Los Andes',              'telefono' => '88123456'],
    ];
}

// Detalle: [id_producto, cantidad, precio unitario]
function obtenerCompras(): array
{
    return [
        ['id' => 1, 'proveedor' => 1, 'fecha' => fechaEnDias(-5), 'detalle' => [[1, 50, 1100], [2, 20, 1000]]],
    ];
}

// Receta: id_producto => cantidad por UNA ración
function obtenerPlatillos(): array
{
    return [
        1 => ['nombre' => 'Arroz con pollo',       'ingredientes' => [1 => 0.080, 6 => 0.070, 3 => 0.005, 7 => 0.020]],
        2 => ['nombre' => 'Casado',                'ingredientes' => [1 => 0.080, 2 => 0.060, 5 => 1.0, 3 => 0.005]],
        3 => ['nombre' => 'Gallo pinto con huevo', 'ingredientes' => [1 => 0.050, 2 => 0.040, 5 => 1.0, 3 => 0.003]],
        4 => ['nombre' => 'Vaso de leche',         'ingredientes' => [4 => 0.250]],
    ];
}

function obtenerRaciones(): array
{
    return [
        ['fecha' => fechaEnDias(-4), 'tiempo' => 'Desayuno', 'platillos' => [3, 4], 'raciones' => 120],
        ['fecha' => fechaEnDias(-4), 'tiempo' => 'Almuerzo', 'platillos' => [1],    'raciones' => 150],
        ['fecha' => fechaEnDias(-3), 'tiempo' => 'Almuerzo', 'platillos' => [2],    'raciones' => 145],
        ['fecha' => fechaEnDias(-2), 'tiempo' => 'Desayuno', 'platillos' => [3, 4], 'raciones' => 118],
        ['fecha' => fechaEnDias(-2), 'tiempo' => 'Almuerzo', 'platillos' => [1],    'raciones' => 152],
        ['fecha' => fechaEnDias(-1), 'tiempo' => 'Almuerzo', 'platillos' => [2],    'raciones' => 140],
    ];
}

function obtenerDesperdicios(): array
{
    return [
        ['producto' => 4, 'fecha' => fechaEnDias(-1), 'cantidad' => 3.0, 'motivo' => 'Vencimiento', 'detalle' => 'Leche vencida en refrigeradora'],
        ['producto' => 7, 'fecha' => fechaEnDias(-1), 'cantidad' => 2.0, 'motivo' => 'Deterioro',   'detalle' => 'Zanahoria en mal estado'],
        ['producto' => 1, 'fecha' => fechaEnDias(-3), 'cantidad' => 1.5, 'motivo' => 'Sobrante',    'detalle' => 'Arroz preparado no consumido'],
    ];
}

function obtenerBeneficiarios(): array
{
    return [
        ['nombre' => 'Sofía Hernández Calvo', 'identificacion' => '1-2345-0001', 'nivel' => '1° grado', 'vacaciones' => true,  'activo' => true],
        ['nombre' => 'Mateo Rodríguez Pérez', 'identificacion' => '1-2345-0002', 'nivel' => '3° grado', 'vacaciones' => false, 'activo' => true],
        ['nombre' => 'Valeria Chaves Brenes', 'identificacion' => '1-2345-0003', 'nivel' => '5° grado', 'vacaciones' => true,  'activo' => true],
        ['nombre' => 'Daniel Alfaro Soto',    'identificacion' => '1-2345-0004', 'nivel' => '6° grado', 'vacaciones' => false, 'activo' => true],
        ['nombre' => 'Emilia Porras Campos',  'identificacion' => '1-2345-0009', 'nivel' => '2° grado', 'vacaciones' => true,  'activo' => true],
        ['nombre' => 'Gabriel Zúñiga Monge',  'identificacion' => '1-2345-0010', 'nivel' => '4° grado', 'vacaciones' => false, 'activo' => true],
        ['nombre' => 'Samuel Rojas Céspedes', 'identificacion' => '1-2345-0012', 'nivel' => '6° grado', 'vacaciones' => false, 'activo' => false],
    ];
}
