<?php

namespace App\Http\Controllers;

use App\Models\ExamQuestion;
use App\Models\UserExamHeader;
use App\Models\UserQuestionAnswer;
use Illuminate\Http\Request;
use App\Models\Badge;
use App\Models\BadgeDetail;
use App\Models\Exam;
use App\Models\Notifications;

class UserExamHeaderController extends Controller
{

    public function index()
    {
        $user =  auth()->user();
        return view('content.courses.exam.rate', compact('user'));
    }

    public function list($id)
    {
        $user_exams = UserExamHeader::select('user_exam_header.*', 'users.name', 'users.last_name')->where(['productor_id' => $id, 'status' => 0])->join('users', 'user_exam_header.user_id', '=', 'users.id')->get();

        return response()->json([
            'data' => $user_exams,
            'message' => 'Data recuperada con exito',
        ], 200);
    }

    public function detailList($header_id)
    {

        $exam_id = UserExamHeader::where('id', $header_id)->get()->first()->exam_id;
        $exam = Exam::select('title', 'max_score')->where('id', $exam_id)->get()->first();
        $details = UserQuestionAnswer::select('options_selected', 'points_gained')->where('user_exam_id', $header_id)->get();
        $questions = ExamQuestion::select('title', 'points', 'question_type_id', 'options')->where('exam_id', $exam_id)->get();
        return compact('details', 'questions', 'exam');
    }

    public function update(Request $request)
    {
        $rate_array = explode(',', $request->rate);
        return $this->setNoteInOpenQuestion($rate_array, $request->exam_id);
    }

    public function setNoteInOpenQuestion($array, $user_exam_id)
    {
        $user_questions_answers = UserQuestionAnswer::where('user_exam_id', $user_exam_id)->get();

        $test = [];
        foreach ($array as $index => $rate) {
            if ($rate != "null") {
                $current_detail_id = $user_questions_answers[$index]->id;
                $current_detail = UserQuestionAnswer::where('id', $current_detail_id)->get()->first();
                $current_detail->points_gained = $rate;
                $current_detail->update();
            }
        }

        $rates = UserQuestionAnswer::where('user_exam_id', $user_exam_id)->pluck('points_gained')->toArray();
        $total_rate = array_sum($rates);

        $header = UserExamHeader::where('id', $user_exam_id)->get()->first();
        $header->status = 1;
        $header->rate = $total_rate;
        $header->condition = $this->getExamCondition($header->exam_id, $total_rate);
        $header->update();
        # Crear notificación que se calificó el exámen
        $this->badgeForPassingTheExam($header->user_id);
    }

    public function getExamCondition($exam_id, $user_rate)
    {
        $min_score = Exam::select('min_passing_score')->where('id', $exam_id)->get()->first()->min_passing_score;
        if ($user_rate >= $min_score) {
            $condition = "Approved";
        } else {
            $condition = "Disaproved";
        }
        return $condition;
    }
    public function badgeForPassingTheExam($user_id)
    {
        // 4 .. 6 = ID LOGRO PARA EL COMPRADOR DE CURSOS 
        $validate_badges_details_complete = BadgeDetail::where('user_id', '=', $user_id)
            ->where('badge_id', '>=', 4)
            ->where('badge_id', '<=', 6)
            ->get();

        //VALIDAR SI YA TIENE LAS 3 INSIGNIAS DE EXAMENES       
        if (count($validate_badges_details_complete) == 3) {
            return;
        }

        $userExamHeader = UserExamHeader::where(['user_id' => $user_id, 'condition' => 'Approved'])->get();

        if (count($userExamHeader) > 0) {
            $badges = Badge::select('id', 'name', 'description', 'level', 'condition', 'icon')
                ->where('id', '>=', 4)
                ->where('id', '<=', 6)
                ->orderBy('condition')
                ->get();

            $this->validateBadge($badges, $userExamHeader, $user_id);
        }
    }

    public function validateBadge($badges, $userExamHeader, $user_id)
    {

        for ($i = 0; $i < count($badges); $i++) {
            $badge = $badges[$i];

            if (count($userExamHeader) >= $badge->condition) {
                $badges_details = BadgeDetail::select('id', 'user_id', 'badge_id',)
                    ->where(['user_id' => $user_id, 'badge_id' => $badge->id])
                    ->get();

                if (count($badges_details) == 0) {
                    $badge_detail = new BadgeDetail();
                    $badge_detail->user_id = $user_id;
                    $badge_detail->badge_id = $badge->id;

                    if ($badge_detail->save()) {

                        $notification = new Notifications();
                        $notification->id_generator = 1;
                        $notification->id_receiver = $user_id;
                        $notification->id_badge = $badge->id;
                        $notification->title = "Logro desbloqueado";
                        $notification->body = "Obtuvo el logro de " . $badge->name;
                        $notification->type = 1;
                        $notification->seen = 0;
                        $notification->save();
                    }
                }
            } else {
                $i = count($badges);
            }
        }
    }
}
