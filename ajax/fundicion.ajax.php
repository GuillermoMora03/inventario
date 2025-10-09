

<?php
require_once '../controladores/fundicion.controlador.php';
require_once '../modelos/fundicion.modelo.php';

class AjaxFundicion {

    /** @var int */
    public $id;

    /**
     * Devuelve un registro de fundición por ID en JSON
     */
    public function ajaxEditarFundicion() {
        $item  = 'id';
        $valor = (int)$this->id;
        $respuesta = ControladorFundicion::ctrMostrarFundicion($item, $valor);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Entry points compatibles
if (isset($_POST['idRegistro'])) {
    $ajax = new AjaxFundicion();
    $ajax->id = (int)$_POST['idRegistro'];
    $ajax->ajaxEditarFundicion();
}

// Compatibilidad por si tu JS reusa "idPieza" (como en troquelado)
if (isset($_POST['idPieza'])) {
    $ajax = new AjaxFundicion();
    $ajax->id = (int)$_POST['idPieza'];
    $ajax->ajaxEditarFundicion();
}
?>