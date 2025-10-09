<?php
// ajax/ml.ajax.php

require_once "../controladores/ml.controlador.php";
require_once "../modelos/ml.modelo.php";

// Verificamos la acción solicitada
if (isset($_POST["action"]) && $_POST["action"] === "fetchMLData") {

  // Obtenemos datos estáticos (o desde la base de datos, en un futuro)
  $datos = ControladorML::ctrMostrarResultadosML();

  // Devolvemos la data en formato JSON
  echo json_encode($datos);
}