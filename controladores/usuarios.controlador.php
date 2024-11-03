<?php

class ControladorUsuarios {

    // Registrar usuarios
    static public function ctrCrearUsuario() {

        if (isset($_POST["usuario"])) {

            $tabla = "usuarios";
            
            // Hashear la contraseña usando password_hash() con BCRYPT
            $encriptar = password_hash($_POST["password"], PASSWORD_BCRYPT);

            $datos = array(
                "nombre" => $_POST["nombre"],
                "usuario" => $_POST["usuario"],
                "password" => $encriptar,
                "perfil" => $_POST["perfil"]
            );

            $respuesta = ModeloUsuarios::mdlGuardarUsuarios($tabla, $datos);

            if ($respuesta == "ok") {
                echo "<script>
                swall({
                    type: 'success',
                    title: 'El usuario ha sido guardado correctamente',
                    showConfirmButton: true,
                    confirmButtonText: 'Cerrar',
                    closeOnConfirm: false
                }).then(function(result){

                    if(result.value){
                        window.location = 'usuarios';
                    }

                });
            </script>";
            } else {
                echo "<script>
                    swall({
                        type: 'error',
                        title: 'El usuario no ha sido guardado correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar',
                        closeOnConfirm: false
                    }).then(function(result){

                        if(result.value){
                            window.location = 'usuarios';
                        }

                    });
                </script>";
            }
        }
    }

    // Mostrar usuarios
    static public function ctrMostrarUsuarios($item, $valor) {
        $tabla = "usuarios";
        $respuesta = ModeloUsuarios::mdlMostrarUsuarios($tabla, $item, $valor);
        return $respuesta;
    }


     // Editar usuarios
     static public function ctrEditarUsuario() {

        if (isset($_POST["editarUsuario"])) {

            $tabla = "usuarios";
            
            // Hashear la contraseña usando password_hash() con BCRYPT
            $encriptar = password_hash($_POST["editarPassword"], PASSWORD_BCRYPT);

            $datos = array(
                "id" => $_POST["id"],
                "nombre" => $_POST["editarNombre"],
                "usuario" => $_POST["editarUsuario"],
                "password" => $encriptar,
                "perfil" => $_POST["editarPerfil"]
            );

            $respuesta = ModeloUsuarios::mdlEditarUsuarios($tabla, $datos);

            if ($respuesta == "ok") {
                echo "<script>
                    swall({
                        type: 'success',
                        title: 'El usuario ha sido modificado correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar',
                        closeOnConfirm: false
                    }).then(function(result){

                        if(result.value){
                            window.location = 'usuarios';
                        }

                    });
                </script>";
            } else {
                echo "<script>
                    swall({
                        type: 'error',
                        title: 'El usuario no ha sido modificado correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar',
                        closeOnConfirm: false
                    }).then(function(result){

                        if(result.value){
                            window.location = 'usuarios';
                        }

                    });
                </script>";
            }
        }
    }

    static public function ctrBorrarUsuarios() {

        if (isset($_GET["idUsuario"])) {

            $tabla = "usuarios";
            $datos = $_GET["idUsuario"];

            $respuesta = ModeloUsuarios::mdlBorrarUsuarios($tabla, $datos);

            if ($respuesta == "ok") {
                echo "<script>
                    swall({
                        type: 'success',
                        title: 'El usuario ha sido eliminado correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar',
                        closeOnConfirm: false
                    }).then(function(result){

                        if(result.value){
                            window.location = 'usuarios';
                        }

                    });
                </script>";
            } else {
                "<script>
                    swall({
                        type: 'error',
                        title: 'El usuario no ha sido eliminado correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar',
                        closeOnConfirm: false
                    }).then(function(result){

                        if(result.value){
                            window.location = 'usuarios';
                        }

                    });
                </script>";
            }
        }
    }
}
?>
