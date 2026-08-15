<?php

namespace Promolider\Application\ExamReviews\UseCases;

use App\Models\UserExamHeader;
use Illuminate\Support\Facades\DB;

class ListExamReviewCoursesUseCase
{
    public function execute($productorId, $isAdmin)
    {
        $query = UserExamHeader::select(
                'courses.id',
                'courses.title',
                'courses.url_portada as cover',
                DB::raw('count(user_exam_header.id) as total_exams'),
                DB::raw('sum(case when user_exam_header.status = 0 then 1 else 0 end) as pending_exams')
            )
            ->join('exam', 'user_exam_header.exam_id', '=', 'exam.id')
            ->join('courses', 'exam.course_id', '=', 'courses.id');

        if (!$isAdmin) {
            $query->where('user_exam_header.productor_id', $productorId);
        }

        return $query->groupBy('courses.id', 'courses.title', 'courses.url_portada')
            ->orderBy('pending_exams', 'desc')
            ->get();
    }
}
