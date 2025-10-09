<?php

  $item = null;
  $valor = null;

  /* ========================================
     PIEZAS
  ======================================== */
  $piezas = ControladorTroquelado::ctrMostrarTroquelado($item, $valor);

  // Manejo de errores: si no es array, lo convertimos a array vacío
  if (!is_array($piezas)) {
      $piezas = [];
  }

  // Recorremos
  $datosP = [];
  foreach ($piezas as $valueP) {
      $datosP[] = $valueP["id"];
  }

  $totalPiezas = count($datosP);

  /* ========================================
     FUNDICION
  ======================================== */
  $fundicion = ControladorFundicion::ctrMostrarFundicion($item, $valor);

  // Manejo de errores: si no es array, lo convertimos a array vacío
  if (!is_array($fundicion)) {
      $fundicion = [];
  }

  $datosF = [];
  foreach ($fundicion as $valueF) {
      // Asumimos que la tabla fundicion tiene columna 'id'
      $datosF[] = $valueF["id"];
  }

  $totalFundicion = count($datosF);


  /* ========================================
     USUARIOS
  ======================================== */
  $usuarios = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);

  // Manejo de errores: si no es array, lo convertimos a array vacío
  if (!is_array($usuarios)) {
      $usuarios = [];
  }

  $datosU = [];
  foreach ($usuarios as $valueU) {
      $datosU[] = $valueU["id"];
  }

  $totalUsuarios = count($datosU);

?>

<!-- A partir de aquí, tu HTML normal -->
<div class="content-wrapper">
  <section class="content-header">
    <h1>Dashboard <small>Panel de Control</small></h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Dashboard</li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <!-- Caja Piezas -->
      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-aqua">
          <div class="inner">
            <h3><?php echo $totalPiezas; ?></h3>
            <p>Piezas de Troquelado</p>
          </div>
          <div class="icon">
            <i class="fa fa-industry"></i>
          </div>
          <a href="troquelado" class="small-box-footer">
            Ir <i class="fa fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>

      <!-- Caja Fundición -->
      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-green">
          <div class="inner">
            <h3><?php echo $totalFundicion; ?></h3>
            <p>Piezas de Fundición</p>
          </div>
          <div class="icon">
            <i class="ion ion-fireball"></i>
          </div>
          <a href="fundicion" class="small-box-footer">
            Ir <i class="fa fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>

      <!-- Caja Usuarios Registrados -->
      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-yellow">
          <div class="inner">
            <h3><?php echo $totalUsuarios; ?></h3>
            <p>Usuarios Registrados</p>
          </div>
          <div class="icon">
            <i class="ion ion-person-add"></i>
          </div>
          <a href="usuarios" class="small-box-footer">
            Ir <i class="fa fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>

    </div>
  </section>
</div>