  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Data Tables
        <small>Usuarios</small>
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
              <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarUsuarios">
                Agregar Usuario
              </button>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table class="table table-bordered table-striped tablas">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Perfil</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>      
                  <?php
                    $item = null;
                    $valor = null;  
                    $usuarios = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);
                    
                    if (is_array($usuarios)) {
                      foreach($usuarios as $key => $valores){
                        echo "
                            <tr>
                              <td>".($key+1)."</td>
                              <td>".$valores['nombre']."</td>
                              <td>".$valores['usuario']."</td>
                              <td>".$valores['perfil']."</td>
                              <td>".$valores['fecha']."</td>
                              <td>
                              <button class='btn btn-primary btnEditarUsuario' idUsuario=".$valores["id"]." data-toggle='modal' 
                              data-target='#modalEditarUsuarios'>Editar</button>
                              <button class='btn btn-danger btnEliminarUsuario' idUsuario=".$valores["id"].">
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
  <div id="modalAgregarUsuarios" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Contenido del modal -->
      <div class="modal-content">
        <form role="form" method="post" action="">
          <!-- Cabecera del modal -->
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Agregar Usuario</h4>
          </div>
          <!-- Cuerpo del modal -->
          <div class="modal-body">
            <div class="box-body">
              <!-- Input para el nombre -->
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-user"></i></span>
                  <input type="text" class="form-control input-lg" name="nombre" placeholder="Ingresar nombre" required>
                </div>
              </div>
              <!-- Input para el usuario -->
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-user"></i></span>
                  <input type="text" class="form-control input-lg" name="usuario" placeholder="Ingresar usuario" required>
                </div>
              </div>
              <!-- Input para la contraseña -->
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-key"></i></span>
                  <input type="password" class="form-control input-lg" name="password" placeholder="Ingresar contraseña" required>
                </div>
              </div>
              <!-- Input para el perfil -->
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-th"></i></span>
                  <input type="text" class="form-control input-lg" name="perfil" placeholder="Ingresar perfil" required>
                </div>
              </div>
            </div>
          </div>
          <!-- Pie del modal -->
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary">Guardar Usuario</button>
          </div>
        </form>

        <?php
        $crearUsuarios = new ControladorUsuarios();
        $crearUsuarios -> ctrCrearUsuario();
        ?>
      </div>
    </div>
  </div>


  
  <!-- Modal para editar usuario -->
  <div id="modalEditarUsuarios" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Contenido del modal -->
      <div class="modal-content">
        <form role="form" method="post" action="">
          <!-- Cabecera del modal -->
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Editar Usuario</h4>
          </div>
          <!-- Cuerpo del modal -->
          <div class="modal-body">
            <div class="box-body">

             <!-- Input para el id -->
             <div class="form-group">
                <div class="input-group">
                  <input type="hidden" class="form-control input-lg" name="id" id="id">
                </div>
              </div>

              <!-- Input para el nombre -->
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-user"></i></span>
                  <input type="text" class="form-control input-lg" name="editarNombre" id="editarnombre">
                </div>
              </div>
              <!-- Input para el usuario -->
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-user"></i></span>
                  <input type="text" class="form-control input-lg" name="editarUsuario" id="editarusuario">
                </div>
              </div>
              <!-- Input para la contraseña -->
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-key"></i></span>
                  <input type="password" class="form-control input-lg" name="editarPassword" >
                </div>
              </div>
              <!-- Input para el perfil -->
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-th"></i></span>
                  <input type="text" class="form-control input-lg" name="editarPerfil" id="editarPerfil">
                </div>
              </div>
            </div>
          </div>
          <!-- Pie del modal -->
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary">Editar Usuario</button>
          </div>
        </form>

        <?php
        $crearUsuarios = new ControladorUsuarios();
        $crearUsuarios -> ctrEditarUsuario();
        ?>
      </div>
    </div>
  </div>



  <?php
        $crearUsuarios = new ControladorUsuarios();
        $crearUsuarios -> ctrBorrarUsuarios();
        ?>
