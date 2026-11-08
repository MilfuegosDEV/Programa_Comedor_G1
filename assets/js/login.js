function iniciarSesion(){
    let correo = document.getElementById("correo").value;
    let contrasena = document.getElementById("contrasena").value;
    let mensaje = document.getElementById("mensaje");

    if(correo.trim() == "" || contrasena.trim() == ""){
        mensaje.textContent = "Debe llenar el correo y la contraseña.";
    }else{
        if(contrasena.length < 6){
            mensaje.textContent = "La contraseña debe tener al menos 6 caracteres.";
        }else{
            mensaje.textContent = "Datos completos. La verificación con la base de datos se conectará más adelante.";
        }
    }
}