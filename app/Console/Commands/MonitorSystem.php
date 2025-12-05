<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Swift_SmtpTransport;
use Swift_Mailer;

class MonitorSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:system
                        {--database : Verificar estado de la base de datos}
                        {--services : Verificar servicios externos}
                        {--performance : Verificar rendimiento del sistema}
                        {--disk : Verificar espacio en disco}
                        {--all : Ejecutar todas las verificaciones}
                        {--json : Salida en formato JSON}
                        {--watch= : Ejecutar monitoreo continuo cada N segundos}
                        {--alert-email= : Enviar alertas por email cuando hay problemas}
                        {--alert-slack= : Enviar alertas por Slack cuando hay problemas}
                        {--log-file= : Guardar resultados en archivo de log}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitorear el estado del sistema de mantenimiento predictivo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $watchInterval = $this->option('watch');

        if ($watchInterval) {
            // Modo watch: monitoreo continuo
            return $this->runWatchMode((int)$watchInterval);
        }

        // Modo normal: una sola ejecución
        return $this->runSingleCheck();
    }

    private function runSingleCheck()
    {
        $this->info('🔍 Iniciando monitoreo del sistema...');
        $this->newLine();

        $results = $this->performChecks();

        if (empty($results)) {
            $this->warn('No se especificó ninguna opción de monitoreo. Use --help para ver las opciones disponibles.');
            return 1;
        }

        if ($this->option('json')) {
            $output = json_encode($results, JSON_PRETTY_PRINT);
            $this->line($output);
            $this->logToFile($output);
        } else {
            $this->displayResults($results);
        }

        // Verificar y enviar alertas si hay problemas críticos
        $this->checkAndSendAlerts($results);

        $this->newLine();
        $this->info('✅ Monitoreo completado.');

        return 0;
    }

    private function runWatchMode($interval)
    {
        $this->info("🔍 Iniciando monitoreo continuo cada {$interval} segundos...");
        $this->info('Presione Ctrl+C para detener el monitoreo.');
        $this->newLine();

        while (true) {
            // Limpiar pantalla
            $this->clearScreen();

            // Mostrar timestamp
            $this->line('⏰ ' . now()->format('Y-m-d H:i:s'));
            $this->line(str_repeat('═', 50));
            $this->newLine();

            // Realizar verificaciones
            $results = $this->performChecks();

            if (!empty($results)) {
                $this->displayResults($results);
            }

            $this->newLine();
            $this->line('Próxima verificación en ' . $interval . ' segundos...');
            $this->line('Presione Ctrl+C para salir.');

            // Esperar el intervalo
            sleep($interval);
        }
    }

    private function performChecks()
    {
        $results = [];

        if ($this->option('all')) {
            $results = array_merge($results, $this->checkDatabase());
            $results = array_merge($results, $this->checkServices());
            $results = array_merge($results, $this->checkPerformance());
            $results = array_merge($results, $this->checkDiskSpace());
        } else {
            if ($this->option('database')) {
                $results = array_merge($results, $this->checkDatabase());
            }
            if ($this->option('services')) {
                $results = array_merge($results, $this->checkServices());
            }
            if ($this->option('performance')) {
                $results = array_merge($results, $this->checkPerformance());
            }
            if ($this->option('disk')) {
                $results = array_merge($results, $this->checkDiskSpace());
            }
        }

        return $results;
    }

    private function clearScreen()
    {
        // Para Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            system('cls');
        } else {
            // Para Unix/Linux
            system('clear');
        }
    }

    private function checkDatabase()
    {
        $this->info('🗄️  Verificando base de datos...');

        $results = ['database' => []];

        try {
            // Verificar conexión y medir tiempo de respuesta
            $startTime = microtime(true);
            DB::connection()->getPdo();
            $connectionTime = (microtime(true) - $startTime) * 1000; // ms

            $results['database']['connection'] = [
                'status' => 'OK',
                'message' => 'CONECTADA',
                'response_time' => round($connectionTime, 2) . 'ms'
            ];

            // Obtener información de conexiones activas (SQLite no tiene conexiones concurrentes como MySQL)
            $results['database']['active_connections'] = [
                'status' => 'INFO',
                'message' => 'SQLite: 1 conexión activa (arquitectura single-writer)',
                'count' => 1
            ];

            // Contar registros en tablas principales
            $tables = [
                'equipos' => 'Equipos',
                'sensores' => 'Sensores',
                'lecturas' => 'Lecturas',
                'alertas' => 'Alertas'
            ];

            foreach ($tables as $table => $name) {
                try {
                    $startTime = microtime(true);
                    $count = DB::table($table)->count();
                    $queryTime = (microtime(true) - $startTime) * 1000;

                    $results['database']['tables'][$table] = [
                        'status' => 'OK',
                        'count' => $count,
                        'query_time' => round($queryTime, 2) . 'ms',
                        'message' => "{$name}: {$count} registros"
                    ];
                } catch (\Exception $e) {
                    $results['database']['tables'][$table] = [
                        'status' => 'ERROR',
                        'message' => "Error al consultar {$name}: " . $e->getMessage()
                    ];
                }
            }

        } catch (\Exception $e) {
            $results['database']['connection'] = [
                'status' => 'ERROR',
                'message' => 'Error de conexión: ' . $e->getMessage()
            ];
        }

        return $results;
    }

    private function checkServices()
    {
        $this->info('🌐 Verificando servicios externos...');

        $results = ['services' => []];

        // Verificar API de IA (Python)
        try {
            $response = Http::timeout(5)->get('http://127.0.0.1:8000/docs');
            if ($response->successful()) {
                $results['services']['ia_api'] = [
                    'status' => 'OK',
                    'message' => 'API Externa: ACTIVO',
                    'response_time' => round(($response->handlerStats()['total_time'] ?? 0) * 1000, 2) . 'ms'
                ];
            } else {
                $results['services']['ia_api'] = [
                    'status' => 'ERROR',
                    'message' => 'API Externa: INACTIVO',
                    'status_code' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            $results['services']['ia_api'] = [
                'status' => 'ERROR',
                'message' => 'API Externa: INACTIVO - ' . $e->getMessage()
            ];
        }

        // Verificar Laravel Reverb (WebSockets)
        try {
            $response = Http::timeout(3)->get('http://localhost:8080');
            if ($response->successful()) {
                $results['services']['reverb'] = [
                    'status' => 'OK',
                    'message' => 'WebSockets: ACTIVO'
                ];
            } else {
                $results['services']['reverb'] = [
                    'status' => 'WARNING',
                    'message' => 'WebSockets: INACTIVO'
                ];
            }
        } catch (\Exception $e) {
            $results['services']['reverb'] = [
                'status' => 'ERROR',
                'message' => 'WebSockets: INACTIVO'
            ];
        }

        // Verificar Redis (si está configurado)
        try {
            if (config('cache.default') === 'redis' || config('queue.default') === 'redis') {
                // Intentar una operación simple de Redis
                Cache::store('redis')->put('health_check', 'ok', 10);
                $value = Cache::store('redis')->get('health_check');

                if ($value === 'ok') {
                    $results['services']['redis'] = [
                        'status' => 'OK',
                        'message' => 'Redis: ACTIVO'
                    ];
                } else {
                    $results['services']['redis'] = [
                        'status' => 'ERROR',
                        'message' => 'Redis: INACTIVO'
                    ];
                }
            } else {
                $results['services']['redis'] = [
                    'status' => 'INFO',
                    'message' => 'Redis: NO CONFIGURADO'
                ];
            }
        } catch (\Exception $e) {
            $results['services']['redis'] = [
                'status' => 'ERROR',
                'message' => 'Redis: INACTIVO - ' . $e->getMessage()
            ];
        }

        // Verificar servicio de Email (SMTP)
        try {
            // Verificar configuración de email
            $mailConfig = config('mail');
            if (!empty($mailConfig['host']) && !empty($mailConfig['port'])) {
                // Intentar crear una conexión SMTP básica (sin enviar email)
                $transport = new \Swift_SmtpTransport(
                    $mailConfig['host'],
                    $mailConfig['port'],
                    $mailConfig['encryption'] ?? null
                );

                if (!empty($mailConfig['username'])) {
                    $transport->setUsername($mailConfig['username']);
                    $transport->setPassword($mailConfig['password']);
                }

                $mailer = new \Swift_Mailer($transport);

                // Intentar conectar (timeout corto)
                try {
                    $transport->start();
                    $results['services']['email'] = [
                        'status' => 'OK',
                        'message' => 'Email Service: ACTIVO'
                    ];
                    $transport->stop();
                } catch (\Exception $e) {
                    $results['services']['email'] = [
                        'status' => 'ERROR',
                        'message' => 'Email Service: INACTIVO - Error de conexión'
                    ];
                }
            } else {
                $results['services']['email'] = [
                    'status' => 'WARNING',
                    'message' => 'Email Service: NO CONFIGURADO'
                ];
            }
        } catch (\Exception $e) {
            $results['services']['email'] = [
                'status' => 'ERROR',
                'message' => 'Email Service: INACTIVO - ' . $e->getMessage()
            ];
        }

        return $results;
    }

    private function checkPerformance()
    {
        $this->info('⚡ Verificando rendimiento del sistema...');

        $results = ['performance' => []];

        // Memoria usada por PHP
        $memoryUsage = memory_get_usage(false);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = $this->parseSize(ini_get('memory_limit'));

        $results['performance']['memory'] = [
            'current_usage' => $this->formatBytes($memoryUsage),
            'peak_usage' => $this->formatBytes($memoryPeak),
            'limit' => $this->formatBytes($memoryLimit),
            'usage_percentage' => $memoryLimit > 0 ? round(($memoryPeak / $memoryLimit) * 100, 2) : 0,
            'status' => ($memoryLimit > 0 && $memoryPeak / $memoryLimit > 0.8) ? 'WARNING' : 'OK'
        ];

        // CPU Usage (estimación basada en carga del sistema)
        if (function_exists('sys_getloadavg')) {
            // Unix/Linux systems
            $cpuLoad = sys_getloadavg();
            $results['performance']['cpu'] = [
                'load_1min' => round($cpuLoad[0], 2),
                'load_5min' => round($cpuLoad[1], 2),
                'load_15min' => round($cpuLoad[2], 2),
                'status' => $cpuLoad[0] > 2.0 ? 'WARNING' : 'OK'
            ];
        } else {
            // Windows or other systems - CPU info not available
            $results['performance']['cpu'] = [
                'status' => 'INFO',
                'message' => 'Información de CPU no disponible en Windows'
            ];
        }

        // Cache hits/misses (si está disponible)
        try {
            $cacheInfo = Cache::store()->getStore();
            if (method_exists($cacheInfo, 'getHits')) {
                $hits = $cacheInfo->getHits() ?? 0;
                $misses = $cacheInfo->getMisses() ?? 0;
                $total = $hits + $misses;

                $results['performance']['cache'] = [
                    'hits' => $hits,
                    'misses' => $misses,
                    'hit_rate' => $total > 0 ? round(($hits / $total) * 100, 2) : 0,
                    'status' => 'OK'
                ];
            }
        } catch (\Exception $e) {
            $results['performance']['cache'] = [
                'status' => 'INFO',
                'message' => 'Información de cache no disponible'
            ];
        }

        // Tiempo de respuesta de la aplicación
        $startTime = microtime(true);
        // Simular una consulta simple
        DB::table('equipos')->count();
        $queryTime = (microtime(true) - $startTime) * 1000; // en ms

        $results['performance']['response_time'] = [
            'query_time_ms' => round($queryTime, 2),
            'status' => $queryTime > 1000 ? 'WARNING' : 'OK'
        ];

        return $results;
    }

    private function checkDiskSpace()
    {
        $this->info('💾 Verificando espacio en disco...');

        $results = ['disk' => []];

        $diskTotal = disk_total_space('/');
        $diskFree = disk_free_space('/');
        $diskUsed = $diskTotal - $diskFree;

        $usagePercentage = round(($diskUsed / $diskTotal) * 100, 2);

        $results['disk']['space'] = [
            'total' => $this->formatBytes($diskTotal),
            'used' => $this->formatBytes($diskUsed),
            'free' => $this->formatBytes($diskFree),
            'usage_percentage' => $usagePercentage,
            'status' => $usagePercentage > 90 ? 'CRITICAL' : ($usagePercentage > 80 ? 'WARNING' : 'OK')
        ];

        // Verificar espacio en directorios específicos
        $directories = [
            'storage' => storage_path(),
            'logs' => storage_path('logs'),
            'database' => database_path()
        ];

        foreach ($directories as $name => $path) {
            if (is_dir($path)) {
                $size = $this->getDirectorySize($path);
                $results['disk']['directories'][$name] = [
                    'size' => $this->formatBytes($size),
                    'status' => $size > 1073741824 ? 'WARNING' : 'OK' // > 1GB
                ];
            }
        }

        return $results;
    }

    private function displayResults($results)
    {
        $output = '';

        foreach ($results as $category => $data) {
            if (!$this->option('quiet')) {
                $this->newLine();
                $this->line(strtoupper($category) . ':');
            }

            $categoryOutput = $this->displayCategoryResults($data, '');
            $output .= strtoupper($category) . ":\n" . $categoryOutput;
        }

        // Log to file if specified
        if ($this->option('log-file')) {
            $this->logToFile($output);
        }
    }

    private function displayCategoryResults($data, $prefix)
    {
        $output = '';

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (isset($value['status'])) {
                    $status = $value['status'];
                    $message = $value['message'] ?? '';

                    // In quiet mode, only show errors and critical issues
                    if ($this->output->isQuiet() && !in_array($status, ['ERROR', 'CRITICAL'])) {
                        continue;
                    }

                    $statusIcon = match($status) {
                        'OK' => '✅',
                        'WARNING' => '⚠️',
                        'ERROR' => '❌',
                        'CRITICAL' => '🚨',
                        'INFO' => 'ℹ️',
                        default => '❓'
                    };

                    $line = "{$prefix}{$statusIcon} {$key}: {$message}";

                    // Add detailed metrics in verbose mode
                    if ($this->output->isVerbose() && isset($value['usage_percentage'])) {
                        $line .= " ({$value['usage_percentage']}%)";
                    }
                    if ($this->output->isVerbose() && isset($value['response_time'])) {
                        $line .= " [{$value['response_time']}]";
                    }
                    if ($this->output->isVerbose() && isset($value['count'])) {
                        $line .= " [{$value['count']} registros]";
                    }

                    if (!$this->option('quiet')) {
                        $this->line($line);
                    }
                    $output .= $line . "\n";
                } else {
                    if (!$this->option('quiet')) {
                        $this->line("{$prefix}{$key}:");
                    }
                    $output .= "{$key}:\n";

                    $subOutput = $this->displayCategoryResults($value, $prefix . '  ');
                    $output .= $subOutput;
                }
            } else {
                if (!$this->option('quiet')) {
                    $this->line("{$prefix}{$key}: {$value}");
                }
                $output .= "{$prefix}{$key}: {$value}\n";
            }
        }

        return $output;
    }

    private function parseSize($size)
    {
        if (is_numeric($size)) {
            return $size;
        }

        $unit = strtolower(substr($size, -1));
        $value = (int)substr($size, 0, -1);

        return match($unit) {
            'g' => $value * 1073741824,
            'm' => $value * 1048576,
            'k' => $value * 1024,
            default => $value
        };
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function getDirectorySize($path)
    {
        $size = 0;

        if (!is_dir($path)) {
            return $size;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    private function checkAndSendAlerts($results)
    {
        $criticalIssues = $this->analyzeCriticalIssues($results);

        if (!empty($criticalIssues)) {
            $this->warn('🚨 Se detectaron problemas críticos en el sistema!');

            if ($this->option('alert-email')) {
                $this->sendEmailAlert($criticalIssues, $this->option('alert-email'));
            }

            if ($this->option('alert-slack')) {
                $this->sendSlackAlert($criticalIssues, $this->option('alert-slack'));
            }
        }
    }

    private function analyzeCriticalIssues($results)
    {
        $criticalIssues = [];

        // Analizar problemas críticos en cada categoría
        foreach ($results as $category => $data) {
            $issues = $this->findCriticalIssuesInCategory($data, $category);
            $criticalIssues = array_merge($criticalIssues, $issues);
        }

        return $criticalIssues;
    }

    private function findCriticalIssuesInCategory($data, $category)
    {
        $issues = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (isset($value['status']) && in_array($value['status'], ['ERROR', 'CRITICAL'])) {
                    $issues[] = [
                        'category' => $category,
                        'component' => $key,
                        'status' => $value['status'],
                        'message' => $value['message'] ?? 'Problema crítico detectado'
                    ];
                } elseif (isset($value['status']) && $value['status'] === 'WARNING') {
                    // Solo incluir warnings si son muy críticos (ej: memoria > 90%)
                    if (isset($value['usage_percentage']) && $value['usage_percentage'] > 90) {
                        $issues[] = [
                            'category' => $category,
                            'component' => $key,
                            'status' => $value['status'],
                            'message' => $value['message'] ?? 'Advertencia crítica'
                        ];
                    }
                }
            }
        }

        return $issues;
    }

    private function sendEmailAlert($issues, $email)
    {
        try {
            $subject = '🚨 ALERTA CRÍTICA - Sistema de Mantenimiento Predictivo';
            $body = $this->buildAlertMessage($issues);

            // Usar el sistema de mail de Laravel
            Mail::raw($body, function ($message) use ($email, $subject) {
                $message->to($email)
                        ->subject($subject);
            });

            $this->info("📧 Alerta enviada por email a: {$email}");
        } catch (\Exception $e) {
            $this->error("❌ Error al enviar email: " . $e->getMessage());
        }
    }

    private function sendSlackAlert($issues, $channel)
    {
        try {
            $webhookUrl = config('services.slack.webhook_url');

            if (!$webhookUrl) {
                $this->warn('⚠️ Webhook URL de Slack no configurado');
                return;
            }

            $message = $this->buildSlackMessage($issues, $channel);

            Http::post($webhookUrl, $message);

            $this->info("💬 Alerta enviada por Slack al canal: {$channel}");
        } catch (\Exception $e) {
            $this->error("❌ Error al enviar mensaje a Slack: " . $e->getMessage());
        }
    }

    private function buildAlertMessage($issues)
    {
        $message = "🚨 ALERTA CRÍTICA DEL SISTEMA\n\n";
        $message .= "Fecha: " . now()->format('Y-m-d H:i:s') . "\n\n";
        $message .= "Se detectaron los siguientes problemas críticos:\n\n";

        foreach ($issues as $issue) {
            $message .= "🔴 [{$issue['status']}] {$issue['category']} - {$issue['component']}\n";
            $message .= "   {$issue['message']}\n\n";
        }

        $message .= "Por favor, revise el sistema inmediatamente.\n\n";
        $message .= "Sistema de Mantenimiento Predictivo";

        return $message;
    }

    private function buildSlackMessage($issues, $channel)
    {
        $blocks = [
            [
                'type' => 'header',
                'text' => [
                    'type' => 'plain_text',
                    'text' => '🚨 ALERTA CRÍTICA DEL SISTEMA'
                ]
            ],
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => '*Fecha:* ' . now()->format('Y-m-d H:i:s')
                ]
            ],
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => '*Problemas detectados:*'
                ]
            ]
        ];

        foreach ($issues as $issue) {
            $blocks[] = [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "🔴 *{$issue['status']}* - {$issue['category']}: {$issue['component']}\n{$issue['message']}"
                ]
            ];
        }

        $blocks[] = [
            'type' => 'section',
            'text' => [
                'type' => 'mrkdwn',
                'text' => '_Por favor, revise el sistema inmediatamente._'
            ]
        ];

        return [
            'channel' => $channel,
            'blocks' => $blocks
        ];
    }

    private function logToFile($content)
    {
        $logFile = $this->option('log-file');

        if (!$logFile) {
            return;
        }

        try {
            $logPath = storage_path('logs/' . $logFile);

            // Create logs directory if it doesn't exist
            $logDir = dirname($logPath);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            // Prepend timestamp to the content
            $timestamp = now()->format('Y-m-d H:i:s');
            $logEntry = "[{$timestamp}]\n{$content}\n" . str_repeat('-', 50) . "\n";

            // Append to log file
            file_put_contents($logPath, $logEntry, FILE_APPEND | LOCK_EX);

            if (!$this->option('quiet')) {
                $this->info("📝 Resultados guardados en: {$logPath}");
            }
        } catch (\Exception $e) {
            $this->error("❌ Error al guardar log: " . $e->getMessage());
        }
    }
}
