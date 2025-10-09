

// vistas/js/ml_fundicion.js
// -----------------------------------------------------------------------------
// Panel "Machine Learning" — FUNDICIÓN (modelo ganador y comparativa)
//  - Botón #btnWinnerMetricsFund   -> GET /fundicion/winner (Flask)
//  - Botón #btnCompareModelsFund   -> GET /fundicion/tuned-metrics (Flask)
//  - Barra de Exactitud CV (Morris)
//  - Matriz de confusión 2×2 (tabla)
//  - Tabla resumen: Accuracy, Precisión(Mala=1), Recall(Mala=1), F1(Mala=1)
// Requiere: jQuery, Raphael y Morris.js
// -----------------------------------------------------------------------------

(function ($) {
    "use strict";

    // Ajusta el puerto si cambiaste el servicio Flask de fundición
    var API_BASE_FUND = "http://127.0.0.1:5003"; // /fundicion/* vive aquí

    function fmt(x, d = 4) {
        if (x === null || x === undefined || isNaN(x)) return "-";
        return Number(x).toFixed(d);
    }

    function num(x) {
        var n = Number(x);
        return isNaN(n) ? 0 : n;
    }

    function renderAccuracyBar(elementId, label, value) {
        var $el = $("#" + elementId);
        if (!$el.length) return;
        $el.empty();
        new Morris.Bar({
            element: elementId,
            data: [{ label: label, value: value }],
            xkey: "label",
            ykeys: ["value"],
            labels: ["Exactitud CV"],
            hideHover: "auto",
            resize: true,
            xLabelAngle: 45,
            ymax: 1
        });
    }

    // --- Renders específicos de Fundición (IDs propios del módulo) ---
    function renderConfusionTableFund(cm) {
        var tn = cm.tn, fp = cm.fp, fn = cm.fn, tp = cm.tp;
        $('#cm_tn_fund').text(tn || 0);
        $('#cm_fp_fund').text(fp || 0);
        $('#cm_fn_fund').text(fn || 0);
        $('#cm_tp_fund').text(tp || 0);
    }

    function renderSummaryFund(metrics) {
        var m = metrics || {};
        var acc = (m.accuracy_CV != null ? m.accuracy_CV : m.accuracy);
        var row = [
            '<tr>',
            '<td>', fmt(acc), '</td>',
            '<td>', fmt(m.precision_mala), '</td>',
            '<td>', fmt(m.recall_mala), '</td>',
            '<td>', fmt(m.f1_mala), '</td>',
            '</tr>'
        ].join('');
        $('#winnerSummaryFund tbody').html(row);
    }

    function renderParamsFund(params) {
        $('#winnerParamsFund').text(JSON.stringify(params || {}, null, 2));
    }

    function renderModelsTableFund(modelos) {
        var $tb = $('#modelsTableFund tbody');
        if (!$tb.length) return;
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

    function renderModelsChartFund(modelos) {
        var elId = 'modelsChartFund';
        var $el = $('#' + elId);
        if (!$el.length) return;
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

    // --- Handlers ---
    if ($('#btnWinnerMetricsFund').length) {
        $('#btnWinnerMetricsFund').off('click.winnerFund').on('click.winnerFund', function () {
            // limpiar contenedores
            $('#winnerAccFund').empty();
            $('#cm_tn_fund,#cm_fp_fund,#cm_fn_fund,#cm_tp_fund').text('-');
            $('#winnerSummaryFund tbody').empty();
            $('#winnerParamsFund').text('');

            $.getJSON(API_BASE_FUND + '/fundicion/winner')
                .done(function (res) {
                    var m = res.metrics || {};
                    var acc = (m.accuracy_CV != null ? m.accuracy_CV : m.accuracy) || 0;
                    renderAccuracyBar('winnerAccFund', res.modelo || 'Modelo ganador', acc);
                    renderConfusionTableFund(m.confusion_matrix || { tn: 0, fp: 0, fn: 0, tp: 0 });
                    renderSummaryFund(m);
                    renderParamsFund(res.best_params || {});
                })
                .fail(function () {
                    if (window.Swal) {
                        Swal.fire('Error', 'No se pudieron obtener las métricas del modelo ganador (fundición).', 'error');
                    } else {
                        alert('No se pudieron obtener las métrricas del modelo ganador (fundición).');
                    }
                });
        });
    }

    if ($('#btnCompareModelsFund').length) {
        $('#btnCompareModelsFund').off('click.compareFund').on('click.compareFund', function () {
            $('#modelsChartFund').empty();
            $('#modelsTableFund tbody').empty();

            $.getJSON(API_BASE_FUND + '/fundicion/tuned-metrics')
                .done(function (res) {
                    var modelos = res.modelos_tuneados || {};
                    renderModelsTableFund(modelos);
                    renderModelsChartFund(modelos);
                    if ($('#compareWinnerFund').length) {
                        $('#compareWinnerFund').text('Mejor modelo: ' + (res.mejor_modelo || '-'));
                    }
                })
                .fail(function () {
                    if (window.Swal) {
                        Swal.fire('Error', 'No se pudieron obtener las métricas comparativas (fundición).', 'error');
                    } else {
                        alert('No se pudieron obtener las métricas comparativas (fundición).');
                    }
                });
        });
    }

})(jQuery);