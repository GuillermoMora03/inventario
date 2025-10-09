# ml_troquelado.py
# ------------------------------------------------------------------------------
# API de predicción para el proceso de troquelado usando el modelo ganador
# (Gradient Boosting) dentro de un pipeline: SMOTE (solo en entrenamiento)
# → StandardScaler → Clasificador.
#
# Endpoints:
#   • GET /winner  : Calcula métricas OOF (cross_val_predict, CV=5) sobre dataset balanceado (SMOTE previo) y devuelve también la matriz de confusión.
#   • GET /predict : Predice la etiqueta y probabilidades para una pareja de
#                    valores (FuerzaTroquelado, VelocidadTroquelado) sin escalar.
#
# Decisiones clave de diseño:
#   • Los datos se cargan crudos desde MySQL; el escalado ocurre dentro del
#     pipeline para evitar fuga de información.
#   • SMOTE solo actúa al entrenar (fit); en predicción no interviene.
#   • Se fija GradientBoosting con hiperparámetros validados (GB_PARAMS).
# ------------------------------------------------------------------------------

import os
from dotenv import load_dotenv
from flask import Flask, jsonify, request
from flask_cors import CORS
import mysql.connector
import pandas as pd

from imblearn.pipeline import Pipeline
from imblearn.over_sampling import SMOTE

from sklearn.model_selection import StratifiedKFold, cross_val_predict
from sklearn.metrics import (
    precision_score, confusion_matrix
)
from sklearn.ensemble import GradientBoostingClassifier
from sklearn.preprocessing import StandardScaler

# =========================
# 1) Configuración general
# =========================
load_dotenv()
DB_HOST     = os.getenv("DB_HOST")
DB_USER     = os.getenv("DB_USER")
DB_PASSWORD = os.getenv("DB_PASSWORD")
DB_NAME     = os.getenv("DB_NAME")

# Semilla y esquema de validación cruzada estratificada (CV=5)
RANDOM_STATE = 42
CV = StratifiedKFold(n_splits=5, shuffle=True, random_state=RANDOM_STATE)

# Hiperparámetros validados para el modelo ganador (Gradient Boosting)
GB_PARAMS = dict(
    n_estimators=200,
    max_depth=7,
    learning_rate=0.2,
    random_state=RANDOM_STATE
)

# Escalador unificado (StandardScaler)
def _make_scaler():
    """Devuelve un StandardScaler nuevo (se ajusta dentro del pipeline)."""
    return StandardScaler()

# =========================
# 2) Flask + CORS
# =========================
app = Flask(__name__)
CORS(app, resources={r"/*": {"origins": "*"}}, supports_credentials=False)

# =========================
# 3) Datos (sin escalado)
# =========================
def cargar_datos_bd() -> pd.DataFrame:
    """Lee columnas relevantes desde MySQL. No se escala aquí."""
    cnx = mysql.connector.connect(
        host=DB_HOST, user=DB_USER, password=DB_PASSWORD, database=DB_NAME
    )
    df = pd.read_sql("""
        SELECT FuerzaTroquelado, VelocidadTroquelado, InspeccionVisual
        FROM troquelado;
    """, cnx)
    cnx.close()
    return df

def _binarizar(df: pd.DataFrame):
    """Convierte 'InspeccionVisual' a etiqueta binaria: 1=Mala, 0=Buena; separa X, y."""
    d = df.copy()
    d['Defecto'] = (d['InspeccionVisual'] == "Mala").astype(int)
    X = d[['FuerzaTroquelado', 'VelocidadTroquelado']]
    y = d['Defecto']
    return X, y

def _make_pipeline():
    """Pipeline por defecto con GradientBoosting."""
    return _make_pipeline_clf(GradientBoostingClassifier(**GB_PARAMS))

def _make_pipeline_clf(clf):
    """Pipeline: SMOTE (solo en fit) → StandardScaler → Clasificador."""
    return Pipeline([
        ('smote',  SMOTE(random_state=RANDOM_STATE)),
        ('scaler', _make_scaler()),
        ('clf',    clf)
    ])

def _make_pipeline_no_smote(clf):
    """Pipeline para evaluación balanceada: solo scaler+clf (sin SMOTE)."""
    return Pipeline([
        ('scaler', _make_scaler()),
        ('clf',    clf)
    ])

def _resample_smote(X, y):
    """Genera un dataset balanceado con SMOTE (para evaluación balanceada)."""
    sm = SMOTE(random_state=RANDOM_STATE)
    return sm.fit_resample(X, y)

def _extract_clf_params(best_params: dict) -> dict:
    """Extrae parámetros del clasificador desde un dict con claves 'clf__*'."""
    return {k.split('clf__',1)[1]: v for k, v in best_params.items() if k.startswith('clf__')}

def _tune_and_pick_winner(X, y):
    """
    Balancea con SMOTE (una sola vez), ajusta grids chicos por modelo y
    SELECCIONA el ganador por **Precisión OOF (Buena=0)** usando cross_val_predict
    sobre el dataset balanceado. Ignoramos best_score_ para evitar optimismo.
    """
    from sklearn.linear_model import LogisticRegression
    from sklearn.ensemble import RandomForestClassifier, AdaBoostClassifier, GradientBoostingClassifier
    from sklearn.svm import SVC
    from sklearn.neighbors import KNeighborsClassifier
    from sklearn.naive_bayes import GaussianNB
    from sklearn.model_selection import GridSearchCV

    # 1) Balanceo previo (evitar SMOTE dentro del CV para no duplicarlo)
    Xb, yb = _resample_smote(X, y)

    # 2) Candidatos + grids pequeños (rápidos)
    configs = {
        'Logística':   {'modelo': LogisticRegression(max_iter=1000), 'grid': {'clf__C':[0.1,1,10], 'clf__solver':['liblinear','lbfgs']}},
        'RandomForest':{'modelo': RandomForestClassifier(random_state=RANDOM_STATE), 'grid': {'clf__n_estimators':[100,200], 'clf__max_depth':[None,10]}},
        'SVM (RBF)':   {'modelo': SVC(kernel='rbf', probability=True, random_state=RANDOM_STATE), 'grid': {'clf__C':[0.1,1,10], 'clf__gamma':['scale','auto']}},
        'k-NN':        {'modelo': KNeighborsClassifier(), 'grid': {'clf__n_neighbors':[3,5,7], 'clf__weights':['uniform','distance']}},
        'AdaBoost':    {'modelo': AdaBoostClassifier(random_state=RANDOM_STATE), 'grid': {'clf__n_estimators':[100,200], 'clf__learning_rate':[0.5,1.0]}},
        'GradBoost':   {'modelo': GradientBoostingClassifier(random_state=RANDOM_STATE), 'grid': {'clf__n_estimators':[100,200], 'clf__learning_rate':[0.1,0.2], 'clf__max_depth':[3,5]}},
        'NaiveBayes':  {'modelo': GaussianNB(), 'grid': {}}
    }

    best_name, best_prec, best_clf_class, best_params = None, -1.0, None, None
    for nombre, cfg in configs.items():
        pipe = _make_pipeline_no_smote(cfg['modelo'])
        grid = GridSearchCV(estimator=pipe, param_grid=cfg['grid'], cv=CV, n_jobs=-1)  # sin scoring explícito
        grid.fit(Xb, yb)
        # Métrica REALISTA: precisión OOF (Buena=0) con el mejor estimador encontrado
        y_pred = cross_val_predict(grid.best_estimator_, Xb, yb, cv=CV, method='predict')
        prec0 = round(precision_score(yb, y_pred, pos_label=0, zero_division=0), 4)

        if prec0 > best_prec:
            best_name = nombre
            best_prec = float(prec0)
            best_params = grid.best_params_
            best_clf_class = cfg['modelo'].__class__

    clf_params = _extract_clf_params(best_params or {})
    return best_name, best_prec, best_clf_class, clf_params

def _confusion_payload(y_true, y_pred) -> dict:
    """Construye la matriz de confusión (conteos y normalizada) y desglosa TN/FP/FN/TP."""
    cm = confusion_matrix(y_true, y_pred, labels=[0, 1])
    cmn = cm.astype('float') / cm.sum(axis=1, keepdims=True)
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

# Elegir ganador por Precisión (Buena) con datos balanceados (SMOTE previo)
_BEST_NAME, _BEST_PREC, _BEST_CLF_CLASS, _BEST_CLF_PARAMS = _tune_and_pick_winner(_X_all, _y_all)

# Entrenar modelo FINAL en todos los datos crudos con SMOTE dentro del pipeline
_best_model = _make_pipeline_clf(_BEST_CLF_CLASS(**_BEST_CLF_PARAMS))
_best_model.fit(_X_all, _y_all)

# =========================
# 5) Endpoints
# =========================
@app.route('/winner', methods=['GET'])
def winner_metric():
    """
    Devuelve el modelo ganador por **Precisión (clase Buena=0)**.
    - La SELECCIÓN del ganador se hace con datos balanceados vía SMOTE (previo al CV)
      y precisión OOF (cross_val_predict) en la clase Buena (0).
    - Para MOSTRAR métricas y matriz, evaluamos igualmente en el dataset balanceado,
      usando un pipeline SIN SMOTE dentro del CV.
    """
    df = cargar_datos_bd()
    X, y = _binarizar(df)

    # ===== Evaluación balanceada que se muestra en la UI =====
    # 1) Balancear una sola vez con SMOTE
    Xb, yb = _resample_smote(X, y)

    # 2) Construir estimador del ganador (mismo tipo y params) SIN SMOTE para CV
    est_bal = _make_pipeline_no_smote(_BEST_CLF_CLASS(**_BEST_CLF_PARAMS))

    # 3) Predicciones out-of-fold sobre el dataset balanceado
    y_pred_bal = cross_val_predict(est_bal, Xb, yb, cv=CV, method='predict')

    # 4) Métrica principal: precisión de la clase Buena (0) + matriz balanceada
    prec0_bal = round(precision_score(yb, y_pred_bal, pos_label=0, zero_division=0), 4)
    metrics_bal = {
        'precision_buena': prec0_bal,
        'confusion_matrix': _confusion_payload(yb, y_pred_bal),
        'n_samples': int(len(yb))
    }

    return jsonify({
        'modelo': _BEST_NAME,
        'precision_oof': round(float(_BEST_PREC), 4),  # Ganador por OOF (Buena)
        'metric_label': 'precision_oof_buena',
        'balanced_eval': metrics_bal,
        'best_params': _BEST_CLF_PARAMS
    })

@app.route('/predict', methods=['GET'])
def predict():
    """
    Predicción con el pipeline FINAL del **modelo ganador**.
    Respuesta: etiqueta + probabilidades.
    """
    try:
        f = float(request.args.get('fuerza'))
        v = float(request.args.get('velocidad'))
    except (TypeError, ValueError):
        return jsonify({'error': 'Parámetros inválidos'}), 400

    # predict_proba devuelve [P(clase=0), P(clase=1)] = [P(Buena), P(Mala)]
    p_buena, p_mala = _best_model.predict_proba([[f, v]])[0]

    # --- LÓGICA DE DECISIÓN AJUSTADA PARA ALTA PRECISIÓN EN "BUENAS" ---
    # Para asegurar que las piezas etiquetadas como "Buenas" sean muy confiables,
    # exigimos un alto nivel de probabilidad para esa clase.
    # Podemos ajustar este valor (0.70, 0.80, etc.) según qué tan estricto queramos ser.
    umbral_de_confianza_buena = 0.80

    # Si la probabilidad de ser "Buena" supera nuestro umbral de confianza, la aceptamos.
    # De lo contrario, la clasificamos como "Mala" por seguridad.
    etiqueta = 'Buena' if p_buena >= umbral_de_confianza_buena else 'Mala'
    # --- FIN DE LA LÓGICA DE DECISIÓN ---

    return jsonify({
        'prediccion':   etiqueta,
        'probabilidad': {
            'Buena': round(float(p_buena), 3),
            'Mala':  round(float(p_mala), 3)
        }
    })

# =========================
# 6) Main
# =========================
if __name__ == '__main__':
    app.run(debug=True, port=5000)