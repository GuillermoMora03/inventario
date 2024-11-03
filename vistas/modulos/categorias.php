  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Data Tables
        <small>Categorias</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Tables</a></li>
        <li class="active">Usuarios</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">

          <div class="box">
            <div class="box-header">
              <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarCategorias">
                Agregar Categorias
              </button>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table class="table table-bordered table-striped tablas">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Nombre</th>                    
                    <th>Fecha</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>      
                  <?php
                    $item = null;
                    $valor = null;  
                    $categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);
                    
                    if (is_array($categorias)) {
                      foreach($categorias as $key => $valores){
                        echo "
                            <tr>
                              <td>".($key+1)."</td>
                              <td>".$valores['nombre']."</td>                             
                              <td>".$valores['fecha']."</td>
                              <td>
                              <button class='btn btn-primary btnEditarCategorias' idCategoria=".$valores["id"]." data-toggle='modal' 
                              data-target='#modalEditarCategorias'>Editar</button>
                              <button class='btn btn-danger btnEliminarCategorias' idCategoria=".$valores["id"].">
                              Eliminar</button>
                              </td>
                            </tr>
                          ";
                      }
                    } else {
                      echo "<tr><td colspan='6'>No hay usuarios para mostrar.</td></tr>";
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

  <!-- Modal para agregar usuario -->
  <div id="modalAgregarCategorias" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Contenido del modal -->
      <div class="modal-content">
        <form role="form" method="post" action="">
          <!-- Cabecera del modal -->
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Agregar Categorias</h4>
          </div>

          <!-- Cuerpo del modal -->
          <div class="modal-body">
            <div class="box-body">
              <!-- Input para el nombre -->
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-user"></i></span>
                  <input type="text" class="form-control input-lg" name="nombrecategoria" placeholder="Ingresar categoria" required>
                </div>
              </div>
                         
          <!-- Pie del modal -->
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary">Guardar Categoria</button>
          </div>
        </form>

        <?php
        $crearCategorias = new ControladorCategorias();
        $crearCategorias -> ctrCrearCategorias();
        ?>
      </div>
    </div>
  </div>


  
  <!-- Modal para editar categorias -->
  <div id="modalEditarCategorias" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Contenido del modal -->
      <div class="modal-content">
        <form role="form" method="post" action="">
          <!-- Cabecera del modal -->
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Editar Categorias</h4>
          </div>
          <!-- Cuerpo del modal -->
          <div class="modal-body">
            <div class="box-body">

             <!-- Input para el id -->
             <div class="form-group">
                <div class="input-group">
                  <input type="hidden" class="form-control input-lg" name="idcategoria" id="idcategoria">
                </div>
              </div>

              <!-- Input para el nombre -->
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-user"></i></span>
                  <input type="text" class="form-control input-lg" name="editarCategoria" id="editarCategoria">
                </div>
              </div>
                            
            </div>
          </div>
          <!-- Pie del modal -->
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary">Editar Categorias</button>
          </div>
        </form>

        <?php
        $crearCategorias = new ControladorCategorias();
        $crearCategorias -> ctrEditarCategorias();
        ?>
      </div>
    </div>
  </div>



  <?php
        $borrarCategorias = new ControladorCategorias();
        $borrarCategorias -> ctrBorrarCategorias();
        ?>
