@extends('layouts.contentLayoutMaster')

@section('title', 'Detalle de la categoria de preguntas')

@section('content')
    <div id="app">
        @include('content.marketing.dinamica.trivia.partials.shell', [
            'active' => 'question-categories',
            'breadcrumbs' => [
                ['label' => 'Marketing', 'url' => route('marketing.tools')],
                ['label' => 'Dinámica', 'url' => route('marketing.dinamica.create')],
                ['label' => 'Trivia', 'url' => route('marketing.dinamica.trivia')],
                ['label' => 'Categorias de preguntas', 'url' => route('marketing.dinamica.trivia.categories.index')],
                ['label' => 'Detalle', 'url' => null],
            ],
        ])

        <question-category-detail
            :category='@json($category)'
            :questions='@json($questions)'
            edit-url="{{ route('marketing.dinamica.trivia.categories.edit', ['category' => $category->id]) }}"
            index-url="{{ route('marketing.dinamica.trivia.categories.index') }}"
            create-question-url="{{ $questionCreateUrl }}"
            question-edit-url-template="{{ $questionEditUrlTemplate }}"
            question-delete-url-template="{{ $questionDeleteUrlTemplate }}"
        ></question-category-detail>
    </div>
@endsection
