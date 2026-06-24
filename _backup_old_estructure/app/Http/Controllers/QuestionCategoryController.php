<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuestionCategoryRequest;
use App\Models\QuestionCategory;
use App\Services\QuestionCategoryService;
use App\Services\QuestionItemService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class QuestionCategoryController extends Controller
{
    public function __construct(
        private QuestionCategoryService $service,
        private QuestionItemService $questionItemService
    )
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $categories = $this->service->list($request->only(['search', 'is_active']));

        return view('content.marketing.dinamica.question-categories.index', [
            'questionCategories' => $categories,
        ]);
    }

    public function create(): View
    {
        return view('content.marketing.dinamica.question-categories.create');
    }

    public function store(QuestionCategoryRequest $request): JsonResponse|RedirectResponse
    {
        $category = $this->service->create($request->validated());

        return $this->respondWithCategory($category, __('Categoria creada correctamente.'), 'marketing.dinamica.trivia.categories.show');
    }

    public function show(QuestionCategory $category): View
    {
        $category->loadMissing(['creator', 'updater']);
        $questions = $this->questionItemService->previewForCategory($category);

        return view('content.marketing.dinamica.question-categories.show', [
            'category' => $category,
            'questions' => $this->transformQuestions($questions),
            'questionCreateUrl' => route('marketing.dinamica.trivia.categories.questions.create', $category),
            'questionEditUrlTemplate' => route('marketing.dinamica.trivia.categories.questions.edit', ['category' => $category->id, 'question' => '__QUESTION__']),
            'questionDeleteUrlTemplate' => route('marketing.dinamica.trivia.categories.questions.destroy', ['category' => $category->id, 'question' => '__QUESTION__']),
        ]);
    }

    public function edit(QuestionCategory $category): View
    {
        return view('content.marketing.dinamica.question-categories.edit', [
            'category' => $category,
        ]);
    }

    public function update(QuestionCategoryRequest $request, QuestionCategory $category): JsonResponse|RedirectResponse
    {
        $category = $this->service->update($category, $request->validated());

        return $this->respondWithCategory($category, __('Categoria actualizada correctamente.'), 'marketing.dinamica.trivia.categories.show');
    }

    public function toggle(QuestionCategory $category): JsonResponse|RedirectResponse
    {
        $category = $this->service->toggleStatus($category);

        return $this->respondWithCategory($category, __('Estado actualizado.'), 'marketing.dinamica.trivia.categories.index');
    }

    protected function respondWithCategory(QuestionCategory $category, string $message, string $redirectRoute): JsonResponse|RedirectResponse
    {
        $payload = [
            'success' => true,
            'message' => $message,
            'category' => $category,
            'redirect' => route($redirectRoute, $category),
        ];

        return request()->wantsJson()
            ? response()->json($payload)
            : redirect()->to($payload['redirect'])->with('status', $message);
    }

    protected function transformQuestions(Collection $questions): array
    {
        return $questions->map(function ($question) {
            return [
                'id' => $question->id,
                'title' => $question->title,
                'status' => $question->status,
                'difficulty' => $question->difficulty,
                'options' => $question->options->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'label' => $option->label,
                        'text' => $option->text,
                        'is_correct' => (bool) $option->is_correct,
                    ];
                })->values(),
            ];
        })->values()->toArray();
    }
}
