# ia/modelo_predictivo.py
import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score
import joblib

class ModeloMantenimiento:
    def __init__(self):
        self.modelo = RandomForestClassifier(n_estimators=100, random_state=42)
        self.caracteristicas = ['temperatura', 'vibracion', 'presion', 'horas_operacion']

    def entrenar(self, datos_historicos):
        """
        Entrena el modelo con datos históricos
        datos_historicos: DataFrame con columnas ['temperatura', 'vibracion',
        'presion', 'horas_operacion', 'fallo']
        """
        X = datos_historicos[self.caracteristicas]
        y = datos_historicos['fallo']  # 1 = fallo, 0 = normal

        # Dividir datos en entrenamiento y prueba
        X_train, X_test, y_train, y_test = train_test_split(
            X, y, test_size=0.2, random_state=42
        )

        # Entrenar modelo
        self.modelo.fit(X_train, y_train)

        # Evaluar precisión
        predicciones = self.modelo.predict(X_test)
        precision = accuracy_score(y_test, predicciones)
        print(f"Precisión del modelo: {precision:.2%}")

        # Guardar modelo entrenado
        joblib.dump(self.modelo, 'modelo_mantenimiento.pkl')
        return precision

    def predecir(self, temperatura, vibracion, presion, horas_operacion):
        """
        Predice la probabilidad de fallo
        """
        datos = np.array([[temperatura, vibracion, presion, horas_operacion]])
        probabilidad = self.modelo.predict_proba(datos)[0][1]  # Probabilidad de fallo
        return probabilidad