<?php
/*
 * Funciones de ayuda usadas en todo el proyecto.
 */

// Hora de Costa Rica para fechas y reportes
date_default_timezone_set('America/Costa_Rica');

// Escapa texto antes de mostrarlo en HTML (evita ataques XSS)
function e($texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

// Rellena un texto respetando tildes y ñ (str_pad cuenta bytes, no letras)
function rellenar(string $texto, int $ancho, bool $derecha = false): string
{
    if (mb_strlen($texto) > $ancho) {
        $texto = mb_substr($texto, 0, $ancho - 1) . '…';
    }
    $espacios = str_repeat(' ', $ancho - mb_strlen($texto));
    return $derecha ? $espacios . $texto : $texto . $espacios;
}

// 75000 -> "75 000.00"
function numero(float $valor, int $decimales = 2): string
{
    return number_format($valor, $decimales, '.', ' ');
}

function colones(float $valor): string
{
    return '₡' . numero($valor);
}

// "2026-11-03" -> "03/11/2026"
function fechaCorta(string $fechaIso): string
{
    return date('d/m/Y', strtotime($fechaIso));
}

// Fecha de hoy +/- una cantidad de días, igual que fechaEnDias() en datos-ejemplo.js
function fechaEnDias(int $dias): string
{
    return date('Y-m-d', strtotime(($dias >= 0 ? '+' : '') . $dias . ' days'));
}

// Valida una fecha con formato AAAA-MM-DD
function fechaValida(string $fecha): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d && $d->format('Y-m-d') === $fecha;
}
