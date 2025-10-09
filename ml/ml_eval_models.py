# ml_eval_models.py
# ------------------------------------------------------------------------------
# TROQUELADO - API de evaluación con múltiples modelos + tuning
# Endpoints:
#   • GET /tuned-metrics  : Evalúa varios modelos con OOF (cross_val_predict) sobre dataset balanceado (SMOTE previo) + CM 2x2.
#   • GET /winner         : Devuelve el ganador balanceado (OOF) + CM + best_params.
# Notas:
#   - Datos CRUDOS desde MySQL; balanceamos una sola vez (SMOTE) *fuera* del CV.
#   - TODAS las métricas reportadas al frontend son OOF (realistas) y NO usamos best_score_ para mostrarlas.
#   - No se usa make_scorer en el tuning; las métricas que mostramos son OOF (precision_buena) calculadas con cross_val_predict.
#   - Tampoco se pasa parámetro "scoring" a GridSearchCV; usamos el comportamiento por defecto del estimador.
# ------------------------------------------------------------------------------

import os
import warnings
from dotenv import load_dotenv
from flask import Flask, jsonify
from flask_cors import CORS
import mysql.connector
import pandas as pd
import matplotlib
matplotlib.use('Agg') # <-- ¡Esta es la línea mágica!
import matplotlib.pyplot as plt


from imblearn.pipeline import Pipeline
from imblearn.over_sampling import SMOTE

from sklearn.model_selection import GridSearchCV, StratifiedKFold, cross_val_predict
from sklearn.linear_model import LogisticRegression
from sklearn.ensemble import RandomForestClassifier, AdaBoostClassifier, GradientBoostingClassifier
from sklearn.svm import SVC
from sklearn.neighbors import KNeighborsClassifier
from sklearn.naive_bayes import GaussianNB
from sklearn.preprocessing import StandardScaler
from sklearn.metrics import precision_score, confusion_matrix
from sklearn.exceptions import ConvergenceWarning

# =========================
# 1) Configuración general
# =========================
load_dotenv()
DB_HOST     = os.getenv("DB_HOST")
DB_USER     = os.getenv("DB_USER")
DB_PASSWORD = os.getenv("DB_PASSWORD")
DB_NAME     = os.getenv("DB_NAME")

RANDOM_STATE = 42
CV = StratifiedKFold(n_splits=5, shuffle=True, random_state=RANDOM_STATE)

def _make_scaler():
    return StandardScaler()

# =========================
# 2) Flask + CORS
# =========================
app = Flask(__name__)
CORS(app, resources={r"/*": {"origins": "*"}}, supports_credentials=False)

# =========================
# 3) Datos
# =========================
def cargar_datos_bd() -> pd.DataFrame:
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
    d = df.copy()
    d['Defecto'] = (d['InspeccionVisual'] == "Mala").astype(int)
    X = d[['FuerzaTroquelado', 'VelocidadTroquelado']]
    y = d['Defecto']
    return X, y

def _make_pipeline_no_smote(modelo):
    """Pipeline sin SMOTE (para evaluar en dataset ya re-muestreado)."""
    return Pipeline([
        ('scaler', _make_scaler()),
        ('clf',    modelo)
    ])


def _resample_smote(X, y):
    """Genera un dataset balanceado con SMOTE (para evaluación balanceada)."""
    sm = SMOTE(random_state=RANDOM_STATE)
    return sm.fit_resample(X, y)

def _confusion_payload(y_true, y_pred) -> dict:
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

def _clas_metrics_with_cm(X, y, estimator) -> dict:
    y_pred = cross_val_predict(estimator, X, y, cv=CV, method='predict')
    return {
        'precision_buena': round(precision_score(y, y_pred, pos_label=0, zero_division=0), 4),
        'confusion_matrix': _confusion_payload(y, y_pred)
    }

# =========================
# 5) Tuning /tuned-metrics
# =========================
PARAM_LABELS = {
    'clf__C':                  'C (regularización)',
    'clf__solver':             'Solver',
    'clf__n_estimators':       'Número de árboles',
    'clf__max_depth':          'Profundidad máxima',
    'clf__kernel':             'Kernel (SVM)',
    'clf__gamma':              'Gamma (SVM)',
    'clf__n_neighbors':        'K vecinos (k-NN)',
    'clf__weights':            'Peso (k-NN)',
    'clf__learning_rate':      'Tasa de aprendizaje',
    'clf__class_weight':       'Peso de clase'
}
def traducir_params(params: dict) -> dict:
    return {PARAM_LABELS.get(k, k.replace('clf__', '')): v for k, v in params.items()}

def tune_model_no_smote(X, y, modelo, param_grid):
    """Ajusta hiperparámetros para las métricas mostradas."""
    pipe = _make_pipeline_no_smote(modelo)
    grid = GridSearchCV(
        estimator=pipe,
        param_grid=param_grid,
        cv=CV,
        n_jobs=-1
    )
    with warnings.catch_warnings():
        warnings.filterwarnings("ignore", category=ConvergenceWarning)
        grid.fit(X, y)
    # Solo devolvemos el mejor estimador y los params traducidos; ignoramos best_score_
    return grid.best_estimator_, traducir_params(grid.best_params_)

@app.route('/tuned-metrics', methods=['GET'])
def get_tuned_metrics():
    df = cargar_datos_bd()
    X, y = _binarizar(df)
    configs = {
        'Logística':  {'modelo': LogisticRegression(max_iter=1000), 'grid': {'clf__C': [0.1, 1, 10], 'clf__solver': ['liblinear','lbfgs'], 'clf__class_weight': [None, 'balanced']}},
        'RandomForest':{'modelo': RandomForestClassifier(random_state=RANDOM_STATE), 'grid': {'clf__n_estimators': [50,100,200], 'clf__max_depth': [None,5,10], 'clf__class_weight': [None, 'balanced', 'balanced_subsample']}},
        'SVM':         {'modelo': SVC(probability=True, random_state=RANDOM_STATE), 'grid': {'clf__C': [0.1,1,10], 'clf__kernel': ['rbf','poly'], 'clf__gamma': ['scale','auto'], 'clf__class_weight': [None, 'balanced']}},
        'k-NN':        {'modelo': KNeighborsClassifier(), 'grid': {'clf__n_neighbors': [3,5,7], 'clf__weights': ['uniform','distance']}},
        'AdaBoost':    {'modelo': AdaBoostClassifier(random_state=RANDOM_STATE), 'grid': {'clf__n_estimators':[50,100,200], 'clf__learning_rate':[0.5,1.0,1.5]}},
        'GradBoost':   {'modelo': GradientBoostingClassifier(random_state=RANDOM_STATE), 'grid': {'clf__n_estimators':[50,100,200],'clf__learning_rate':[0.05,0.1,0.2],'clf__max_depth':[3,5,7]}},
        'NaiveBayes':  {'modelo': GaussianNB(), 'grid': {}}
    }

    # 1. Dataset balanceado con SMOTE (como ya lo tienes)
    Xb, yb = _resample_smote(X, y)

    # =================== INICIO DEL EXPERIMENTO OFFSET ===================
    # Creamos un DataFrame para manipularlo y graficarlo fácilmente
    Xb_df = pd.DataFrame(Xb, columns=['FuerzaTroquelado', 'VelocidadTroquelado'])
    yb_s = pd.Series(yb, name='Defecto')

    # Definimos el offset. Puedes experimentar cambiando este valor.
    offset_fuerza = 13  # Un valor grande para que sea muy visible en la gráfica

    # Aplicamos el offset SOLO a las piezas "Malas" (Defecto == 1)
    indices_malas = yb_s[yb_s == 1].index
    Xb_df.loc[indices_malas, 'FuerzaTroquelado'] += offset_fuerza
    
    # Convertimos de nuevo a arrays de numpy para dárselo al modelo
    Xb_modificado = Xb_df.values
    # =================== FIN DEL EXPERIMENTO OFFSET =====================

    resultados = {}
    for nombre, cfg in configs.items():
        # ¡IMPORTANTE! Usamos los datos modificados (Xb_modificado) para el tuning y la evaluación
        est, params = tune_model_no_smote(Xb_modificado, yb, cfg['modelo'], cfg['grid'])
        m = _clas_metrics_with_cm(Xb_modificado, yb, est)
        resultados[nombre] = {
            'balanced_metrics': m,
            'precision': m['precision_buena'],
            'balanced_best_params': params,
            'balanced_n_samples': int(len(yb)),
            'metric_label': 'precision_oof_buena'
        }

    ganador_bal = max(resultados.items(), key=lambda kv: kv[1]['precision'])[0]
    return jsonify({'modelos_tuneados': resultados, 'mejor_modelo_balanceado': ganador_bal})


# =========================
# 6) Winner /winner
# =========================
@app.route('/winner', methods=['GET'])
def get_winner():
    df = cargar_datos_bd()
    X, y = _binarizar(df)
    configs = {
        'Logística':   {'modelo': LogisticRegression(max_iter=1000), 'grid': {'clf__C': [0.1, 1, 10],
                                                                             'clf__solver': ['liblinear','lbfgs'],
                                                                             'clf__class_weight': [None, 'balanced']}},
        'RandomForest':{'modelo': RandomForestClassifier(random_state=RANDOM_STATE), 'grid': {'clf__n_estimators': [50,100,200],
                                                                                           'clf__max_depth': [None,5,10],
                                                                                           'clf__class_weight': [None, 'balanced', 'balanced_subsample']}},
        'SVM':         {'modelo': SVC(probability=True, random_state=RANDOM_STATE), 'grid': {'clf__C': [0.1,1,10],
                                                                                            'clf__kernel': ['rbf','poly'],
                                                                                            'clf__gamma': ['scale','auto'],
                                                                                            'clf__class_weight': [None, 'balanced']}},
        'k-NN':        {'modelo': KNeighborsClassifier(), 'grid': {'clf__n_neighbors': [3,5,7], 'clf__weights': ['uniform','distance']}},
        'AdaBoost':    {'modelo': AdaBoostClassifier(random_state=RANDOM_STATE), 'grid': {'clf__n_estimators':[50,100,200], 'clf__learning_rate':[0.5,1.0,1.5]}},
        'GradBoost':   {'modelo': GradientBoostingClassifier(random_state=RANDOM_STATE), 'grid': {'clf__n_estimators':[50,100,200],'clf__learning_rate':[0.05,0.1,0.2],'clf__max_depth':[3,5,7]}},
        'NaiveBayes':  {'modelo': GaussianNB(), 'grid': {}}
    }

    # Balancear una vez el dataset completo
    Xb, yb = _resample_smote(X, y)

    best_name, best_prec, best_est, best_params = None, -1.0, None, None
    for nombre, cfg in configs.items():
        est, params = tune_model_no_smote(Xb, yb, cfg['modelo'], cfg['grid'])
        m_tmp = _clas_metrics_with_cm(Xb, yb, est)
        prec = m_tmp['precision_buena']
        if prec > best_prec:
            best_name, best_prec, best_est, best_params = nombre, prec, est, params

    m = _clas_metrics_with_cm(Xb, yb, best_est)

    return jsonify({
        'balanced_eval': {
            'modelo': best_name,
            'metrics': m,
            'precision': round(float(best_prec), 4),   # misma métrica que la comparativa
            'metric_label': 'precision_oof_buena',
            'best_params': best_params,
            'n_samples': int(len(yb))
        }
    })
    
# =========================
# 7) Main
# =========================
if __name__ == '__main__':
    app.run(debug=True, port=5001)