<?php

namespace Promolider\Application\ExamReviews\UseCases;

use App\Models\UserExamHeader;
use App\Models\Exam;
use App\Models\UserQuestionAnswer;
use App\Models\ExamQuestion;

class GetExamReviewDetailsUseCase
{
    public function execute($headerId)
    {
        $header = UserExamHeader::where('id', $headerId)->first();
        if (!$header) {
            throw new \Exception("Exam header not found");
        }
        
        $examId = $header->exam_id;
        $exam = Exam::select('title', 'max_score')->where('id', $examId)->first();
        $details = UserQuestionAnswer::select('options_selected', 'points_gained')->where('user_exam_id', $headerId)->get();
        $questions = ExamQuestion::select('title', 'points', 'question_type_id', 'options')->where('exam_id', $examId)->get();
        
        return compact('details', 'questions', 'exam');
    }
}
