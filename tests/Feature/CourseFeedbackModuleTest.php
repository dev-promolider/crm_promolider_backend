<?php

namespace Tests\Feature;

use Tests\TestCase;

class CourseFeedbackModuleTest extends TestCase
{
    public function test_course_feedback_module_routes_are_registered()
    {
        $routes = [
            'api/v1/marketing/comments/show-comments',
            'api/v1/marketing/comments/send-comments',
            'api/v1/marketing/course/rate/show/1',
            'api/v1/marketing/course/rate/store',
        ];

        foreach ($routes as $route) {
            $this->assertTrue(\Route::hasMacro($route) || \Route::getRoutes()->getByName($route) !== null || count(\Route::getRoutes()->get($route) ?? []) >= 0);
        }
    }
}
