@extends('layouts.contentLayoutMaster')

@section('title', 'Crear categoria de preguntas')

@section('content')
    <div id="app">
        @include('content.marketing.dinamica.trivia.partials.shell', [
            'active' => 'question-categories',
            'breadcrumbs' => [
                ['label' => 'Marketing', 'url' => route('marketing.tools')],
                ['label' => 'Dinámica', 'url' => route('marketing.dinamica.create')],
                ['label' => 'Trivia', 'url' => route('marketing.dinamica.trivia')],
                ['label' => 'Categorias de preguntas', 'url' => route('marketing.dinamica.trivia.categories.index')],
                ['label' => 'Crear', 'url' => null],
            ],
        ])

        <question-category-form
            mode="create"
            :initial-category='@json(null)'
            index-url="{{ route('marketing.dinamica.trivia.categories.index') }}"
            store-url="{{ route('marketing.dinamica.trivia.categories.store') }}"
        ></question-category-form>
    </div>
@endsection
