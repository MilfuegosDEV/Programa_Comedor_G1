/* =====================================================================
   Script común de las páginas que usan jQuery + DataTables
   (se carga cuando la página define $usarTablas = true).
   - Toda tabla con la clase .tabla--datos se convierte en DataTable
   - Validación de fechas en el formulario de reportes
   ===================================================================== */
'use strict';

// Textos de DataTables en español
var IDIOMA_TABLAS = {
  decimal: '.',
  thousands: ' ',
  emptyTable: 'No hay datos para mostrar',
  info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
  infoEmpty: 'Mostrando 0 registros',
  infoFiltered: '(filtrado de _MAX_ registros)',
  lengthMenu: 'Mostrar _MENU_ registros',
  loadingRecords: 'Cargando…',
  processing: 'Procesando…',
  search: 'Buscar:',
  zeroRecords: 'No se encontraron coincidencias',
  paginate: { first: 'Primero', last: 'Último', next: 'Siguiente', previous: 'Anterior' },
  aria: {
    orderable: 'Ordenar por esta columna',
    orderableReverse: 'Invertir el orden de esta columna'
  }
};

$(function () {
  /* ---------- DataTables ---------- */
  // Opciones por tabla con atributos data-*:
  //   data-por-pagina="10"    registros por página
  //   data-orden-columna="0"  columna para el orden inicial
  //   data-orden="asc|desc"   dirección del orden inicial
  //   data-sin-orden="true"   respeta el orden en que vienen las filas
  $('table.tabla--datos').each(function () {
    var $tabla = $(this);
    var porPagina = Number($tabla.data('por-pagina')) || 10;

    $tabla.DataTable({
      language: IDIOMA_TABLAS,
      pageLength: porPagina,
      lengthMenu: [5, 10, 25, 50, 100],
      order: $tabla.data('sin-orden')
        ? []
        : [[Number($tabla.data('orden-columna')) || 0, $tabla.data('orden') || 'asc']],
      columnDefs: [{ targets: 'sin-orden', orderable: false }],
      // Si caben todas las filas no se muestran paginación ni selector
      layout: {
        topStart: $tabla.find('tbody tr').length > porPagina ? 'pageLength' : null,
        bottomEnd: $tabla.find('tbody tr').length > porPagina ? 'paging' : null
      }
    });
  });

  /* ---------- Formulario de reportes ---------- */
  var $formReporte = $('#form-reporte');

  $formReporte.on('submit', function (evento) {
    var $desde = $('#desde');
    var $hasta = $('#hasta');
    if (!$desde.prop('disabled') && $desde.val() > $hasta.val()) {
      evento.preventDefault();
      $hasta[0].setCustomValidity('La fecha "Hasta" debe ser igual o posterior a "Desde".');
      $hasta[0].reportValidity();
    }
  });

  $('#hasta, #desde').on('input', function () {
    $('#hasta')[0].setCustomValidity('');
  });

  // Existencias y beneficiarios no usan fechas
  function revisarFechas() {
    var sinFechas = ['existencias', 'beneficiarios'].indexOf($('#tipo').val()) !== -1;
    $('#desde, #hasta').prop('disabled', sinFechas);
  }
  $('#tipo').on('change', revisarFechas);
  revisarFechas();

  /* ---------- Vista de texto del reporte ---------- */
  $('#btn-ver-texto').on('click', function () {
    var $texto = $('#reporte-texto');
    var visible = $texto.is(':visible');
    $texto.slideToggle(150);
    $(this)
      .attr('aria-expanded', !visible)
      .text(visible ? 'Ver como texto (.txt)' : 'Ocultar texto');
  });
});
