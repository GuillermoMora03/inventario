<?php

class ControladorUsuarios {

    /*=============================================
    =            INGRESAR USUARIO (LOGIN)         =
    =============================================*/
    static public function ctrIngresarUsuario() {

        if (isset($_POST["ingresoUsuario"])) {

            // Ajusta tu validación si realmente tus usuarios son correos:
            // if (filter_var($_POST["ingresoUsuario"], FILTER_VALIDATE_EMAIL)) { ... }
            // O amplía tu regex para permitir '@', '.', '-', '_', etc.
            if (preg_match('/^[a-zA-Z0-9]+$/', $_POST["ingresoUsuario"])) {

                // Tres parámetros: $tabla, $item, $valor                
                $item  = "usuario";
                $valor = $_POST["ingresoUsuario"];

                $respuesta = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);

                // Verificamos que $respuesta no esté vacío y luego la contraseña
                if ($respuesta && 
                    $respuesta["usuario"] == $_POST["ingresoUsuario"] && 
                    password_verify($_POST["password"], $respuesta["password"])) {

                    // Contraseña correcta, iniciamos sesión
                    $_SESSION["iniciarSesion"] = "ok";
                    $_SESSION["id"]       = $respuesta["id"];
                    $_SESSION["nombre"]   = $respuesta["usuario"];
                    $_SESSION["usuario"]  = $respuesta["usuario"];
                    $_SESSION["perfil"]   = $respuesta["perfil"];
                    $_SESSION["fecha"]    = $respuesta["fecha"];

                    echo '<script>
                            window.location = "index.php";
                          </script>';

                } else {
                    // Error: usuario o contraseña no coinciden
                    echo '<br><div class="alert alert-danger">Error al ingresar, verifica los datos</div>';
                }
            }
        }
    }

    /*=============================================
    =            CREAR USUARIO (REGISTRO)         =
    =============================================*/
    static public function ctrCrearUsuario() {

        if (isset($_POST["usuario"])) {

            $tabla = "usuarios";

            // Hashear la contraseña con password_hash (BCRYPT)
            $encriptar = password_hash($_POST["password"], PASSWORD_BCRYPT);

            $datos = array(
                "nombre"   => $_POST["nombre"],
                "usuario"  => $_POST["usuario"],
                "password" => $encriptar,
                "perfil"   => $_POST["perfil"]
            );

            $respuesta = ModeloUsuarios::mdlGuardarUsuarios($tabla, $datos);

            if ($respuesta == "ok") {
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'El usuario ha sido guardado correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar'
                    }).then((result) => {
                        if(result.isConfirmed){
                            window.location = 'usuarios';
                        }
                    });
                </script>";
            } else {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'El usuario no ha sido guardado correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar'
                    }).then((result) => {
                        if(result.isConfirmed){
                            window.location = 'usuarios';
                        }
                    });
                </script>";
            }
        }
    }

    /*=============================================
    =            MOSTRAR USUARIOS                 =
    =============================================*/
    static public function ctrMostrarUsuarios($item, $valor) {

        $tabla = "usuarios";
        $respuesta = ModeloUsuarios::mdlMostrarUsuarios($tabla, $item, $valor);
        return $respuesta;
    }

    /*=============================================
    =            EDITAR USUARIO                   =
    =============================================*/
    static public function ctrEditarUsuario() {

        if (isset($_POST["editarUsuario"])) {

            $tabla = "usuarios";

            // Hashear la contraseña usando password_hash() con BCRYPT
            $encriptar = password_hash($_POST["editarPassword"], PASSWORD_BCRYPT);

            $datos = array(
                "id"       => $_POST["id"],
                "nombre"   => $_POST["editarNombre"],
                "usuario"  => $_POST["editarUsuario"],
                "password" => $encriptar,
                "perfil"   => $_POST["editarPerfil"]
            );

            $respuesta = ModeloUsuarios::mdlEditarUsuarios($tabla, $datos);

            if ($respuesta == "ok") {
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'El usuario ha sido modificado correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar'
                    }).then((result) => {
                        if(result.isConfirmed){
                            window.location = 'usuarios';
                        }
                    });
                </script>";
            } else {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'El usuario no ha sido modificado correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar'
                    }).then((result) => {
                        if(result.isConfirmed){
                            window.location = 'usuarios';
                        }
                    });
                </script>";
            }
        }
    }

    /*=============================================
    =            BORRAR USUARIO                   =
    =============================================*/
    static public function ctrBorrarUsuarios() {

        if (isset($_GET["idUsuario"])) {

            $tabla = "usuarios";
            $datos = $_GET["idUsuario"];

            $respuesta = ModeloUsuarios::mdlBorrarUsuarios($tabla, $datos);

            if ($respuesta == "ok") {
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'El usuario ha sido eliminado correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar'
                    }).then((result) => {
                        if(result.isConfirmed){
                            window.location = 'usuarios';
                        }
                    });
                </script>";
            } else {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'El usuario no ha sido eliminado correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar'
                    }).then((result) => {
                        if(result.isConfirmed){
                            window.location = 'usuarios';
                        }
                    });
                </script>";
            }
        }
    }
}