

# ml_fundicion.py
# ------------------------------------------------------------------------------
# API de predicción para el proceso de FUNDICIÓN usando el MODELO GANADOR
# (Random Forest por resultados de tu comparativa) dentro de un pipeline:
# StandardScaler → Clasificador. **Sin SMOTE** (ya generaste malas sintéticas).
#
# Endpoints (prefijo /fundicion para no chocar con troquelado):
#   • GET  /fundicion/winner   : Métricas por CV=5 + matriz de confusión + params
#   • POST /fundicion/predict  : Predice etiqueta y probas para (temp_horno, temp_material)
# ------------------------------------------------------------------------------

import os
from dotenv import load_dotenv
from flask import Flask, jsonify, request
from flask_cors import CORS
import mysql.connector
import pandas as pd

from sklearn.pipeline import Pipeline
from sklearn.model_selection import StratifiedKFold, cross_val_predict, cross_val_score
from sklearn.metrics import accuracy_score, precision_score, recall_score, f1_score, confusion_matrix
from sklearn.preprocessing import StandardScaler
from sklearn.ensemble import RandomForestClassifier

# =========================
# 1) Configuración general
# =========================
load_dotenv()
DB_HOST     = os.getenv("DB_HOST", "localhost")
DB_USER     = os.getenv("DB_USER", "root")
DB_PASSWORD = os.getenv("DB_PASSWORD", "")
DB_NAME     = os.getenv("DB_NAME", "inventariopiezas")

RANDOM_STATE = 42
CV = StratifiedKFold(n_splits=5, shuffle=True, random_state=RANDOM_STATE)

# Hiperparámetros del ganador
RF_PARAMS = dict(
    n_estimators=200,
    max_depth=7,
    random_state=RANDOM_STATE
)

# =========================
# 2) Flask + CORS
# =========================
app = Flask(__name__)
CORS(app, resources={r"/fundicion/*": {"origins": "*"}})

# =========================
# 3) Datos (sin escalado)
# =========================

def cargar_datos_bd() -> pd.DataFrame:
    cnx = mysql.connector.connect(
        host=DB_HOST, user=DB_USER, password=DB_PASSWORD, database=DB_NAME
    )
    df = pd.read_sql(
        """
        SELECT temp_horno, temp_material, clase
        FROM fundicion;
        """,
        cnx,
    )
    cnx.close()
    return df


def _binarizar(df: pd.DataFrame):
    d = df.copy()
    # clase: 'buena' / 'mala' (minúsculas o mayúsculas)
    d['Defecto'] = (d['clase'].str.lower() == 'mala').astype(int)
    X = d[['temp_horno', 'temp_material']]
    y = d['Defecto']
    return X, y


def _make_pipeline():
    return Pipeline([
        ('scaler', StandardScaler()),
        ('clf',    RandomForestClassifier(**RF_PARAMS))
    ])


def _confusion_payload(y_true, y_pred) -> dict:
    cm = confusion_matrix(y_true, y_pred, labels=[0, 1])
    row_sums = cm.sum(axis=1, keepdims=True)
    row_sums[row_sums == 0] = 1
    cmn = cm.astype('float') / row_sums
    return {
        'labels': ['Buena (0)', 'Mala (1)'],
        'matrix': cm.tolist(),  # [[tn, fp],[fn, tp]]
        'norm':   [[round(float(cmn[0,0]),4), round(float(cmn[0,1]),4)],
                   [round(float(cmn[1,0]),4), round(float(cmn[1,1]),4)]],
        'tn': int(cm[0,0]), 'fp': int(cm[0,1]),
        'fn': int(cm[1,0]), 'tp': int(cm[1,1])
    }


# ==========================================
# 4) Modelo FINAL (entrenado al iniciar app)
# ==========================================
_df_all = cargar_datos_bd()
_X_all, _y_all = _binarizar(_df_all)

_best_model = _make_pipeline()
_best_model.fit(_X_all, _y_all)


# =========================
# 5) Endpoints
# =========================
@app.route('/fundicion/winner', methods=['GET'])
def winner_metric_fund():
    """Métricas del modelo ganador por CV=5 + matriz de confusión."""
    df = cargar_datos_bd()
    X, y = _binarizar(df)
    pipe = _make_pipeline()

    # Predicciones para métricas de clasificación
    y_pred = cross_val_predict(pipe, X, y, cv=CV, method='predict')

    metrics = {        
        'precision_mala': round(precision_score(y, y_pred, pos_label=1, zero_division=0), 4),                
        'confusion_matrix': _confusion_payload(y, y_pred)
    }

    # Exactitud promedio de CV (para tu gráfica de barra)
    acc_cv = cross_val_score(pipe, X, y, cv=CV, scoring='accuracy').mean()
    metrics['accuracy_CV'] = round(float(acc_cv), 4)

    best_params_es = {
        'Número de árboles':   RF_PARAMS['n_estimators'],
        'Profundidad máxima':  RF_PARAMS['max_depth'],
    }

    return jsonify({
        'modelo':      'RandomForest',
        'metrics':     metrics,
        'best_params': best_params_es
    })


@app.route('/fundicion/predict', methods=['POST'])
def predict_fund():
    """Predicción con el pipeline FINAL de fundición.
    Espera JSON: { "temp_horno": int/float, "temp_material": int/float }
    Devuelve: clase (buena/mala) + probabilidades.
    """
    payload = request.get_json(silent=True) or {}
    try:
        th = float(payload.get('temp_horno'))
        tm = float(payload.get('temp_material'))
    except (TypeError, ValueError):
        return jsonify({'error': 'Parámetros inválidos'}), 400

    proba = None
    if hasattr(_best_model.named_steps['clf'], 'predict_proba'):
        proba = _best_model.predict_proba([[th, tm]])[0]
        p_buena, p_mala = float(proba[0]), float(proba[1])
    else:
        # En caso de que el clasificador no tenga predict_proba
        pred = int(_best_model.predict([[th, tm]])[0])
        p_buena, p_mala = (1.0, 0.0) if pred == 0 else (0.0, 1.0)

    etiqueta = 'mala' if p_mala >= 0.5 else 'buena'

    return jsonify({
        'clase': etiqueta,
        'probabilidad': {
            'Buena': round(p_buena, 4),
            'Mala':  round(p_mala, 4)
        }
    })


# =========================
# 6) Main
# =========================
if __name__ == '__main__':
    port = int(os.getenv('PORT', 5002))
    app.run(debug=True, port=port)