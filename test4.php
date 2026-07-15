<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$repo = app(Promolider\Infrastructure\Dashboard\Out\Persistence\EloquentDashboardRepository::class);
try {
    print_r($repo->getTopbarStats(868));
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
