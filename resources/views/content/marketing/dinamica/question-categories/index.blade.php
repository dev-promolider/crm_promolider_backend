@extends('layouts.contentLayoutMaster')

@section('title', 'Categorias de preguntas')

@section('content')
    <div id="app">
        @include('content.marketing.dinamica.trivia.partials.shell', [
            'active' => 'question-categories',
            'breadcrumbs' => [
                ['label' => 'Marketing', 'url' => route('marketing.tools')],
                ['label' => 'Dinámica', 'url' => route('marketing.dinamica.create')],
                ['label' => 'Trivia', 'url' => route('marketing.dinamica.trivia')],
                ['label' => 'Categorias de preguntas', 'url' => null],
            ],
        ])

        <question-category-list
            :initial-categories='@json($questionCategories)'
            create-url="{{ route('marketing.dinamica.trivia.categories.create') }}"
            show-url-template="{{ route('marketing.dinamica.trivia.categories.show', ['category' => '__ID__']) }}"
            edit-url-template="{{ route('marketing.dinamica.trivia.categories.edit', ['category' => '__ID__']) }}"
            toggle-url-template="{{ route('marketing.dinamica.trivia.categories.toggle', ['category' => '__ID__']) }}"
            question-create-url-template="{{ route('marketing.dinamica.trivia.categories.questions.create', ['category' => '__ID__']) }}"
        ></question-category-list>
    </div>
@endsection
