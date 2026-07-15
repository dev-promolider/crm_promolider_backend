<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Models\MasterclassImage;
use App\Models\EbookImage;
use App\Models\MiniCourseImage;
use App\Models\MasterclassDocument;
use App\Models\EbookDocument;

class MigrateImagesToS3 extends Command
{
    protected $signature   = 'storage:migrate-to-s3
                                {--source-domain=https://crm.promolider.info : URL base del servidor de producción}
                                {--dry-run : Solo simula, no sube nada}';

    protected $description = 'Migra imágenes y documentos desde el servidor de producción hacia el bucket S3';

    /** Modelos y columnas a migrar */
    private array $targets = [
        ['model' => MasterclassImage::class,  'col' => 'image',    'label' => 'Masterclass Images'],
        ['model' => EbookImage::class,         'col' => 'image',    'label' => 'Ebook Images'],
        ['model' => MiniCourseImage::class,    'col' => 'image',    'label' => 'MiniCourse Images'],
        ['model' => MasterclassDocument::class,'col' => 'document', 'label' => 'Masterclass Documents'],
        ['model' => EbookDocument::class,      'col' => 'document', 'label' => 'Ebook Documents'],
    ];

    public function handle(): int
    {
        $sourceDomain = rtrim($this->option('source-domain'), '/');
        $dryRun       = $this->option('dry-run');

        $this->info('=== Migración de archivos a S3 ===');
        if ($dryRun) {
            $this->warn('  [DRY-RUN] No se subirá nada al bucket.');
        }
        $this->line("  Origen: {$sourceDomain}");
        $this->line("  Bucket: " . config('filesystems.disks.s3.bucket'));
        $this->newLine();

        $totalOk  = 0;
        $totalErr = 0;

        foreach ($this->targets as $target) {
            $model = $target['model'];
            $col   = $target['col'];
            $label = $target['label'];

            $this->info("► {$label}");

            $records = $model::whereNotNull($col)->get();

            if ($records->isEmpty()) {
                $this->line("  (sin registros)");
                continue;
            }

            $bar = $this->output->createProgressBar($records->count());
            $bar->start();

            foreach ($records as $record) {
                $path = $record->$col;

                // Si ya es URL completa (http/https), la ignoramos o la descargamos
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    $downloadUrl = $path;
                    $s3Key       = $this->urlToS3Key($path);
                } else {
                    // Ruta relativa: "storage/masterclasses/..." o "masterclasses/..."
                    $cleanPath   = preg_replace('#(?<!:)//+#', '/', $path);
                    $cleanPath   = ltrim($cleanPath, '/');
                    $downloadUrl = "{$sourceDomain}/{$cleanPath}";
                    $s3Key       = $cleanPath;
                }

                // Omitir si ya existe en S3
                if (!$dryRun && Storage::disk('s3')->exists($s3Key)) {
                    $bar->advance();
                    continue;
                }

                if ($dryRun) {
                    $bar->advance();
                    $totalOk++;
                    continue;
                }

                try {
                    $response = Http::timeout(30)->get($downloadUrl);

                    if (!$response->successful()) {
                        $this->newLine();
                        $this->warn("  [SKIP] {$downloadUrl} → HTTP {$response->status()}");
                        $totalErr++;
                        $bar->advance();
                        continue;
                    }

                    Storage::disk('s3')->put($s3Key, $response->body(), 'public');

                    $totalOk++;
                } catch (\Exception $e) {
                    $this->newLine();
                    $this->error("  [ERR] {$downloadUrl}: " . $e->getMessage());
                    $totalErr++;
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        $this->newLine();
        $this->info("✅ Migrados correctamente: {$totalOk}");
        if ($totalErr > 0) {
            $this->warn("⚠️  Con errores/skipped:    {$totalErr}");
        }

        $this->newLine();
        $this->info('💡 Ahora actualiza tu .env:');
        $this->line('   STORAGE_DOMAIN=https://promolider-storage-user.s3-accelerate.amazonaws.com');

        return self::SUCCESS;
    }

    /** Convierte una URL completa en una S3 key relativa */
    private function urlToS3Key(string $url): string
    {
        // Extraer solo el path de la URL
        $path = parse_url($url, PHP_URL_PATH) ?? $url;
        return ltrim($path, '/');
    }
}
