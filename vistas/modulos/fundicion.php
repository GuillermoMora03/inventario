<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Piezas
      <small>Datos</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="index.php"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Piezas Fundición</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">

        <div class="box">
          <div class="box-header">
            <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarPieza">
              Agregar Pieza
            </button>
          </div>
          <!-- /.box-header -->
          <div class="box-body">
            <table class="table table-bordered table-striped tablas">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Temp. Horno</th>
                  <th>Temp. Material</th>
                  <th>Clase</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $item = null; $valor = null;
                  $fundicion = ControladorFundicion::ctrMostrarFundicion($item, $valor);

                  if (is_array($fundicion)) {
                    foreach($fundicion as $key => $valores){
                      echo "
                        <tr>
                          <td>".($key+1)."</td>
                          <td>".$valores['temp_horno']."</td>
                          <td>".$valores['temp_material']."</td>
                          <td>".$valores['clase']."</td>
                          <td>
                            <button class='btn btn-primary btnEditarPieza btnEditarRegistroFund'
                                    idPieza=".$valores['id']."
                                    idRegistro=".$valores['id']."
                                    data-temp_horno=".$valores['temp_horno']."
                                    data-temp_material=".$valores['temp_material']."
                                    data-clase=".$valores['clase']."
                                    data-toggle='modal' data-target='#modalEditarPieza'>Editar</button>

                            <button class='btn btn-danger btnEliminarPieza btnEliminarRegistroFund'
                                    idPieza=".$valores['id']."
                                    idRegistro=".$valores['id'].">Eliminar</button>
                          </td>
                        </tr>
                      ";
                    }
                  } else {
                    echo "<tr><td colspan='5'>No hay registros de fundición para mostrar.</td></tr>";
                  }
                ?>
              </tbody>
            </table>
          </div>
          <!-- /.box-body -->
        </div>
        <!-- /.box -->
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
  </section>
  <!-- /.content -->
</div>

<!-- Modal para agregar registro -->
<div id="modalAgregarPieza" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post" action="">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar Pieza</h4>
        </div>
        <div class="modal-body">
          <div class="box-body">
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-thermometer-half"></i></span>
                <input type="number" class="form-control input-lg" name="temp_horno" placeholder="Temp. Horno (°C)" required min="0" step="1">
              </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-thermometer-half"></i></span>
                <input type="number" class="form-control input-lg" name="temp_material" placeholder="Temp. Material (°C)" required min="0" step="1">
              </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-check"></i></span>
                <select class="form-control input-lg" name="clase" required>
                  <option value="buena">Buena</option>
                  <option value="mala">Mala</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </form>
      <?php
        $crearFund = new ControladorFundicion();
        $crearFund->ctrCrearRegistro();
      ?>
    </div>
  </div>
</div>

<!-- Modal para editar registro -->
<div id="modalEditarPieza" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post" action="">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar Pieza</h4>
        </div>
        <div class="modal-body">
          <div class="box-body">
            <input type="hidden" class="form-control input-lg" name="idRegistro" id="idRegistroFund">

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-thermometer-half"></i></span>
                <input type="number" class="form-control input-lg" name="editar_temp_horno" id="editar_temp_horno" required min="0" step="1">
              </div>
            </div>

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-thermometer-half"></i></span>
                <input type="number" class="form-control input-lg" name="editar_temp_material" id="editar_temp_material" required min="0" step="1">
              </div>
            </div>

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-check"></i></span>
                <select class="form-control input-lg" name="editar_clase" id="editar_clase" required>
                  <option value="buena">Buena</option>
                  <option value="mala">Mala</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-primary" name="editarRegistroFund" value="1">Guardar cambios</button>
        </div>
      </form>
      <?php
        $editarFund = new ControladorFundicion();
        $editarFund->ctrEditarRegistro();
      ?>
    </div>
  </div>
</div>

<?php
  $borrarFund = new ControladorFundicion();
  $borrarFund->ctrBorrarRegistro();
?>
