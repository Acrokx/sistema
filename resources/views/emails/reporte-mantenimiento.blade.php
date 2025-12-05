<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Mantenimiento Predictivo</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 800px; margin: 0 auto; background: white; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .content { padding: 30px; }
        .section { margin-bottom: 30px; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
        .section-header { background: #f8f9fa; padding: 15px 20px; border-bottom: 1px solid #e0e0e0; font-weight: bold; color: #495057; }
        .section-content { padding: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-card { background: #f8f9fa; padding: 15px; border-radius: 6px; text-align: center; border-left: 4px solid #007bff; }
        .stat-number { font-size: 24px; font-weight: bold; color: #007bff; }
        .stat-label { font-size: 12px; color: #6c757d; text-transform: uppercase; }
        .table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; }
        .table th { background: #f8f9fa; font-weight: 600; color: #495057; }
        .alert-critical { background: #f8d7da; border-left-color: #dc3545; }
        .alert-warning { background: #fff3cd; border-left-color: #ffc107; }
        .alert-info { background: #d1ecf1; border-left-color: #17a2b8; }
        .trend-up { color: #dc3545; }
        .trend-down { color: #28a745; }
        .trend-stable { color: #6c757d; }
        .recommendations { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 6px; }
        .footer { background: #343a40; color: white; padding: 20px; text-align: center; font-size: 12px; }
        .highlight { background: #fff3cd; padding: 2px 4px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Reporte de Mantenimiento Predictivo</h1>
            <h2>{{ ucfirst($tipo) }} - {{ \Carbon\Carbon::parse($datos['periodo']['inicio'])->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($datos['periodo']['fin'])->format('d/m/Y') }}</h2>
            <p>Generado el: {{ $fechaGeneracion }}</p>
        </div>

        <div class="content">
            <!-- Estadísticas Generales -->
            <div class="section">
                <div class="section-header">📈 Estadísticas Generales</div>
                <div class="section-content">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number">{{ $datos['estadisticas_generales']['total_equipos'] }}</div>
                            <div class="stat-label">Total Equipos</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">{{ $datos['estadisticas_generales']['equipos_activos'] }}</div>
                            <div class="stat-label">Equipos Activos</div>
                        </div>
                        <div class="stat-card alert-critical">
                            <div class="stat-number">{{ $datos['estadisticas_generales']['alertas_criticas'] }}</div>
                            <div class="stat-label">Alertas Críticas</div>
                        </div>
                        <div class="stat-card alert-warning">
                            <div class="stat-number">{{ $datos['estadisticas_generales']['alertas_moderadas'] }}</div>
                            <div class="stat-label">Alertas Moderadas</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertas por Equipo -->
            @if(count($datos['alertas_por_equipo']) > 0)
            <div class="section">
                <div class="section-header">🚨 Alertas por Equipo</div>
                <div class="section-content">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Equipo</th>
                                <th>Total Alertas</th>
                                <th>Críticas</th>
                                <th>Moderadas</th>
                                <th>Última Alerta</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($datos['alertas_por_equipo'] as $equipo)
                            <tr>
                                <td><strong>{{ $equipo['equipo'] }}</strong></td>
                                <td>{{ $equipo['total_alertas'] }}</td>
                                <td class="alert-critical">{{ $equipo['criticas'] }}</td>
                                <td class="alert-warning">{{ $equipo['moderadas'] }}</td>
                                <td>{{ $equipo['ultima_alerta'] ? \Carbon\Carbon::parse($equipo['ultima_alerta'])->format('d/m/Y H:i') : 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Equipos Críticos -->
            @if(count($datos['equipos_criticos']) > 0)
            <div class="section">
                <div class="section-header">⚠️ Equipos Requiriendo Atención Inmediata</div>
                <div class="section-content">
                    @foreach($datos['equipos_criticos'] as $equipo)
                    <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 6px; margin: 10px 0;">
                        <strong>{{ $equipo['nombre'] }}</strong> ({{ $equipo['ubicacion'] }})<br>
                        <small>Alertas críticas recientes: <span class="highlight">{{ $equipo['alertas_recientes'] }}</span></small>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Tendencias -->
            <div class="section">
                <div class="section-header">📊 Tendencias y Comparativas</div>
                <div class="section-content">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                        <div>
                            <h4>Comparación con período anterior:</h4>
                            <ul>
                                <li>Alertas actuales: <strong>{{ $datos['tendencias']['comparacion_periodos']['actual'] }}</strong></li>
                                <li>Alertas período anterior: <strong>{{ $datos['tendencias']['comparacion_periodos']['anterior'] }}</strong></li>
                                <li>Diferencia: <strong class="{{ $datos['tendencias']['comparacion_periodos']['diferencia'] > 0 ? 'trend-up' : ($datos['tendencias']['comparacion_periodos']['diferencia'] < 0 ? 'trend-down' : 'trend-stable') }}">
                                    {{ $datos['tendencias']['comparacion_periodos']['diferencia'] > 0 ? '+' : '' }}{{ $datos['tendencias']['comparacion_periodos']['diferencia'] }}
                                    ({{ number_format($datos['tendencias']['comparacion_periodos']['porcentaje_cambio'], 1) }}%)
                                </strong></li>
                            </ul>
                        </div>
                        <div>
                            <h4>Estado General:</h4>
                            <div style="font-size: 18px; font-weight: bold; color:
                                @if($datos['tendencias']['tendencia'] === 'aumento') #dc3545;
                                @elseif($datos['tendencias']['tendencia'] === 'disminucion') #28a745;
                                @else #6c757d; @endif">
                                Tendencia: {{ ucfirst($datos['tendencias']['tendencia']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recomendaciones -->
            @if(count($datos['recomendaciones']) > 0)
            <div class="section">
                <div class="section-header">💡 Recomendaciones</div>
                <div class="section-content">
                    @foreach($datos['recomendaciones'] as $recomendacion)
                    <div class="recommendations" style="margin: 10px 0;">
                        <strong>{{ $recomendacion['tipo'] === 'critico' ? '🚨 CRÍTICO' : '⚠️ ' . strtoupper($recomendacion['tipo']) }}:</strong>
                        {{ $recomendacion['mensaje'] }}<br>
                        <em>Acción recomendada: {{ $recomendacion['accion'] }}</em>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Información del Destinatario -->
            <div class="section">
                <div class="section-header">👤 Información del Reporte</div>
                <div class="section-content">
                    <p><strong>Reporte generado para:</strong> {{ $destinatario->name ?? $destinatario->email }}</p>
                    <p><strong>Tipo de reporte:</strong> {{ ucfirst($tipo) }}</p>
                    <p><strong>Período cubierto:</strong> {{ \Carbon\Carbon::parse($datos['periodo']['inicio'])->format('d/m/Y H:i') }} - {{ \Carbon\Carbon::parse($datos['periodo']['fin'])->format('d/m/Y H:i') }}</p>
                    <p><strong>Fecha de generación:</strong> {{ $fechaGeneracion }}</p>
                </div>
            </div>
        </div>

        <div class="footer">
            <p><strong>Sistema de Mantenimiento Predictivo</strong></p>
            <p>Este reporte fue generado automáticamente por el sistema de monitoreo.</p>
            <p>Para más información, contacte al administrador del sistema.</p>
        </div>
    </div>
</body>
</html>