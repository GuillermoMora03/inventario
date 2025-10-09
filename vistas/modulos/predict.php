<?php // vistas/modulos/predict.php ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Predicción de Calidad de Piezas</h1>
  </section>

  <section class="content">
    <div class="row">

      <!-- Formulario de entrada -->
      <div class="col-md-6">
        <div class="box box-primary box-solid">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-industry"></i> Parámetros de troquelado</h3>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            </div>
          </div>
          <div class="box-body">
            <form id="predictForm">
              <div class="form-group">
                <label for="fuerza">Fuerza Troquelado (kN)</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-bolt"></i></span>
                  <input type="number" step="0.00001" class="form-control" id="fuerza" placeholder="Ej. 1234.56789" required>
                </div>
              </div>
              <div class="form-group">
                <label for="velocidad">Velocidad Troquelado (m/s)</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-tachometer"></i></span>
                  <input type="number" step="0.01" class="form-control" id="velocidad" placeholder="Ej. 2.50" required>
                </div>
              </div>
              <div class="btn-group">
                <button type="submit" class="btn btn-primary"><i class="fa fa-play"></i> Predecir</button>
                <button type="button" id="btnMoreInfo" class="btn btn-info" style="margin-left:8px;"><i class="fa fa-info-circle"></i> Más información</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Resultado de la predicción -->
      <div class="col-md-6">
        <div class="box box-info box-solid">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-bar-chart"></i> Resultado</h3></div>
          <div class="box-body" id="predictResult">
            <p class="text-muted">Introduce parámetros y pulsa "Predecir".</p>
          </div>
        </div>
      </div>

    </div>

    <!-- ============================ -->
    <!--  Predicción — Fundición      -->
    <!-- ============================ -->
    <div class="row">

      <!-- Formulario de entrada (Fundición) -->
      <div class="col-md-6">
        <div class="box box-primary box-solid">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-fire"></i> Parámetros de fundición</h3>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            </div>
          </div>
          <div class="box-body">
            <form id="predictFundForm">
              <div class="form-group">
                <label for="inpTempHornoFund">Temp. Horno (°C)</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-thermometer-half"></i></span>
                  <input type="number" step="1" min="0" class="form-control" id="inpTempHornoFund" placeholder="Ej. 750" required>
                </div>
              </div>
              <div class="form-group">
                <label for="inpTempMaterialFund">Temp. Material (°C)</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-thermometer-half"></i></span>
                  <input type="number" step="1" min="0" class="form-control" id="inpTempMaterialFund" placeholder="Ej. 680" required>
                </div>
              </div>
              <div class="btn-group">
                <button type="button" class="btn btn-primary" id="btnPredecirFundicion"><i class="fa fa-play"></i> Predecir</button>
                <button type="button" class="btn btn-info" id="btnMoreInfoFund" style="margin-left:8px;"><i class="fa fa-info-circle"></i> Más información</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Resultado de la predicción (Fundición) -->
      <div class="col-md-6">
        <div class="box box-info box-solid">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-bar-chart"></i> Resultado — Fundición</h3></div>
          <div class="box-body" id="fund-pred-resultado">
            <p class="text-muted">Introduce temperaturas y pulsa "Predecir".</p>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<!-- Modal: métricas del ganador -->
<div class="modal fade" id="metricsModal" tabindex="-1" role="dialog" aria-labelledby="metricsModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="metricsModalLabel">Modelo ganador — Precisión (clase Buena)</h4>
      </div>

      <div class="modal-body" id="metricsModalBody">
        <p class="text-muted">Cargando métricas…</p>
        <div id="modalWinnerPrecTroq" style="height:320px; margin-top:10px;"></div>
      </div>

      <div class="modal-footer">
        <!-- (Opcional) enlace a tu página de comparativa si la mantienes -->
        <a href="index.php?ruta=ml" class="btn btn-default">Ver comparativa completa</a>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>

<!-- Modal: métricas del ganador (Fundición) -->
<div class="modal fade" id="metricsModalFund" tabindex="-1" role="dialog" aria-labelledby="metricsModalFundLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="metricsModalFundLabel">Modelo ganador — Fundición (Precisión, clase Buena)</h4>
      </div>

      <div class="modal-body" id="metricsModalBodyFund">
        <p class="text-muted">Cargando métricas…</p>
        <div id="modalWinnerPrecFund" style="height:320px; margin-top:10px;"></div>
      </div>

      <div class="modal-footer">
        <a href="index.php?ruta=ml&proceso=fundicion" class="btn btn-default">Ver comparativa completa</a>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>

<!-- Tu script -->
<script src="vistas/js/predict.js"></script>

<!-- CSS mínimo para la cuadrícula 2×2 dentro del modal -->
<style>
  .cm-grid {
    display: grid;
    grid-template-columns: 160px 1fr 1fr;
    grid-template-rows: 44px 1fr 1fr;
    gap: 8px;
  }
  .cm-top-left { display:flex; align-items:center; justify-content:center; font-weight:600; color:#555; }
  .cm-col-header, .cm-row-header {
    display:flex; align-items:center; justify-content:center;
    font-weight:600; color:#2f3b52; background:#f6f8fb; border:1px solid #e5ecf6; border-radius:8px;
  }
  .cm-cell {
    border:1px solid #d7e4f7; border-radius:12px; background:#eaf3ff; padding:10px; text-align:center;
    display:flex; flex-direction:column; align-items:center; justify-content:center;
  }
  .cm-title { font-weight:700; margin-bottom:4px; }
  .cm-count { font-size:20px; font-weight:700; }
  .cm-percent { font-size:13px; color:#556; }
</style>