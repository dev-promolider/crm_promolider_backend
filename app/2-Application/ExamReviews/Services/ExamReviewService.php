<?php

namespace Promolider\Application\ExamReviews\Services;

use App\Models\Exam;
use App\Models\UserExamHeader;
use App\Models\UserQuestionAnswer;
use App\Models\Badge;
use App\Models\BadgeDetail;
use App\Models\Notifications;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExamReviewService
{
    public function setNoteInOpenQuestion($ratesArray, $userExamId)
    {
        try {
            DB::beginTransaction();

            $userQuestionsAnswers = UserQuestionAnswer::where('user_exam_id', $userExamId)->orderBy('id', 'asc')->get();

            foreach ($ratesArray as $index => $rate) {
                if ($rate !== "null" && isset($userQuestionsAnswers[$index])) {
                    $currentDetail = $userQuestionsAnswers[$index];
                    $currentDetail->points_gained = (float) $rate;
                    $currentDetail->update();
                }
            }

            $rates = UserQuestionAnswer::where('user_exam_id', $userExamId)->pluck('points_gained')->toArray();
            $totalRate = array_sum($rates);

            $header = UserExamHeader::where('id', $userExamId)->first();
            if (!$header) {
                throw new \Exception("Exam header not found");
            }

            $header->status = 1; // Evaluated
            $header->rate = $totalRate;
            $header->condition = $this->getExamCondition($header->exam_id, $totalRate);
            $header->update();

            if ($header->condition === 'Approved') {
                $this->badgeForPassingTheExam($header->user_id);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error reviewing exam: " . $e->getMessage());
            throw $e;
        }
    }

    private function getExamCondition($examId, $userRate)
    {
        $exam = Exam::select('min_passing_score')->where('id', $examId)->first();
        $minScore = $exam ? $exam->min_passing_score : 0;
        
        if ($userRate >= $minScore) {
            return "Approved";
        } else {
            return "Disaproved"; // Note: typo kept from original codebase
        }
    }

    private function badgeForPassingTheExam($userId)
    {
        // 4 .. 6 = ID LOGRO PARA EL COMPRADOR DE CURSOS 
        $validateBadgesDetailsComplete = BadgeDetail::where('user_id', $userId)
            ->where('badge_id', '>=', 4)
            ->where('badge_id', '<=', 6)
            ->get();

        // VALIDAR SI YA TIENE LAS 3 INSIGNIAS DE EXAMENES       
        if (count($validateBadgesDetailsComplete) == 3) {
            return;
        }

        $userExamHeader = UserExamHeader::where(['user_id' => $userId, 'condition' => 'Approved'])->get();

        if (count($userExamHeader) > 0) {
            $badges = Badge::select('id', 'name', 'description', 'level', 'condition', 'icon')
                ->where('id', '>=', 4)
                ->where('id', '<=', 6)
                ->orderBy('condition')
                ->get();

            $this->validateBadge($badges, $userExamHeader, $userId);
        }
    }

    private function validateBadge($badges, $userExamHeader, $userId)
    {
        for ($i = 0; $i < count($badges); $i++) {
            $badge = $badges[$i];

            if (count($userExamHeader) >= $badge->condition) {
                $badgesDetails = BadgeDetail::select('id', 'user_id', 'badge_id')
                    ->where(['user_id' => $userId, 'badge_id' => $badge->id])
                    ->get();

                if (count($badgesDetails) == 0) {
                    $badgeDetail = new BadgeDetail();
                    $badgeDetail->user_id = $userId;
                    $badgeDetail->badge_id = $badge->id;

                    if ($badgeDetail->save()) {
                        $notification = new Notifications();
                        $notification->id_generator = 1;
                        $notification->id_receiver = $userId;
                        $notification->id_badge = $badge->id;
                        $notification->title = "Logro desbloqueado";
                        $notification->body = "Obtuvo el logro de " . $badge->name;
                        $notification->type = 1;
                        $notification->seen = 0;
                        $notification->save();
                    }
                }
            } else {
                $i = count($badges); // Break loop
            }
        }
    }
}
