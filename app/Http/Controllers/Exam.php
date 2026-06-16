<?php

namespace App\Http\Controllers;

use App\Models\Clas;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\UserExam;
use App\Models\ExamQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\UserExamHeader;
use App\Models\UserConfiguration;
use App\Models\Exam as ModelsExam;
use App\Models\UserClassroomPoint;
use App\Models\UserQuestionAnswer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ClassroomPointDetail;
use App\Http\Controllers\UserExamHeaderController;

class Exam extends Controller
{
    public function getIndicators(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $totalUsers = UserExamHeader::whereBetween('created_at', [$start_date, $end_date])->select('user_id')->distinct()->get()->count();
        $totalApprovedUsers = UserExamHeader::whereBetween('created_at', [$start_date, $end_date])->where('condition', 'Aproved')->select('user_id')->distinct()->get()->count();
        $approvalRate = ($totalApprovedUsers / $totalUsers) * 100;

        return response()->json([
            'totalUsers' => $totalUsers,
            'totalApprovedUsers' => $totalApprovedUsers,
            'approvalRate' => $approvalRate
        ], 200);
    }

    public function getCalification(Request $request)
    {
        // search exam by lesson id
        $exists = ModelsExam::where('lesson_id', $request->lesson_id)->exists();
        if ($exists) {
            $exam = ModelsExam::where('lesson_id', $request->lesson_id)->first();
            $detail = UserExamHeader::where('exam_id', $exam->id)->where('user_id', $request->user_id)->first();
            $productor = User::where('id', $exam->productor_id)->first();
            return response()->json([
                'title' => $exam->title,
                'teacher' => $productor->name . ' ' . $productor->last_name,
                'max_score' => $exam->max_score,
                'calification' => $detail->rate,
                'status' => $detail->condition,
                'message' => 'Examen ya realizado'
            ]);
        } else {
            return response()->json([
                'message' => 'Examen no realizado'
            ]);
        }
    }

    public function create($id)
    {
        $user_id = auth()->user()->id;
        $course = Course::where('courses.id', $id)->join('categories', 'courses.id_categories', '=', 'categories.id')->join('course_level', 'courses.course_level_id', '=', 'course_level.id')->select('courses.*', 'course_level.description as level', 'categories.name as category')->get()->first();
        $isUserCourse =  $this->isUserContent('course', $user_id, $course->id);
        if (is_null($course) || $isUserCourse === false) {
            return view('content.miscellaneous.error');
        } else {
            return view('content.courses.exam.index', compact('course'));
        };
    }

    public function createModuleExam($course_id)
    {
        $user_id = auth()->user()->id;
        $isUserCourse =  $this->isUserContent('module', $user_id, $course_id);
        if ($isUserCourse === false) {
            return view('content.miscellaneous.error');
        } else {
            $modules = Course::find($course_id)->modules;
            return view('content.courses.exam.module.index', compact('modules', 'course_id'));
        };
    }

    public function createLessonExam($course_id)
    {
        $user_id = auth()->user()->id;
        $isUserCourse =  $this->isUserContent('lesson', $user_id, $course_id);


        if ($isUserCourse === false) {
            return view('content.miscellaneous.error');
        } else {
            $modules = Course::find($course_id)->modules;
            return view('content.courses.exam.lesson.index', compact('modules', 'course_id'));
        };
    }

    // Verify if is user content
    public function isUserContent($type, $user_id, $course_id)
    {
        switch ($type) {
            case 'course':
            case 'module':
            case 'lesson':
                $user_courses = Course::where('user_id', $user_id)->pluck('id')->toArray();
                if (in_array($course_id, $user_courses)) {
                    return true;
                } else {
                    return false;
                }
                break;
            default:
                return false;
        }
    }

    // Ya se almacena el tiempo en segundos falta crear la tabla de ranking al concluir el examen y calcular la nota
    # Al agregar una pregunta desactivar el examen
    public function store(Request $request)
    {
        $user_id = auth()->user()->id;

        try {
            DB::beginTransaction();
            $exam = new ModelsExam();

            if ($request->exam_for == 'course') {
                $exam->course_id = $request->course_id;
            } elseif ($request->exam_for == 'module') {
                $exam->module_id = $request->module_id;
            } elseif ($request->exam_for == 'lesson') {
                $exam->lesson_id = $request->lesson_id;
            }
            $exam->productor_id = $user_id;
            $exam->title = $request->title;
            if ($request->has('time')) {
                $exam->time = $this->timeToSeconds($request->time);
            }
            $exam->min_passing_score = $request->min_passing_score;
            $exam->max_score = $request->max_score;
            $exam->status = 0;
            $exam->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $exam,
                'message' => 'Data recuperada con exito',
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'data' => $exam,
                'message' => 'Data recuperada con exito' . $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    function timeToSeconds(string $time): int
    {
        $formated = "$time:00";
        $arr = explode(':', $formated);
        if (count($arr) === 3) {
            return $arr[0] * 3600 + $arr[1] * 60 + $arr[2];
        }
        return $arr[0] * 60 + $arr[1];
    }

    public function edit($exam_id)
    {
        $user_id = auth()->user()->id;
        $exam = ModelsExam::where('id', $exam_id)->get()->first();
        $course_id = $exam->course_id;
        $type = $this->determineType($exam_id);

        if (!is_null($exam)) {
            $isUserExam = $this->isUserExam($user_id, $course_id);
            return $this->getViewResponse($isUserExam, $exam, $course_id, $type);
        } else {
            return view('content.miscellaneous.error');
        }
    }

    public function moduleEdit($exam_id)
    {
        $user_id = auth()->user()->id;
        $exam = ModelsExam::where('id', $exam_id)->get()->first();
        $type = $this->determineType($exam_id);

        if (!is_null($exam)) {
            $module_exam_id = $exam->module_id;
            $course_id =  Module::where('id', $module_exam_id)->get()->first()->id_courses;
            $isUserExam = $this->isUserExam($user_id, $course_id);
            return $this->getViewResponse($isUserExam, $exam, $course_id, $type);
        } else {
            return view('content.miscellaneous.error');
        }
    }

    public function lessonEdit($exam_id)
    {
        $user_id = auth()->user()->id;
        $exam = ModelsExam::where('id', $exam_id)->get()->first();
        $type = $this->determineType($exam_id);
        $clas_exam_id = $exam->lesson_id;
        $module_id =  Clas::where('id', $clas_exam_id)->get()->first()->id_modules;
        $course_id =  Module::where('id', $module_id)->get()->first()->id_courses;
        if (!is_null($exam)) {
            $isUserExam = $this->isUserExam($user_id, $course_id);
            return $this->getViewResponse($isUserExam, $exam, $course_id, $type);
        } else {
            return view('content.miscellaneous.error');
        }
    }

    public function getViewResponse($isUserExam, $exam, $course_id, $type)
    {
        if ($isUserExam === true) {
            return view('content.courses.exam.edit', compact('exam', 'course_id', 'type'));
        } else {
            return view('content.miscellaneous.error');
        }
    }

    public function isUserExam($user_id, $course_id)
    {

        $user_courses = Course::where('user_id', $user_id)->pluck('id')->toArray();
        $isUserExam = in_array($course_id, $user_courses);
        if ($isUserExam === false) {
            return false;
        } else {
            return true;
        }
    }

    public function list($course_id)
    {
        $exams = ModelsExam::where('course_id', $course_id)->get();

        return response()->json([
            'data' => $exams,
            'message' => 'Data recuperada con exito'
        ], 200);
    }

    public function moduleList($module_id)
    {
        $exams = ModelsExam::where('module_id', $module_id)->get();
        return response()->json([
            'status' => 'success',
            'data' => $exams,
            'message' => 'Data recuperada con exito',
        ], Response::HTTP_OK);
    }

    public function lessonList($lesson_id)
    {
        $exams = ModelsExam::where('lesson_id', $lesson_id)->get();
        return response()->json([
            'status' => 'success',
            'data' => $exams,
            'message' => 'Data recuperada con exito',
        ], Response::HTTP_OK);
    }

    public function preview($exam_id)
    {
        $exam = ModelsExam::where('id', $exam_id)->get()->first();
        $questions = ExamQuestion::where('exam_id', $exam->id)->get();

        return response()->json([
            'status' => 'success',
            'data' => $questions,
            'message' => 'Data generada con exito',
        ], Response::HTTP_OK);
    }

    public function activate(Request $request)
    {
        define("ENABLE", 1);
        define("DISABLE", 0);

        try {
            DB::beginTransaction();
            if ($this->sumPointsQuestion($request->id) === true) {

                $selected_exam = ModelsExam::where('id', $request->id)->get()->first();
                $type = $this->determineType($request->id);
                switch ($type) {
                    case 1: # course
                        $similar_id = $selected_exam->course_id;

                        if ($selected_exam->status == ENABLE) {
                            $selected_exam->status = DISABLE;
                        } else {
                            $selected_exam->status = ENABLE;
                        }

                        if ($selected_exam->update()) {
                            $this->disableOtherExams($request->id, $similar_id, 'course_id');
                        }
                        break;
                    case 2: # module
                        $similar_id = $selected_exam->module_id;

                        if ($selected_exam->status == ENABLE) {
                            $selected_exam->status = DISABLE;
                        } else {
                            $selected_exam->status = ENABLE;
                        }

                        if ($selected_exam->update()) {
                            $this->disableOtherExams($request->id, $similar_id, 'module_id');
                        }
                        break;
                    case 3: # lesson
                        $similar_id = $selected_exam->lesson_id;
                        if ($selected_exam->status == ENABLE) {
                            $selected_exam->status = DISABLE;
                        } else {
                            $selected_exam->status = ENABLE;
                        }
                        if ($selected_exam->update()) {
                            $this->disableOtherExams($request->id, $similar_id, 'lesson_id');
                        }
                        break;
                    default:
                        return 'Error';
                        break;
                }
                $message = 'success';
            } else {
                $message = 'failed';
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $message,
                'message' => 'Operación exitosa',
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function disableOtherExams($exam_active_id, $similar_id, $exam_type)
    {
        $exams = ModelsExam::where($exam_type, $similar_id)->whereNotIn('id', array($exam_active_id))->get();
        foreach ($exams as $exam) {
            $exam->status = 0;
            $exam->update();
        }
    }

    public function sumPointsQuestion($exam_id)
    {
        $exam_max_score = ModelsExam::where('id', $exam_id)->get()->first()->max_score;
        $total_points = array_sum(ExamQuestion::where('exam_id', $exam_id)->pluck('points')->toArray());
        if ($exam_max_score == $total_points) {
            return true;
        } else {
            return false;
        }
    }

    public function getActiveExam(Request $request)
    {
        $exam_type = $request->exam_type;
        $id_type = $request->id_type;

        switch ($exam_type) {
            case "course":
                $field_id = 'course_id';
                break;
            case "module":
                $field_id = 'module_id';
                break;
            case "class":
                $field_id = 'lesson_id';
                break;
            default:
                echo "No se especificó el tipo de examen";
        }

        $exam =  ModelsExam::where([$field_id => $id_type, 'status' => 1])->get()->first();

        $user_id = auth()->user()->id;

        // la posicion [1] entrega un booleano true false, la posicion [0] entrega un mensaje
        if ($exam) {
            $userisAble = $this->userIsAbleToDoExam($exam->id, $user_id);
            if ($userisAble[1]) {
                return $exam->id;
            } else {
                return $userisAble[0];
            }
        } else {
            return 'No existe el examen';
        }
    }

    //ESTA FUNCION VA A RETORNAR UN ARRAY, EL PRIMER VALOR ES EL MENSAJE , EL SEGUNDO ES EL BOOLEANRO TRUE FALSE
    public function userIsAbleToDoExam($exam_id, $user_id)
    {

        $fact = array();
        # Aprobó el examen
        $user_exam_conditions = UserExamHeader::where(['exam_id' => $exam_id, 'user_id' => $user_id])->pluck('condition')->toArray();

        if (in_array("Approved", $user_exam_conditions)) {
            $msg = 'El usuario ya aprobó el examen';
            $bol = false;
            array_push($fact, $msg);
            array_push($fact, $bol);
            return $fact;
        } else {
        }

        # Examen is Waiting
        if (in_array("Waiting", $user_exam_conditions)) {
            $msg = 'examen en espera';
            $bol = false;
            array_push($fact, $msg);
            array_push($fact, $bol);
            return $fact;
        }

        # Intentos del usuario 
        $trys = UserExamHeader::where(['exam_id' => $exam_id, 'user_id' => $user_id])->get()->count();

        if ($trys == 3) {
            $msg = 'limite de intentos alcanzado';
            $bol = false;
            array_push($fact, $msg);
            array_push($fact, $bol);
            return $fact;
        }

        $msg = 'ok';
        $bol = true;
        array_push($fact, $msg);
        array_push($fact, $bol);
        return $fact;
    }

    public function getActiveExamModules(Request $request)
    {

        $modules_exam = array();
        //EXAM COURSE
        $exam_course = ModelsExam::select('id', 'title')->where([
            'course_id' => $request->id_course,
            'module_id' => null,
            'lesson_id' => null,
            'status' => 1
        ])->get()->first();
        $exam_course = $this->validateExamModule($exam_course);

        //EXAMS MODULES
        $modules = Module::select('id', 'id_courses')->where(['id_courses' => $request->id_course])->get();
        foreach ($modules as $module) {

            $exam = ModelsExam::select('id', 'title')
                ->where(['module_id' => $module->id, 'lesson_id' => null, 'status' => 1])->get()->first();

            $objt_module_exam = $this->validateExamModule($exam);
            if ($objt_module_exam) {
                array_push($modules_exam, $objt_module_exam);
            }
        }
        $objTotal = (object) array('course_exam' => $exam_course, 'module_exams' => $modules_exam);
        return $objTotal;
    }

    public function validateExamModule($exam)
    {
        if ($exam) {
            $user_exam_headers = UserExamHeader::where(['exam_id' => $exam->id, 'status' => 1])->get();
            if (count($user_exam_headers) > 0) {

                $valueApproved = false;
                $countDisapproved = 0;

                foreach ($user_exam_headers as $user_exam_h) {
                    if ($user_exam_h->condition == 'Approved') {
                        $valueApproved = true;
                        break;
                    } else if ($user_exam_h->condition == 'Disapproved' || $user_exam_h->condition == 'Waiting') {
                        $countDisapproved++;
                    }
                }
                if (!$valueApproved && ($countDisapproved < 3)) {
                    return $exam;
                }
            } else {
                return $exam;
            }
        } else {
            return;
        }
    }

    public function getAnswers(Request $request)
    {
        $user_id = auth()->user()->id;
        $id_exam = $request->id_exam;
        $user_answers = (array) $request->answers;
        $seconds_used = $request->seconds_used;
        $course_id =  $request->course_id;
        $answers = ExamQuestion::select('points', 'options', 'correct', 'question_type_id')->where('exam_id', $id_exam)->get();
        $exam = ModelsExam::find($id_exam);

        try {
            DB::beginTransaction();
            $header = new UserExamHeader();
            $header->productor_id = $exam->productor_id;
            $header->user_id = $user_id;
            $header->exam_id = $id_exam;
            $header->rate = 0;
            $header->condition = "Waiting";
            $header->status = false;
            $header->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        $nota = $this->rateExam($user_answers, $answers, $header->id);

        $hasOpenQuestions = $this->hasOpenQuestions($id_exam);

        // examen de pregunta abierta se espera a que el productor califique
        if ($hasOpenQuestions == true) {
            $response = 'Waiting';
        } else {
            $hasTimer = $this->hasTimer($id_exam); # counter?

            if ($hasTimer == true) {

                $type = $this->determineType($id_exam);
                $header = UserExamHeader::where('id', $header->id)->get()->first();
                $header->condition = $this->getExamCondition($header->exam_id, $nota);
                $header->status = 1; # make visible to user
                $header->rate = $nota;
                $header->update();

                $response = $this->saveUserExam($exam, $nota, $type, $course_id, $seconds_used);
            } else {
                $type = $this->determineType($id_exam);
                $header = UserExamHeader::where('id', $header->id)->get()->first();
                $header->condition = $this->getExamCondition($header->exam_id, $nota);
                $header->rate = $nota;
                $header->status = 1; # make visible to user
                $header->update();
                app(UserExamHeaderController::class)->badgeForPassingTheExam($header->user_id);
                $points_option_id = ConfigurationController::getOptionId('points_exam_course');
                $virtual_points_gained = UserConfiguration::select('value')->where(['configuration_id' => $points_option_id, 'user_id' => $user_id])->get()->first()->value;
                $response = array('rate' => $nota, 'points_gained' => $virtual_points_gained);
            }
        }

        return $response;
    }

    public function getExamCondition($exam_id, $user_rate)
    {
        $min_score = ModelsExam::select('min_passing_score')->where('id', $exam_id)->get()->first()->min_passing_score;
        if ($user_rate >= $min_score) {
            $condition = "Approved";
        } else {
            $condition = "Disaproved";
        }
        return $condition;
    }

    public function hasOpenQuestions($exam_id)
    {
        $questions_type = ExamQuestion::select('question_type_id')->where('exam_id', $exam_id)->get();
        $array = [];
        foreach ($questions_type as $question_type) {
            array_push($array, $question_type->question_type_id);
        }
        // define("OPEN_QUESTION", 4);
        $bool = in_array(4, $array);
        return $bool;
    }

    public function determineType($exam_id)
    {
        $exam = ModelsExam::find($exam_id);
        if ($exam->course_id != null) {
            $type = '1';
        } elseif ($exam->module_id != null) {
            $type = '2';
        } elseif ($exam->lesson_id != null) {
            $type = '3';
        }
        return $type;
    }

    public function saveUserExam($exam, $nota, $type, $course_id, $seconds_used)
    {
        try {

            DB::beginTransaction();
            $userExam = new UserExam();
            $userExam->course_id = $course_id;
            $userExam->user_id = auth()->user()->id;
            $userExam->exam_type_id = $type;
            $userExam->exam_note = $nota;
            $userExam->save();

            return $this->saveclassroomPointDetail($exam, $nota, $type, $course_id, $seconds_used);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function calculatePercentage($total, $number)
    {
        if ($number == 0) {
            $percentage =   (float) 0.1;
        } else {
            $percentage =    (float) 100 / ((float) $total / (float) $number) / 100;
        }
        return $percentage;
    }


    /**
     * Falta añadir puntos por tipo de examen
     */
    public function saveclassroomPointDetail($exam, $nota, $type, $course_id, $seconds_used)
    {

        $user_id = auth()->user()->id;
        if ($nota >= $exam->min_passing_score) {
            $total_time = $exam->time;
            $left_time = $total_time - $seconds_used;
            $virtual_points_gained = 0;
            $status = 'Aprobado';
            $position = 0;
            // $this->updateUserVirtualPoints($course_id, $virtual_points_gained, $type);
        } else {
            $virtual_points_gained = 0;
            $status = 'Desaprobado';
            $position = 0;
        }
        return array("rate" => $nota, "message" => $status, "points" => $virtual_points_gained, 'rank' => $position);
    }

    public function updateUserVirtualPoints($course_id, $virtual_points_gained, $type)
    {
        // $userClassroomPoint = UserClassroomPoint::where('id_user', auth()->user()->id)->get()->first();
        if (!UserClassroomPoint::where('id_user', auth()->user()->id)->exists()) {
            $userClassroomPoint = new UserClassroomPoint();
            $userClassroomPoint->id_user = auth()->user()->id;
            $userClassroomPoint->total_points = 0;
            $userClassroomPoint->save();
        } else {
            $userClassroomPoint = UserClassroomPoint::where('id_user', auth()->user()->id)
                ->first();
        }
        $course = Course::find($course_id);

        # create detail 
        $point_detail = new ClassroomPointDetail();
        $point_detail->id_user_classroom_points = $userClassroomPoint->id;
        $point_detail->increment_points = $virtual_points_gained;
        $point_detail->description = $this->createDescription($type, $course);
        $point_detail->save();
        $userClassroomPoint->total_points = $userClassroomPoint->total_points + $virtual_points_gained;
        $userClassroomPoint->update();
    }

    public function hasTimer($exam_id)
    {
        $time = ModelsExam::select('time')->where('id', $exam_id)->get()->first()->time;
        $result = $time != null ? true : false;
        return $result;
    }

    public function createDescription($type, $course)
    {

        switch ($type) {
            case '1':
                $str = "Examen del curso $course->title";
                break;
            case '2':
                $str = "Examen de un módulo del curso $course->title";
                break;
            case '3':
                $str = "Examen de una clase del curso $course->title";
                break;
            default:
                $str = "Error";
                break;
        }
        return $str;
    }

    public function rateExam($user_answers, $answers, $header_id)
    {
        $nota = 0;
        $notas = [];
        foreach ($user_answers as $index => $user_answer) {
            $user_option = ($user_answer);
            $question_config = $answers[$index];
            $nota = $this->evaluateAccordingDataType($user_option['option'], $question_config, $header_id);
            array_push($notas, $nota);
        }
        return array_sum($notas);
    }

    public function evaluateAccordingDataType($user_options_selected, $question_config, $header_id)
    {
        $data_type = $question_config->question_type_id;

        switch ($data_type) {
            case 1:
                $rate_per_question = $this->rateSimpleSelectionQuestion($question_config, $user_options_selected, $header_id);
                break;
            case 2:
                $rate_per_question = $this->rateMultipleOptionQuestion($question_config, $user_options_selected, $header_id);
                break;
            case 3:
                $rate_per_question = $this->rateBooleanQuestion($question_config, $user_options_selected, $header_id);
                break;
            case 4:
                # Guardar la respuesta del usuario y tener en cuenta sus respuestas para mostrar un exámen

                $detail = new UserQuestionAnswer();
                $detail->user_exam_id = $header_id;
                $detail->points_gained = 0;
                $detail->options_selected = array('response' => $user_options_selected);
                $detail->save();
                $rate_per_question = 0;
                break;
            default:
                $rate_per_question = 'Error';
                break;
        }

        return $rate_per_question;
    }

    public function rateBooleanQuestion($question_config, $user_options_selected, $header_id)
    {
        $index = $question_config->correct;
        if ($index == $user_options_selected) {
            $nota = $question_config->points;
        } else {
            $nota = 0;
        }
        $detail = new UserQuestionAnswer();
        $detail->user_exam_id = $header_id;
        $detail->points_gained = $nota;
        $detail->options_selected = $user_options_selected;
        $detail->save();
        return $nota;
    }

    public function rateSimpleSelectionQuestion($question_config, $user_options_selected, $header_id)
    {
        $index = $question_config->correct;
        if ($index == $user_options_selected) {
            $nota = $question_config->points;
        } else {
            $nota = 0;
        }

        $detail = new UserQuestionAnswer();
        $detail->user_exam_id = $header_id;
        $detail->points_gained = $nota;
        $detail->options_selected = strval($user_options_selected);
        $detail->save();
        return $nota;
    }

    public function getIncorrectOptions($options, $correct_options)
    {
        $incorrect_index_options = [];
        foreach ($options as $index => $item) {
            if (!in_array($index, $correct_options)) {
                array_push($incorrect_index_options, $index);
            }
        }

        return $incorrect_index_options;
    }

    public function rateMultipleOptionQuestion($question_config, $user_options_selected, $header_id)
    {
        $correct_options = explode(',', $question_config->correct);
        $correctAnswersCount = $this->correctAnswersCount($correct_options, $user_options_selected);
        $incorrectAnswersCount = $this->incorrectAnswersCount($question_config->options, $correct_options, $user_options_selected);
        $negative_points = $this->calculateNegativePoints($question_config, $incorrectAnswersCount);
        $positive_points = $this->calculatePositivePoints($question_config, $correctAnswersCount,  $correct_options);
        $final_rate = $positive_points - $negative_points;
        // evitar que la nota sea negativa
        if ($final_rate < 0) {
            $final_rate = 0;
        }
        $detail = new UserQuestionAnswer();
        $detail->user_exam_id = $header_id;
        $detail->points_gained = $final_rate;
        $detail->options_selected = $user_options_selected;
        $detail->save();

        return $final_rate;
    }

    public function correctAnswersCount($correct_options, $user_options_selected)
    {
        $count = count(array_intersect($correct_options, $user_options_selected));
        return $count;
    }

    public function incorrectAnswersCount($question_options, $correct_options, $user_options_selected)
    {
        $incorrect_options = $this->getIncorrectOptions($question_options, $correct_options);
        $count = count(array_intersect($incorrect_options, $user_options_selected));
        return $count;
    }

    public function calculatePointsPerIncorrectAnswer($question_config)
    {
        $points_per_answer = $question_config->points / count($question_config->options);
        return $points_per_answer;
    }

    public function calculatePointsPerCorrectAnswer($question_config, $correct_options)
    {
        $points_per_answer = $question_config->points / count($correct_options);
        return $points_per_answer;
    }

    public function calculateNegativePoints($question_config, $incorrect_quantity_selected)
    {
        $point_per_incorrect_answer = $this->calculatePointsPerIncorrectAnswer($question_config);
        $points = $incorrect_quantity_selected * $point_per_incorrect_answer;
        return $points;
    }

    public function calculatePositivePoints($question_config, $correctAnswersCount,  $correct_options)
    {
        $points_per_correct_question = $this->calculatePointsPerCorrectAnswer($question_config, $correct_options);
        $points = $correctAnswersCount * $points_per_correct_question;
        return $points;
    }

    public function examList(Request $request)
    {

        $classes = Module::join('class', 'modules.id', '=', 'class.id_modules')
            ->join('courses', 'modules.id_courses', '=', 'courses.id')
            ->where('courses.id', $request->id)
            ->where('courses.status', '!=', 0)
            ->select('class.id as class_id', 'class.name', 'class.slug')
            ->get();
        $modules = Course::join('modules', 'courses.id', '=', 'modules.id_courses')
            ->where('courses.id', $request->id)
            ->select('modules.id as module_id', 'modules.name', 'modules.name as slug')
            ->get();
        $course = Course::where('id', $request->id)
            ->select('id as course_id', 'title', 'slug')
            ->first();
        $data = [];
        $counter_class = 0;
        $count_exist_exam = 0;
        $count_approved_exam = 0;
        foreach ($classes as $class) {

            if (ModelsExam::where(['lesson_id' => $class->class_id, 'status' => 1])->exists()) {
                $class->exist = true;
                $exam_id = ModelsExam::where('lesson_id', $class->class_id)->pluck('id');
                $user_attempt = UserExamHeader::where(['exam_id' => $exam_id[0], 'user_id' => auth()->user()->id])->latest();

                if ($user_attempt->exists()) {
                    $class->approved = $user_attempt->first()->condition;

                    if ($user_attempt->where('condition', 'Approved')->exists()) {
                        $count_approved_exam++;
                    }
                }

                $class->exam_id = $exam_id[0];
                $counter_class++;
                $count_exist_exam++;
            } else {
                $class->exist = false;
                $class->approved = false;
            }
        }
        $counter_module = 0;
        foreach ($modules as $module) {
            if (ModelsExam::where(['module_id' => $module->module_id, 'status' => 1])->exists()) {
                $module->exist = true;
                $exam_id = ModelsExam::where('module_id', $module->module_id)->pluck('id');
                $user_attempt = UserExamHeader::where(['exam_id' => $exam_id[0], 'user_id' => auth()->user()->id]);
                if ($user_attempt->exists()) {
                    if ($user_attempt->where('condition', 'Approved')->exists()) {

                        $count_approved_exam++;
                    }
                    $module->approved = $user_attempt->value('condition');
                }
                $module->exam_id = $exam_id[0];
                $counter_module++;
                $count_exist_exam++;
            } else {
                $module->exist = false;
                $module->approved = false;
            }
        }
        if (ModelsExam::where(['course_id' => $course->course_id, 'status' => 1])->exists()) {
            $course->exist = true;
            $exam_id = ModelsExam::where('course_id', $course->course_id)->pluck('id');
            $user_attempt = UserExamHeader::where(['exam_id' => $exam_id[0], 'user_id' => auth()->user()->id]);
            if ($user_attempt->exists()) {
                if ($user_attempt->where('condition', 'Approved')->exists()) {

                    $count_approved_exam++;
                }
                $course->approved = $user_attempt->value('condition');
            }
            $course->exam_id = $exam_id[0];
            $counter_course = 1;
            $count_exist_exam++;
        } else {
            $course->exist = false;
            $course->approved = false;
            $counter_course = 0;
        }
        $exam_progress = $count_exist_exam == 0 ? "empty" : round(($count_approved_exam / $count_exist_exam) * 100);
        $data = [
            'exams_class' => $classes,
            'exams_module' => $modules,
            'exam_course' => $course,
            'counter_class' => $counter_class,
            'counter_module' => $counter_module,
            'counter_course' => $counter_course,
            'exam_progress' => $exam_progress
        ];
        return $data;
    }

    public function getResults(Request $request)
    {
        $questions = ExamQuestion::where('exam_id', $request->examId)->get();

        $latest_attempt_id = UserExamHeader::where('user_id', $request->user()->id)->where('exam_id', $request->examId)->latest()->value('id');
        $approved = UserExamHeader::where('id', $latest_attempt_id)->value('condition') === 'Approved';
        $user_answers = UserQuestionAnswer::where('user_exam_id', $latest_attempt_id)->pluck('options_selected');
        $result_detail = [];
        foreach ($questions as $index => $question) {
            $options = $question->options;
            $user_selected = $options[$user_answers[$index]];
            $answer = $options[$question->correct];
            $resultadoPregunta = array(
                'pregunta' => $question->title,
                'respuestaSeleccionada' => $user_selected,
                'respuestaCorrecta' => $answer
            );
            array_push($result_detail, $resultadoPregunta);
        }
        return ['result' => $approved, 'detail' => $result_detail];
    }


    public function allLessonsExam(Request $request)
    {
        $classes = Clas::join('modules', 'class.id_modules', '=', 'modules.id')
            ->where('modules.id_courses', $request->course_id)
            ->select('class.name as class_name', 'modules.name as module_name', 'class.id as class_id', 'modules.id as module_id')
            ->get();
        foreach ($classes as $class) {
            if (ModelsExam::where('lesson_id', $class->class_id)->exists()) {
                $exam = ModelsExam::where('lesson_id', $class->class_id)->first();
                $class->exam_exist = true;
                $class->exam_status = $exam->status;
                $class->exam_name = $exam->title;
                $class->course_id = $request->course_id;
                $class->exam_id = $exam->id;
            } else {
                $class->exam_exist = false;
                $class->exam_status = "---";
                $class->exam_name = "---";
                $class->course_id = $request->course_id;
            }
        }
        return response()->json([
            'status' => 'success',
            'data' => $classes,
            'message' => 'Data generada con exito',
        ], Response::HTTP_OK);
    }
}
