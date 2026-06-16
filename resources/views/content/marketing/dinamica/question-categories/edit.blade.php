@extends('layouts.contentLayoutMaster')

@section('title', 'Editar categoria de preguntas')

@section('content')
    <div id="app">
        @include('content.marketing.dinamica.trivia.partials.shell', [
            'active' => 'question-categories',
            'breadcrumbs' => [
                ['label' => 'Marketing', 'url' => route('marketing.tools')],
                ['label' => 'Dinámica', 'url' => route('marketing.dinamica.create')],
                ['label' => 'Trivia', 'url' => route('marketing.dinamica.trivia')],
                ['label' => 'Categorias de preguntas', 'url' => route('marketing.dinamica.trivia.categories.index')],
                ['label' => 'Editar', 'url' => null],
            ],
        ])

        <question-category-form
            mode="edit"
            :initial-category='@json($category)'
            index-url="{{ route('marketing.dinamica.trivia.categories.index') }}"
            view-url="{{ route('marketing.dinamica.trivia.categories.show', ['category' => $category->id]) }}"
            update-url="{{ route('marketing.dinamica.trivia.categories.update', ['category' => $category->id]) }}"
        ></question-category-form>
    </div>
@endsection
