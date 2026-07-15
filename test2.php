<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$cols = Illuminate\Support\Facades\DB::select('SHOW COLUMNS FROM classified');
print_r($cols);
$rows = Illuminate\Support\Facades\DB::select('SELECT * FROM classified LIMIT 5');
print_r($rows);
