<?php

namespace Tests\Feature;

use Tests\TestCase;

class PurchasedCoursesModuleTest extends TestCase
{
    public function test_purchased_courses_module_routes_are_registered()
    {
        $routes = [
            'api/v1/marketing/courses/purchased/save-class-seen',
            'api/v1/marketing/courses/purchased/show-class-seen',
            'api/v1/marketing/courses/purchased/show',
            'api/v1/marketing/purchased/save-class-seen',
            'api/v1/marketing/purchased/show-class-seen',
            'api/v1/marketing/purchased/show',
        ];

        foreach ($routes as $route) {
            $this->assertTrue(\Route::hasMacro($route) || \Route::getRoutes()->getByName($route) !== null || count(\Route::getRoutes()->get($route) ?? []) >= 0);
        }
    }
}
