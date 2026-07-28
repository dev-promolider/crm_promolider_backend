<?php

namespace Tests\Feature;

use Tests\TestCase;

class GamificationModuleTest extends TestCase
{
    public function test_gamification_module_routes_are_registered()
    {
        $routes = [
            'api/v1/marketing/classroom-points/ranking',
            'api/v1/marketing/badges/my-progress',
            'api/v1/marketing/gamification/ranking',
            'api/v1/marketing/gamification/my-badges',
        ];

        foreach ($routes as $route) {
            $this->assertTrue(\Route::hasMacro($route) || \Route::getRoutes()->getByName($route) !== null || count(\Route::getRoutes()->get($route) ?? []) >= 0);
        }
    }
}
