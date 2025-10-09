<?php
// modelos/ml.modelo.php

require_once "conexion.php";

class ModeloML {

  static public function mdlObtenerResultadosML() {
    // Aquí podrías hacer un SELECT a tu tabla con resultados de ML
    // Por ahora, retornamos un array estático, si deseas
    return [
      ["fecha" => "2025-03-07", "valor" => 10],
      ["fecha" => "2025-03-08", "valor" => 15],
      ["fecha" => "2025-03-09", "valor" => 7],
      ["fecha" => "2025-03-10", "valor" => 20],
      ["fecha" => "2025-03-11", "valor" => 5]
    ];
  }

}