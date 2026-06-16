<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuestionItemRequest;
use App\Models\QuestionCategory;
use App\Models\QuestionItem;
use App\Services\QuestionItemService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class QuestionItemController extends Controller
{
    public function __construct(private QuestionItemService $service)
    {
        $this->middleware('auth');
    }

    public function create(QuestionCategory $category): View
    {
        return view('content.marketing.dinamica.question-items.create', [
            'category' => $category,
            'defaults' => [
                'difficulty' => 'medium',
                'status' => 'draft',
                'is_active' => true,
            ],
        ]);
    }

    public function store(QuestionItemRequest $request, QuestionCategory $category): JsonResponse|RedirectResponse
    {
        $question = $this->service->create($category, $request->validated());

        $payload = [
            'success' => true,
            'message' => __('Pregunta creada correctamente.'),
            'question' => $question,
            'redirect' => route('marketing.dinamica.trivia.categories.show', $category),
        ];

        return $request->wantsJson()
            ? response()->json($payload)
            : redirect()->to($payload['redirect'])->with('status', $payload['message']);
    }

    public function edit(QuestionCategory $category, QuestionItem $question): View
    {
        $this->ensureQuestionInCategory($category, $question);

        return view('content.marketing.dinamica.question-items.edit', [
            'category' => $category,
            'question' => $question->loadMissing('options'),
            'defaults' => $this->formatQuestionForForm($question),
        ]);
    }

    public function update(QuestionItemRequest $request, QuestionCategory $category, QuestionItem $question): JsonResponse|RedirectResponse
    {
        $this->ensureQuestionInCategory($category, $question);

        $question = $this->service->update($question, $request->validated());

        $payload = [
            'success' => true,
            'message' => __('Pregunta actualizada correctamente.'),
            'question' => $question,
            'redirect' => route('marketing.dinamica.trivia.categories.show', $category),
        ];

        return $request->wantsJson()
            ? response()->json($payload)
            : redirect()->to($payload['redirect'])->with('status', $payload['message']);
    }

    public function destroy(QuestionCategory $category, QuestionItem $question): JsonResponse
    {
        $this->ensureQuestionInCategory($category, $question);

        $this->service->delete($category, $question);

        return response()->json([
            'success' => true,
            'message' => __('Pregunta eliminada correctamente.'),
        ]);
    }

    protected function ensureQuestionInCategory(QuestionCategory $category, QuestionItem $question): void
    {
        abort_if($question->question_category_id !== $category->id, 404);
    }

    protected function formatQuestionForForm(QuestionItem $question): array
    {
        return [
            'id' => $question->id,
            'title' => $question->title,
            'body' => $question->body,
            'difficulty' => $question->difficulty,
            'status' => $question->status,
            'time_limit' => $question->time_limit,
            'is_active' => $question->is_active,
            'options' => $question->options->map(function ($option) {
                return [
                    'text' => $option->text,
                    'is_correct' => (bool) $option->is_correct,
                ];
            })->values()->toArray(),
        ];
    }
}
