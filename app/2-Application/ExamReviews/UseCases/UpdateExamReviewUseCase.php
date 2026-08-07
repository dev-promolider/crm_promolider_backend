<?php

namespace Promolider\Application\ExamReviews\UseCases;

use Promolider\Application\ExamReviews\Services\ExamReviewService;

class UpdateExamReviewUseCase
{
    private $examReviewService;

    public function __construct(ExamReviewService $examReviewService)
    {
        $this->examReviewService = $examReviewService;
    }

    public function execute($ratesArray, $examId)
    {
        return $this->examReviewService->setNoteInOpenQuestion($ratesArray, $examId);
    }
}
