<?php

namespace Promolider\Application\ExamReviews\UseCases;

use App\Models\UserExamHeader;

class ListExamsByCourseUseCase
{
    public function execute($courseId, $productorId, $isAdmin)
    {
        $query = UserExamHeader::select('user_exam_header.*', 'users.name', 'users.last_name', 'exam.title as exam_title', 'exam.course_id')
            ->join('users', 'user_exam_header.user_id', '=', 'users.id')
            ->join('exam', 'user_exam_header.exam_id', '=', 'exam.id')
            ->where('exam.course_id', $courseId);

        if (!$isAdmin) {
            $query->where('user_exam_header.productor_id', $productorId);
        }

        // status = 0 is pending, status = 1 is graded
        // order by pending first (status 0), then created_at
        return $query->orderBy('user_exam_header.status', 'asc')
            ->orderBy('user_exam_header.created_at', 'desc')
            ->get();
    }
}
