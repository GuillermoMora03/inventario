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
                echo "Usuario registrado correctamente";
            } else {
                echo "Error al registrar usuario";
            }
        }
    }

    // Mostrar usuarios
    static public function ctrMostrarUsuarios($item, $valor) {
        $tabla = "usuarios";
        $respuesta = ModeloUsuarios::mdlMostrarUsuarios($tabla, $item, $valor);
        return $respuesta;
    }
}
?>
