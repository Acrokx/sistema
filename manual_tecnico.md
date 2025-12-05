# Arquitectura del Sistema

## Backend Laravel

El backend está desarrollado con Laravel, un framework PHP que maneja la lógica de negocio, autenticación, rutas API y comunicación con la base de datos. Procesa solicitudes del frontend, coordina con los servicios de IA y gestiona el almacenamiento de datos.

## Servicios de IA

Los servicios de IA se ejecutan en Python utilizando un entorno virtual (ia_env). Incluyen un servidor API (ia/api_ia.py) que proporciona funcionalidades de inteligencia artificial, como predicciones de mantenimiento basadas en modelos entrenados (modelo_mantenimiento.pkl).

## Base de Datos

La base de datos almacena información del sistema de mantenimiento predictivo. Utiliza configuraciones estándar de Laravel para conexiones, probablemente MySQL o similar, con migraciones y modelos Eloquent para gestionar entidades como equipos y datos de mantenimiento.

## Frontend

El frontend se desarrolla con tecnologías web modernas, ejecutándose con npm run dev. Maneja la interfaz de usuario, envía solicitudes al backend y muestra resultados de predicciones de IA.

## Flujo de Datos

El flujo de datos sigue un patrón cliente-servidor con integración de IA:

```
Usuario
   |
   v
Frontend (npm run dev)
   |
   v
Backend Laravel (php artisan serve)
   |     \
   |      \
   v       v
Base de Datos    Servicios de IA (python ia/api_ia.py)
   ^       /
   |      /
   v     /
Respuesta al Usuario
```

Datos fluyen desde el usuario al frontend, luego al backend, que consulta la base de datos y/o los servicios de IA para procesar y devolver resultados.
## Guía de Instalación

### Prerrequisitos

Antes de instalar el sistema, asegúrate de tener instalados los siguientes componentes en tu entorno de desarrollo:

- **PHP**: Versión 8.1 o superior, con extensiones necesarias para Laravel (como pdo_mysql, mbstring, etc.).
- **Composer**: Gestor de dependencias para PHP, disponible en [getcomposer.org](https://getcomposer.org/).
- **Node.js**: Versión 16 o superior, para el manejo del frontend y dependencias JavaScript.
- **Python**: Versión 3.8 o superior, para los servicios de inteligencia artificial.

### Instalación de Dependencias

1. **Dependencias PHP (Laravel)**:
   - Ejecuta `composer install` en la raíz del proyecto para instalar todas las dependencias de PHP definidas en `composer.json`.

2. **Dependencias JavaScript/Node.js**:
   - Ejecuta `npm install` en la raíz del proyecto para instalar todas las dependencias del frontend definidas en `package.json`.

3. **Entorno Virtual Python**:
   - Si no existe el entorno virtual `ia_env`, créalo con `python -m venv ia_env`.
   - Activa el entorno virtual: En Windows, ejecuta `ia_env\Scripts\activate`.
   - Instala las dependencias de Python necesarias (si hay un archivo `requirements.txt`, ejecuta `pip install -r requirements.txt`; de lo contrario, instala manualmente las librerías requeridas como scikit-learn, pandas, etc., basadas en los scripts en la carpeta `ia/`).

### Configuración de Base de Datos

1. Configura la conexión a la base de datos en el archivo `config/database.php` o mediante variables de entorno (.env).
2. Ejecuta las migraciones de Laravel para crear las tablas necesarias:
   - `php artisan migrate`

### Entrenamiento del Modelo IA

Antes de ejecutar el sistema, entrena el modelo de mantenimiento predictivo:
- Activa el entorno virtual Python: `ia_env\Scripts\activate`
- Ejecuta el script de entrenamiento: `python ia/entrenar_modelo.py`
- Esto generará o actualizará el archivo `modelo_mantenimiento.pkl` utilizado por la API de IA.

### Ejecución del Sistema

Una vez completados los pasos anteriores, ejecuta los siguientes comandos en terminales separadas para iniciar el sistema completo:

1. **Backend Laravel**:
   - `php artisan serve`

2. **Servicios de IA**:
   - Activa el entorno virtual: `ia_env\Scripts\activate`
   - `python ia/api_ia.py`

3. **Frontend**:
   - `npm run dev`

El sistema estará disponible en las direcciones locales correspondientes (generalmente http://localhost:8000 para Laravel, y puertos específicos para el frontend y la API de IA).
# Documentación de APIs

## APIs de Laravel

Las APIs de Laravel están disponibles bajo el prefijo `/api` y manejan operaciones CRUD para equipos, sensores y dashboard.

### Endpoints de Equipos

- **GET /api/equipos**
  - **Descripción**: Lista todos los equipos registrados en el sistema.
  - **Método**: GET
  - **Parámetros**: Ninguno
  - **Respuesta**: JSON array de objetos equipo
  - **Ejemplo de respuesta**:
    ```json
    [
      {
        "id": 1,
        "nombre": "Equipo 1",
        "tipo": "Motor",
        "ubicacion": "Planta A"
      }
    ]
    ```

- **POST /api/equipos**
  - **Descripción**: Crea un nuevo equipo.
  - **Método**: POST
  - **Parámetros**: JSON con campos del equipo (nombre, tipo, ubicacion, etc.)
  - **Ejemplo de solicitud**:
    ```json
    {
      "nombre": "Equipo Nuevo",
      "tipo": "Bomba",
      "ubicacion": "Planta B"
    }
    ```
  - **Respuesta**: JSON del equipo creado con código 201

- **GET /api/equipos/{id}**
  - **Descripción**: Obtiene detalles de un equipo específico, incluyendo sensores, lecturas y alertas relacionadas.
  - **Método**: GET
  - **Parámetros**: id (integer) en la URL
  - **Respuesta**: JSON del equipo con relaciones anidadas

### Endpoints de Sensores

Los endpoints de sensores están definidos pero no implementados actualmente.

### Endpoints de Dashboard

- **GET /api/dashboard**
  - **Descripción**: Obtiene datos estadísticos para el dashboard del sistema.
  - **Método**: GET
  - **Parámetros**: Ninguno
  - **Estado**: No implementado

### Endpoint de Usuario

- **GET /api/user**
  - **Descripción**: Obtiene información del usuario autenticado.
  - **Método**: GET
  - **Middleware**: auth:sanctum
  - **Respuesta**: JSON con datos del usuario

## API de IA en Python

La API de IA se ejecuta en Python usando FastAPI y proporciona funcionalidades de predicción de mantenimiento.

### Endpoint de Predicción

- **POST /predecir**
  - **Descripción**: Predice la probabilidad de fallo de un equipo basado en datos de sensores.
  - **Método**: POST
  - **Parámetros**: JSON con los siguientes campos (todos float):
    - `temperatura`: Temperatura del equipo
    - `vibracion`: Nivel de vibración
    - `presion`: Presión del sistema
    - `horas_operacion`: Horas de operación acumuladas
  - **Ejemplo de solicitud**:
    ```json
    {
      "temperatura": 75.5,
      "vibracion": 2.3,
      "presion": 101.2,
      "horas_operacion": 1500.0
    }
    ```
  - **Respuesta**: JSON con:
    - `probabilidad_fallo`: Porcentaje de probabilidad de fallo (0-100)
    - `nivel_riesgo`: Nivel de riesgo ("bajo", "moderado", "alto", "crítico")
    - `recomendacion`: Recomendación de acción basada en el riesgo
  - **Ejemplo de respuesta**:
    ```json
    {
      "probabilidad_fallo": 85.5,
      "nivel_riesgo": "alto",
      "recomendacion": "Programar mantenimiento en las próximas 24 horas"
    }
    ```