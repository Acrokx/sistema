# Manual de Usuario - Sistema de Mantenimiento Predictivo

## Introducción

Bienvenido al Sistema de Mantenimiento Predictivo, una herramienta avanzada diseñada para ayudar a los operadores y técnicos a monitorear el estado de los equipos industriales de manera proactiva. Utilizando inteligencia artificial y datos en tiempo real de sensores, el sistema predice posibles fallos antes de que ocurran, permitiendo programar mantenimientos preventivos y reducir tiempos de inactividad.

### ¿Qué es el Mantenimiento Predictivo?

El mantenimiento predictivo es una estrategia que utiliza datos históricos y algoritmos de aprendizaje automático para anticipar cuándo un equipo podría fallar. En lugar de realizar mantenimientos basados en calendarios fijos o en fallos reactivos, este sistema analiza continuamente variables como temperatura, vibración, presión y horas de operación para calcular probabilidades de fallo.

### Beneficios del Sistema

- **Reducción de Costos**: Minimiza reparaciones de emergencia y optimiza el uso de recursos.
- **Mejora la Seguridad**: Identifica riesgos potenciales antes de que se conviertan en accidentes.
- **Aumento de Productividad**: Reduce tiempos de inactividad no planificados.
- **Toma de Decisiones Informada**: Proporciona datos precisos para planificar mantenimientos.

## Acceso al Sistema

### Requisitos Previos

Para acceder al sistema, asegúrese de que:

1. El sistema esté correctamente instalado y ejecutándose (consulte el manual técnico para detalles de instalación).
2. Tenga un navegador web moderno (Chrome, Firefox, Edge, Safari).
3. La conexión a internet esté disponible (aunque el sistema puede funcionar localmente).

### Cómo Acceder

1. Abra su navegador web.
2. Navegue a la dirección proporcionada por su administrador del sistema (generalmente `http://localhost:8000` o similar).
3. Si el sistema requiere autenticación, ingrese sus credenciales de usuario.

**Nota**: Si experimenta problemas de acceso, contacte al administrador del sistema o al equipo de soporte técnico.

## Navegación en el Dashboard

Una vez dentro del sistema, será recibido por el Dashboard Principal, que proporciona una visión general del estado de todos los equipos monitoreados.

### Elementos del Dashboard

#### Panel de Estadísticas

En la parte superior del dashboard encontrará tres tarjetas principales:

- **Equipos**: Muestra el número total de equipos registrados en el sistema.
- **Sensores**: Indica la cantidad total de sensores activos conectados a los equipos.
- **Alertas Activas**: Número de alertas críticas que requieren atención inmediata.

Cada tarjeta incluye un ícono representativo y se actualiza automáticamente cuando cambian los datos.

#### Sección de Alertas en Tiempo Real

Debajo del panel de estadísticas se encuentra la sección "Alertas en Tiempo Real", que muestra las notificaciones más recientes del sistema.

##### Tipos de Alertas

Las alertas se clasifican por nivel de criticidad:

- **Bajo (Verde)**: Alertas informativas que no requieren acción inmediata.
- **Medio (Amarillo)**: Situaciones que deben monitorearse pero no son críticas.
- **Alto (Rojo)**: Problemas críticos que requieren atención inmediata.

Cada alerta incluye:
- **Tipo de Falla**: Descripción breve del problema detectado.
- **Nivel de Criticidad**: Indicador visual del nivel de riesgo.
- **Descripción**: Detalles específicos del problema.
- **Timestamp**: Fecha y hora en que se generó la alerta.

## Gestión de Equipos

### Visualización de Equipos

Para ver la lista completa de equipos:

1. Desde el dashboard, busque la opción "Equipos" en el menú de navegación (si está disponible).
2. La lista mostrará todos los equipos registrados con información básica como nombre, tipo y ubicación.

### Detalles de un Equipo

Al seleccionar un equipo específico, podrá ver:

- **Información General**: Nombre, tipo, ubicación, descripción y fecha de instalación.
- **Sensores Asociados**: Lista de sensores conectados al equipo con sus rangos operativos.
- **Lecturas Recientes**: Valores actuales de cada sensor.
- **Historial de Alertas**: Registro de problemas pasados y su resolución.

### Registro de Nuevos Equipos

Si tiene permisos para agregar equipos:

1. Acceda a la sección de gestión de equipos.
2. Haga clic en "Agregar Equipo" o "Nuevo Equipo".
3. Complete los campos requeridos:
   - **Nombre**: Identificador único del equipo.
   - **Tipo**: Categoría del equipo (ej: Motor, Bomba, Generador).
   - **Ubicación**: Lugar físico donde se encuentra.
   - **Descripción**: Detalles adicionales opcionales.
   - **Fecha de Instalación**: Cuando se instaló el equipo.
4. Guarde los cambios.

**Nota**: Algunos campos pueden ser obligatorios. Asegúrese de completar toda la información necesaria.

## Predicciones y Análisis de IA

### Cómo Funcionan las Predicciones

El sistema utiliza un modelo de inteligencia artificial entrenado con datos históricos para calcular la probabilidad de fallo de cada equipo. El análisis considera múltiples variables:

- **Temperatura**: Nivel de calor generado por el equipo.
- **Vibración**: Movimiento anormal detectado.
- **Presión**: Niveles de presión en sistemas hidráulicos o neumáticos.
- **Horas de Operación**: Tiempo total de funcionamiento acumulado.

### Interpretación de Resultados

Cuando el sistema genera una predicción, proporciona:

- **Probabilidad de Fallo**: Porcentaje que indica la likelihood de un problema (0-100%).
- **Nivel de Riesgo**: Clasificación automática:
  - **Bajo**: Probabilidad < 30%
  - **Moderado**: Probabilidad 30-70%
  - **Alto**: Probabilidad 70-90%
  - **Crítico**: Probabilidad > 90%
- **Recomendación**: Acción sugerida basada en el análisis.

### Acciones Recomendadas por Nivel de Riesgo

- **Bajo**: Continuar monitoreo normal, no se requiere acción inmediata.
- **Moderado**: Programar inspección visual en las próximas semanas.
- **Alto**: Programar mantenimiento preventivo en las próximas 24-48 horas.
- **Crítico**: Detener operación del equipo inmediatamente y realizar mantenimiento de emergencia.

## Solución de Problemas

### Problemas Comunes y Soluciones

#### El Dashboard no se Carga

**Síntomas**: Página en blanco o mensaje de error al acceder.

**Soluciones**:
1. Verifique que el navegador esté actualizado.
2. Borre la caché del navegador (Ctrl+F5).
3. Confirme que el servidor backend esté ejecutándose.
4. Contacte al administrador si el problema persiste.

#### Alertas no se Actualizan en Tiempo Real

**Síntomas**: Las alertas nuevas no aparecen automáticamente.

**Soluciones**:
1. Actualice la página manualmente (F5).
2. Verifique la conexión a internet.
3. Confirme que el servicio de WebSockets esté activo.
4. Reinicie el navegador.

#### Datos de Sensores no se Muestran

**Síntomas**: Valores de sensores aparecen como "N/A" o no se actualizan.

**Soluciones**:
1. Verifique que los sensores físicos estén conectados y funcionando.
2. Confirme que el servicio de IA esté ejecutándose.
3. Revise los logs del sistema para errores de comunicación.
4. Contacte al equipo técnico para diagnóstico de hardware.

#### Error de Autenticación

**Síntomas**: No puede iniciar sesión o recibe mensaje de "credenciales inválidas".

**Soluciones**:
1. Verifique que el nombre de usuario y contraseña sean correctos.
2. Asegúrese de que las mayúsculas/minúsculas sean correctas.
3. Si olvidó su contraseña, contacte al administrador.
4. Confirme que su cuenta esté activa.

#### Rendimiento Lento del Sistema

**Síntomas**: El sistema responde lentamente o se congela.

**Soluciones**:
1. Cierre otras aplicaciones que puedan estar consumiendo recursos.
2. Actualice la página.
3. Verifique el uso de memoria y CPU del servidor.
4. Contacte al administrador del sistema.

### Consejos para un Mejor Rendimiento

- Mantenga el navegador actualizado.
- Evite tener múltiples pestañas del sistema abiertas simultáneamente.
- Realice mantenimientos regulares del equipo donde se ejecuta el sistema.
- Monitoree el espacio disponible en disco.

## Contacto y Soporte

### Soporte Técnico

Si experimenta problemas que no puede resolver con este manual:

- **Administrador del Sistema**: [Nombre/Contacto del administrador local]
- **Equipo de Desarrollo**: [Correo electrónico o teléfono del equipo técnico]
- **Documentación Técnica**: Consulte el manual técnico para detalles avanzados.

### Reporte de Problemas

Cuando reporte un problema, incluya:

1. Descripción detallada del problema.
2. Pasos para reproducirlo.
3. Capturas de pantalla si es relevante.
4. Información del navegador y sistema operativo.
5. Fecha y hora del incidente.

### Actualizaciones y Mejoras

El sistema se actualiza periódicamente. Para mantenerse informado sobre nuevas funcionalidades:

- Revise regularmente las actualizaciones del dashboard.
- Participe en capacitaciones cuando estén disponibles.
- Proporcione retroalimentación sobre el uso del sistema.

---

**Versión del Manual**: 1.0
**Fecha de Última Actualización**: Noviembre 2025
**Sistema Versión**: 1.0

Este manual está diseñado para usuarios finales sin conocimientos técnicos avanzados. Para información técnica detallada, consulte el manual técnico del sistema.