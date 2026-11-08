// Datos de ejemplo para los módulos de Inventario y Compras.

function formatoFecha(fecha){
    let anio = fecha.getFullYear();
    let mes = String(fecha.getMonth() + 1).padStart(2, "0");
    let dia = String(fecha.getDate()).padStart(2, "0");
    return anio + "-" + mes + "-" + dia;
}

// Devuelve la fecha de hoy +/- cierta cantidad de días: "2026-05-30"
function fechaEnDias(dias){
    let fecha = new Date();
    fecha.setDate(fecha.getDate() + dias);
    return formatoFecha(fecha);
}

function formatoColones(monto){
    return "₡" + monto.toLocaleString("es-CR", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function mostrarMensajeEn(idMensaje, texto, esExito){
    let mensaje = document.getElementById(idMensaje);
    mensaje.textContent = texto;
    mensaje.className = esExito ? "mensaje exito" : "mensaje";
}

function buscarProducto(idProducto){
    return productos.find(function(p){
        return p.id_producto == idProducto;
    });
}

function buscarProveedor(idProveedor){
    return proveedores.find(function(p){
        return p.id_proveedor == idProveedor;
    });
}

// Llena un <select> con una opción inicial y las opciones de una lista
function llenarSelect(idSelect, lista, campoId, campoTexto){
    let select = document.getElementById(idSelect);
    select.innerHTML = "";

    let inicial = document.createElement("option");
    inicial.value = "";
    inicial.textContent = "Seleccione una opción";
    select.appendChild(inicial);

    lista.forEach(function(elemento){
        let opcion = document.createElement("option");
        opcion.value = elemento[campoId];
        opcion.textContent = elemento[campoTexto];
        select.appendChild(opcion);
    });
}

// Datos de ejemplo 
let productos = [
    {id_producto: 1, nombre: "Arroz", unidad_medida: "kg", stock_minimo: 20},
    {id_producto: 2, nombre: "Frijoles", unidad_medida: "kg", stock_minimo: 15},
    {id_producto: 3, nombre: "Aceite", unidad_medida: "L", stock_minimo: 5},
    {id_producto: 4, nombre: "Leche", unidad_medida: "L", stock_minimo: 20},
    {id_producto: 5, nombre: "Huevos", unidad_medida: "unidad", stock_minimo: 60},
    {id_producto: 6, nombre: "Pollo", unidad_medida: "kg", stock_minimo: 15},
    {id_producto: 7, nombre: "Zanahoria", unidad_medida: "kg", stock_minimo: 8}
];

// fecha_vencimiento es un campo adicional (esto edward tiene que verlo porque no está en el diagrama relacional y
//  lo pedía el módulo de inventario entonces me la jugue a como pude)
let inventario = [
    {id_inventario: 1, id_producto: 1, cantidad_actual: 80, fecha_vencimiento: fechaEnDias(200)},
    {id_inventario: 2, id_producto: 2, cantidad_actual: 12, fecha_vencimiento: fechaEnDias(150)},
    {id_inventario: 3, id_producto: 3, cantidad_actual: 10, fecha_vencimiento: fechaEnDias(120)},
    {id_inventario: 4, id_producto: 4, cantidad_actual: 30, fecha_vencimiento: fechaEnDias(3)},
    {id_inventario: 5, id_producto: 5, cantidad_actual: 120, fecha_vencimiento: fechaEnDias(20)},
    {id_inventario: 6, id_producto: 6, cantidad_actual: 25, fecha_vencimiento: fechaEnDias(5)},
    {id_inventario: 7, id_producto: 7, cantidad_actual: 6, fecha_vencimiento: fechaEnDias(-1)}
];

let movimientos = [
    {id_movimiento: 1, id_producto: 1, tipo: "Entrada", cantidad: 50, fecha: fechaEnDias(-3)},
    {id_movimiento: 2, id_producto: 4, tipo: "Salida", cantidad: 10, fecha: fechaEnDias(-1)}
];

let proveedores = [
    {id_proveedor: 1, nombre: "Consejo Nacional de Producción (CNP)", telefono: "22567890"},
    {id_proveedor: 2, nombre: "Distribuidora Los Andes", telefono: "88123456"}
];

let compras = [
    {
        id_compra: 1,
        id_proveedor: 1,
        fecha: fechaEnDias(-5),
        total: 75000,
        detalle: [
            {id_producto: 1, cantidad: 50, precio_unitario: 1100},
            {id_producto: 2, cantidad: 20, precio_unitario: 1000}
        ]
    }
];