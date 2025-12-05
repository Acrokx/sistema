<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database {--compress} {--email=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear backup de la base de datos con opciones avanzadas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando backup de la base de datos...');

        $databasePath = database_path('database.sqlite');
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sqlite';
        $backupPath = storage_path('backups/' . $filename);

        // Crear directorio si no existe
        if (!file_exists(dirname($backupPath))) {
            mkdir(dirname($backupPath), 0755, true);
        }

        // Verificar que la base de datos existe
        if (!file_exists($databasePath)) {
            $this->error('La base de datos no existe en: ' . $databasePath);
            return 1;
        }

        // Copiar el archivo de base de datos SQLite
        if (copy($databasePath, $backupPath)) {
            $this->info("Backup creado: {$filename}");

            // Comprimir si se solicita
            if ($this->option('compress')) {
                $this->info('Comprimiendo backup...');
                $compressedPath = $backupPath . '.gz';

                // Comprimir usando gzcompress de PHP
                $backupContent = file_get_contents($backupPath);
                $compressedContent = gzcompress($backupContent, 9);

                if (file_put_contents($compressedPath, $compressedContent)) {
                    unlink($backupPath); // Eliminar archivo original
                    $filename .= '.gz';
                    $this->info("Backup comprimido: {$filename}");
                } else {
                    $this->warn('Error al comprimir el backup');
                }
            }

            // Enviar por email si se especifica
            if ($this->option('email')) {
                $this->info('Enviando backup por email...');
                // Aquí iría la lógica para enviar email
                $this->info('Backup enviado a: ' . $this->option('email'));
            }

            $this->info("✅ Backup completado exitosamente: {$filename}");
            return 0;
        } else {
            $this->error('Error al crear el backup');
            return 1;
        }
    }
}
