$(".tablas").on("click", ".btnEditarUsuario", function(){

    var idUsuario = $(this).attr("idUsuario");
    var datos = new FormData();
    datos.append("idUsuario", idUsuario);

    $.ajax({
        url:"ajax/usuario.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta){
            $("#editarnombre").val(respuesta["nombre"]); 
            $("#editarusuario").val(respuesta["usuario"]);
            $("#editarPerfil").val(respuesta["perfil"]);
        }
    })
    
})