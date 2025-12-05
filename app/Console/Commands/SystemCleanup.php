<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class SystemCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:cleanup
                        {--logs : Limpiar archivos de log}
                        {--cache : Limpiar cache}
                        {--sessions : Limpiar sesiones expiradas}
                        {--temp : Limpiar archivos temporales}
                        {--all : Ejecutar todas las limpiezas}
                        {--days=7 : Días de antigüedad para eliminar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpiar archivos temporales, logs, cache y sesiones del sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');

        $this->info("🧹 Iniciando limpieza del sistema (archivos de {$days} días de antigüedad)...");

        if ($this->option('all')) {
            $this->cleanLogs($days);
            $this->cleanCache();
            $this->cleanSessions($days);
            $this->cleanTempFiles($days);
        } else {
            if ($this->option('logs')) {
                $this->cleanLogs($days);
            }
            if ($this->option('cache')) {
                $this->cleanCache();
            }
            if ($this->option('sessions')) {
                $this->cleanSessions($days);
            }
            if ($this->option('temp')) {
                $this->cleanTempFiles($days);
            }
        }

        if (!$this->option('logs') && !$this->option('cache') && !$this->option('sessions') && !$this->option('temp') && !$this->option('all')) {
            $this->warn('No se especificó ninguna opción de limpieza. Use --help para ver las opciones disponibles.');
            return 1;
        }

        $this->info('✅ Limpieza del sistema completada exitosamente.');
        return 0;
    }

    private function cleanLogs($days)
    {
        $this->info('🗂️  Limpiando archivos de log...');

        $logPath = storage_path('logs');
        $files = glob($logPath . '/*.log');
        $deleted = 0;

        foreach ($files as $file) {
            if (filemtime($file) < strtotime("-{$days} days")) {
                unlink($file);
                $deleted++;
            }
        }

        $this->info("📄 Eliminados {$deleted} archivos de log antiguos");
    }

    private function cleanCache()
    {
        $this->info('🗄️  Limpiando cache del sistema...');

        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        $this->info('🧽 Cache limpiado completamente');
    }

    private function cleanSessions($days)
    {
        $this->info('🕒 Limpiando sesiones expiradas...');

        $sessionPath = storage_path('framework/sessions');
        $files = glob($sessionPath . '/*');
        $deleted = 0;

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < strtotime("-{$days} days")) {
                unlink($file);
                $deleted++;
            }
        }

        $this->info("🗂️  Eliminadas {$deleted} sesiones expiradas");
    }

    private function cleanTempFiles($days)
    {
        $this->info('🗂️  Limpiando archivos temporales...');

        $tempPaths = [
            storage_path('app/temp'),
            storage_path('app/tmp'),
            sys_get_temp_dir() . '/laravel-*',
        ];

        $deleted = 0;

        foreach ($tempPaths as $path) {
            if (is_dir($path)) {
                $files = glob($path . '/*');
                foreach ($files as $file) {
                    if (is_file($file) && filemtime($file) < strtotime("-{$days} days")) {
                        unlink($file);
                        $deleted++;
                    }
                }
            }
        }

        $this->info("🗑️  Eliminados {$deleted} archivos temporales");
    }
}
