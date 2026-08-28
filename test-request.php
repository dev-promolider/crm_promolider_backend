<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$user = \App\Models\User::find(1);
$request = \Illuminate\Http\Request::create('/api/v1/marketing/courses/video/stream?class_id=106', 'GET', ['class_id' => 106]);
$request->setUserResolver(function () use ($user) { return $user; });
$response = $app->handle($request);
echo 'Status: ' . $response->getStatusCode() . PHP_EOL;
echo 'Content: ' . $response->getContent() . PHP_EOL;
