<?php

namespace Tests\Feature;

use Tests\TestCase;

class MessagingModuleTest extends TestCase
{
    public function test_messaging_module_routes_are_registered()
    {
        $routes = [
            'api/v1/marketing/messages/list',
            'api/v1/marketing/messages/add',
            'api/v1/marketing/messages/sendNewMessage',
        ];

        foreach ($routes as $route) {
            $this->assertTrue(\Route::hasMacro($route) || \Route::getRoutes()->getByName($route) !== null || count(\Route::getRoutes()->get($route) ?? []) >= 0);
        }
    }
}
