
const DIAS_ALERTA_VENCIMIENTO = 7;

function diasParaVencer(fechaVencimiento){
    let hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    let vence = new Date(fechaVencimiento + "T00:00:00");
    return Math.round((vence - hoy) / 86400000);
}

// Devuelve una lista con las alertas de un registro de inventario
function obtenerAlertas(item){
    let producto = buscarProducto(item.id_producto);
    let alertas = [];

    if(item.cantidad_actual <= producto.stock_minimo){
        alertas.push("Stock bajo");
    }

    if(item.fecha_vencimiento){
        let dias = diasParaVencer(item.fecha_vencimiento);
        if(dias < 0){
            alertas.push("Vencido");
        }else if(dias <= DIAS_ALERTA_VENCIMIENTO){
            alertas.push("Por vencer (" + dias + " días)");
        }
    }

    return alertas;
}

function crearCelda(texto){
    let celda = document.createElement("td");
    celda.textContent = texto;
    return celda;
}

function mostrarAlertas(){
    let lista = document.getElementById("lista-alertas");
    lista.innerHTML = "";
    let hayAlertas = false;

    inventario.forEach(function(item){
        let producto = buscarProducto(item.id_producto);
        obtenerAlertas(item).forEach(function(alerta){
            hayAlertas = true;
            let linea = document.createElement("li");
            linea.className = "estado-alerta";
            linea.textContent = producto.nombre + ": " + alerta.toLowerCase() +
                " (" + item.cantidad_actual + " " + producto.unidad_medida + ")";
            lista.appendChild(linea);
        });
    });

    if(!hayAlertas){
        let linea = document.createElement("li");
        linea.className = "estado-ok";
        linea.textContent = "No hay alertas por el momento.";
        lista.appendChild(linea);
    }
}

function mostrarInventario(){
    let cuerpo = document.getElementById("cuerpo-inventario");
    cuerpo.innerHTML = "";

    inventario.forEach(function(item){
        let producto = buscarProducto(item.id_producto);
        let alertas = obtenerAlertas(item);
        let fila = document.createElement("tr");

        fila.appendChild(crearCelda(producto.nombre));
        fila.appendChild(crearCelda(item.cantidad_actual + " " + producto.unidad_medida));
        fila.appendChild(crearCelda(producto.stock_minimo + " " + producto.unidad_medida));
        fila.appendChild(crearCelda(item.fecha_vencimiento || "—"));

        let celdaEstado = crearCelda(alertas.length > 0 ? alertas.join(" / ") : "Normal");
        celdaEstado.className = alertas.length > 0 ? "estado-alerta" : "estado-ok";
        fila.appendChild(celdaEstado);

        cuerpo.appendChild(fila);
    });
}

function mostrarMovimientos(){
    let cuerpo = document.getElementById("cuerpo-movimientos");
    cuerpo.innerHTML = "";

    // Los 10 movimientos más recientes, el último primero
    let recientes = movimientos.slice().reverse().slice(0, 10);

    recientes.forEach(function(mov){
        let producto = buscarProducto(mov.id_producto);
        let fila = document.createElement("tr");

        fila.appendChild(crearCelda(mov.fecha));
        fila.appendChild(crearCelda(producto.nombre));
        fila.appendChild(crearCelda(mov.tipo));
        fila.appendChild(crearCelda(mov.cantidad + " " + producto.unidad_medida));

        cuerpo.appendChild(fila);
    });
}

function actualizarPantalla(){
    mostrarAlertas();
    mostrarInventario();
    mostrarMovimientos();
}

function registrarMovimiento(){
    let idProducto = document.getElementById("producto").value;
    let tipo = document.getElementById("tipo").value;
    let cantidadTexto = document.getElementById("cantidad").value;
    let vencimiento = document.getElementById("vencimiento").value;

    if(idProducto == "" || tipo == "" || cantidadTexto.trim() == ""){
        mostrarMensajeEn("mensaje", "Debe seleccionar el producto, el tipo de movimiento y la cantidad.", false);
        return;
    }

    let cantidad = Number(cantidadTexto);
    if(isNaN(cantidad) || cantidad <= 0){
        mostrarMensajeEn("mensaje", "La cantidad debe ser un número mayor que cero.", false);
        return;
    }

    let item = inventario.find(function(i){
        return i.id_producto == idProducto;
    });

    if(tipo == "Salida" && cantidad > item.cantidad_actual){
        mostrarMensajeEn("mensaje", "No hay suficiente existencia. Cantidad disponible: " + item.cantidad_actual + ".", false);
        return;
    }

    if(tipo == "Entrada" && vencimiento != "" && diasParaVencer(vencimiento) < 0){
        mostrarMensajeEn("mensaje", "La fecha de vencimiento no puede ser anterior a hoy.", false);
        return;
    }

    if(tipo == "Entrada"){
        let cantidadExistente = item.cantidad_actual;
        item.cantidad_actual = Number((item.cantidad_actual + cantidad).toFixed(2));
        if(vencimiento != "" && (cantidadExistente <= 0 || !item.fecha_vencimiento || vencimiento < item.fecha_vencimiento)){
            item.fecha_vencimiento = vencimiento;
        }
    }else{
        item.cantidad_actual = Number((item.cantidad_actual - cantidad).toFixed(2));
    }

    movimientos.push({
        id_movimiento: movimientos.length + 1,
        id_producto: Number(idProducto),
        tipo: tipo,
        cantidad: cantidad,
        fecha: fechaEnDias(0)
    });

    document.getElementById("producto").value = "";
    document.getElementById("tipo").value = "";
    document.getElementById("cantidad").value = "";
    document.getElementById("vencimiento").value = "";

    actualizarPantalla();
    mostrarMensajeEn("mensaje", "Movimiento registrado correctamente.", true);
}

// Inicio de la página
llenarSelect("producto", productos, "id_producto", "nombre");
actualizarPantalla();