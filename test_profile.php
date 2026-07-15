<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = App\Models\User::find(868);

$request = Illuminate\Http\Request::create('/api/v1/profile/info', 'GET');
$request->setUserResolver(function() use ($user) { return $user; });

$response = $kernel->handle($request);
echo $response->status() . "\n";
echo $response->content();
