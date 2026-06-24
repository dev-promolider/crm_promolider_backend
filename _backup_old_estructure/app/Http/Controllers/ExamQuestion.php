<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\QuestionType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Models\ExamQuestion as ModelsExamQuestion;

class ExamQuestion extends Controller
{
    public function list($exam_id)
    {
       try {
            $questions = ModelsExamQuestion::where('exam_id', $exam_id)->get();
            
            return response()->json([
                'status' => 'success',
                'data' => $questions,
                'message' => 'Data generada con exito',
            ], Response::HTTP_OK);
       } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrio un error ' . $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
       }
    }

    public function get($id)
    {
        $question = ModelsExamQuestion::where('id', $id)->get()->first();
        
        return response()->json([
            'status' => 'success',
            'data' => $question,
            'message' => 'Data generada con exito',
        ], Response::HTTP_OK);
    }

    public function edit($id)
    {
        $question = ModelsExamQuestion::where('id', $id)->get()->first();
        $exam = Exam::where('id', $question->exam_id)->get()->first();
        $type = $this->determineType($exam->id);
        $question_types = QuestionType::all();
        return view('content.courses.exam.question.edit', compact('question', 'question_types', 'exam', 'type'));
    }

    public function determineType($exam_id)
    {
        $exam = Exam::find($exam_id);
        if ($exam->course_id != null) {
            $type = '1'; // course
        } elseif ($exam->module_id != null) {
            $type = '2'; // module
        } elseif ($exam->lesson_id != null) {
            $type = '3'; // lesson
        }
        return $type;
    }


    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $question = new ModelsExamQuestion();
            $question->exam_id = $request->exam_id;
            $question->title = $request->title;
            $question->points = 0;
            $question->type = ''; //delete this
            $question->question_type_id = 1; // simple selection is default
            $question->save();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $question,
                'message' => 'Registro exitoso',
            ], Response::HTTP_OK);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrio un error al realizar esta operación ' . $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function optionsStore(Request $request)
    {
        try {
            DB::beginTransaction();
            $question = ModelsExamQuestion::where('id', $request->exam_question_id)->get()->first();
            $question->title =  $request->title;
            $question->points =  $request->points;
            $question->question_type_id =  $request->exam_type;
            $answers = $request->correct;
            $options = $this->buildArray($request->opt_1, $request->opt_2, $request->opt_3, $request->opt_4, $request->opt_5);
            $question->correct =  $answers;
            $question->options = $options;
            $question->type = $request->exam_type;
            $question->update();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();
            $question = ModelsExamQuestion::where('id', $id);
            $question->delete();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $question,
                'message' => 'Registro eliminado',
            ], Response::HTTP_OK);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrio un error al eliminar el registro ' . $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Build clean Array
     */
    public function buildArray($item1, $item2, $item3, $item4, $item5)
    {
        // validar si es null "null" o undefined no hacer un push
        $items = [];
        if (is_null($item1) !== true & $item1 != "null" & $item1 != "undefined") {
            array_push($items, $item1);
        }
        if (is_null($item2) !== true & $item2 != "null" & $item2 != "undefined") {
            array_push($items, $item2);
        }
        if (is_null($item3) !== true & $item3 != "null" & $item3 != "undefined") {
            array_push($items, $item3);
        }
        if (is_null($item4) !== true & $item4 != "null" & $item4 != "undefined") {
            array_push($items, $item4);
        }
        if (is_null($item5) !== true & $item5 != "null" & $item5 != "undefined") {
            array_push($items, $item5);
        }



        // array_push($items, $item2);
        // array_push($items, $item3);
        // array_push($items, $item4);
        // array_push($items, $item5);
        // return $this->deleteNullFromArray(($items));
        return ($items);
    }


    // public function deleteNullFromArray($array)
    // {
    //     if (($key = array_search("null", $array)) !== false) {
    //         unset($array[$key]);
    //     }
    //     if (($key = array_search(null, $array)) !== false) {
    //         unset($array[$key]);
    //     }

    //     if (($key = array_search("undefined", $array)) !== false) {
    //         unset($array[$key]);
    //     }

    //     return array_values($array);
    // }
}
