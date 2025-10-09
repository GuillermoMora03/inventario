// vistas/js/predict.js
$(function () {
  var API_BASE = "http://127.0.0.1:5000";
  var API_EVAL = "http://127.0.0.1:5001";
  var API_BASE_FUND = "http://127.0.0.1:5002";

  // --- Helpers de métrica: Precisión (clase Buena=0) ---
  function renderBar(elementId, label, value, yLabel) {
    var $el = $("#" + elementId);
    if (!$el.length) return;
    $el.empty();
    new Morris.Bar({
      element: elementId,
      data: [{ label: label, value: value }],
      xkey: "label",
      ykeys: ["value"],
      labels: [yLabel || "Valor"],
      ymax: 1,
      hideHover: "auto",
      resize: true,
      xLabelAngle: 45
    });
  }
  function precisionBuenaFromMetrics(m) {
    if (!m) return 0;
    if (m.precision_buena != null) return Number(m.precision_buena);
    var cm = m.confusion_matrix || {};
    var tn = Number(cm.tn || 0), fn = Number(cm.fn || 0);
    var denom = tn + fn;
    if (denom === 0) return 0;
    return tn / denom;
  }
  function getPrecisionFromResponse(res) {
    // Devuelve { value: Number, usedBalanced: Boolean }
    var out = { value: 0, usedBalanced: false };
    if (!res) return out;

    // --- Preferir SIEMPRE el bloque balanceado, probando varias claves ---
    if (res.balanced_eval) {
      var b = res.balanced_eval;
      if (b.precision_buena != null) return { value: Number(b.precision_buena), usedBalanced: true };
      if (b.precision_cv_buena != null) return { value: Number(b.precision_cv_buena), usedBalanced: true };
      if (b.precision_CV != null) return { value: Number(b.precision_CV), usedBalanced: true };
      // Soportar tanto b.metrics como cálculo directo desde la CM en 'b'
      if (b.metrics) {
        var vb = precisionBuenaFromMetrics(b.metrics);
        if (!isNaN(vb)) return { value: vb, usedBalanced: true };
      } else {
        var vb2 = precisionBuenaFromMetrics(b);
        if (!isNaN(vb2)) return { value: vb2, usedBalanced: true };
      }
    }

    // --- Fallback: métricas no balanceadas ---
    if (res.precision_oof != null) return { value: Number(res.precision_oof), usedBalanced: false };
    if (res.precision != null) return { value: Number(res.precision), usedBalanced: false };
    if (res.precision_cv_buena != null) return { value: Number(res.precision_cv_buena), usedBalanced: false };
    if (res.precision_CV != null) return { value: Number(res.precision_CV), usedBalanced: false };
    if (res.metrics) {
      var v = precisionBuenaFromMetrics(res.metrics || res);
      if (!isNaN(v)) return { value: v, usedBalanced: false };
    }

    return out;
  }

  // ===== Predicción =====
  $('#predictForm').on('submit', function (e) {
    e.preventDefault();
    var f = $('#fuerza').val();
    var v = $('#velocidad').val();

    $('#predictResult').html('<p class="text-info">Calculando…</p>');

    $.getJSON(API_BASE + '/predict', { fuerza: f, velocidad: v })
      .done(function (res) {
        var etiqueta = res.prediccion;
        var probBuena = res.probabilidad.Buena;
        var probMala = res.probabilidad.Mala;

        $('#predictResult').html(
          '<p><strong>Predicción:</strong> ' +
          '<span class="label label-' + (etiqueta === 'Buena' ? 'success' : 'danger') + '">' + etiqueta + '</span></p>' +
          '<p><strong>Probabilidad Buena:</strong> ' + probBuena + '</p>' +
          '<p><strong>Probabilidad Mala:</strong> ' + probMala + '</p>'
        );
      })
      .fail(function () {
        $('#predictResult').html('<p class="text-danger">Error al predecir.</p>');
      });
  });

  // ===== Modal: SOLO la barra del winner (Precisión, clase Buena) =====
  $('#btnMoreInfo').off('click.winner').on('click.winner', function () {
    $('#metricsModal').modal('show');

    $('#metricsModalBody').html('<div id="modalWinnerPrecTroq" style="height:320px;"></div>');

    // botón del footer (si existe)
    $('#modalGoToML').off('click').on('click', function () {
      window.location.href = 'index.php?ruta=ml';
    }).show();

    $.getJSON(API_EVAL + '/tuned-metrics?t=' + Date.now())
      .done(function (res) {
        var modelos = res.modelos_tuneados || {};
        var ganador = res.mejor_modelo_balanceado;
        var info = modelos[ganador] || {};
        var bm = info.balanced_metrics || {};
        var prec0 = precisionBuenaFromMetrics(bm);
        var yLbl = 'Precisión (Buena) — balanceado';
        var label = (ganador || 'Modelo ganador') + ' (eval)';
        renderBar('modalWinnerPrecTroq', label, prec0, yLbl);
      })
      .fail(function () {
        $('#metricsModalBody').html('<p class="text-danger">No se pudieron cargar las métricas.</p>');
      });
  });

  // ===== Modal Fundición: barra del modelo ganador (Precisión, clase Buena) =====
  $('#btnMoreInfoFund').off('click.winnerFund').on('click.winnerFund', function () {
    $('#metricsModalFund').modal('show');

    $('#metricsModalBodyFund').html('<div id="modalWinnerPrecFund" style="height:320px;"></div>');

    var $goFund = $('#modalGoToMLFund');
    if ($goFund.length) {
      $goFund.off('click').on('click', function () {
        window.location.href = 'index.php?ruta=ml_fundicion';
      }).show();
    }

    $.getJSON(API_BASE_FUND + '/fundicion/winner?t=' + Date.now())
      .done(function (res) {
        var modelo = (res && res.modelo) || (res && res.balanced_eval && res.balanced_eval.modelo) || 'Modelo ganador';
        var pr = getPrecisionFromResponse(res);
        var yLbl = pr.usedBalanced ? 'Precisión CV (Buena) — balanceado' : 'Precisión CV (Buena)';
        renderBar('modalWinnerPrecFund', modelo + ' (CV)', pr.value, yLbl);
      })
      .fail(function () {
        $('#metricsModalBodyFund').html('<p class="text-danger">No se pudieron cargar las métricas.</p>');
      });
  });
  // ===== Fundición: Predecir =====
  $('#btnPredecirFundicion').off('click.predFund').on('click.predFund', function () {
    var th = parseInt($('#inpTempHornoFund').val(), 10);
    var tm = parseInt($('#inpTempMaterialFund').val(), 10);
    var $res = $('#fund-pred-resultado');

    if (isNaN(th) || isNaN(tm)) {
      $res.html('<p class="text-warning">Ingresa ambas temperaturas.</p>');
      return;
    }

    $res.html('<p class="text-info">Calculando…</p>');

    $.ajax({
      url: API_BASE_FUND + '/fundicion/predict',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ temp_horno: th, temp_material: tm })
    })
      .done(function (r) {
        var claseRaw = (r && (r.clase || r.prediccion)) || '';
        var clase = (claseRaw + '').toLowerCase();
        var isGood = (clase === 'buena' || clase === '0');
        var probB = r && r.probabilidad && r.probabilidad.Buena;
        var probM = r && r.probabilidad && r.probabilidad.Mala;
        var labelClass = isGood ? 'label-success' : 'label-danger';
        var claseText = isGood ? 'Buena' : 'Mala';

        var html = '<p><strong>Predicción:</strong> ' +
          '<span class="label ' + labelClass + '">' + claseText + '</span></p>';
        if (probB != null && probM != null) {
          html += '<p><strong>Probabilidad Buena:</strong> ' + probB + '<br>' +
            '<strong>Probabilidad Mala:</strong> ' + probM + '</p>';
        }
        $res.html(html);
      })
      .fail(function () {
        $res.html('<p class="text-danger">Error al predecir (fundición). Verifica que el servicio Flask esté activo en ' + API_BASE_FUND + '.</p>');
      });
  });
});