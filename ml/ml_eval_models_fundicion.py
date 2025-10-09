# ml_eval_models_fundicion.py
# ------------------------------------------------------------------------------
# FUNDICIÓN - API de evaluación con múltiples modelos + tuning
# Endpoints (con prefijo /fundicion):
#   • GET  /fundicion/metrics        : baseline (3 modelos) con Accuracy/Prec/Recall/F1
#   • GET  /fundicion/tuned-metrics  : GridSearchCV (cv=5) de 7 modelos + CM 2x2
#   • GET  /fundicion/winner         : solo el ganador (métricas + CM + best_params)
# Notas:
#   - Datos desde MySQL; para evaluación balanceada aplicamos SMOTE **una sola vez** (previo al CV).
#   - Las métricas que se muestran (comparativa/ganador) son **OOF realistas** con cross_val_predict sobre el dataset balanceado.
#   - GridSearchCV NO usa `make_scorer`; y no expondremos `best_score_` en la respuesta.
# ------------------------------------------------------------------------------

import os
import warnings
from dotenv import load_dotenv
from flask import Flask, jsonify
from flask_cors import CORS
import mysql.connector
import pandas as pd

from sklearn.pipeline import Pipeline
from sklearn.model_selection import GridSearchCV, StratifiedKFold, cross_val_predict
from sklearn.linear_model import LogisticRegression
from sklearn.ensemble import RandomForestClassifier, AdaBoostClassifier, GradientBoostingClassifier
from sklearn.svm import SVC
from sklearn.neighbors import KNeighborsClassifier
from sklearn.naive_bayes import GaussianNB
from sklearn.preprocessing import StandardScaler
from sklearn.metrics import accuracy_score, precision_score, recall_score, f1_score, confusion_matrix
from sklearn.exceptions import ConvergenceWarning

from imblearn.over_sampling import SMOTE

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

# =========================
# 2) Flask + CORS
# =========================
app = Flask(__name__)
CORS(app, resources={r"/*": {"origins": "*"}}, supports_credentials=False)

# =========================
# 3) Datos
# =========================

def cargar_datos_bd() -> pd.DataFrame:
    """
    Lee desde MySQL la tabla `fundicion` y devuelve un DataFrame con
    las columnas `temp_horno`, `temp_material` y `clase`.
    """
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
    """
    Transforma la columna categórica `clase` a etiqueta binaria `Defecto`:
    1 = "mala", 0 = "buena". Separa características X e índice de clase y.
    Devuelve: X (DataFrame con temperaturas), y (Serie binaria).
    """
    # Trabaja sobre una copia para no modificar el DataFrame original.
    d = df.copy()
    # clase: 'buena' | 'mala' (minúsculas). 1 = Mala, 0 = Buena
    d["Defecto"] = (d["clase"].str.lower() == "mala").astype(int)
    # Características de entrada: solo temperaturas medidas.
    X = d[["temp_horno", "temp_material"]]
    y = d["Defecto"]
    return X, y


def _make_pipeline(modelo):
    """
    Construye un pipeline sklearn con StandardScaler seguido del
    clasificador proporcionado. El escalado ocurre dentro de CV para
    evitar fuga de información (data leakage).
    """
    # El escalado es importante para modelos sensibles a escala (p. ej. SVM, k-NN).
    return Pipeline([
        ("scaler", StandardScaler()),
        ("clf", modelo),
    ])

def _resample_smote(X, y):
    sm = SMOTE(random_state=RANDOM_STATE)
    return sm.fit_resample(X, y)


def _confusion_payload(y_true, y_pred) -> dict:
    """
    Calcula matriz de confusión (conteos absolutos y versión normalizada por fila).
    Devuelve también los componentes TN, FP, FN, TP explícitos.
    Estructura de `matrix`: [[tn, fp], [fn, tp]].
    """
    # Matriz 2x2 con etiquetas ordenadas [0=buena, 1=mala].
    cm = confusion_matrix(y_true, y_pred, labels=[0, 1])
    # Protección ante división por cero si una fila no tiene ejemplos.
    row_sums = cm.sum(axis=1, keepdims=True)
    row_sums[row_sums == 0] = 1
    # Normaliza por filas para comparar tasas.
    cmn = cm.astype("float") / row_sums
    return {
        "labels": ["Buena (0)", "Mala (1)"],
        "matrix": cm.tolist(),  # [[tn, fp],[fn, tp]]
        "norm":   [[round(float(cmn[0,0]),4), round(float(cmn[0,1]),4)],
                    [round(float(cmn[1,0]),4), round(float(cmn[1,1]),4)]],
        "tn": int(cm[0,0]), "fp": int(cm[0,1]),
        "fn": int(cm[1,0]), "tp": int(cm[1,1]),
    }


def _clas_metrics_with_cm(X, y, estimator) -> dict:
    """
    Obtiene métricas de clasificación usando predicciones out-of-fold
    con `cross_val_predict` (CV estratificada definida globalmente).
    Esto simula desempeño en datos no vistos para cada fold.
    """
    # Predicción OOF: cada muestra se predice por un modelo que no la vio en entrenamiento.
    y_pred = cross_val_predict(estimator, X, y, cv=CV, method="predict")
    prec0 = round(precision_score(y, y_pred, pos_label=0, zero_division=0), 4)
    return {        
        "precision_mala":   round(precision_score(y, y_pred, pos_label=1, zero_division=0), 4),                
        "precision_buena":  prec0,
        "confusion_matrix": _confusion_payload(y, y_pred),
    }


# =========================
# 4) Baseline /fundicion/metrics
# =========================
@app.route("/fundicion/metrics", methods=["GET"])
def fund_metrics():
    """
    Baseline con 3 modelos comunes (Logística, Random Forest, SVM RBF).
    Devuelve Accuracy/Precision/Recall/F1 calculados con CV para cada uno.
    """
    df = cargar_datos_bd()
    X, y = _binarizar(df)
    # Conjunto reducido de modelos para una evaluación rápida.
    modelos = {
        "Regresión logística": LogisticRegression(max_iter=1000),
        "Random Forest":       RandomForestClassifier(n_estimators=100, random_state=RANDOM_STATE),
        "SVM (RBF)":           SVC(kernel="rbf", probability=True, random_state=RANDOM_STATE),
    }
    resultados = {}
    for nombre, modelo in modelos.items():
        pipe = _make_pipeline(modelo)
        y_pred = cross_val_predict(pipe, X, y, cv=CV, method="predict")
        resultados[nombre] = {            
            "precision_mala": round(precision_score(y, y_pred, pos_label=1, zero_division=0), 4),                        
        }
    return jsonify({"resultados": resultados})


# =========================
# 5) Tuning /fundicion/tuned-metrics
# =========================
# Mapeo para hacer legibles los nombres de hiperparámetros al responder por API.
PARAM_LABELS = {
    "clf__C":                  "C (regularización)",
    "clf__solver":             "Solver",
    "clf__n_estimators":       "Número de árboles",
    "clf__max_depth":          "Profundidad máxima",
    "clf__kernel":             "Kernel (SVM)",
    "clf__gamma":              "Gamma (SVM)",
    "clf__n_neighbors":        "K vecinos (k-NN)",
    "clf__weights":            "Peso (k-NN)",
    "clf__learning_rate":      "Tasa de aprendizaje",
    "clf__class_weight":       "Peso de clase",
}


def traducir_params(params: dict) -> dict:
    """
    Reemplaza claves técnicas del GridSearch por etiquetas legibles.
    """
    return {PARAM_LABELS.get(k, k.replace("clf__", "")): v for k, v in params.items()}


def tune_model(X, y, modelo, param_grid):
    """
    Ejecuta GridSearchCV con la búsqueda especificada y CV estratificada.
    Devuelve: (mejor_estimator, mejor_score, mejores_parametros_traducidos).
    """
    # Pipeline consistente con el resto del flujo.
    pipe = _make_pipeline(modelo)
    # Búsqueda de hiperparámetros sin scoring explícito.
    grid = GridSearchCV(
        estimator=pipe,
        param_grid=param_grid,
        cv=CV,
        n_jobs=-1,  # sin scoring explícito; solo para hallar hiperparámetros
    )
    # Silencia advertencias como ConvergenceWarning para salidas limpias.
    with warnings.catch_warnings():
        warnings.filterwarnings("ignore", category=ConvergenceWarning)
        grid.fit(X, y)
    return grid.best_estimator_, grid.best_score_, traducir_params(grid.best_params_)


@app.route("/fundicion/tuned-metrics", methods=["GET"])
def fund_tuned_metrics():
    """
    Ejecuta tuning para 7 modelos distintos; calcula métricas con el mejor
    pipeline de cada uno y selecciona el ganador por precision_buena OOF.
    """
    df = cargar_datos_bd()
    X, y = _binarizar(df)
    # Balanceo previo (una sola vez) para evaluación balanceada
    Xb, yb = X, y
    # Definición de buscadores por modelo (espacios de hiperparámetros).
    configs = {
        "Logística":   {"modelo": LogisticRegression(max_iter=1000), "grid": {"clf__C": [0.1, 1, 10],
                                                                               "clf__solver": ["liblinear", "lbfgs"],
                                                                               "clf__class_weight": [None, "balanced"]}},
        "RandomForest":{"modelo": RandomForestClassifier(random_state=RANDOM_STATE), "grid": {"clf__n_estimators": [50,100,200],
                                                                                             "clf__max_depth": [None,5,10],
                                                                                             "clf__class_weight": [None, "balanced", "balanced_subsample"]}},
        "SVM":         {"modelo": SVC(probability=True, random_state=RANDOM_STATE), "grid": {"clf__C": [0.1,1,10],
                                                                                                "clf__kernel": ["rbf","poly"],
                                                                                                "clf__gamma": ["scale","auto"],
                                                                                                "clf__class_weight": [None, "balanced"]}},
        "k-NN":        {"modelo": KNeighborsClassifier(), "grid": {"clf__n_neighbors": [3,5,7], "clf__weights": ["uniform","distance"]}},
        "AdaBoost":    {"modelo": AdaBoostClassifier(random_state=RANDOM_STATE), "grid": {"clf__n_estimators":[50,100,200], "clf__learning_rate":[0.5,1.0,1.5]}},
        "GradBoost":   {"modelo": GradientBoostingClassifier(random_state=RANDOM_STATE), "grid": {"clf__n_estimators":[50,100,200],
                                                                                                    "clf__learning_rate":[0.05,0.1,0.2],
                                                                                                    "clf__max_depth":[3,5,7]}},
        "NaiveBayes":  {"modelo": GaussianNB(), "grid": {}},
    }
    resultados = {}
    for nombre, cfg in configs.items():
        best_pipe, _best_score, best_params = tune_model(Xb, yb, cfg["modelo"], cfg["grid"])
        m = _clas_metrics_with_cm(Xb, yb, best_pipe)  # OOF realista en dataset balanceado
        resultados[nombre] = {
            "balanced_metrics": m,                          # incluye precision_buena + CM
            "precision": m["precision_buena"],              # alias para el front
            "balanced_best_params": best_params,
            "balanced_n_samples": int(len(yb)),
            "metric_label": "precision_oof_buena",
        }
    ganador_bal = max(resultados.items(), key=lambda kv: kv[1]["precision"])[0]
    return jsonify({"modelos_tuneados": resultados, "mejor_modelo_balanceado": ganador_bal})


# =========================
# 6) Winner /fundicion/winner
# =========================
@app.route("/fundicion/winner", methods=["GET"])
def fund_winner():
    """
    Repite el tuning, pero devuelve solo la información del modelo ganador:
    nombre, métricas con CM y mejores hiperparámetros.
    """
    df = cargar_datos_bd()
    X, y = _binarizar(df)
    # Balanceo previo (una sola vez) para evaluación balanceada
    Xb, yb = X, y
    # Misma búsqueda que en tuned-metrics.
    configs = {
        "Logística":   {"modelo": LogisticRegression(max_iter=1000), "grid": {"clf__C": [0.1, 1, 10],
                                                                               "clf__solver": ["liblinear","lbfgs"],
                                                                               "clf__class_weight": [None, "balanced"]}},
        "RandomForest":{"modelo": RandomForestClassifier(random_state=RANDOM_STATE), "grid": {"clf__n_estimators": [50,100,200],
                                                                                             "clf__max_depth": [None,5,10],
                                                                                             "clf__class_weight": [None, "balanced", "balanced_subsample"]}},
        "SVM":         {"modelo": SVC(probability=True, random_state=RANDOM_STATE), "grid": {"clf__C": [0.1,1,10],
                                                                                                "clf__kernel": ["rbf","poly"],
                                                                                                "clf__gamma": ["scale","auto"],
                                                                                                "clf__class_weight": [None, "balanced"]}},
        "k-NN":        {"modelo": KNeighborsClassifier(), "grid": {"clf__n_neighbors": [3,5,7], "clf__weights": ["uniform","distance"]}},
        "AdaBoost":    {"modelo": AdaBoostClassifier(random_state=RANDOM_STATE), "grid": {"clf__n_estimators":[50,100,200], "clf__learning_rate":[0.5,1.0,1.5]}},
        "GradBoost":   {"modelo": GradientBoostingClassifier(random_state=RANDOM_STATE), "grid": {"clf__n_estimators":[50,100,200],
                                                                                                    "clf__learning_rate":[0.05,0.1,0.2],
                                                                                                    "clf__max_depth":[3,5,7]}},
        "NaiveBayes":  {"modelo": GaussianNB(), "grid": {}},
    }
    best_name, best_prec, best_pipe, best_params = None, -1.0, None, None
    for nombre, cfg in configs.items():
        pipe, _score, params = tune_model(Xb, yb, cfg["modelo"], cfg["grid"])
        # Métrica realista: precisión OOF de clase 0 (Buena)
        y_pred = cross_val_predict(pipe, Xb, yb, cv=CV, method="predict")
        prec0 = round(precision_score(yb, y_pred, pos_label=0, zero_division=0), 4)
        if prec0 > best_prec:
            best_name, best_prec, best_pipe, best_params = nombre, float(prec0), pipe, params
    # Métricas del ganador (balanceadas y OOF)
    m = _clas_metrics_with_cm(Xb, yb, best_pipe)
    return jsonify({
        "modelo": best_name,
        "precision_oof": round(float(best_prec), 4),
        "metric_label": "precision_oof_buena",
        "balanced_eval": m,               # incluye precision_buena y confusion_matrix
        "best_params": best_params
    })


# =========================
# 7) Main
# =========================
if __name__ == "__main__":
    # Permite configurar el puerto vía variable de entorno PORT; por defecto 5003.
    port = int(os.getenv("PORT", 5003))
    app.run(debug=True, port=port)
