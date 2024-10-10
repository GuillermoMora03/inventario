

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
              <h3 class="box-title">Panel Usuarios</h3>
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
                <<tbody>      
              
              <?php
                $item = null;
                $valor = null;  
            
                $usuarios = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);
                
                // Verificar si $usuarios es un array antes de recorrerlo
                if (is_array($usuarios)) {
                  foreach($usuarios as $key => $valores){
                    echo "
                        <tr>
                          <td>".($key+1)."</td>
                          <td>".$valores["nombre"]."</td>
                          <td>".$valores["usuario"]."</td>
                          <td>".$valores["perfil"]."</td>
                          <td>".$valores["fecha"]."</td>

                          <td><button class='btn btn-primary'>Editar</button> </td>
                        </tr>
                      ";
                  }
                } else {
                  echo "<tr><td colspan='5'>No hay usuarios para mostrar.</td></tr>";
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