$(".tablas").on("click", ".btnEditarUsuario", function() {
    var idUsuario = $(this).attr("idUsuario");
    var datos = new FormData();
    datos.append("idUsuario", idUsuario);

    $.ajax({
        url: "ajax/usuario.ajax.php",  // Asegúrate de que la ruta esté correcta
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
            if (respuesta) {
                $("#id").val(respuesta["id"]);
                $("#editarnombre").val(respuesta["nombre"]);
                $("#editarusuario").val(respuesta["usuario"]);
                $("#editarPerfil").val(respuesta["perfil"]);
            } else {
                console.log("No se encontraron datos para el usuario.");
            }
        },
        error: function(xhr, status, error) {
            console.log("Error en la petición AJAX:", error);
        }
    });
});

$(".tablas").on("click", ".btnEliminarUsuario", function() {
    var idUsuario = $(this).attr("idUsuario");
    swal({
        title: "¿Está seguro de borrar el usuario?",
        text: "¡Si no lo está puede cancelar la acción!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Si, borrar usuario!"
    }).then(function(result) { //Si se confirma la eliminación
        if(result.value) {
            window.location = "index.php?ruta=usuarios&idUsuario=" + idUsuario;
        }
    }); //Si se cancela la eliminación
});

