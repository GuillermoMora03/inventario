

<?php

class ControladorFundicion {

    /* =============================================
     *   CREAR REGISTRO (INSERT)
     * ============================================= */
    public function ctrCrearRegistro() {
        if (isset($_POST['temp_horno'], $_POST['temp_material'], $_POST['clase'])) {
            $tabla = 'fundicion'; // si tu tabla tiene tilde, cambia a 'fundición'

            $datos = [
                'temp_horno'    => (int)$_POST['temp_horno'],
                'temp_material' => (int)$_POST['temp_material'],
                'clase'         => ($_POST['clase'] === 'mala' ? 'mala' : 'buena')
            ];

            $respuesta = ModeloFundicion::mdlIngresarFundicion($tabla, $datos);

            if ($respuesta === 'ok') {
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'El registro ha sido guardado correctamente',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar'
                    }).then((result) => { if (result.isConfirmed) { window.location = 'fundicion'; } });
                </script>";
            } else {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'El registro no se pudo guardar',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar'
                    }).then((result) => { if (result.isConfirmed) { window.location = 'fundicion'; } });
                </script>";
            }
        }
    }

    /* =============================================
     *   MOSTRAR REGISTROS (SELECT)
     * ============================================= */
    public static function ctrMostrarFundicion($item, $valor) {
        $tabla = 'fundicion'; // si tu tabla tiene tilde, cambia a 'fundición'
        return ModeloFundicion::mdlMostrarFundicion($tabla, $item, $valor);
    }

    /* =============================================
     *   EDITAR REGISTRO (UPDATE)
     * ============================================= */
    public function ctrEditarRegistro() {
        // Puede venir con bandera editarRegistroFund o con idRegistro directo
        if (isset($_POST['editarRegistroFund']) || isset($_POST['idRegistro'])) {
            if (!isset($_POST['idRegistro'])) return; // seguridad

            $tabla = 'fundicion'; // si tu tabla tiene tilde, cambia a 'fundición'
            $datos = [
                'id'            => (int)$_POST['idRegistro'],
                'temp_horno'    => (int)($_POST['editar_temp_horno'] ?? 0),
                'temp_material' => (int)($_POST['editar_temp_material'] ?? 0),
                'clase'         => (isset($_POST['editar_clase']) && $_POST['editar_clase'] === 'mala') ? 'mala' : 'buena'
            ];

            $respuesta = ModeloFundicion::mdlEditarFundicion($tabla, $datos);

            if ($respuesta === 'ok') {
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'El registro ha sido actualizado',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar'
                    }).then((result) => { if (result.isConfirmed) { window.location = 'fundicion'; } });
                </script>";
            } else {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'El registro no se pudo actualizar',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar'
                    }).then((result) => { if (result.isConfirmed) { window.location = 'fundicion'; } });
                </script>";
            }
        }
    }

    /* =============================================
     *   BORRAR REGISTRO (DELETE)
     * ============================================= */
    public static function ctrBorrarRegistro() {
        // Soporta GET idRegistro (como en troquelado) o POST idEliminar (por si usas form)
        if (isset($_GET['idRegistro']) || isset($_POST['idEliminar'])) {
            $tabla = 'fundicion'; // si tu tabla tiene tilde, cambia a 'fundición'
            $id = isset($_GET['idRegistro']) ? (int)$_GET['idRegistro'] : (int)$_POST['idEliminar'];

            $respuesta = ModeloFundicion::mdlBorrarFundicion($tabla, $id);

            if ($respuesta === 'ok') {
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'El registro ha sido eliminado',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar'
                    }).then((result) => { if (result.isConfirmed) { window.location = 'fundicion'; } });
                </script>";
            } else {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'El registro no se pudo eliminar',
                        showConfirmButton: true,
                        confirmButtonText: 'Cerrar'
                    }).then((result) => { if (result.isConfirmed) { window.location = 'fundicion'; } });
                </script>";
            }
        }
    }
}
?>