<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserDailyQuizz;
use App\Models\AccountTypePointsMoney;
use App\Models\AccountType;
use App\Models\Badge;
use App\Models\BadgeDetail;
use App\Models\ClassroomPointDetail;
use App\Models\Option;
use App\Models\UserClassroomPoint;
use Illuminate\Support\Facades\DB;
use App\Models\Notifications;
use Illuminate\Support\Facades\Cache;

class DailyQuestion extends Controller
{
    public function get()
    {
        $user = auth()->user();
        if (!$user->daily_quizz_status) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://the-trivia-api.com/api/questions?limit=1",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => "",
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "cache-control: no-cache"
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            $data = json_decode($response, true);
            if ($data && !empty($data)) {
                $questionData = $data[0];

                // Almacenar la respuesta correcta en caché por 24 horas
                $cacheKey = "daily_question_" . $user->id . "_" . date('Y-m-d');
                Cache::put($cacheKey, [
                    'question_id' => $questionData['id'] ?? uniqid(),
                    'correct_answer' => $questionData['correctAnswer']
                ], now()->addHours(24));

                // Retornar sin la respuesta correcta
                $questionData['correctAnswer'] = null;
                return $questionData;
            }
            return array("error" => "Could not fetch question");
        } else {
            return array("message" => "try again tomorrow");
        }
    }

    public function validateResponseDaily(Request $request)
    {
        $request->validate([
            'userAnswer' => 'required|string',
            'questionId' => 'string'
        ]);
    
        $user = auth()->user();
        
        // Verificar si ya respondió hoy (protección adicional)
        if ($user->daily_quizz_status) {
            return response()->json(['error' => 'Already answered today'], 400);
        }
    
        // Obtener la respuesta correcta almacenada en caché
        $cacheKey = "daily_question_" . $user->id . "_" . date('Y-m-d');
        $storedQuestion = Cache::get($cacheKey);
        
        if (!$storedQuestion) {
            return response()->json(['error' => 'Question expired or not found'], 404);
        }
    
        $actual_points = UserClassroomPoint::where('id_user', $user->id)->first()->total_points;
    
        try {
            DB::beginTransaction();
            
            // Marcar como respondida ANTES de procesar puntos
            $this->validateOneTryPerDay($user->id);
            
            // Validar la respuesta comparando con la almacenada en el servidor
            $isCorrect = strtolower(trim($request->userAnswer)) === strtolower(trim($storedQuestion['correct_answer']));
            
            // Limpiar caché después de responder
            Cache::forget($cacheKey);
        
            if ($isCorrect) {
                $points = Option::where('description', 'daily_question')->first()->value;
                $this->storeDetailPoints($user->id, $points);
                $actual_points = $actual_points + $points;
            
                //INCREMENTAR CONTADOR DAILY
                $quizz = UserDailyQuizz::where('id_user', $user->id)->first()->passed_quizz;
                $new_quizz = $quizz + 1;
                UserDailyQuizz::where('id_user', $user->id)->update(['passed_quizz' => $new_quizz]);
            
                // Badge logic (sin cambios)
                $badge_level_one_id = 13;
                $userHasBadge1 = $this->validateIfUserHasBadge($badge_level_one_id, $user->id);
                if (!$userHasBadge1) {
                    $goal = Badge::where('id', $badge_level_one_id)->first()->condition;
                    $count_passed_quizz = UserDailyQuizz::where('id_user', $user->id)->first()->passed_quizz;
                    if ($count_passed_quizz >= $goal) {
                        $badge = new BadgeDetail();
                        $badge->user_id = $user->id;
                        $badge->badge_id = $badge_level_one_id;
                        $badge->save();
                        $title = 'Logro desbloqueado';
                        $body = 'Ha conseguido el logro por responder la pregunta diaria correctamente';
                        $this->notification($user->id, $title, $body);
                    }
                }
            
                $badge_level_two_id = 14;
                $userHasBadge2 = $this->validateIfUserHasBadge($badge_level_two_id, $user->id);
                if (!$userHasBadge2) {
                    $goal = Badge::where('id', $badge_level_two_id)->first()->condition;
                    $count_passed_quizz = UserDailyQuizz::where('id_user', $user->id)->first()->passed_quizz;
                    if ($count_passed_quizz >= $goal) {
                        $badge = new BadgeDetail();
                        $badge->user_id = $user->id;
                        $badge->badge_id = $badge_level_two_id;
                        $badge->save();
                        $title = 'Logro desbloqueado';
                        $body = "Ha conseguido el logro por responder la pregunta diaria correctamente $goal veces";
                        $this->notification($user->id, $title, $body);
                    }
                }
            
                $badge_level_three_id = 15;
                $userHasBadge3 = $this->validateIfUserHasBadge($badge_level_three_id, $user->id);
                if (!$userHasBadge3) {
                    $goal = Badge::where('id', $badge_level_three_id)->first()->condition;
                    $count_passed_quizz = UserDailyQuizz::where('id_user', $user->id)->first()->passed_quizz;
                    if ($count_passed_quizz >= $goal) {
                        $badge = new BadgeDetail();
                        $badge->user_id = $user->id;
                        $badge->badge_id = $badge_level_three_id;
                        $badge->save();
                        $title = 'Logro desbloqueado';
                        $body = "Ha conseguido el logro por responder la pregunta diaria correctamente $goal veces";
                        $this->notification($user->id, $title, $body);
                    }
                }
            
                DB::commit();
                return array("earned_points" => $points, "total_points" => $actual_points, "correct" => true);
            } else {
                DB::commit();
                return array("earned_points" => 0, "total_points" => $actual_points, "correct" => false);
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function validateIfUserHasBadge($badge_id, $user_id)
    {
        $bool = BadgeDetail::where(['user_id' => $user_id, 'badge_id' => $badge_id])->exists();
        return $bool;
    }

    public function notification($id_user, $title, $body)
    {
        $notification = new Notifications();
        $notification->id_generator = $id_user;
        $notification->id_receiver =  $id_user;
        $notification->title = $title;
        $notification->body = $body;
        $notification->type = 1;
        $notification->save();
    }

    public function validateOneTryPerDay($user_id)
    {
        try {
            DB::beginTransaction();
            User::where('id', $user_id)->update(['daily_quizz_status' => true]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function storeDetailPoints($user_id, $points)
    {
        $detail = new ClassroomPointDetail();
        $header_id = UserClassroomPoint::where('id_user', $user_id)->get()->first()->id;
        $detail->id_user_classroom_points = $header_id;
        $detail->increment_points = $points;
        $detail->description = 'Pregunta diaria';
        $detail->save();
        $this->addPoints($header_id, $points);
    }

    /**
     * header -> user_classroom_points id
     */
    public function addPoints($header_id, $points)
    {
        $actual_points = UserClassroomPoint::where('id', $header_id)->get()->first()->total_points;
        $new_total_points = $actual_points + $points;
        UserClassroomPoint::where('id', $header_id)->update(['total_points' => $new_total_points]);
    }
}
