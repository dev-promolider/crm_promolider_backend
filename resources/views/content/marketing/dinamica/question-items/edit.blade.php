@extends('layouts.contentLayoutMaster')

@section('title', 'Editar pregunta')

@section('content')
    <div id="app">
        @include('content.marketing.dinamica.trivia.partials.shell', [
            'active' => 'question-categories',
            'breadcrumbs' => [
                ['label' => 'Marketing', 'url' => route('marketing.tools')],
                ['label' => 'Dinámica', 'url' => route('marketing.dinamica.create')],
                ['label' => 'Trivia', 'url' => route('marketing.dinamica.trivia')],
                ['label' => 'Categorías de preguntas', 'url' => route('marketing.dinamica.trivia.categories.index')],
                ['label' => $category->name, 'url' => route('marketing.dinamica.trivia.categories.show', ['category' => $category->id])],
                ['label' => 'Editar pregunta', 'url' => null],
            ],
        ])

        <question-item-form
            :category='@json($category)'
            :defaults='@json($defaults)'
            submit-url="{{ route('marketing.dinamica.trivia.categories.questions.update', ['category' => $category->id, 'question' => $question->id]) }}"
            category-url="{{ route('marketing.dinamica.trivia.categories.show', ['category' => $category->id]) }}"
            index-url="{{ route('marketing.dinamica.trivia.categories.index') }}"
            mode="edit"
            http-method="PUT"
        ></question-item-form>
    </div>
@endsection
