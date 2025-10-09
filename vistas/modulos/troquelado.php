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
        <li class="active">Piezas Troquelado</li>
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
                    <th>Nombre</th>
                    <th>Fuerza de Troquelado</th>
                    <th>Inspeccion de Pieza</th>
                    <th>Velocidad de Troquelado</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>      
                  <?php
                    $item = null;
                    $valor = null;  
                    $troquelado = ControladorTroquelado::ctrMostrarTroquelado($item, $valor);
                    
                    if (is_array($troquelado)) {
                      foreach($troquelado as $key => $valores){
                        echo "
                            <tr>
                              <td>".($key+1)."</td>
                              <td>".$valores['nombre']."</td>
                              <td>".$valores['FuerzaTroquelado']."</td>
                              <td>".$valores['InspeccionVisual']."</td>
                              <td>".$valores['VelocidadTroquelado']."</td>
                              <td>
                              <button class='btn btn-primary btnEditarPieza' idPieza=".$valores["id"]."
                              data-toggle='modal' data-target='#modalEditarPieza'>Editar</button>

                              <button class='btn btn-danger btnEliminarPieza' idPieza=".$valores["id"].">
                              Eliminar</button>
                              </td>
                            </tr>
                          ";
                      }
                    } else {
                      echo "<tr><td colspan='6'>No hay piezas para mostrar.</td></tr>";
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

  <!-- Modal para agregar pieza -->
  <div id="modalAgregarPieza" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Contenido del modal -->
      <div class="modal-content">
        <form role="form" method="post" action="">
          <!-- Cabecera del modal -->
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Agregar Pieza</h4>
          </div>
          <!-- Cuerpo del modal -->
          <div class="modal-body">
            <div class="box-body">
              <!-- Input para el Nombre -->
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-th"></i></span>
                  <input type="text" class="form-control input-lg" name="nombre" placeholder="Ingresar el nombre de la pieza" required>
                </div>
              </div>
              <!-- Input para la Fuerza Troquelado -->
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-th"></i></span>
                  <!-- Agregar step="any" o step="0.01" para permitir decimales -->
                  <input type="number" step="0.00001" class="form-control input-lg"
                  name="FuerzaTroquelado" 
                  placeholder="Ingresar la fuerza de troquelado" required>
                </div>
              </div>
              <!-- Input para la Inspeccion de Piezas -->
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-th"></i></span>
                  <input type="text" class="form-control input-lg" name="InspeccionVisual" placeholder="Ingresar la inspeccion visual ( Buena o Mala )" required>
                </div>
              </div>
              <!-- Input para Velocidad Troquelado -->
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-th"></i></span>
                  <!-- Agregar step="any" o step="0.01" para permitir decimales -->
                  <input type="number" step="0.01" class="form-control input-lg"
                  name="VelocidadTroquelado"
                  placeholder="Ingresar la velocidad de troquelado" required>
                </div>
              </div>
            </div>
          </div>
          <!-- Pie del modal -->
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary">Guardar Pieza</button>
          </div>
        </form>

        <?php
        $crearPieza = new ControladorTroquelado();
        $crearPieza -> ctrCrearPiezaTro();
        ?>
      </div>
    </div>
  </div>

  
  <!-- Modal para editar pieza -->
  <div id="modalEditarPieza" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Contenido del modal -->
      <div class="modal-content">
        <form role="form" method="post" action="">
          <!-- Cabecera del modal -->
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Editar Pieza</h4>
          </div>
          <!-- Cuerpo del modal -->
          <div class="modal-body">
            <div class="box-body">

             <!-- Input para el idPieza -->
             <div class="form-group">
                <div class="input-group">
                  <input type="hidden" class="form-control input-lg" name="idPieza" id="idPieza">
                </div>
              </div>

              <!-- editarNombre -->
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-user"></i></span>
                  <input type="text" class="form-control input-lg" name="editarNombre" id="editarnombre">
                </div>
              </div>
              <!-- editarFuerzaTroquelado -->
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-user"></i></span>
                  <!-- Cambiar a type="number" y agregar step para decimales -->
                  <input type="number" step="any" class="form-control input-lg" 
                        name="editarFuerzaTroquelado" 
                        id="editarFuerzaTroquelado">
                </div>
              </div>
              <!-- editarInspeccionVisual -->
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-key"></i></span>
                  <input type="text" class="form-control input-lg" name="editarInspeccionVisual" id="editarInspeccionVisual">
                </div>
              </div>
              <!-- editarVelocidadTroquelado -->
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-th"></i></span>
                  <!-- Cambiar a type="number" y agregar step para decimales -->
                  <input type="number" step="any" class="form-control input-lg" 
                        name="editarVelocidadTroquelado" 
                        id="editarVelocidadTroquelado">
                </div>
              </div>
            </div>
          </div>
          <!-- Pie del modal -->
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary" name="editarPiezaTro" value="1">Editar Pieza</button>
          </div>
        </form>

        <?php
        $editarPieza = new ControladorTroquelado();
        $editarPieza -> ctrEditarPiezaTro();
        ?>
      </div>
    </div>
  </div>



  <?php
        $borrarPieza = new ControladorTroquelado();
        $borrarPieza -> ctrBorrarPieza();
  ?>
