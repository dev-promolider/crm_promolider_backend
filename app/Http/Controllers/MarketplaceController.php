<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Support\Facades\DB;

class MarketplaceController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:marketplace.toggle')->only('viewMarketPlaceManagement');

        $this->middleware('can:marketplace.toggle')->only('toggleMarketplaceViewability');
    }

    public function viewMarketPlaceManagement()
    {
        $sql = "
        WITH course_subscribers AS (
            SELECT course_id, COUNT(user_id) AS subscriber_number
            FROM purchased_courses
            GROUP BY course_id
        )
        SELECT courses.*, users.username, users.name, users.last_name, course_subscribers.subscriber_number AS subscribers
        FROM courses
        LEFT JOIN course_subscribers ON courses.id = course_subscribers.course_id
        JOIN users ON users.id = courses.user_id
        WHERE courses.status != 0
    ";

        $courses = DB::select(DB::raw($sql));

        return view('content.marketplace.manage', compact('courses'));
    }

    public function toggleMarketplaceViewability($courseId)
    {
        $course = Course::find($courseId);
        $course->marketplace_listed = !$course->marketplace_listed;
        $course->save();

        return response()->json([
            'data' => $course->marketplace_listed,
            'message' => 'Actualizado con éxito.'
        ], 200);
    }
}
