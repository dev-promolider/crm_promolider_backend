<?php

namespace Promolider\Infrastructure\VCR\In\Http\Controllers;

use App\Models\FrequentQuestion;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VcrFrequentQuestionsController extends Controller
{
    /**
     * GET /api/v1/frequent-questions
     */
    public function index()
    {
        $questions = FrequentQuestion::select('id', 'question', 'answer')
            ->where('status', 1)
            ->get();

        return response()->json($questions);
    }

    /**
     * GET /api/v1/frequent-questions/all
     */
    public function all()
    {
        $questions = FrequentQuestion::all();

        return response()->json([
            'status' => 'ok',
            'data' => $questions,
        ]);
    }

    /**
     * POST /api/v1/frequent-questions/store
     */
    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
        ]);

        $faq = FrequentQuestion::create([
            'question' => $request->input('question'),
            'answer' => $request->input('answer'),
            'status' => 1,
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Pregunta frecuente creada con éxito',
            'data' => $faq,
        ]);
    }

    /**
     * POST /api/v1/frequent-questions/update
     */
    public function update(Request $request)
    {
        $id = $request->input('id');
        $faq = FrequentQuestion::find($id);

        if (!$faq) {
            return response()->json(['status' => 'error', 'message' => 'Pregunta frecuente no encontrada'], 404);
        }

        $faq->question = $request->input('question', $faq->question);
        $faq->answer = $request->input('answer', $faq->answer);
        $faq->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Pregunta frecuente actualizada con éxito',
            'data' => $faq,
        ]);
    }

    /**
     * POST /api/v1/frequent-questions/change-status
     */
    public function changeStatus(Request $request)
    {
        $id = $request->input('id');
        $faq = FrequentQuestion::find($id);

        if (!$faq) {
            return response()->json(['status' => 'error', 'message' => 'Pregunta frecuente no encontrada'], 404);
        }

        $faq->status = $faq->status == 1 ? 0 : 1;
        $faq->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Estado actualizado con éxito',
            'data' => $faq,
        ]);
    }

    /**
     * DELETE /api/v1/frequent-questions/{id}
     */
    public function destroy($id)
    {
        $faq = FrequentQuestion::find($id);

        if (!$faq) {
            return response()->json(['status' => 'error', 'message' => 'Pregunta frecuente no encontrada'], 404);
        }

        $faq->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'Pregunta frecuente eliminada con éxito',
        ]);
    }
}
