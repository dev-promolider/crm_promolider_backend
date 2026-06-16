<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Inscripción: {{ $masterclass->title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style type="text/css">
        /* Estilos generales */
        body {
            background-color: #f0f2f5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        /* Contenedor principal estilo tarjeta */
        .registration-card {
            display: flex;
            max-width: 1000px;
            width: 100%;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Columna izquierda: Información y Formulario */
        .form-section {
            width: 60%;
            padding: 40px;
            box-sizing: border-box;
        }
        
        /* Columna derecha: Imagen promocional */
        .image-section {
            width: 40%;
            background-size: cover;
            background-position: center;
        }
        
        /* Títulos jerarquizados */
        .main-title {
            color: #1ce501;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        
        .masterclass-name {
            color: #333;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .form-title {
            color: #666;
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f2f5;
        }
        
        /* Detalles del curso */
        .course-details {
            margin-bottom: 25px;
            font-size: 15px;
            line-height: 1.6;
        }
        .course-details p {
            margin-bottom: 8px;
        }
        
        /* Estilos de los campos del formulario */
        .form-row {
            display: flex;
            gap: 20px;
        }
        .form-group {
            flex: 1;
            margin-bottom: 1rem;
        }
        .form-group label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #555;
        }
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            height: auto;
            border: 1px solid #ced4da;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }

        /* Botón de envío */
        .submit-button {
            background-color: #1ce501;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin-top: 15px;
            transition: background-color 0.3s;
        }
        .submit-button:hover:not(:disabled) {
            background-color: #218838;
        }
        
        /* Botón deshabilitado */
        .submit-button:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        /* Alerta de evento pasado */
        .event-passed-alert {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
        }

        /* Estilos del modal de éxito */
        .success-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .success-modal-content {
            background: white;
            border-radius: 20px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.4s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header-success {
            background: linear-gradient(135deg, #1ce501 0%, #17b801 100%);
            padding: 30px;
            text-align: center;
            border-radius: 20px 20px 0 0;
        }

        .checkmark-circle {
            width: 70px;
            height: 70px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }

        .checkmark-circle::before {
            content: '✓';
            font-size: 40px;
            color: #1ce501;
            font-weight: bold;
        }

        .modal-header-success h2 {
            color: white;
            font-size: 26px;
            font-weight: 700;
            margin: 0;
        }

        .modal-body-success {
            padding: 30px;
        }

        .modal-body-success p {
            color: #333;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .masterclass-info-box {
            background: #f8f9fa;
            border-left: 4px solid #1ce501;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .masterclass-info-box h3 {
            color: #1ce501;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .masterclass-info-box .info-item {
            margin-bottom: 8px;
        }

        .info-box-blue {
            background: #e3f2fd;
            border: 2px solid #2196f3;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }

        .info-box-blue h4 {
            color: #1976d2;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .warning-box-yellow {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }

        .warning-box-yellow h4 {
            color: #856404;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .final-msg {
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            font-size: 17px;
            font-weight: 600;
            margin-top: 20px;
        }

        .close-modal-btn {
            background: #1ce501;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: background 0.3s;
        }

        .close-modal-btn:hover {
            background: #17b801;
        }
        
        /* Estilos para pantallas pequeñas (móviles) */
        @media (max-width: 768px) {
            .registration-card {
                flex-direction: column;
            }
            .form-section, .image-section {
                width: 100%;
            }
            .image-section {
                min-height: 200px;
            }
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            .main-title {
                font-size: 26px;
            }
            .masterclass-name {
                font-size: 20px;
            }
            .success-modal-content {
                margin: 20px;
            }
            .modal-body-success {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <div class="registration-card">

        <div class="form-section">
            <h1 class="main-title">Masterclass Gratuita</h1>
            <h2 class="masterclass-name">{{ $masterclass->title }}</h2>
            <h3 class="form-title">Formulario de Inscripción</h3>

            <div class="course-details">
                <p><strong>Categoría:</strong> {{ $masterclass->category_name }}</p>
                <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($masterclass->date)->format('d/m/Y') }}</p>
                <p><strong>Descripción:</strong> {{ $masterclass->description }}</p>
                <p><strong>Objetivos:</strong> {{ $masterclass->objectives }}</p>
            </div>

            @php
                $eventDate = \Carbon\Carbon::parse($masterclass->date);
                $isEventPassed = $eventDate->isPast();
            @endphp

            @if($isEventPassed)
                <div class="event-passed-alert">
                    <strong>⚠️ Este evento ya ha finalizado.</strong> La fecha de la masterclass fue {{ $eventDate->format('d/m/Y') }}.
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="post" action="{{ route('masterclass-register') }}">
                @csrf
                <input type="hidden" name="id_invitation" value="{{ $id_invitation }}">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nombres</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Ej: Juan" required {{ $isEventPassed ? 'disabled' : '' }}>
                    </div>
                    <div class="form-group">
                        <label for="lastname">Apellidos</label>
                        <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Ej: Pérez" required {{ $isEventPassed ? 'disabled' : '' }}>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="tu.correo@ejemplo.com" required {{ $isEventPassed ? 'disabled' : '' }}>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Teléfono</label>
                        <input type="tel" id="phone" name="phone" class="form-control" placeholder="Ej: 987654321" required {{ $isEventPassed ? 'disabled' : '' }}>
                    </div>
                    <div class="form-group">
                        <label for="nationality">País</label>
                        <input type="text" id="nationality" name="nationality" class="form-control" placeholder="Ej: Perú" required {{ $isEventPassed ? 'disabled' : '' }}>
                    </div>
                    <div class="form-group">
                        <label for="age">Edad</label>
                        <input type="number" id="age" name="age" class="form-control" placeholder="Ej: 25" required {{ $isEventPassed ? 'disabled' : '' }}>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><strong>User Type</strong></label>
                    <div class="d-flex align-items-center" style="gap: 20px;">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="user_type" id="guest" value="Guest" required {{ $isEventPassed ? 'disabled' : '' }}>
                            <label class="form-check-label" for="guest">Guest</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="user_type" id="affiliate" value="Affiliate" required {{ $isEventPassed ? 'disabled' : '' }}>
                            <label class="form-check-label" for="affiliate">Affiliate</label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="submit-button" {{ $isEventPassed ? 'disabled' : '' }}>
                    {{ $isEventPassed ? 'Evento Finalizado' : 'Inscribirme Ahora' }}
                </button>
            </form>
        </div>

        <div class="image-section" style="background-image: url('{{ $masterclass->images->count() > 0 ? $masterclass->images[0]->image : 'https://via.placeholder.com/600x800' }}');">
        </div>

    </div>

    <!-- Modal de Éxito -->
    @if(session('success'))
    <div class="success-modal" id="successModal">
        <div class="success-modal-content">
            <div class="modal-header-success">
                <div class="checkmark-circle"></div>
                <h2>¡Felicidades! Tu Asiento VIP está Confirmado</h2>
            </div>
            <div class="modal-body-success">
                <p>Te has asegurado tu lugar en la Masterclass Exclusiva:</p>
                
                <div class="masterclass-info-box">
                    <h3>{{ $masterclass->title }}</h3>
                    <div class="info-item"><strong>📅 Fecha:</strong> {{ \Carbon\Carbon::parse($masterclass->date)->format('d/m/Y') }}</div>
                    <div class="info-item"><strong>🕐 Hora:</strong> {{ \Carbon\Carbon::parse($masterclass->date)->format('h:i A') }}</div>
                </div>

                <div class="info-box-blue">
                    <h4>📧 Paso Importante: Revisa tu Correo</h4>
                    <p>Acabamos de enviarte el <strong>link de acceso directo a la sala</strong>. Si no lo ves en 5 minutos, revisa tu carpeta de <em>Spam</em> o <em>Promociones</em>. <strong>¡Guárdalo en un lugar seguro AHORA mismo!</strong></p>
                </div>

                <div class="warning-box-yellow">
                    <h4>⚠️ Un Aviso Importante</h4>
                    <p>Debido al alto interés y la naturaleza interactiva de esta sesión, las <strong>plazas son limitadas</strong> en la sala. Asegúrate de unirte <strong>10 minutos antes</strong> de la hora indicada.</p>
                </div>

                <div class="final-msg">
                    🎯 ¡Nos vemos en la sala! Prepárate para lo que viene.
                </div>

                <button class="close-modal-btn" onclick="closeModal()">Entendido</button>
            </div>
        </div>
    </div>
    @endif

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 1. Función que se llama al dar clic en "Entendido"
        function closeModal() {
            const modal = document.getElementById('successModal');
            if (modal) {
                modal.style.display = 'none';
            }
            
            // Intentar cerrar la pestaña del navegador
            window.close();
            
            // Si window.close() no funciona (algunas pestañas no se pueden cerrar por seguridad),
            // redirigir a la página principal
            setTimeout(function() {
                window.location.href = '{{ config("app.url") }}';
            }, 500);
        }

        // 2. Escuchar clics en toda la ventana para detectar si fue "afuera"
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('successModal');
            
            // Si el modal existe Y lo que se clickeó fue el fondo oscuro (el modal en sí)
            // y no la caja blanca (success-modal-content), entonces cerramos.
            if (modal && event.target === modal) {
                closeModal();
            }
        });
    </script>
</body>
</html>