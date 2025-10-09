
<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Inventario</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="vistas/bower_components/bootstrap/dist/css/bootstrap.min.css">

  <!-- DataTables -->
  <link rel="stylesheet" href="vistas/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="vistas/bower_components/font-awesome/css/font-awesome.min.css">

  <!-- Ionicons -->
  <link rel="stylesheet" href="vistas/bower_components/Ionicons/css/ionicons.min.css">

  <!-- AdminLTE (Theme style) -->
  <link rel="stylesheet" href="vistas/dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="vistas/dist/css/skins/_all-skins.min.css">

  <!-- Morris chart -->
  <link rel="stylesheet" href="vistas/bower_components/morris.js/morris.css">

  <!-- jvectormap -->
  <link rel="stylesheet" href="vistas/bower_components/jvectormap/jquery-jvectormap.css">

  <!-- Date Picker -->
  <link rel="stylesheet" href="vistas/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">

  <!-- Google Font (opcional, AdminLTE la usa) -->
  <link rel="stylesheet" 
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

  <!-- jQuery 3 -->
  <script src="vistas/bower_components/jquery/dist/jquery.min.js"></script>
  <!-- jQuery UI (sin SRI) -->
  <link   rel="stylesheet"
          href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

  <!-- Bootstrap 3.3.7 -->
  <script src="vistas/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
  <!-- DataTables -->
  <script src="vistas/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
  <script src="vistas/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
  
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Morris.js charts -->
  <script src="vistas/bower_components/raphael/raphael.min.js"></script>
  <script src="vistas/bower_components/morris.js/morris.min.js"></script>
  
  <!-- Sparkline -->
  <script src="vistas/bower_components/jquery-sparkline/dist/jquery.sparkline.min.js"></script>

  <!-- jQuery Knob Chart -->
  <script src="vistas/bower_components/jquery-knob/dist/jquery.knob.min.js"></script>
  
  <!-- daterangepicker -->
  <script src="vistas/bower_components/moment/min/moment.min.js"></script>
  <script src="vistas/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
  
  <!-- datepicker -->
  <script src="vistas/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
  <script src="vistas/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
  
  <!-- FastClick -->
  <script src="vistas/bower_components/fastclick/lib/fastclick.js"></script>


  <!-- AdminLTE App -->
  <script src="vistas/dist/js/adminlte.min.js"></script>
  
  <!-- AdminLTE dashboard demo (solo para demo) -->
  <script src="vistas/dist/js/pages/dashboard.js"></script>
  
  <!-- AdminLTE for demo purposes -->
  <script src="vistas/dist/js/demo.js"></script>

</head>

<!-- 
  Si el usuario inició sesión, aplicamos "skin-blue sidebar-mini" (layout de AdminLTE).
  Si NO inició sesión, aplicamos "login-page" (fondo gris, caja centrada).
-->
<body class="hold-transition 
  <?php echo (isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] == "ok") 
      ? 'skin-blue sidebar-mini' 
      : 'login-page'; ?>">

<?php
/*=============================================
=    Si hay sesión, mostramos el layout completo
=    (header, menú, contenido, footer).
=    Si no hay sesión, solo mostramos login.
=============================================*/

if (isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] == "ok") {

    echo '<div class="wrapper">';

    /*===== Header =====*/
    include "modulos/header.php";

    /*===== Menú lateral =====*/
    include "modulos/menu.php";

    /*===== Contenido dinámico según 'ruta' =====*/
    if (isset($_GET["ruta"])) {
      if (
        $_GET["ruta"] == "usuarios" ||        
        $_GET["ruta"] == "troquelado" ||
        $_GET["ruta"] == "fundicion" ||
        $_GET["ruta"] == "login" ||        
        $_GET["ruta"] == "ml" ||
        $_GET["ruta"] == "predict" ||
        $_GET["ruta"] == "salir"
        // Agrega aquí más rutas si son necesitas
      ) {
        include "modulos/" . $_GET["ruta"] . ".php";
      }
      else {
        // Página 404 o algo similar si la ruta no coincide
        include "modulos/404.php";
      }
    } else {
      // Página de inicio por defecto
      include "modulos/inicio/cajas.php";
    }

    /*===== Footer =====*/
    include "modulos/footer.php";

    echo '</div>'; // cierra <div class="wrapper">

} else {

    // Si NO hay sesión, incluimos el login
    include "modulos/login.php";
}
?>

<!-- Scripts al final (tus scripts personalizados) -->
<script src="vistas/js/plantilla.js"></script>
<script src="vistas/js/usuarios.js"></script>
<script src="vistas/js/ml.js"></script>
<script src="vistas/js/troquelado.js"></script>
<script src="vistas/js/fundicion.js"></script>
<script src="vistas/js/predict.js"></script>

</body>
</html>