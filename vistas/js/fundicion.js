

// ===============================
//  Editar registro de fundición
// ===============================
$(".tablas").on("click", ".btnEditarRegistroFund", function () {
    // Tomar el id desde diferentes atributos posibles
    var id = $(this).data("id") || $(this).attr("idRegistro") || $(this).attr("idPieza");
    if (!id) {
        console.warn("No se encontró ID del registro a editar.");
        return;
    }

    var datos = new FormData();
    datos.append("idRegistro", id); // fundicion.ajax.php soporta idRegistro e idPieza

    $.ajax({
        url: "ajax/fundicion.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (respuesta) {
            if (respuesta) {
                $("#idRegistroFund").val(respuesta["id"]);
                $("#editar_temp_horno").val(respuesta["temp_horno"]);
                $("#editar_temp_material").val(respuesta["temp_material"]);
                $("#editar_clase").val(respuesta["clase"]);
                // Si deseas forzar la apertura del modal (por si no usas data-target)
                // $("#modalEditarFundicion").modal("show");
            } else {
                console.log("No se encontraron datos para el registro de fundición.");
            }
        },
        error: function (xhr, status, error) {
            console.log("Error en la petición AJAX:", error);
        },
    });
});

// ===============================
//  Eliminar registro de fundición
// ===============================
$(".tablas").on("click", ".btnEliminarRegistroFund", function () {
    var id = $(this).attr("idRegistro") || $(this).data("id") || $(this).attr("idPieza");
    if (!id) {
        console.warn("No se encontró ID del registro a eliminar.");
        return;
    }

    Swal.fire({
        title: "¿Está seguro de eliminar el registro?",
        text: "¡Si no lo está puede cancelar la acción!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Sí, eliminar",
    }).then((result) => {
        if (result.isConfirmed) {
            // El controlador espera GET idRegistro (compatibilidad con nuestro controlador)
            window.location = "index.php?ruta=fundicion&idRegistro=" + encodeURIComponent(id);
        }
    });
});