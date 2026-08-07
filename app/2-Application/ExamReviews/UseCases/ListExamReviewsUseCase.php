<?php

namespace Promolider\Application\ExamReviews\UseCases;

use App\Models\UserExamHeader;

class ListExamReviewsUseCase
{
    public function execute($productorId)
    {
        return UserExamHeader::select('user_exam_header.*', 'users.name', 'users.last_name')
            ->where(['productor_id' => $productorId, 'status' => 0])
            ->join('users', 'user_exam_header.user_id', '=', 'users.id')
            ->get();
    }
}
