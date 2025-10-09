// Función para editar pieza
$(".tablas").on("click", ".btnEditarPieza", function() {
    var idPieza = $(this).attr("idPieza");
    var datos = new FormData();
    datos.append("idPieza", idPieza);

    $.ajax({
        url: "ajax/troquelado.ajax.php",  // Asegúrate de que la ruta esté correcta
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
            if (respuesta) {
                $("#idPieza").val(respuesta["id"]);
                $("#editarnombre").val(respuesta["nombre"]);
                $("#editarFuerzaTroquelado").val(respuesta["FuerzaTroquelado"]);
                $("#editarInspeccionVisual").val(respuesta["InspeccionVisual"]);
                $("#editarVelocidadTroquelado").val(respuesta["VelocidadTroquelado"]);
            } else {
                console.log("No se encontraron datos para la pieza.");
            }
        },
        error: function(xhr, status, error) {
            console.log("Error en la petición AJAX:", error);
        }
    });
});

// Función para eliminar pieza
$(".tablas").on("click", ".btnEliminarPieza", function() {
    var idPieza = $(this).attr("idPieza");
    Swal.fire({
        title: "¿Está seguro de borrar la pieza?",
        text: "¡Si no lo está puede cancelar la acción!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Si, borrar la pieza!"
    }).then((result) => {
        if (result.isConfirmed) { 
            window.location = "index.php?ruta=troquelado&idPieza=" + idPieza;
        }
    });
});