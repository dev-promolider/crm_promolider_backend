<?php

namespace Promolider\Infrastructure\Marketing\Out\Persistence;

use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\UserExamHeader;
use App\Models\UserQuestionAnswer;
use App\Models\UserExam as UserExamModel;
use Promolider\Domain\Marketing\Ports\Out\ExamRepositoryInterface;

class EloquentExamRepository implements ExamRepositoryInterface
{
    public function getActiveExam(string $examType, int $idType): ?array
    {
        $fieldMap = [
            'course' => 'course_id',
            'module' => 'module_id',
            'class'  => 'lesson_id',
        ];

        $field = $fieldMap[$examType] ?? null;

        if (!$field) {
            return null;
        }

        $exam = Exam::where($field, $idType)
            ->where('status', 1)
            ->first();

        return $exam ? $exam->toArray() : null;
    }

    public function getExamQuestions(int $examId): array
    {
        return ExamQuestion::where('exam_id', $examId)
            ->orderBy('id')
            ->get()
            ->toArray();
    }

    public function findExamById(int $examId): ?array
    {
        $exam = Exam::find($examId);
        return $exam ? $exam->toArray() : null;
    }

    public function userHasApproved(int $examId, int $userId): bool
    {
        return UserExamHeader::where('exam_id', $examId)
            ->where('user_id', $userId)
            ->where('condition', 'Approved')
            ->exists();
    }

    public function getUserAttemptsCount(int $examId, int $userId): int
    {
        return UserExamHeader::where('exam_id', $examId)
            ->where('user_id', $userId)
            ->count();
    }

    public function userHasWaitingAttempt(int $examId, int $userId): bool
    {
        return UserExamHeader::where('exam_id', $examId)
            ->where('user_id', $userId)
            ->where('condition', 'Waiting')
            ->exists();
    }

    public function createUserExamHeader(int $userId, int $productorId, int $examId): int
    {
        $header = new UserExamHeader();
        $header->user_id = $userId;
        $header->productor_id = $productorId;
        $header->exam_id = $examId;
        $header->rate = 0;
        $header->condition = 'Waiting';
        $header->status = false;
        $header->save();

        return $header->id;
    }

    public function saveUserAnswer(int $userExamId, float $pointsGained, $optionsSelected): void
    {
        $answer = new UserQuestionAnswer();
        $answer->user_exam_id = $userExamId;
        $answer->points_gained = $pointsGained;
        $answer->options_selected = is_array($optionsSelected) ? $optionsSelected : [ 'response' => $optionsSelected ];
        $answer->save();
    }

    public function updateUserExamHeader(int $headerId, float $rate, string $condition, bool $status): void
    {
        $header = UserExamHeader::find($headerId);
        if ($header) {
            $header->rate = $rate;
            $header->condition = $condition;
            $header->status = $status;
            $header->save();
        }
    }

    public function getLatestUserExamHeader(int $examId, int $userId): ?array
    {
        $header = UserExamHeader::where('exam_id', $examId)
            ->where('user_id', $userId)
            ->latest()
            ->first();

        return $header ? $header->toArray() : null;
    }

    public function getUserAnswers(int $userExamId): array
    {
        return UserQuestionAnswer::where('user_exam_id', $userExamId)
            ->orderBy('id')
            ->get()
            ->toArray();
    }

    public function saveUserExam(int $courseId, int $userId, int $examTypeId, float $note): void
    {
        $userExam = new UserExamModel();
        $userExam->course_id = $courseId;
        $userExam->user_id = $userId;
        $userExam->exam_type_id = $examTypeId;
        $userExam->exam_note = $note;
        $userExam->save();
    }

    public function getCalificationByLesson(int $lessonId, int $userId): ?array
    {
        $exam = Exam::where('lesson_id', $lessonId)->first();
        if (!$exam) {
            return null;
        }

        $header = UserExamHeader::where('exam_id', $exam->id)
            ->where('user_id', $userId)
            ->first();

        if (!$header) {
            return null;
        }

        return [
            'exam_id' => $exam->id,
            'title' => $exam->title,
            'max_score' => $exam->max_score,
            'rate' => $header->rate,
            'condition' => $header->condition,
        ];
    }

    public function getExamProducer(int $examId): ?array
    {
        $exam = Exam::find($examId);
        if (!$exam || !$exam->productor_id) {
            return null;
        }

        $producer = \App\Models\User::find($exam->productor_id);
        return $producer ? $producer->toArray() : null;
    }

    public function getMinPassingScore(int $examId): int
    {
        $exam = Exam::find($examId);
        return $exam ? (int) $exam->min_passing_score : 0;
    }
}
