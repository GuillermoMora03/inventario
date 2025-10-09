<?php
// controladores/ml.controlador.php

class ControladorML {

  /*=============================================
    Mostrar Resultados de Machine Learning
  =============================================*/
  static public function ctrMostrarResultadosML() {
    // En el futuro, aquí llamarás a un modelo 
    // que consulte tu base de datos o un servicio de Python.
    // De momento, devolvemos datos estáticos:

    $datosEjemplo = [
      ["fecha" => "2025-03-07", "valor" => 10],
      ["fecha" => "2025-03-08", "valor" => 15],
      ["fecha" => "2025-03-09", "valor" => 7],
      ["fecha" => "2025-03-10", "valor" => 20],
      ["fecha" => "2025-03-11", "valor" => 5]
    ];

    return $datosEjemplo;
  }

}