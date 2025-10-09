import pandas as pd

# ======================= CONFIGURACIÓN FÁCIL =======================
# Ajusta los valores en esta sección si es necesario.

# 1. Nombre de tu archivo CSV
nombre_archivo_csv = 'datosFundicion.csv'

# 2. Nombre de la columna que tiene las etiquetas de clasificación
columna_clase = 'clase'

# 3. VALOR exacto que identifica a la clase "Buena"
#    (Puede ser 'Buena', 'buena', 0, etc.)
valor_buena = 'buena'

# 4. Lista de las columnas que quieres analizar
columnas_a_analizar = ['TemperaturaHorno', 'TemperaturaMaterial']
# ===================================================================


# --- Carga y Filtrado de Datos ---
try:
    df = pd.read_csv(nombre_archivo_csv)
    print(f"Archivo '{nombre_archivo_csv}' cargado exitosamente.\n")
except FileNotFoundError:
    print(f"Error: No se pudo encontrar el archivo '{nombre_archivo_csv}'.")
    exit()

try:
    # Filtramos el DataFrame para quedarnos solo con los datos "Buenos"
    df_buenas = df[df[columna_clase] == valor_buena]

    if df_buenas.empty:
        print(f"*** ADVERTENCIA: No se encontraron filas donde la columna '{columna_clase}' sea igual a '{valor_buena}'. ***")
        print("Por favor, verifica los valores en la sección de CONFIGURACIÓN.")
        exit()

except KeyError:
    print(f"*** ERROR: No se encontró la columna '{columna_clase}'. ***")
    print("Por favor, revisa el nombre en la sección de CONFIGURACIÓN.")
    exit()


# --- Análisis Descriptivo ---
print("--- ANÁLISIS DESCRIPTIVO DE LOS DATOS 'BUENOS' ---")

for columna in columnas_a_analizar:
    try:
        print(f"\nEstadísticas para la columna: '{columna}'")
        print("-" * 40)
        # Usamos .describe() para obtener el resumen estadístico
        descripcion = df_buenas[columna].describe()
        print(descripcion)
        print("-" * 40)
    except KeyError:
        print(f"\n*** ADVERTENCIA: La columna '{columna}' no fue encontrada. Se omitirá. ***")

print("\nAnálisis completado.")