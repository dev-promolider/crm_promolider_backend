@extends('layouts/contentLayoutMaster')

@section('title', 'Curso Gratuito')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap4.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap4.min.css')) }}">
@endsection

@section('content')
    <div class="container mt-5">
        <!-- Botón para crear un minicurso, alineado a la izquierda -->
        <div class="d-flex justify-content-start mb-3">
            <a href="{{ route('freecourse.create') }}" class="btn btn-success">
                Crear Minicurso
            </a>
        </div>

        <!-- Tabla de minicursos, aquí mostrarás los datos de los minicursos -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Título</th>
                        <th>Categoría</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Nivel de dificultad</th>
                        <th>Enlace</th>
                        <th>Imagen Destacada</th>
                        <th>Video Introductorio</th>
                        <th>Presentación</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Aquí colocarás los datos de los minicursos -->
                    <tr>
                        <td>1</td>
                        <td>Curso de Ejemplo</td>
                        <td>Desarrollo Web</td>
                        <td>12/05/2025</td>
                        <td>14:00</td>
                        <td>Nivel 1</td>
                        <td><a href="link_del_minicurso">Ver curso</a></td>
                        <td><img src="imagen_destacada.jpg" alt="Imagen destacada" width="50"></td>
                        <td><a href="video_intro.mp4">Ver video</a></td>
                        <td><a href="presentacion.pdf">Ver presentación</a></td>
                        <td>
                            <a href="#" class="btn btn-primary btn-sm">Editar</a>
                            <a href="#" class="btn btn-danger btn-sm">Eliminar</a>
                        </td>
                    </tr>
                    <!-- Más minicursos pueden ser agregados aquí dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('vendor-script')
    <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.bootstrap4.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap4.js')) }}"></script>
    <script>
        $(document).ready(function() {
            // Activar la tabla de datos
            $('.table').DataTable();
        });
    </script>
@endsection
