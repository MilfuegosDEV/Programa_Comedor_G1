
// Productos agregados a la compra que se está registrando
let detalleActual = [];

function crearCelda(texto){
    let celda = document.createElement("td");
    celda.textContent = texto;
    return celda;
}

function calcularTotal(detalle){
    let total = 0;
    detalle.forEach(function(linea){
        total += linea.cantidad * linea.precio_unitario;
    });
    return Number(total.toFixed(2));
}

// Proveedores 
function mostrarProveedores(){
    let cuerpo = document.getElementById("cuerpo-proveedores");
    cuerpo.innerHTML = "";

    proveedores.forEach(function(proveedor){
        let fila = document.createElement("tr");
        fila.appendChild(crearCelda(proveedor.nombre));
        fila.appendChild(crearCelda(proveedor.telefono));
        cuerpo.appendChild(fila);
    });
}

function registrarProveedor(){
    let nombre = document.getElementById("nombre-proveedor").value.trim();
    let telefono = document.getElementById("telefono-proveedor").value.trim();

    if(nombre == "" || telefono == ""){
        mostrarMensajeEn("mensaje-proveedor", "Debe llenar el nombre y el teléfono del proveedor.", false);
        return;
    }

    if(!/^[0-9]{8}$/.test(telefono)){
        mostrarMensajeEn("mensaje del proveedor", "El teléfono debe tener exactamente 8 dígitos.", false);
        return;
    }

    let repetido = proveedores.some(function(p){
        return p.nombre.toLowerCase() == nombre.toLowerCase();
    });
    if(repetido){
        mostrarMensajeEn("mensaje-proveedor", "Ya existe un proveedor con ese nombre.", false);
        return;
    }

    proveedores.push({
        id_proveedor: proveedores.length + 1,
        nombre: nombre,
        telefono: telefono
    });

    document.getElementById("nombre-proveedor").value = "";
    document.getElementById("telefono-proveedor").value = "";

    mostrarProveedores();
    llenarSelect("proveedor", proveedores, "id_proveedor", "nombre");
    mostrarMensajeEn("mensaje-proveedor", "Proveedor registrado correctamente.", true);
}

// ---------- Detalle de la compra ----------
function mostrarDetalle(){
    let cuerpo = document.getElementById("cuerpo-detalle");
    cuerpo.innerHTML = "";

    detalleActual.forEach(function(linea, posicion){
        let producto = buscarProducto(linea.id_producto);
        let fila = document.createElement("tr");

        fila.appendChild(crearCelda(producto.nombre));
        fila.appendChild(crearCelda(linea.cantidad + " " + producto.unidad_medida));
        fila.appendChild(crearCelda(formatoColones(linea.precio_unitario)));
        fila.appendChild(crearCelda(formatoColones(linea.cantidad * linea.precio_unitario)));

        let celdaAccion = document.createElement("td");
        let boton = document.createElement("button");
        boton.textContent = "Quitar";
        boton.onclick = function(){
            quitarDetalle(posicion);
        };
        celdaAccion.appendChild(boton);
        fila.appendChild(celdaAccion);

        cuerpo.appendChild(fila);
    });

    document.getElementById("total-compra").textContent = formatoColones(calcularTotal(detalleActual));
}

function agregarDetalle(){
    let idProducto = document.getElementById("producto-compra").value;
    let cantidadTexto = document.getElementById("cantidad-compra").value;
    let precioTexto = document.getElementById("precio-compra").value;

    if(idProducto == "" || cantidadTexto.trim() == "" || precioTexto.trim() == ""){
        mostrarMensajeEn("mensaje-detalle", "Debe seleccionar el producto e indicar la cantidad y el costo unitario.", false);
        return;
    }

    let cantidad = Number(cantidadTexto);
    let precio = Number(precioTexto);

    if(isNaN(cantidad) || cantidad <= 0 || isNaN(precio) || precio <= 0){
        mostrarMensajeEn("mensaje-detalle", "La cantidad y el costo deben ser números mayores que cero.", false);
        return;
    }

    detalleActual.push({
        id_producto: Number(idProducto),
        cantidad: cantidad,
        precio_unitario: precio
    });

    document.getElementById("producto-compra").value = "";
    document.getElementById("cantidad-compra").value = "";
    document.getElementById("precio-compra").value = "";

    mostrarDetalle();
    mostrarMensajeEn("mensaje-detalle", "Producto agregado a la compra.", true);
}

function quitarDetalle(posicion){
    detalleActual.splice(posicion, 1);
    mostrarDetalle();
}

// Compras 
function mostrarCompras(){
    let cuerpo = document.getElementById("cuerpo-compras");
    cuerpo.innerHTML = "";

    compras.slice().reverse().forEach(function(compra){
        let proveedor = buscarProveedor(compra.id_proveedor);
        let fila = document.createElement("tr");

        fila.appendChild(crearCelda(compra.fecha));
        fila.appendChild(crearCelda(proveedor.nombre));
        fila.appendChild(crearCelda(compra.detalle.length));
        fila.appendChild(crearCelda(formatoColones(compra.total)));

        cuerpo.appendChild(fila);
    });
}

function guardarCompra(){
    let idProveedor = document.getElementById("proveedor").value;
    let fecha = document.getElementById("fecha-compra").value;

    if(idProveedor == "" || fecha == ""){
        mostrarMensajeEn("mensaje-compra", "Debe seleccionar el proveedor y la fecha de la compra.", false);
        return;
    }

    if(fecha > fechaEnDias(0)){
        mostrarMensajeEn("mensaje-compra", "La fecha de la compra no puede ser futura.", false);
        return;
    }

    if(detalleActual.length == 0){
        mostrarMensajeEn("mensaje-compra", "Debe agregar al menos un producto a la compra.", false);
        return;
    }

    compras.push({
        id_compra: compras.length + 1,
        id_proveedor: Number(idProveedor),
        fecha: fecha,
        total: calcularTotal(detalleActual),
        detalle: detalleActual
    });

    detalleActual = [];
    document.getElementById("proveedor").value = "";
    document.getElementById("fecha-compra").value = "";

    mostrarDetalle();
    mostrarCompras();
    mostrarMensajeEn("mensaje-compra", "Compra registrada correctamente.", true);
}

// Inicio de la página
llenarSelect("proveedor", proveedores, "id_proveedor", "nombre");
llenarSelect("producto-compra", productos, "id_producto", "nombre");
mostrarProveedores();
mostrarDetalle();
mostrarCompras();