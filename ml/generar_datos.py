import pandas as pd
import numpy as np

# ======================= CONFIGURACIÓN FÁCIL =======================
# Nombre del archivo que contiene tus datos (puede ser el balanceado, se filtrará)
nombre_archivo_entrada = 'datosFundicion.csv'

# Nombre que tendrá el nuevo archivo CSV con los datos realistas
nombre_archivo_salida = 'fundicion_balanceado.csv'

# Nombres de las columnas y clases
columna_clase = 'clase'
columna_horno = 'TemperaturaHorno'
columna_material = 'TemperaturaMaterial'
valor_buena = 'buena'
valor_mala = 'mala'
# ===================================================================

print("Iniciando la generación de datos realistas...")

try:
    # Cargar el dataset y filtrar solo los datos buenos originales
    df_original = pd.read_csv(nombre_archivo_entrada)
    df_buenas = df_original[df_original[columna_clase] == valor_buena].copy()
    
    if df_buenas.empty:
        raise ValueError(f"No se encontraron datos 'Buenos' con la etiqueta '{valor_buena}'.")

    num_buenas = len(df_buenas)
    num_malas_a_generar = num_buenas # Para tener un dataset perfectamente balanceado
    
    print(f"Se usarán {num_buenas} muestras 'Buenas' como base.")
    print(f"Se generarán {num_malas_a_generar} muestras 'Malas' nuevas.")

    # --- Definición de rangos "malos" basados en tus estadísticas ---
    # Usaremos los cuartiles (25% y 75%) de tus datos buenos como límites
    
    # Para TemperaturaHorno (Buenas: min 700, 25% 710, 75% 728, max 798)
    rango_malo_horno_bajo = (700, 710)  # Ligeramente frío
    rango_malo_horno_alto = (728, 740)  # Ligeramente caliente (acotado para no ser extremo)

    # Para TemperaturaMaterial (Buenas: min 650, 25% 660, 75% 674, max 686)
    rango_malo_material_bajo = (650, 660) # Ligeramente frío
    rango_malo_material_alto = (674, 686) # Ligeramente caliente

    # --- Generación de Datos Malos (con NÚMEROS ENTEROS) ---
    np.random.seed(42) # Para que los resultados sean reproducibles
    
    # Generamos la mitad de los datos en el rango bajo y la otra mitad en el alto
    mitad_malas = num_malas_a_generar // 2
    
    # CAMBIO: Usamos np.random.randint en lugar de np.random.uniform y sumamos 1 al límite superior
    horno_malo_bajo = np.random.randint(low=rango_malo_horno_bajo[0], high=rango_malo_horno_bajo[1] + 1, size=mitad_malas)
    horno_malo_alto = np.random.randint(low=rango_malo_horno_alto[0], high=rango_malo_horno_alto[1] + 1, size=num_malas_a_generar - mitad_malas)
    
    material_malo_bajo = np.random.randint(low=rango_malo_material_bajo[0], high=rango_malo_material_bajo[1] + 1, size=mitad_malas)
    material_malo_alto = np.random.randint(low=rango_malo_material_alto[0], high=rango_malo_material_alto[1] + 1, size=num_malas_a_generar - mitad_malas)

    # Combinar los datos malos
    temperaturas_horno_malas = np.concatenate([horno_malo_bajo, horno_malo_alto])
    temperaturas_material_malas = np.concatenate([material_malo_bajo, material_malo_alto])
    
    # Crear el DataFrame de datos malos
    df_malas = pd.DataFrame({
        columna_horno: temperaturas_horno_malas,
        columna_material: temperaturas_material_malas,
        columna_clase: valor_mala
    })
    
    # --- Combinar, Mezclar y Guardar ---
    df_final = pd.concat([df_buenas, df_malas], ignore_index=True)
    
    # Mezclar todas las filas aleatoriamente
    df_final_shuffled = df_final.sample(frac=1, random_state=42).reset_index(drop=True)
    
    # Guardar el nuevo dataset en un archivo CSV
    df_final_shuffled.to_csv(nombre_archivo_salida, index=False)
    
    print("\n¡Proceso completado!")
    print(f"Nuevo dataset guardado como: '{nombre_archivo_salida}'")
    print(f"Total de filas: {len(df_final_shuffled)}")

except (FileNotFoundError, ValueError, KeyError) as e:
    print(f"\n*** ERROR: {e} ***")
    print("Asegúrate de que el nombre del archivo y las columnas en la configuración son correctos.")