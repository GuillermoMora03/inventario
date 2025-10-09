// vistas/js/ml.js
// -----------------------------------------------------------------------------
// Panel "Machine Learning": SOLO modelo ganador.
//  - Botón #btnWinnerMetrics -> GET /winner (Flask)
//  - Barra de Precisión CV (clase Buena=0)
//  - Matriz de confusión 2×2 (tabla)
//  - Tabla resumen con: Precisión (Buena=0)
//  *No hay AUC-ROC en ningún lugar*
// Requiere: jQuery, Raphael y Morris.js
// -----------------------------------------------------------------------------

(function ($) {
  "use strict";

  var API_BASE = "http://127.0.0.1:5000";
  // Hosts por proceso
  var API_EVAL_TROQ = "http://127.0.0.1:5001"; // troquelado (eval)
  var API_EVAL_FUND = "http://127.0.0.1:5003"; // fundición (eval)
  // (Opcional) Segmento de API para dominios: '' (troquelado) o '/fundicion' (fundición)
  var API_SEGMENT = (window.ML_API_SEGMENT || '');
  // NOTA: EVAL_BASE se define más abajo, luego de la auto-detección del segmento
  var API_EVAL = API_EVAL_TROQ; // valor por defecto; se recalcula tras la autodetección
  var EVAL_BASE = API_EVAL;     // se recalcula tras la autodetección

  // Auto-detecta Fundición por URL si no se definió ML_API_SEGMENT
  // Soporta: ?proceso=fundicion, rutas antiguas ml_fundicion y paths /fundicion
  if (!window.ML_API_SEGMENT) {
    var href = String(window.location.href || '');
    var qs = String(window.location.search || '');
    if (/[?&]proceso=fundicion\b/i.test(qs) || /ml_fundicion|\/fundicion\b/i.test(href)) {
      API_SEGMENT = '/fundicion';
    }
  }
  // Recalcula host y base según segmento final
  API_EVAL = (API_SEGMENT === '/fundicion') ? API_EVAL_FUND : API_EVAL_TROQ;
  EVAL_BASE = API_EVAL + (API_SEGMENT || '');

  // Oculta contenedores LEGACY (Accuracy/Recall/F1) para trabajar SOLO con Precisión
  $(function () {
    $('#modelsTable, #modelsChart, #winnerSummary, #winnerPrec, #nSamples, #winnerParams').hide();
  });

  // Helper: intenta primero con EVAL_BASE y, si no trae estructura válida, reintenta con '/fundicion'
  function fetchEval(path, onDone, onFail) {
    $.getJSON(EVAL_BASE + path)
      .done(function (res) {
        var ok = res && (res.modelos_tuneados || res.balanced_eval || res.metrics || res.balanced_metrics);
        if (ok) {
          if (typeof onDone === 'function') onDone(res);
        } else {
          // fallback explícito a /fundicion si no estamos ya en ese segmento
          if (API_SEGMENT !== '/fundicion') {
            $.getJSON(API_EVAL_FUND + '/fundicion' + path)
              .done(function (res2) { if (typeof onDone === 'function') onDone(res2); })
              .fail(function () { if (typeof onFail === 'function') onFail(); });
          } else {
            if (typeof onFail === 'function') onFail();
          }
        }
      })
      .fail(function () {
        // intento de respaldo directo a /fundicion
        if (API_SEGMENT !== '/fundicion') {
          $.getJSON(API_EVAL_FUND + '/fundicion' + path)
            .done(function (res2) { if (typeof onDone === 'function') onDone(res2); })
            .fail(function () { if (typeof onFail === 'function') onFail(); });
        } else {
          if (typeof onFail === 'function') onFail();
        }
      });
  }

  function fmt(x, d = 4) {
    if (x === null || x === undefined || isNaN(x)) return "-";
    return Number(x).toFixed(d);
  }

  // Convierte a número seguro (NaN -> 0)
  function num(x) {
    var n = Number(x);
    return isNaN(n) ? 0 : n;
  }

  // Obtiene la precisión para la clase Buena=0. Si no viene explícita, la calcula con la CM: TN / (TN + FN)
  function precisionBuenaFromMetrics(m) {
    if (!m) return 0;
    if (m.precision_buena != null) return Number(m.precision_buena);
    var cm = m.confusion_matrix || {};
    var tn = num(cm.tn), fn = num(cm.fn);
    var denom = tn + fn;
    if (denom === 0) return 0;
    return tn / denom;
  }

  // ---- Helpers para legacy (Accuracy, Prec/Rec/F1 para clase Mala=1) ----
  function accuracyFromCM(cm) {
    var tn = num(cm.tn), fp = num(cm.fp), fn = num(cm.fn), tp = num(cm.tp);
    var tot = tn + fp + fn + tp;
    if (tot === 0) return 0;
    return (tn + tp) / tot;
  }
  function prfMalaFromCM(cm) {
    var tn = num(cm.tn), fp = num(cm.fp), fn = num(cm.fn), tp = num(cm.tp);
    var prec = (tp + fp) > 0 ? (tp / (tp + fp)) : 0;
    var rec = (tp + fn) > 0 ? (tp / (tp + fn)) : 0;
    var f1 = (prec + rec) > 0 ? (2 * prec * rec) / (prec + rec) : 0;
    return { precision_mala: prec, recall_mala: rec, f1_mala: f1 };
  }
  function renderLegacySummaryFromMetrics(tableSelector, metrics) {
    var $table = $(tableSelector);
    if (!$table.length) return;
    var m = metrics || {};
    var cm = m.confusion_matrix || {};
    var acc = accuracyFromCM(cm);
    var prf = prfMalaFromCM(cm);
    var row = [
      "<tr>",
      "<td>", fmt(acc), "</td>",
      "<td>", fmt(prf.precision_mala), "</td>",
      "<td>", fmt(prf.recall_mala), "</td>",
      "<td>", fmt(prf.f1_mala), "</td>",
      "</tr>"
    ].join("");
    $table.find('tbody').html(row);
  }

  // ---- Auto-detección de contenedores (fallback para Fundición) ----
  function findWinnerChartId() {
    // Prioridad: balanceado, legado conocido, y por último heurística por id
    if ($('#winnerPrecBal').length) return 'winnerPrecBal';
    if ($('#winnerPrec').length) return 'winnerPrec';
    // Heurística: primer DIV con id que contenga 'winner' y ('prec'|'acc'|'chart')
    var $cand = $('div[id]').filter(function () {
      var id = (this.id || '').toLowerCase();
      return id.indexOf('winner') >= 0 && (id.indexOf('prec') >= 0 || id.indexOf('acc') >= 0 || id.indexOf('chart') >= 0);
    }).first();
    return $cand.length ? $cand.attr('id') : null;
  }
  function findWinnerSummarySelector() {
    if ($('#winnerSummaryBalanced').length) return '#winnerSummaryBalanced';
    if ($('#winnerSummary').length) return '#winnerSummary';
    // Heurística: primer TABLE con id que contenga 'winner' y 'summary'
    var $cand = $('table[id]').filter(function () {
      var id = (this.id || '').toLowerCase();
      return id.indexOf('winner') >= 0 && id.indexOf('summary') >= 0;
    }).first();
    if ($cand.length) return '#' + $cand.attr('id');
    // Otra heurística: primer TABLE cuyo thead contenga Accuracy y F1
    var $cand2 = $('table').filter(function () {
      var txt = ($(this).find('thead').text() || '').toLowerCase();
      return txt.indexOf('accuracy') >= 0 && txt.indexOf('f1') >= 0;
    }).first();
    if ($cand2.length) return '#' + $cand2.attr('id');
    return null;
  }

  // Dibuja una barra genérica para una métrica (por ejemplo: Precisión CV)
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

  // Rellena la tabla de matriz 2×2 (IDs fijos en el HTML)
  function renderConfusionTable(cm) {
    var tn = cm.tn, fp = cm.fp, fn = cm.fn, tp = cm.tp;
    $('#cm_tn').text(tn || 0);
    $('#cm_fp').text(fp || 0);
    $('#cm_fn').text(fn || 0);
    $('#cm_tp').text(tp || 0);
  }
  // Rellena una matriz 2x2 en IDs personalizados (si existen)
  function renderConfusionTableTo(ids, cm) {
    if (!cm) cm = {};
    var tnSel = ids.tn, fpSel = ids.fp, fnSel = ids.fn, tpSel = ids.tp;
    if (tnSel && $(tnSel).length) $(tnSel).text(cm.tn != null ? cm.tn : 0);
    if (fpSel && $(fpSel).length) $(fpSel).text(cm.fp != null ? cm.fp : 0);
    if (fnSel && $(fnSel).length) $(fnSel).text(cm.fn != null ? cm.fn : 0);
    if (tpSel && $(tpSel).length) $(tpSel).text(cm.tp != null ? cm.tp : 0);
  }

  // Rellena la fila de resumen SOLO con Precisión (Buena=0)
  function renderSummary(metrics) {
    var m = metrics || {};
    var prec0 = precisionBuenaFromMetrics(m);
    var row = [
      "<tr>",
      "<td>", fmt(prec0), "</td>",
      "</tr>"
    ].join("");
    $('#winnerSummary tbody').html(row);
  }
  // Rellena una tabla resumen personalizada SOLO con Precisión (Buena=0)
  function renderSummaryTo(tableSelector, metrics) {
    var $table = $(tableSelector);
    if (!$table.length) return;
    var prec0 = precisionBuenaFromMetrics(metrics || {});
    var row = [
      "<tr>",
      "<td>", fmt(prec0), "</td>",
      "</tr>"
    ].join("");
    $table.find('tbody').html(row);
  }

  // Parámetros (bloque <pre>)
  function renderParams(params) {
    $('#winnerParams').text(JSON.stringify(params || {}, null, 2));
  }

  // Tabla comparativa de modelos (si existe #modelsTable tbody)
  function renderModelsTable(modelos) {
    var $tb = $('#modelsTable tbody');
    if (!$tb.length) return; // no existe en el DOM
    var rows = [];
    Object.keys(modelos).forEach(function (nombre) {
      var info = modelos[nombre] || {};
      var m = info.metrics || {};
      var acc = (m.accuracy_CV != null ? m.accuracy_CV : m.accuracy);
      rows.push([
        '<tr>',
        '<td><b>', nombre, '</b></td>',
        '<td>', fmt(acc), '</td>',
        '<td>', fmt(m.precision_mala), '</td>',
        '<td>', fmt(m.recall_mala), '</td>',
        '<td>', fmt(m.f1_mala), '</td>',
        '</tr>'
      ].join(''));
    });
    $tb.html(rows.join(''));
  }

  // Gráfico de barras con Morris para comparar F1 por modelo (si existe #modelsChart)
  function renderModelsChart(modelos) {
    var elId = 'modelsChart';
    var $el = $('#' + elId);
    if (!$el.length) return; // no existe contenedor
    $el.empty();
    var data = Object.keys(modelos).map(function (nombre) {
      var info = modelos[nombre] || {};
      var m = info.metrics || {};
      return { model: nombre, f1: num(m.f1_mala) };
    });
    if (!data.length) return;
    new Morris.Bar({
      element: elId,
      data: data,
      xkey: 'model',
      ykeys: ['f1'],
      labels: ['F1 (Mala)'],
      ymax: 1,
      hideHover: 'auto',
      resize: true
    });
  }

  // Tabla comparativa BALANCEADA (si existe #modelsTableBalanced tbody)
  function renderModelsTableBalanced(modelos) {
    var $tb = $('#modelsTableBalanced tbody');
    if (!$tb.length) return;
    var rows = [];
    Object.keys(modelos).forEach(function (nombre) {
      var info = modelos[nombre] || {};
      var m = info.balanced_metrics || {};
      var prec0 = precisionBuenaFromMetrics(m);
      rows.push([
        '<tr>',
        '<td><b>', nombre, '</b></td>',
        '<td>', fmt(prec0), '</td>',
        '</tr>'
      ].join(''));
    });
    $tb.html(rows.join(''));
  }

  // Gráfico de barras BALANCEADO con Morris (si existe #modelsChartBalanced)
  function renderModelsChartBalanced(modelos) {
    var elId = 'modelsChartBalanced';
    var $el = $('#' + elId);
    if (!$el.length) return;
    $el.empty();
    var data = Object.keys(modelos).map(function (nombre) {
      var info = modelos[nombre] || {};
      var m = info.balanced_metrics || {};
      return { model: nombre, prec: num(precisionBuenaFromMetrics(m)) };
    });
    if (!data.length) return;
    new Morris.Bar({
      element: elId,
      data: data,
      xkey: 'model',
      ykeys: ['prec'],
      labels: ['Precisión (Buena) — Balanceado'],
      ymax: 1,
      hideHover: 'auto',
      resize: true
    });
  }

  // Click: obtener métricas del ganador (SOLO balanceado en la UI)
  if ($('#btnWinnerMetrics').length) {
    $('#btnWinnerMetrics').off('click.winner').on('click.winner', function () {
      // Limpia contenedores (balanceados y legado)
      $('#winnerPrecBal, #winnerPrec').empty();
      $('#winnerSummaryBalanced tbody, #winnerSummary tbody').empty();
      $('#balancedNSamples, #nSamples').text('');
      $('#cm_tn_bal,#cm_fp_bal,#cm_fn_bal,#cm_tp_bal,#cm_tn,#cm_fp,#cm_fn,#cm_tp').text('-');
      $('#winnerParamsBalanced, #winnerParams').text('');

      fetchEval('/tuned-metrics',
        function (res) {
          var modelos = res.modelos_tuneados || {};
          // Si no hay modelos, intentar /winner directamente
          if (!modelos || Object.keys(modelos).length === 0) {
            return fetchEval('/winner',
              function (rw) {
                var bm2 = (rw && (rw.balanced_eval || rw.metrics || rw.balanced_metrics)) || {};
                var ganador2 = rw && (rw.modelo || rw.mejor_modelo_balanceado || rw.mejor_modelo) || 'Modelo ganador';
                var prec02 = precisionBuenaFromMetrics(bm2);
                var cm2 = bm2.confusion_matrix || { tn: 0, fp: 0, fn: 0, tp: 0 };

                // Barras (SOLO balanceado)
                if ($('#winnerPrecBal').length) {
                  renderBar('winnerPrecBal', ganador2 + ' (balanceado)', prec02, 'Precisión (Buena)');
                }

                // Matriz (SOLO balanceado)
                renderConfusionTableTo({ tn: '#cm_tn_bal', fp: '#cm_fp_bal', fn: '#cm_fn_bal', tp: '#cm_tp_bal' }, cm2);

                // Resumen (SOLO balanceado)
                if ($('#winnerSummaryBalanced').length) renderSummaryTo('#winnerSummaryBalanced', bm2);

                // N muestras (SOLO balanceado)
                var nS2 = (rw && rw.n_samples) || (bm2 && bm2.n_samples);
                if ($('#balancedNSamples').length && nS2 != null) $('#balancedNSamples').text(nS2);

                // Parámetros (SOLO balanceado)
                var params2 = (rw && rw.best_params) || {};
                if ($('#winnerParamsBalanced').length) $('#winnerParamsBalanced').text(JSON.stringify(params2, null, 2));
              },
              function () {
                if (window.Swal) Swal.fire('Error', 'No se pudieron obtener las métricas del modelo ganador.', 'error');
                else alert('No se pudieron obtener las métricas del modelo ganador.');
              }
            );
          }

          // Hay modelos
          var ganador = res.mejor_modelo_balanceado || res.mejor_modelo || Object.keys(modelos)[0];
          var info = modelos[ganador] || {};
          var bm = info.balanced_metrics || info.metrics || {};
          var cm = bm.confusion_matrix || { tn: 0, fp: 0, fn: 0, tp: 0 };
          var prec0 = precisionBuenaFromMetrics(bm);

          // Barras (SOLO balanceado)
          if ($('#winnerPrecBal').length) {
            renderBar('winnerPrecBal', (ganador || 'Modelo ganador') + ' (balanceado)', prec0, 'Precisión (Buena)');
          }

          // Matriz (SOLO balanceado)
          renderConfusionTableTo({ tn: '#cm_tn_bal', fp: '#cm_fp_bal', fn: '#cm_fn_bal', tp: '#cm_tp_bal' }, cm);

          // Resumen (SOLO balanceado)
          if ($('#winnerSummaryBalanced').length) renderSummaryTo('#winnerSummaryBalanced', bm);

          // N muestras (SOLO balanceado)
          var nSamples = (info.balanced_n_samples != null ? info.balanced_n_samples : res.n_samples);
          if ($('#balancedNSamples').length && nSamples != null) $('#balancedNSamples').text(nSamples);

          // Parámetros (SOLO balanceado)
          var params = info.balanced_best_params || info.best_params || res.best_params || {};
          if ($('#winnerParamsBalanced').length) $('#winnerParamsBalanced').text(JSON.stringify(params, null, 2));
        },
        function () {
          if (window.Swal) Swal.fire('Error', 'No se pudieron obtener las métricas del modelo ganador.', 'error');
          else alert('No se pudieron obtener las métricas del modelo ganador.');
        }
      );
    });
  }

  // Click: comparar modelos (eval). Requiere contenedores opcionales en el DOM:
  // - #modelsTable > tbody
  // - #modelsChart (div)
  if ($('#btnCompareModels').length) {
    $('#btnCompareModels').off('click.compare').on('click.compare', function () {
      // limpiar contenedores BALANCEADOS y legados si existen
      $('#modelsChartBalanced').empty();
      $('#modelsTableBalanced tbody').empty();
      $('#modelsChart').empty();
      $('#modelsTable tbody').empty();
      if ($('#compareWinnerBalanced').length) { $('#compareWinnerBalanced').text('—'); }
      if ($('#compareWinner').length) { $('#compareWinner').text('—'); }

      fetchEval('/tuned-metrics',
        function (res) {
          var modelos = res.modelos_tuneados || {};
          var ganador = res.mejor_modelo_balanceado || res.mejor_modelo || '-';

          // Render SOLO balanceado
          if ($('#modelsTableBalanced tbody').length) {
            renderModelsTableBalanced(modelos);
          }
          if ($('#modelsChartBalanced').length) {
            renderModelsChartBalanced(modelos);
          }
          if ($('#compareWinnerBalanced').length) {
            $('#compareWinnerBalanced').text('Mejor modelo (balanceado): ' + ganador);
          }
        },
        function () {
          if (window.Swal) Swal.fire('Error', 'No se pudieron obtener las métricas comparativas.', 'error');
          else alert('No se pudieron obtener las métrricas comparativas.');
        }
      );
    });
  }

})(jQuery);