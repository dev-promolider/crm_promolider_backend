<?php
$class = \App\Models\Clas::where('name', 'like', '%Fundamentos del ciclo de vida%')->first();
echo 'Class ID: ' . $class->id . PHP_EOL;
echo 'Video: ' . ($class->video ? 'YES' : 'NO');
