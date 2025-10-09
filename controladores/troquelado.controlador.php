<?php

class ControladorTroquelado {

    // Registrar pieza de troquelado
    static public function ctrCrearPiezaTro() {

        if (isset($_POST["nombre"])) {

            $tabla = "troquelado";

            $datos = array(
                "nombre" => $_POST["nombre"],
                "FuerzaTroquelado" => $_POST["FuerzaTroquelado"],
                "InspeccionVisual" => $_POST["InspeccionVisual"],
                "VelocidadTroquelado" => $_POST["VelocidadTroquelado"]
            );

            $respuesta = ModeloTroquelado::mdlGuardarTroquelado($tabla, $datos);

            if ($respuesta == "ok") {
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'La pieza ha sido guardada correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar'
                    }).then((result) => {
                        if(result.isConfirmed){                        
                            window.location = 'troquelado';
                        }
                    });
                </script>";
            } else {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'La pieza no ha sido guardada correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar'
                    }).then((result) => {
                        if(result.isConfirmed){
                            window.location = 'troquelado';
                        }
                    });
                </script>";
            }
        }
    }

    // Mostrar datos Troquelado
    static public function ctrMostrarTroquelado($item, $valor) {
        $tabla = "troquelado";
        $respuesta = ModeloTroquelado::mdlMostrarTroquelado($tabla, $item, $valor);
        return $respuesta;
    }

    // Editar Pieza de Troquelado
    static public function ctrEditarPiezaTro() {

        if (isset($_POST["editarPiezaTro"])) {

            $tabla = "troquelado";
                      
            $datos = array(
                "id" => $_POST["idPieza"],
                "nombre" => $_POST["editarNombre"],
                "FuerzaTroquelado" => $_POST["editarFuerzaTroquelado"],
                "InspeccionVisual" => $_POST["editarInspeccionVisual"],
                "VelocidadTroquelado" => $_POST["editarVelocidadTroquelado"]
            );

            $respuesta = ModeloTroquelado::mdlEditarPiezaTro($tabla, $datos);

            if ($respuesta == "ok") {
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'La pieza ha sido modificada correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar'
                    }).then((result) => {
                        if(result.isConfirmed){
                            window.location = 'troquelado';
                        }
                    });
                </script>";
            } else {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'La pieza no ha sido modificada correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar'
                    }).then((result) => {
                        if(result.isConfirmed){
                            window.location = 'troquelado';
                        }
                    });
                </script>";
            }
        }
    }

    // Eliminar Pieza
    static public function ctrBorrarPieza() {

        if (isset($_GET["idPieza"])) {

            $tabla = "troquelado";
            $datos = $_GET["idPieza"];

            $respuesta = ModeloTroquelado::mdlBorrarPieza($tabla, $datos);

            if ($respuesta == "ok") {
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'La pieza ha sido eliminada correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar'
                    }).then((result) => {
                        if(result.isConfirmed){
                            window.location = 'troquelado';
                        }
                    });
                </script>";
            } else {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'La pieza no ha sido eliminada correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar'
                    }).then((result) => {
                        if(result.isConfirmed){
                            window.location = 'troquelado';
                        }
                    });
                </script>";
            }
        }
    }
}
?>