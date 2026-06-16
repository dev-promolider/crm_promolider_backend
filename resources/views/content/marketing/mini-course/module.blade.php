<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $miniCourse->title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #f2f5fa 0%, #e8f4f8 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-container {
            background: white;
            min-height: 100vh;
        }

        .content-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Columna principal - 70% */
        .main-content {
            flex: 0 0 60%;
            width: 60%;
            padding: 2rem;
            background: white;
        }

        .course-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .video-container {
            width: 100%;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
            background: #000;
        }

        .video-container video {
            width: 100%;
            height: auto;
            display: block;
        }

        .module-description {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 15px;
            border-left: 4px solid #4f46e5;
        }

        /* Estilos para los botones de Resumen y Recursos */
        .tab-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .tab-button {
            background-color: #e5e7eb;
            color: #6b7280;
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .tab-button.active {
            background-color: #1ae800;
            color: white;
            box-shadow: 0 4px 15px rgba(26, 232, 0, 0.3);
            transform: translateY(-2px);
        }

        .tab-button:hover:not(.active) {
            background-color: #d1d5db;
        }

        /* Sidebar - 30% */
        .sidebar {
            flex: 0 0 40%;
            width: 40%;
            background: #f2f5fa;
            padding: 1.5rem;
            border-left: 1px solid #e5e7eb;
            overflow-y: auto;
            max-height: 100vh;
        }

        /* Docente Card */
        .docente-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .docente-header {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #1f2937;
        }

        .instructor-info {
            display: flex;
            align-items: center;
        }

        .instructor-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1rem;
        }

        .instructor-details h6 {
            margin: 0;
            font-weight: 600;
            color: #374151;
        }

        .instructor-details small {
            color: #6b7280;
        }

        /* Temario Card */
        .temario-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .temario-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .temario-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .search-container {
            flex: 1;
            margin-left: 1rem;
        }

        .search-input {
            position: relative;
            border-radius: 25px;
            overflow: hidden;
            background: #f3f4f6;
            transition: all 0.3s ease;
        }

        .search-input:hover,
        .search-input:focus-within {
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .search-input input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: none;
            background: transparent;
            outline: none;
            font-size: 0.9rem;
        }

        .search-input input::placeholder {
            color: #9ca3af;
        }

        /* Módulos */
        .module-item {
            border: none;
            margin-bottom: 1rem;
        }

        .module-title {
            margin: 0;
            font-weight: 600;
            font-size: 1rem;
        }

        .module-duration {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-top: 0.5rem;
        }

        .lessons-list {
            max-height: 200px;
            overflow-y: auto;
            padding: 0;
        }

        .lesson-item {
            display: flex;
            align-items: center;
            padding: 0.4rem;
            border-radius: 6px;
            border-bottom: none;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-left: 15px;
        }

        .lesson-item:hover {
            background: white;
        }

        .lesson-item:last-child {
            border-bottom: none;
        }

        .lesson-checkbox {
            margin-right: 0.75rem;
            cursor: pointer;
        }

        .lesson-name {
            flex: 1;
            font-size: 0.9rem;
            color: #374151;
            text-decoration: none;
            margin: 0;
            transition: background-color 0.2s ease;
            padding: 0.25rem 0.5rem;
        }

        .lesson-item.active {
        }

        .lesson-item.active .lesson-name {
            background-color: #1ae800;
            color: #000000ff;
            font-weight: 600;
            border-radius: 10px;
        }

        /* Status indicators */
        .course-stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-badge {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        /* Documentos y recursos */
        .resources-section {
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 15px;
        }

        .resources-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .document-badge {
            background: linear-gradient(135deg, #e0f2fe 0%, #f3e5f5 100%);
            color: #1565c0;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            text-decoration: none;
            font-size: 0.875rem;
            margin: 0.25rem;
            display: inline-block;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .document-badge:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.3);
        }

        /* Responsive design */
        @media (max-width: 1024px) {
            .content-wrapper {
                flex-direction: column;
            }
            
            .main-content {
                flex: none;
                width: 100%;
            }
            
            .sidebar {
                flex: none;
                width: 100%;
                max-height: none;
                border-left: none;
                border-top: 1px solid #e5e7eb;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            
            .sidebar {
                padding: 1rem;
            }
            
            .temario-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .search-container {
                margin-left: 0;
                width: 100%;
            }
        }

        /* Animaciones */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.6s ease forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Botón de menú para mobile */
        .menu-btn {
            background: #1ae800;
            border: none;
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .no-content {
            color: #9ca3af;
            font-style: italic;
            font-size: 0.9rem;
            padding: 1rem;
            background: #f9fafb;
            border-radius: 10px;
            text-align: center;
        }

        /* Scrollbar personalizado */
        .lessons-list::-webkit-scrollbar {
            width: 4px;
        }

        .lessons-list::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .lessons-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 2px;
        }

        .lessons-list::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .custom-checkbox {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #4caf50;
            border-radius: 50%;
            outline: none;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
        }
        
        /* Cuando está marcado */
        .custom-checkbox:checked {
            background-color: #4caf50;
            border-color: #4caf50;
        }
        
        /* Dibujar el check */
        .custom-checkbox:checked::after {
            content: "✓";
            color: white;
            font-size: 14px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
    </style>
</head>
<body>

<div class="main-container">
    <div class="content-wrapper">
        <div class="main-content">
            <div class="fade-in">
                <h1 class="course-title">{{ $miniCourse->title }}</h1>
                
                <div class="course-stats">
                    <span class="stat-badge">
                        <i class="fas fa-clock me-1"></i>
                        {{ $miniCourse->duration }} minutos
                    </span>
                    <span class="stat-badge">
                        <i class="fas fa-signal me-1"></i>
                        {{ ucfirst($miniCourse->level) }}
                    </span>
                    <span class="stat-badge">
                        <i class="fas fa-layer-group me-1"></i>
                        {{ $miniCourse->modules->count() }} módulos
                    </span>
                </div>

                @php
                    $currentModule = $miniCourse->modules->first();
                    $currentVideo = $currentModule ? $miniCourse->classes->where('mini_course_module_id', $currentModule->id)->first() : null;
                @endphp

                @if($currentVideo)
                    <div class="video-container">
                        <video 
                            id="videoPlayer" 
                            src="{{ $currentVideo->video }}" 
                            controls 
                            preload="metadata"
                            controlsList="nodownload" 
                            oncontextmenu="return false;"
                            style="pointer-events: auto;"
                        >
                            Tu navegador no soporta el elemento video.
                        </video>
                    </div>
                @endif
                
                <div class="tab-buttons">
                    <button class="tab-button active" id="btnResumen" onclick="showSummary()">Resumen</button>
                    <button class="tab-button" id="btnRecursos" onclick="showResources()">Recursos</button>
                </div>

                <div id="dynamicContent">
                    @if($currentModule)
                        <div class="module-description" id="summarySection">
                            <h5>{{ $currentModule->title }}</h5>
                            <p class="mb-0">{{ $currentModule->content }}</p>
                        </div>
                    @endif
                    <div class="resources-section" id="resourcesSection" style="display: none;">
                        <h6 class="resources-title">
                            <i class="fas fa-file-alt"></i>
                            Recursos del módulo
                        </h6>
                        <div id="resourcesContainer">
                            @if($currentModule && $currentVideo)
                                @php
                                    // Buscar la primera clase del módulo actual para obtener sus documentos
                                    $currentClass = $miniCourse->classes->where('mini_course_module_id', $currentModule->id)->first();
                                    $currentModuleDocuments = $currentClass ? $currentClass->documents : collect();
                                @endphp
                                
                                @if($currentModuleDocuments->count() > 0)
                                    @foreach($currentModuleDocuments as $doc)
                                        <a href="{{ route('documents.view', $doc->id) }}" target="_blank" class="document-badge">
                                            <i class="fas fa-paperclip me-1"></i>
                                            {{ basename($doc->document) }}
                                        </a>
                                    @endforeach
                                @else
                                    <div class="no-content">
                                        <i class="fas fa-inbox me-2"></i>
                                        No hay documentos disponibles para esta clase
                                    </div>
                                @endif
                            @else
                                <div class="no-content">
                                    <i class="fas fa-inbox me-2"></i>
                                    No hay documentos disponibles
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="sidebar">
            <div class="docente-card fade-in">
                <div class="docente-header">Docente</div>
                <div class="instructor-info">
                    <img src="{{ asset('images/defaultavatar/avatar1.png') }}" 
                         alt="Instructor" class="instructor-avatar">
                    <div class="instructor-details">
                        <h6>{{ $miniCourse->user->name ?? 'Instructor' }}</h6>
                        <small>{{ $miniCourse->user->email ?? 'Email no disponible' }}</small>
                    </div>
                </div>
            </div>

            <div class="temario-card fade-in">
                <div class="temario-header">
                    <p class="temario-title">Temario</p>
                    <div class="search-container">
                        <div class="search-input">
                            <input type="text" placeholder="Buscar un tema" id="searchInput">
                        </div>
                    </div>
                    <button class="menu-btn" onclick="toggleMenu()">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>

                <div class="modules-container" style="max-height: 400px; overflow-y: auto;">
                    @foreach($miniCourse->modules as $index => $module)
                        <div class="module-item">
                            <div class="module-header" onclick="toggleModule('module-{{ $index }}')">
                                <div class="module-title">
                                    <span class="me-2">{{ $index + 1 }}.</span>
                                    {{ $module->title }}
                                </div>
                            </div>
                            
                            <div class="lessons-list" id="module-{{ $index }}" style="display: block;">
                                @php
                                    $moduleVideos = $miniCourse->classes->where('mini_course_module_id', $module->id);
                                @endphp
                                
                                @if($moduleVideos->count() > 0)
                                    @foreach($moduleVideos as $class)
                                        <div class="lesson-item" onclick="loadVideo('{{ $class->video }}', '{{ $class->title }}', '{{ $class->description }}', {{ $module->id }}, {{ $class->id }})">
                                            <input type="checkbox" class="lesson-checkbox custom-checkbox" id="lesson-{{ $class->id }}" onclick="event.stopPropagation()">
                                            <p class="lesson-name">{{ $class->title }}</p>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="lesson-item">
                                        <p class="lesson-name" style="color: #9ca3af; font-style: italic;">No hay videos disponibles</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Variables para almacenar la descripción y el ID del módulo actual
    let currentModuleDescription = '';
    let currentModuleId = null;
    let currentClassId = null; // Nueva variable para almacenar el ID de la clase actual

    document.addEventListener('DOMContentLoaded', function() {
        // Asignar el contenido inicial del primer módulo y primera clase
        const initialModule = @json($currentModule);
        const initialClass = @json($currentVideo);

        if (initialModule) {
            currentModuleDescription = initialModule.content;
            currentModuleId = initialModule.id;
        }

        if (initialClass) {
            currentClassId = initialClass.id;
        }

        // Animaciones de entrada
        const fadeElements = document.querySelectorAll('.fade-in');
        fadeElements.forEach((element, index) => {
            setTimeout(() => {
                element.style.animation = `fadeIn 0.6s ease ${index * 0.1}s forwards`;
            }, 100);
        });

        // Funcionalidad de búsqueda
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const lessons = document.querySelectorAll('.lesson-item');

                lessons.forEach(lesson => {
                    const lessonText = lesson.textContent.toLowerCase();
                    if (lessonText.includes(searchTerm)) {
                        lesson.style.display = 'flex';
                    } else {
                        lesson.style.display = 'none';
                    }
                });
            });
        }

        // Manejo de errores de video
        const videos = document.querySelectorAll('video');
        videos.forEach(video => {
            video.addEventListener('error', function() {
                console.error('Error al cargar video:', this.src);
                const wrapper = this.closest('.video-container');
                if (wrapper) {
                    wrapper.innerHTML = `
                        <div class="alert alert-warning m-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No se pudo cargar el video. Por favor, verifica que el archivo esté disponible.
                        </div>
                    `;
                }
            });
        });

        // Marcar la primera lección como activa al cargar la página
        const firstLessonItem = document.querySelector('.lesson-item');
        if (firstLessonItem) {
            firstLessonItem.classList.add('active');
        }
    });

    function toggleModule(moduleId) {
        const module = document.getElementById(moduleId);
        if (module) {
            if (module.style.display === 'none') {
                module.style.display = 'block';
            } else {
                module.style.display = 'none';
            }
        }
    }

    function loadVideo(videoSrc, title, description, moduleId, classId) {
        // Actualizar el video
        const videoPlayer = document.getElementById('videoPlayer');
        if (videoPlayer) {
            videoPlayer.src = videoSrc;
            videoPlayer.load(); // Cargar el nuevo video
        }

        // Actualizar las variables globales
        currentModuleDescription = description;
        currentModuleId = moduleId;
        currentClassId = classId; // Actualizar el ID de la clase actual

        // Mostrar el resumen y marcar el botón de resumen como activo por defecto
        showSummary();

        // Marcar la lección como activa y remover de otras
        document.querySelectorAll('.lesson-item').forEach(item => {
            item.classList.remove('active');
        });
        event.currentTarget.classList.add('active');
    }

    function showSummary() {
        const dynamicContent = document.getElementById('dynamicContent');
        const btnResumen = document.getElementById('btnResumen');
        const btnRecursos = document.getElementById('btnRecursos');

        // Marcar el botón activo
        btnResumen.classList.add('active');
        btnRecursos.classList.remove('active');

        // Generar el HTML del resumen
        const summaryHtml = `
            <div class="module-description">
                <h5>Resumen de la clase</h5>
                <p class="mb-0">${currentModuleDescription}</p>
            </div>
        `;

        // Insertar el contenido y limpiar el contenedor
        dynamicContent.innerHTML = summaryHtml;
    }

    function showResources() {
        const dynamicContent = document.getElementById('dynamicContent');
        const btnResumen = document.getElementById('btnResumen');
        const btnRecursos = document.getElementById('btnRecursos');

        // Marcar el botón activo
        btnRecursos.classList.add('active');
        btnResumen.classList.remove('active');

        // Limpiar el contenido actual
        dynamicContent.innerHTML = '';

        // Datos de documentos agrupados por clase (actualizado)
        const classDocuments = @json($miniCourse->classes->mapWithKeys(function($class) {
            return [$class->id => $class->documents];
        }));

        const resourcesHtml = document.createElement('div');
        resourcesHtml.className = 'resources-section';
        resourcesHtml.innerHTML = `
            <h6 class="resources-title">
                <i class="fas fa-file-alt"></i>
                Recursos de la clase
            </h6>
            <div id="resourcesContainer"></div>
        `;

        const resourcesContainer = resourcesHtml.querySelector('#resourcesContainer');

        if (classDocuments[currentClassId] && classDocuments[currentClassId].length > 0) {
            classDocuments[currentClassId].forEach(doc => {
                const docLink = document.createElement('a');
                docLink.href = `/documents/${doc.id}/view`;
                docLink.target = '_blank';
                docLink.className = 'document-badge';
                docLink.innerHTML = `<i class="fas fa-paperclip me-1"></i>${getFileName(doc.document)}`;
                resourcesContainer.appendChild(docLink);
            });
        } else {
            resourcesContainer.innerHTML = `
                <div class="no-content">
                    <i class="fas fa-inbox me-2"></i>
                    No hay documentos disponibles para esta clase
                </div>
            `;
        }

        // Insertar el contenido de recursos
        dynamicContent.appendChild(resourcesHtml);
    }

    function getFileName(path) {
        return path.split('/').pop();
    }

    function toggleMenu() {
        console.log('Menu clicked');
    }
</script>

</body>
</html>