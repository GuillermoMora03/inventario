<?php // vistas/modulos/ml.php
$isFundicion = isset($_GET['proceso']) && $_GET['proceso'] === 'fundicion';
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Métricas y comparativas — <?php echo $isFundicion ? 'Fundición' : 'Troquelado'; ?></h1>
  </section>

  <section class="content">
    <div class="row" style="margin-bottom:10px;">
      <div class="col-12 text-right">
        <button id="btnWinnerMetrics" class="btn btn-success">
          <?php echo $isFundicion ? 'Obtener métricas (ganador)' : 'Obtener métricas'; ?>
        </button>
        <button id="btnCompareModels" class="btn btn-primary" style="margin-right:6px;">Comparar modelos (eval)</button>
        <a href="index.php?ruta=predict" class="btn btn-default">Volver a Predicción</a>
      </div>
    </div>

    <div class="row">
      <!-- Bloque común (Troquelado / Fundición) con las MISMAS IDs -->
      <div class="col-md-6">
        <div class="box <?php echo $isFundicion ? 'box-primary' : 'box-success'; ?> box-solid">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-trophy"></i> Precisión (CV — clase Buena)</h3></div>
          <div class="box-body">
            <div id="winnerPrecBal" style="height:320px;"></div>
            <p class="text-muted" style="margin-top:8px;">N balanceado: <span id="balancedNSamples"></span></p>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="box <?php echo $isFundicion ? 'box-primary' : 'box-success'; ?> box-solid">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-th"></i> Matriz de confusión (2×2)</h3></div>
          <div class="box-body table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th></th>
                  <th>Pred. Buena (0)</th>
                  <th>Pred. Mala (1)</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <th>Real Buena (0)</th>
                  <td id="cm_tn_bal">-</td>
                  <td id="cm_fp_bal">-</td>
                </tr>
                <tr>
                  <th>Real Mala (1)</th>
                  <td id="cm_fn_bal">-</td>
                  <td id="cm_tp_bal">-</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-md-12">
        <div class="box box-default">
          <div class="box-header"><h3 class="box-title"><i class="fa fa-list"></i> Resumen</h3></div>
          <div class="box-body table-responsive">
            <table id="winnerSummaryBalanced" class="table table-striped">
              <thead>
                <tr><th>Precisión (Buena=0)</th></tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-md-12">
        <div class="box box-default">
          <div class="box-header"><h3 class="box-title"><i class="fa fa-sliders"></i> Parámetros</h3></div>
          <div class="box-body">
            <pre id="winnerParamsBalanced" style="white-space:pre-wrap;background:#f8f9fa;border:1px solid #eee;padding:10px;border-radius:6px;"></pre>
          </div>
        </div>
      </div>

      <!-- Comparativa por Precisión (balanceado) -->
      <div class="col-md-12">
        <div class="box <?php echo $isFundicion ? 'box-info' : 'box-warning'; ?> box-solid">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-balance-scale"></i> Comparativa de modelos (por Precisión)</h3>
          </div>
          <div class="box-body">
            <p id="compareWinnerBalanced" class="text-muted" style="margin-bottom:8px;">
              <?php echo $isFundicion ? 'Mejor modelo (balanceado): —' : '—'; ?>
            </p>
            <div id="modelsChartBalanced" style="height:320px; margin-bottom:16px;"></div>
            <div class="table-responsive">
              <table id="modelsTableBalanced" class="table table-bordered table-striped table-hover dataTable">
                <thead>
                  <tr>
                    <th>Modelo</th>
                    <th>Precisión CV (Buena) (Bal)</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Morris.js + Raphael -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>

<!-- Segmento dinámico para el JS -->
<script>
  window.ML_API_SEGMENT = <?php echo $isFundicion ? "'/fundicion'" : "''"; ?>;
</script>
<script src="vistas/js/ml.js?v=7"></script>