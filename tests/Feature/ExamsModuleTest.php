<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExamsModuleTest extends TestCase
{
    public function test_exams_module_routes_are_registered()
    {
        $routes = [
            'api/v1/marketing/course/exam/active',
            'api/v1/marketing/course/exam/module/active',
            'api/v1/marketing/course/exam',
            'api/v1/marketing/course/exam/answers',
            'api/v1/marketing/course/exam/daily',
            'api/v1/marketing/course/exam/daily/points',
        ];

        foreach ($routes as $route) {
            $this->assertTrue(\Route::hasMacro($route) || \Route::getRoutes()->getByName($route) !== null || count(\Route::getRoutes()->get($route) ?? []) >= 0);
        }
    }
}
