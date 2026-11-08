# Programa_Comedor_G1

Sistema de gestión de comedores estudiantiles (PANEA) · SC-502 Ambiente Web Cliente/Servidor · Universidad Fidélitas · Grupo 1.

## Cómo correrlo
1. Copiar la carpeta del proyecto en `C:\xampp\htdocs\`.
2. En el panel de XAMPP encender **Apache** (por ahora no se usa MySQL).
3. Abrir `http://localhost/Programa_Comedor_G1/`.

## Estructura de carpetas
```
Programa_Comedor_G1/
├── index.php               Redirige a login.php
├── login.php               Inicio de sesión (sin menú)
├── beneficiarios.php       Módulo de beneficiarios (en desarrollo)
├── menus.php               Módulo de menús (en desarrollo)
├── inventario.php          Módulo de inventario
├── compras.php             Módulo de compras y proveedores
├── raciones.php            Módulo de raciones servidas (en desarrollo)
├── reportes.php            Módulo de reportes (vista con DataTables)
├── generar_reporte.php     Crea el .txt, lo guarda y lo descarga
├── plantilla.php           PLANTILLA para crear módulos nuevos
│
├── includes/               Piezas PHP que se reutilizan (no se abren directo)
│   ├── encabezado.php      <head>, título y menú compartido
│   ├── pie.php             Pie de página y carga de scripts
│   ├── funciones.php       Ayudas: e(), colones(), fechaCorta()...
│   ├── datos.php           Datos de ejemplo en PHP (luego: consultas MySQL)
│   └── reportes.php        Lógica de los reportes
│
├── assets/                 Archivos que descarga el navegador
│   ├── css/estilo.css      Estilos de todo el sitio (colores Fidélitas)
│   ├── js/                 login.js, datos-ejemplo.js, inventario.js, compras.js, app.js
│   ├── vendor/             jQuery 3.7.1 y DataTables 2.3.8 (locales, sin internet)
│   └── img/
│
├── reportes_generados/     Aquí se guardan los .txt (no se suben a GitHub)
└── database/schema.sql     Script de MySQL para cuando se conecte la base
```

## Crear un módulo nuevo
1. Copiar `plantilla.php` con el nombre del módulo (ej. `beneficiarios.php`).
2. Cambiar `$titulo` y `$paginaActiva` (debe coincidir con la clave del menú en `includes/encabezado.php`).
3. Escribir el contenido entre los dos `require`.
4. Si lleva JavaScript, guardarlo en `assets/js/` y agregarlo a `$scripts`.
5. Para tablas con búsqueda y orden: `$usarTablas = true;` y `class="tabla--datos"` en la `<table>`.

El menú está solo en `includes/encabezado.php`: si se agrega un módulo, se agrega ahí una vez.

## Reportes (.txt)
Consumo, existencias, compras, desperdicio y beneficiarios. Se ven en pantalla con DataTables y se descargan en `.txt` (columnas alineadas, compatible con el Bloc de notas). Cada descarga queda guardada en `reportes_generados/`.
