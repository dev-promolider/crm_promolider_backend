<?php
$user = \App\Models\User::find(1);
$request = \Illuminate\Http\Request::create('/api/v1/video/stream-video?class_id=106', 'GET', ['class_id' => 106]);
$request->headers->set('Accept', 'application/json');
$request->setUserResolver(function () use ($user) { return $user; });
$response = app()->handle($request);
echo 'Status: ' . $response->getStatusCode() . PHP_EOL;
echo 'Content: ' . $response->getContent() . PHP_EOL;
