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
