@extends('layouts.contentLayoutMaster')

@section('title', 'Blueprint Trivia')

@section('content')
    <div id="app">
        @include('content.marketing.dinamica.trivia.partials.shell', [
            'active' => 'blueprint',
            'breadcrumbs' => [
                ['label' => 'Marketing', 'url' => route('marketing.tools')],
                ['label' => 'Dinámica', 'url' => route('marketing.dinamica.create')],
                ['label' => 'Trivia', 'url' => route('marketing.dinamica.trivia')],
                ['label' => 'Blueprint', 'url' => null],
            ],
        ])


        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                <div>
                    <h4 class="card-title mb-0">Diseñar trivia interactiva</h4>
                    <small class="text-muted">Estructura inicial · Categorías reutilizables</small>
                </div>
                <span class="badge badge-light mt-2 mt-md-0">Marketing · Dinámicas</span>
            </div>
            <div class="card-body">
                <trivia-dynamic-designer 
                    mode="{{ $mode ?? 'create' }}"
                    :initial-categories='@json($categories ?? [])'
                    categories-index-url="{{ url('/marketing/dinamica/trivia/question-categories') }}"
                    categories-create-url="{{ url('/marketing/dinamica/trivia/question-categories/create') }}"
                    :initial-registration-config='@json($registrationConfig ?? (object) [])'
                    :initial-trivia-config='@json($triviaConfig ?? (object) [])'
                    :initial-game-blocks='@json($gameBlocks ?? [])'
                    :dinamica-id='@json($dinamicaId ?? null)'
                    store-url="{{ route('marketing.dinamica.trivia.store') }}"
                    redirect-url="{{ route('marketing.dinamica.create') }}"
                ></trivia-dynamic-designer>
            </div>
        </div>
    </div>
@endsection
