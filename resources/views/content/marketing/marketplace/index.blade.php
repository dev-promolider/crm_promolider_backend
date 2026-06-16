@extends('layouts/contentLayoutMaster')
@section('title', 'Marketplace')
@section('vendor-style')
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
@endsection

@section('content')
    @if($permission)
    {{-- Contenedor blanco para la barra de navegación --}}
    <div class="card">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8 col-md-12 mb-1 mb-lg-0">
                    {{-- AÑADIDO: Un nuevo div con una clase para identificar los botones en JS --}}
                    <div class="marketplace-nav-buttons">
                        {{-- Se añadió un margen a cada botón (mr-1 mb-1) --}}
                        <button type="button" class="btn btn-primary active mr-1 mb-1" id="btn-masterclass" data-target="masterclass">
                            Masterclasses
                        </button>
                        <button type="button" class="btn btn-primary mr-1 mb-1" id="btn-ebook" data-target="ebook">
                            E-books
                        </button>
                        <button type="button" class="btn btn-primary mr-1 mb-1" id="btn-minicourse" data-target="minicourse">
                            Mini Cursos
                        </button>
                        
                        {{-- Botón visible solo para Producers --}}
                        @if($user_role === 'Producer')
                        <button type="button" class="btn btn-primary mr-1 mb-1" id="btn-campaigns" data-target="campaigns">
                            Mis Campañas
                        </button>
                        @endif
                    </div>
                </div>
                <div class="col-lg-4 col-md-12">
                    <div class="input-group">
                        <input type="text" class="form-control" id="searchInput" placeholder="Buscar en marketplace...">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button" id="searchBtn">
                                <i data-feather="search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Contenido de las pestañas --}}
    <div class="tab-content mt-2" id="marketplaceTabsContent">
        <div class="tab-pane fade show active" id="masterclass" role="tabpanel">
            <masterclass-card :user="{{ json_encode($user) }}"></masterclass-card>
        </div>
        <div class="tab-pane fade" id="ebook" role="tabpanel">
            <ebook-card :user="{{ json_encode($user) }}"></ebook-card>
        </div>
        <div class="tab-pane fade" id="minicourse" role="tabpanel">
            <mini-course-card :user="{{ json_encode($user) }}"></mini-course-card>
        </div>
        
        {{-- Contenido de Mis Campañas --}}
        @if($user_role === 'Producer')
        <div class="tab-pane fade" id="campaigns" role="tabpanel">
            <campaigns-card :user="{{ json_encode($user) }}"></campaigns-card>
        </div>
        @endif
    </div>
    @endif
@endsection

@section('vendor-script')
    <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap4.js')) }}"></script>
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
            $('.marketplace-nav-buttons button').on('click', function() {
                const target = $(this).data('target');
                
                // Remover clase active de todos los botones en este grupo
                $('.marketplace-nav-buttons button').removeClass('active');
                
                // Agregar clase active al botón clickeado
                $(this).addClass('active');
                
                // Ocultar todos los tab-pane
                $('.tab-pane').removeClass('show active');
                
                // Mostrar el tab-pane correspondiente
                $('#' + target).addClass('show active');
                
                // NUEVO: Re-emitir el evento de búsqueda para la nueva pestaña
                const searchTerm = $('#searchInput').val();
                if (searchTerm) {
                    setTimeout(function() {
                        window.dispatchEvent(new CustomEvent('marketplace-search', {
                            detail: { searchTerm: searchTerm }
                        }));
                    }, 100);
                }
            });
            
            // Funcionalidad del botón de búsqueda
            $('#searchInput').on('keyup', function(e) {
                if(e.key === 'Enter') {
                    performSearch();
                }
            });

            $('#searchBtn').on('click', function() {
                performSearch();
            });

            function performSearch() {
                const searchTerm = $('#searchInput').val();
                // Emitir evento para que los componentes Vue lo escuchen
                window.dispatchEvent(new CustomEvent('marketplace-search', {
                    detail: { searchTerm: searchTerm }
                }));
            }

            // Inicializar iconos de Feather
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
    </script>
@endsection