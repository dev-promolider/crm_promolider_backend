@extends('layouts.contentLayoutMaster')

@section('title', 'Editar trivia')

@section('content')
    <div id="app">
        @php
            $questionCategories = [
                ['id' => 1, 'name' => 'Bienestar integral'],
                ['id' => 2, 'name' => 'Cultura corporativa'],
                ['id' => 3, 'name' => 'Innovacion y tecnologia'],
                ['id' => 4, 'name' => 'Productos estrella'],
                ['id' => 5, 'name' => 'Historia de la marca'],
            ];

            $mockTrivia = [
                'title' => 'Trivia Desafio Primavera',
                'slug' => 'desafio-primavera',
                'description' => 'Edicion especial para el lanzamiento trimestral.',
                'status' => 'published',
                'points_min' => 5,
                'points_max' => 25,
                'is_active' => true,
            ];

            $mockDynamics = [
                [
                    'id' => 101,
                    'title' => 'Juego 1 · Lanzamiento',
                    'description' => 'Introduce novedades y preguntas rapidas.',
                    'position' => 1,
                    'is_active' => true,
                    'categories' => [
                        ['id' => 201, 'question_category_id' => 1, 'position' => 1, 'questions_count' => 5, 'is_active' => true],
                        ['id' => 202, 'question_category_id' => 4, 'position' => 2, 'questions_count' => 5, 'is_active' => true],
                    ],
                ],
                [
                    'id' => 102,
                    'title' => 'Juego 2 · Revancha',
                    'description' => 'Mayor dificultad y preguntas situacionales.',
                    'position' => 2,
                    'is_active' => false,
                    'categories' => [
                        ['id' => 203, 'question_category_id' => 2, 'position' => 1, 'questions_count' => 6, 'is_active' => true],
                        ['id' => 204, 'question_category_id' => 5, 'position' => 2, 'questions_count' => 6, 'is_active' => true],
                    ],
                ],
            ];
        @endphp

        @include('content.marketing.dinamica.trivia.partials.shell', [
            'active' => 'edit',
            'breadcrumbs' => [
                ['label' => 'Marketing', 'url' => route('marketing.tools')],
                ['label' => 'Dinámica', 'url' => route('marketing.dinamica.create')],
                ['label' => 'Trivia', 'url' => route('marketing.dinamica.trivia')],
                ['label' => "Editar #{$triviaId}", 'url' => null],
            ],
        ])

        <div class="card shadow-sm border-0">
            <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                <div>
                    <h4 class="card-title mb-0">Editar trivia #{{ $triviaId }}</h4>
                    <small class="text-muted">Version mock para alinear campos antes del backend</small>
                </div>
                <span class="badge badge-light mt-2 mt-md-0">Marketing · Dinamicas</span>
            </div>
            <div class="card-body">
                <trivia-form-builder
                    mode="edit"
                    :initial-trivia='@json($mockTrivia)'
                    :initial-dynamics='@json($mockDynamics)'
                    :question-categories='@json($questionCategories)'
                ></trivia-form-builder>
            </div>
        </div>
    </div>
@endsection
