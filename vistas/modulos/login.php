<style>
  /* Enhancements for the login card */
  .login-box .login-logo a { color: #2563eb; letter-spacing: 0.5px; }
  .login-box-body.enhanced-login {
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,.08);
    border: 1px solid #e9ecef;
  }
  .enhanced-login .login-box-msg {
    font-weight: 600;
    color: #334155;
    margin-bottom: 18px;
  }
  .enhanced-login .input-group .form-control { height: 44px; }
  .enhanced-login .input-group-addon,
  .enhanced-login .input-group-btn .btn { height: 44px; }
  .enhanced-login .btn-primary {
    background-image: linear-gradient(135deg, #2563eb, #1d4ed8);
    border: none;
  }
  .enhanced-login .btn-primary:hover { filter: brightness(1.05); }
  .login-extra { text-align:center; margin-top: 10px; }
</style>
<div class="login-box">
  <div class="login-logo">
    <a href="#"><b>Mar</b>Rob</a>
  </div>
  <!-- /.login-logo -->
  <div class="login-box-body enhanced-login">
    <p class="login-box-msg">Accede al Panel de Control</p>

    <!-- Ajusta "action" si quieres procesar el login en el mismo archivo -->
    <form method="post">
      <div class="form-group">
        <div class="input-group">
          <span class="input-group-addon"><i class="fa fa-user"></i></span>
          <input type="text" name="ingresoUsuario" class="form-control" placeholder="Usuario" required>
        </div>
      </div>

      <div class="form-group">
        <div class="input-group">
          <span class="input-group-addon"><i class="fa fa-lock"></i></span>
          <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
          <span class="input-group-btn">
            <button type="button" class="btn btn-default" id="togglePass"><i class="fa fa-eye"></i></button>
          </span>
        </div>
      </div>

      <div class="row">
        <div class="col-xs-12">
          <div style="display:flex; justify-content:center;">
            <button type="submit" class="btn btn-primary btn-flat">Login</button>
          </div>
        </div>
      </div>

      <?php
        // Llamada al controlador para procesar el login si quieres:
        $login = new ControladorUsuarios();
        $login->ctrIngresarUsuario();
      ?>
    </form>

  </div>
  <!-- /.login-box-body -->
</div>
<!-- /.login-box -->
<script>
  (function () {
    var btn = document.getElementById('togglePass');
    if (!btn) return;
    btn.addEventListener('click', function () {
      var inp = document.querySelector('input[name="password"]');
      if (!inp) return;
      if (inp.type === 'password') {
        inp.type = 'text';
        this.firstElementChild.className = 'fa fa-eye-slash';
      } else {
        inp.type = 'password';
        this.firstElementChild.className = 'fa fa-eye';
      }
    });
  })();
</script>