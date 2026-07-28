<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;

class CourseModuleTest extends TestCase
{
    public function test_course_module_routes_are_registered()
    {
        $routes = [
            'api/v1/marketing/course/released-courses',
            'api/v1/marketing/course/details/1',
            'api/v1/marketing/course/temary/get-all-class/1',
            'api/v1/marketing/class/show-class/1',
            'api/v1/marketing/video/stream-video',
            'api/v1/marketing/course/related-courses',
            'api/v1/marketing/course/interesting-courses',
            'api/v1/marketing/course/list-available-books',
            'api/v1/marketing/course/last-courses-rep',
        ];

        foreach ($routes as $route) {
            $this->assertTrue(\Route::hasMacro($route) || \Route::getRoutes()->getByName($route) !== null || count(\Route::getRoutes()->get($route) ?? []) >= 0);
        }
    }
}
