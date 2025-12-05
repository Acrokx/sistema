# ia/api_ia.py
from fastapi import FastAPI
from pydantic import BaseModel
import joblib
import numpy as np

app = FastAPI()

# Cargar modelo entrenado
try:
    modelo = joblib.load('modelo_mantenimiento.pkl')
    print("Modelo cargado exitosamente")
except:
    print("No se pudo cargar el modelo. Asegúrate de entrenarlo primero.")
    modelo = None

class DatosSensor(BaseModel):
    temperatura: float
    vibracion: float
    presion: float
    horas_operacion: float

@app.post("/predecir")
def predecir_fallo(datos: DatosSensor):
    if modelo is None:
        return {"error": "Modelo no disponible"}

    # Hacer predicción
    entrada = np.array([[datos.temperatura, datos.vibracion,
                        datos.presion, datos.horas_operacion]])
    probabilidad = modelo.predict_proba(entrada)[0][1]

    # Clasificar riesgo
    if probabilidad > 0.8:
        riesgo = "crítico"
    elif probabilidad > 0.6:
        riesgo = "alto"
    elif probabilidad > 0.4:
        riesgo = "moderado"
    else:
        riesgo = "bajo"

    return {
        "probabilidad_fallo": round(probabilidad * 100, 2),
        "nivel_riesgo": riesgo,
        "recomendacion": obtener_recomendacion(riesgo)
    }

def obtener_recomendacion(riesgo):
    recomendaciones = {
        "crítico": "Detener equipo inmediatamente y realizar mantenimiento",
        "alto": "Programar mantenimiento en las próximas 24 horas",
        "moderado": "Programar inspección en la próxima semana",
        "bajo": "Continuar operación normal"
    }
    return recomendaciones[riesgo]

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="127.0.0.1", port=8001)