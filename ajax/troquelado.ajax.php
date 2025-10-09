<?php
require_once '../controladores/troquelado.controlador.php';
require_once '../modelos/troquelado.modelo.php';

class AjaxTroquelado {

    /*=============================================
    EDITAR TROQUELADO
    =============================================*/  

    public $idTroquelado;

    public function AjaxEditarPiezaTro() {

        $item = "id";    
        $valor = $this->idTroquelado;

        $respuesta = ControladorTroquelado::ctrMostrarTroquelado($item, $valor);

        echo json_encode($respuesta); // Devuelve la respuesta en formato JSON
    }
}

// Verificar si se ha enviado el ID de la pieza
if (isset($_POST["idPieza"])) {

    $editar = new AjaxTroquelado();
    $editar->idTroquelado = $_POST["idPieza"];
    $editar->AjaxEditarPiezaTro();
}
?>
