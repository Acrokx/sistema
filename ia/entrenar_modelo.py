# ia/entrenar_modelo.py
import pandas as pd
import numpy as np
from modelo_predictivo import ModeloMantenimiento

def generar_datos_entrenamiento(n_muestras=1000):
    """
    Genera datos de entrenamiento simulados
    """
    np.random.seed(42)

    # Generar datos normales
    temperatura_normal = np.random.normal(40, 10, n_muestras//2)
    vibracion_normal = np.random.normal(20, 5, n_muestras//2)
    presion_normal = np.random.normal(2, 0.5, n_muestras//2)
    horas_normal = np.random.uniform(0, 1000, n_muestras//2)

    # Generar datos de fallo
    temperatura_fallo = np.random.normal(70, 15, n_muestras//2)
    vibracion_fallo = np.random.normal(80, 20, n_muestras//2)
    presion_fallo = np.random.normal(8, 2, n_muestras//2)
    horas_fallo = np.random.uniform(800, 2000, n_muestras//2)

    # Combinar datos
    datos_normales = pd.DataFrame({
        'temperatura': temperatura_normal,
        'vibracion': vibracion_normal,
        'presion': presion_normal,
        'horas_operacion': horas_normal,
        'fallo': 0
    })

    datos_fallo = pd.DataFrame({
        'temperatura': temperatura_fallo,
        'vibracion': vibracion_fallo,
        'presion': presion_fallo,
        'horas_operacion': horas_fallo,
        'fallo': 1
    })

    # Combinar y mezclar
    datos_completos = pd.concat([datos_normales, datos_fallo], ignore_index=True)
    datos_completos = datos_completos.sample(frac=1, random_state=42).reset_index(drop=True)

    return datos_completos

if __name__ == "__main__":
    print("Generando datos de entrenamiento...")
    datos_entrenamiento = generar_datos_entrenamiento(2000)
    print(f"Datos generados: {len(datos_entrenamiento)} muestras")
    print(datos_entrenamiento.head())

    print("\nEntrenando modelo...")
    modelo = ModeloMantenimiento()
    precision = modelo.entrenar(datos_entrenamiento)
    print(f"Modelo entrenado con precisión: {precision:.2%}")