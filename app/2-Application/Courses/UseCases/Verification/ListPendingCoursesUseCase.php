<?php

namespace Promolider\Application\Courses\UseCases\Verification;

use App\Models\Course;

class ListPendingCoursesUseCase
{
    public function execute()
    {
        return Course::where('status', 1)
            ->join('users', 'courses.user_id', '=', 'users.id')
            ->select('courses.*', 'users.name as name', 'users.last_name as last_name')
            ->get();
    }
}
