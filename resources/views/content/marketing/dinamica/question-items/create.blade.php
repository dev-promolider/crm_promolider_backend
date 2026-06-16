@extends('layouts.contentLayoutMaster')

@section('title', 'Agregar pregunta')

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
                ['label' => 'Agregar pregunta', 'url' => null],
            ],
        ])

        <question-item-form
            :category='@json($category)'
            :defaults='@json($defaults)'
            submit-url="{{ route('marketing.dinamica.trivia.categories.questions.store', ['category' => $category->id]) }}"
            category-url="{{ route('marketing.dinamica.trivia.categories.show', ['category' => $category->id]) }}"
            index-url="{{ route('marketing.dinamica.trivia.categories.index') }}"
            mode="create"
        ></question-item-form>
    </div>
@endsection
