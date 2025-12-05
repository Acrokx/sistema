<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚨 ALERTA CRÍTICA - {{ $equipo->nombre }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #dee2e6; }
        .alert-box { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .critical { color: #dc3545; font-weight: bold; }
        .details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .footer { background: #6c757d; color: white; padding: 15px; text-align: center; border-radius: 0 0 5px 5px; font-size: 12px; }
        .action-button { display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚨 ALERTA CRÍTICA DE MANTENIMIENTO</h1>
            <h2>{{ $equipo->nombre }}</h2>
        </div>

        <div class="content">
            <div class="alert-box">
                <h3 class="critical">⚠️ LECTURA CRÍTICA DETECTADA</h3>
                <p><strong>Equipo:</strong> {{ $equipo->nombre }}</p>
                <p><strong>Ubicación:</strong> {{ $equipo->ubicacion }}</p>
                <p><strong>Sensor:</strong> {{ $sensor->tipo_sensor }}</p>
                <p><strong>Valor Actual:</strong> <span class="critical">{{ $lectura->valor }}</span></p>
                <p><strong>Nivel de Criticidad:</strong> <span class="critical">{{ $nivelCriticidad }}</span></p>
                <p><strong>Timestamp:</strong> {{ $lectura->timestamp_lectura->format('d/m/Y H:i:s') }}</p>
            </div>

            <div class="details">
                <h4>📊 Detalles Técnicos</h4>
                <ul>
                    <li><strong>ID del Equipo:</strong> {{ $equipo->id }}</li>
                    <li><strong>ID del Sensor:</strong> {{ $sensor->id }}</li>
                    <li><strong>ID de la Lectura:</strong> {{ $lectura->id }}</li>
                    <li><strong>Límite Superior:</strong> {{ $sensor->limite_alerta_alto ?? 'No definido' }}</li>
                    <li><strong>Límite Inferior:</strong> {{ $sensor->limite_alerta_bajo ?? 'No definido' }}</li>
                </ul>
            </div>

            <div style="text-align: center; margin: 20px 0;">
                <a href="{{ url('/equipos/' . $equipo->id) }}" class="action-button">
                    👁️ Ver Detalles del Equipo
                </a>
            </div>

            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;">
                <h4>⚡ Acción Requerida</h4>
                <p>Esta alerta requiere atención inmediata. Por favor, revise el equipo y tome las medidas correctivas necesarias.</p>
                <ul>
                    <li>Verificar el estado físico del equipo</li>
                    <li>Revisar las lecturas del sensor</li>
                    <li>Realizar mantenimiento preventivo si es necesario</li>
                    <li>Documentar las acciones tomadas</li>
                </ul>
            </div>
        </div>

        <div class="footer">
            <p><strong>Sistema de Mantenimiento Predictivo</strong></p>
            <p>Esta es una notificación automática del sistema de monitoreo.</p>
            <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>