<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $miniCourse->title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para iconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin: 2rem auto;
            padding: 2rem;
            max-width: 1200px;
        }

        .course-header {
            text-align: center;
            padding: 2rem 0;
            background: linear-gradient(135deg, #007cba, #0056b3);
            color: white;
            border-radius: 15px;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }

        .course-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .course-header h1 {
            position: relative;
            z-index: 1;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .course-header .lead {
            position: relative;
            z-index: 1;
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .course-meta {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 25px;
            backdrop-filter: blur(5px);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 3rem 0 1.5rem 0;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid #007cba;
        }

        .section-header h2 {
            color: #007cba;
            font-weight: 600;
            margin: 0;
        }

        .section-icon {
            font-size: 2rem;
            color: #007cba;
        }

        .progress-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .progress-text {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .progress {
            height: 8px;
            border-radius: 10px;
            background-color: #e9ecef;
        }

        .progress-bar {
            background: linear-gradient(90deg, #007cba, #0056b3);
            border-radius: 10px;
        }

        .welcome-message {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
        }

        /* Estilos de módulos tomados de module.blade.php */
        .module-card {
            margin-bottom: 30px;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        .module-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 20px;
            border-bottom: none;
        }
        .module-title {
            margin-bottom: 0;
            font-weight: 600;
        }
        .module-body {
            padding: 25px;
        }
        .document-badge {
            background: linear-gradient(135deg, #e0f2fe 0%, #f3e5f5 100%);
            color: #1565c0;
            padding: 8px 15px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 0.875rem;
            margin: 5px;
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
        .video-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .video-card:hover {
            transform: translateY(-3px);
        }
        .video-header {
            background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
            color: white;
            padding: 15px 20px;
        }
        .video-title {
            margin-bottom: 0;
            font-weight: 600;
            font-size: 1.1rem;
        }
        .module-documents, .module-videos {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
        }
        .subsection-title {
            color: #374151;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .no-content {
            color: #9ca3af;
            font-style: italic;
            font-size: 0.9rem;
            padding: 15px;
            background: #f9fafb;
            border-radius: 10px;
            text-align: center;
        }
        .video-wrapper {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            background: #000;
        }
        .video-wrapper video {
            width: 100%;
            height: auto;
            display: block;
        }
        .video-meta {
            padding: 15px;
            background: #f8fafc;
        }
        .module-video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }

        /* Videos generales */
        .general-videos-section {
            margin-top: 40px;
            padding: 30px;
            background: linear-gradient(135deg, #fef7cd 0%, #fde68a 100%);
            border-radius: 15px;
        }

        @media (max-width: 768px) {
            .main-container {
                margin: 1rem;
                padding: 1rem;
            }
            
            .course-header h1 {
                font-size: 2rem;
            }
            
            .course-meta {
                flex-direction: column;
                gap: 1rem;
            }
            
            .section-header {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>

<div class="main-container">
    <!-- Mensaje de bienvenida personalizado -->
    <div class="welcome-message">
        <h4><i class="fas fa-user-graduate"></i> ¡Bienvenido/a, {{ $miniCourseUser->name }}!</h4>
        <p class="mb-0">Estás a punto de comenzar una experiencia de aprendizaje increíble</p>
    </div>

    <!-- Encabezado del curso -->
    <div class="course-header">
        <h1>{{ $miniCourse->title }}</h1>
        <p class="lead">{{ $miniCourse->description }}</p>
        
        <div class="course-meta">
            <div class="meta-item">
                <i class="fas fa-clock"></i>
                <span>{{ $miniCourse->duration }} minutos</span>
            </div>
            <div class="meta-item">
                <i class="fas fa-layer-group"></i>
                <span>{{ count($miniCourse->modules) }} módulos</span>
            </div>
            <div class="meta-item">
                <i class="fas fa-signal"></i>
                <span>{{ ucfirst($miniCourse->level ?? 'intermedio') }}</span>
            </div>
        </div>
    </div>

    <!-- Progreso del curso -->
    <div class="progress-container">
        <div class="progress-text">Progreso del curso</div>
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    <!-- Módulos del curso con formato de module.blade.php -->
    @if(count($miniCourse->modules) > 0)
    <div class="section-header">
        <i class="fas fa-puzzle-piece section-icon"></i>
        <h2>Módulos del Curso</h2>
    </div>

    @foreach($miniCourse->modules as $index => $modulo)
        <div class="card module-card">
            <div class="module-header">
                <h5 class="module-title">
                    <span class="badge bg-light text-dark me-3">Módulo {{ $index + 1 }}</span>
                    {{ $modulo->title }}
                </h5>
                <div class="mt-2">
                    <small>
                        <i class="fas fa-clock me-1"></i>
                        Duración: {{ $modulo->duration }} minutos
                    </small>
                </div>
            </div>
            
            <div class="module-body">
                <p class="mb-4">{{ $modulo->content }}</p>
                
                <!-- Documentos del módulo -->
                <div class="module-documents">
                    <h6 class="subsection-title">
                        <i class="fas fa-file-alt"></i>
                        Documentos del módulo
                    </h6>
                    @php
                        $moduleDocuments = $miniCourse->documents->where('mini_course_module_id', $modulo->id);
                    @endphp
                    
                    @if($moduleDocuments->count() > 0)
                        <div>
                            @foreach($moduleDocuments as $doc)
                                <a href="{{ asset($doc->document) }}" target="_blank" class="document-badge">
                                    <i class="fas fa-paperclip me-1"></i>
                                    {{ basename($doc->document) }}
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="no-content">
                            <i class="fas fa-inbox me-2"></i>
                            No hay documentos disponibles para este módulo
                        </div>
                    @endif
                </div>

                <!-- Videos del módulo -->
                <div class="module-videos">
                    <h6 class="subsection-title">
                        <i class="fas fa-video"></i>
                        Videos del módulo
                    </h6>
                    @php
                        $moduleVideos = $miniCourse->videos->where('mini_course_module_id', $modulo->id)->sortBy('order');
                    @endphp
                    
                    @if($moduleVideos->count() > 0)
                        <div class="module-video-grid">
                            @foreach($moduleVideos as $video)
                                <div class="video-card">
                                    <div class="video-header">
                                        <h6 class="video-title">{{ $video->title }}</h6>
                                        <small>
                                            <i class="fas fa-play-circle me-1"></i>
                                            {{ $video->duration }} min
                                            @if($video->order)
                                                | Orden: {{ $video->order }}
                                            @endif
                                        </small>
                                    </div>
                                    <div class="video-wrapper">
                                        <video src="{{ asset($video->video) }}" controls preload="metadata">
                                            Tu navegador no soporta el elemento video.
                                        </video>
                                    </div>
                                    @if($video->description)
                                        <div class="video-meta">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>
                                                {{ $video->description }}
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="no-content">
                            <i class="fas fa-video-slash me-2"></i>
                            No hay videos disponibles para este módulo
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
    @endif

    <!-- Videos generales (si existen, para compatibilidad) -->
    @php
        $generalVideos = $miniCourse->videos->whereNull('mini_course_module_id');
    @endphp
    
    @if($generalVideos->count() > 0)
        <div class="general-videos-section">
            <div class="section-header">
                <i class="fas fa-film section-icon"></i>
                <h2>Videos Generales del Curso</h2>
            </div>
            <div class="row">
                @foreach($generalVideos as $video)
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="video-card">
                            <div class="video-header">
                                <h6 class="video-title">{{ $video->title }}</h6>
                                <small>
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $video->duration }} minutos
                                </small>
                            </div>
                            <div class="video-wrapper">
                                <video src="{{ asset($video->video) }}" controls preload="metadata">
                                    Tu navegador no soporta el elemento video.
                                </video>
                            </div>
                            @if($video->description)
                                <div class="video-meta">
                                    <p class="mb-0">{{ $video->description }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Documentos generales (sin módulo específico) -->
    @php
        $generalDocuments = $miniCourse->documents->whereNull('mini_course_module_id');
    @endphp
    
    @if($generalDocuments->count() > 0)
        <div class="section-header">
            <i class="fas fa-folder-open section-icon"></i>
            <h2>Documentos Generales del Curso</h2>
        </div>
        <div class="card">
            <div class="card-body">
                @foreach($generalDocuments as $doc)
                    <a href="{{ asset($doc->document) }}" target="_blank" class="document-badge">
                        <i class="fas fa-download me-1"></i>
                        {{ basename($doc->document) }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Mensaje de finalización -->
    <div class="text-center mt-5 p-4">
        <h4 class="text-primary">
            <i class="fas fa-trophy"></i>
            ¡Felicitaciones por completar el curso!
        </h4>
        <p class="text-muted">Has adquirido nuevos conocimientos que te ayudarán en tu crecimiento profesional.</p>
        
        <div class="mt-3">
            <button class="btn btn-success btn-lg" onclick="markAsCompleted()">
                <i class="fas fa-check-circle"></i>
                Marcar como Completado
            </button>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Funcionalidad para marcar progreso
    function markAsCompleted() {
        const progressBar = document.querySelector('.progress-bar');
        progressBar.style.width = '100%';
        progressBar.setAttribute('aria-valuenow', '100');
        
        // Mostrar mensaje de éxito
        const completionMessage = document.createElement('div');
        completionMessage.className = 'alert alert-success mt-3';
        completionMessage.innerHTML = '<i class="fas fa-check-circle"></i> ¡Curso completado exitosamente!';
        
        const button = event.target;
        button.parentNode.appendChild(completionMessage);
        button.style.display = 'none';
    }

    // Script mejorado para la experiencia de video
    document.addEventListener('DOMContentLoaded', function() {
        // Mejorar la carga de videos
        const videos = document.querySelectorAll('video');
        videos.forEach(video => {
            video.addEventListener('loadstart', function() {
                console.log('Cargando video:', this.src);
            });
            
            video.addEventListener('error', function() {
                console.error('Error al cargar video:', this.src);
                const wrapper = this.closest('.video-wrapper');
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
        
        // Animaciones de entrada
        const cards = document.querySelectorAll('.module-card, .video-card');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '0';
                    entry.target.style.transform = 'translateY(20px)';
                    entry.target.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                    
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, 100);
                    
                    observer.unobserve(entry.target);
                }
            });
        });
        
        cards.forEach(card => {
            observer.observe(card);
        });
    });

    // Tracking de progreso de videos
    document.querySelectorAll('video').forEach(video => {
        video.addEventListener('ended', function() {
            // Aquí podrías enviar una petición AJAX para actualizar el progreso
            console.log('Video completado:', this.closest('.video-card').querySelector('.video-title').textContent);
        });
    });
</script>

</body>
</html>