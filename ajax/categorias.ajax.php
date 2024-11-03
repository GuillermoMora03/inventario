<?php
require_once '../controladores/categorias.controlador.php';
require_once '../modelos/categorias.modelo.php';

class AjaxCategorias {

    /*=============================================
    EDITAR CATEGORIA
    =============================================*/  

    public $idCategoria;

    public function AjaxEditarCategorias() {

        $item = "id";    
        $valor = $this->idCategoria;

        $respuesta = ControladorCategorias::ctrMostrarCategorias($item, $valor);

        echo json_encode($respuesta); // Devuelve la respuesta en formato JSON
    }
}

// Verificar si se ha enviado el ID de usuario
if (isset($_POST["idCategoria"])) {

    $editar = new AjaxCategorias();
    $editar->idCategoria = $_POST["idCategoria"];
    $editar->AjaxEditarCategorias();
}
?>
