<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$repo = app(\Promolider\Infrastructure\Dashboard\Out\Persistence\EloquentDashboardRepository::class);
$stats = $repo->getTopbarStats(868);
print_r($stats);
