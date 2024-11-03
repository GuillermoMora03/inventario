<?php

class ControladorCategorias {

    // Registrar usuarios
    static public function ctrCrearCategorias() {

        if (isset($_POST["nombrecategoria"])) {

            $tabla = "categorias";        

            $datos = array("nombre" => $_POST["nombrecategoria"]);

            $respuesta = ModeloCategorias::mdlGuardarCategorias($tabla, $datos);

            if ($respuesta == "ok") {
                echo "<script>
                swal({
                    type: 'success',
                    title: 'La categoria ha sido guardada correctamente',
                    showConfirmButton: true,
                    confirmButtonText: 'Cerrar',
                    closeOnConfirm: false
                }).then(function(result){

                    if(result.value){
                        window.location = 'categorias';
                    }

                });
            </script>";
            } else {
                echo "<script>
                    swal({
                        type: 'error',
                        title: 'La categoria no ha sido guardada correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar',
                        closeOnConfirm: false
                    }).then(function(result){

                        if(result.value){
                            window.location = 'categorias';
                        }

                    });
                </script>";
            }
        }
    }

    // Mostrar categorias
    static public function ctrMostrarCategorias($item, $valor) {
        $tabla = "categorias";
        $respuesta = ModeloCategorias::mdlMostrarCategorias($tabla, $item, $valor);
        return $respuesta;
    }


     // Editar usuarios
     static public function ctrEditarCategorias() {

        if (isset($_POST["editarCategoria"])) {

            $tabla = "categorias";                        

            $datos = array(
                "id" => $_POST["idcategoria"],
                "nombre" => $_POST["editarCategoria"],                
            );

            $respuesta = ModeloCategorias::mdlEditarCategorias($tabla, $datos);

            if ($respuesta == "ok") {
                echo "<script>
                    swal({
                        type: 'success',
                        title: 'La categoria ha sido modificada correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar',
                        closeOnConfirm: false
                    }).then(function(result){

                        if(result.value){
                            window.location = 'categorias';
                        }

                    });
                </script>";
            } else {
                echo "<script>
                    swal({
                        type: 'error',
                        title: 'La categoria no ha sido modificada correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar',
                        closeOnConfirm: false
                    }).then(function(result){

                        if(result.value){
                            window.location = 'categorias';
                        }

                    });
                </script>";
            }
        }
    }

    static public function ctrBorrarCategorias() {

        if (isset($_GET["idCategoria"])) {

            $tabla = "categorias";
            $datos = $_GET["idCategoria"];

            $respuesta = ModeloCategorias::mdlBorrarCategorias($tabla, $datos);

            if ($respuesta == "ok") {
                echo "<script>
                    swal({
                        type: 'success',
                        title: 'La categoria ha sido eliminada correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar',
                        closeOnConfirm: false
                    }).then(function(result){

                        if(result.value){
                            window.location = 'categorias';
                        }

                    });
                </script>";
            } else {
                "<script>
                    swal({
                        type: 'error',
                        title: 'La categoria no ha sido eliminada correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar',
                        closeOnConfirm: false
                    }).then(function(result){

                        if(result.value){
                            window.location = 'categorias';
                        }

                    });
                </script>";
            }
        }
    }
}
?>
