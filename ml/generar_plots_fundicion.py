import pandas as pd
import matplotlib.pyplot as plt

# ======================= CONFIGURACIÓN FÁCIL =======================

# 1. CAMBIO: Apuntamos al nuevo archivo con los datos realistas
nombre_archivo_csv = 'fundicion_balanceado.csv'

# 2. Nombre de la columna que tiene las etiquetas "Buena" y "Mala"
columna_clase = 'clase'

# 3. VALORES exactos para las clases "Buena" y "Mala"
valor_buena = 'buena'
valor_mala  = 'mala'

# 4. Nombres de las columnas de temperatura
columna_horno = 'TemperaturaHorno'
columna_material = 'TemperaturaMaterial'
# ===================================================================


# --- Carga y Diagnóstico de Datos ---
try:
    df = pd.read_csv(nombre_archivo_csv)
    print(f"Archivo '{nombre_archivo_csv}' cargado exitosamente.")
except FileNotFoundError:
    print(f"Error: No se pudo encontrar el archivo '{nombre_archivo_csv}'.")
    exit()

print("\nValores únicos en la columna de clasificación:", df[columna_clase].unique())
print("-" * 30)


# --- Procesamiento y Graficación ---
try:
    # Separar los datos usando los valores de la configuración
    buenos = df[df[columna_clase] == valor_buena]
    malos = df[df[columna_clase] == valor_mala]
    
    print(f"Se encontraron {len(buenos)} registros 'Buenos' y {len(malos)} registros 'Malos'.\n")

    # --- Gráfico 1: Temperatura del Horno ---
    fig1, ax1 = plt.subplots(figsize=(8, 6))
    ax1.set_title('Distribución de Temperatura del Horno')
    ax1.set_ylabel('Temperatura (°C)')
    bplot1 = ax1.boxplot([buenos[columna_horno].dropna(), malos[columna_horno].dropna()],
                         vert=True, patch_artist=True, labels=['Buenas', 'Malas'])
    colors = ['#90EE90', '#FFB6C1']
    for patch, color in zip(bplot1['boxes'], colors):
        patch.set_facecolor(color)
    ax1.yaxis.grid(True, linestyle='--', color='grey', alpha=0.7)
    nombre_grafica_horno = 'boxplot_temp_horno.png'
    plt.savefig(nombre_grafica_horno)
    plt.close(fig1)
    print(f"Gráfica del horno guardada como: '{nombre_grafica_horno}'")

    # --- Gráfico 2: Temperatura del Material ---
    fig2, ax2 = plt.subplots(figsize=(8, 6))
    ax2.set_title('Distribución de Temperatura del Material')
    ax2.set_ylabel('Temperatura (°C)')
    bplot2 = ax2.boxplot([buenos[columna_material].dropna(), malos[columna_material].dropna()],
                         vert=True, patch_artist=True, labels=['Buenas', 'Malas'])
    for patch, color in zip(bplot2['boxes'], colors):
        patch.set_facecolor(color)
    ax2.yaxis.grid(True, linestyle='--', color='grey', alpha=0.7)
    nombre_grafica_material = 'boxplot_temp_material.png'
    plt.savefig(nombre_grafica_material)
    plt.close(fig2)
    print(f"Gráfica del material guardada como: '{nombre_grafica_material}'")

except KeyError as e:
    print(f"\n*** ERROR: No se encontró la columna {e}. Revisa la configuración. ***")