<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\BackupMailable;
use App\Services\PHPMailerService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
class SendBackupMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backupmail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía email de backup de base de datos usando PHPMailerService';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $phpMailerService = new PHPMailerService();
            
            // Email del administrador
            $email = 'dsanchez@promolider.org';
            
            // Datos para la plantilla de backup
            $templateData = [
                'backup_date' => now()->format('d/m/Y H:i:s'),
                'server_name' => gethostname(),
                'database_name' => env('DB_DATABASE', 'promolider'),
                'admin_email' => $email
            ];

            $subject = 'Backup de Base de Datos - ' . now()->format('d/m/Y');
            
            // Usar plantilla de email para backup
            $phpMailerService->sendEmailWithTemplate(
                $email,
                $subject,
                'emails.database-backup', // Plantilla para backup
                $templateData,
                'Promolíder - Sistema de Backup'
            );
            
            $this->info('Email de backup enviado exitosamente a: ' . $email);
            
            Log::info('Comando de backup ejecutado exitosamente', [
                'email' => $email,
                'timestamp' => now()
            ]);
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error enviando email de backup: ' . $e->getMessage());
            
            Log::error('Error en comando de backup: ' . $e->getMessage());
            
            return 1;
        }
    }
}