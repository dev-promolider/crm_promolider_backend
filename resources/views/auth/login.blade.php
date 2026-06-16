@extends('layouts/fullLayoutMaster')

@section('title', 'Login Page')

@section('page-style')
    {{-- Archivos CSS de la página --}}
    <link rel="stylesheet" href="{{ asset(mix('css/base/pages/page-auth.css')) }}">
    <style>
        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 70%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
        }

        /* ========================================
           CONTENEDOR PRINCIPAL DE reCAPTCHA
           ======================================== */
        .g-recaptcha-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            margin: 20px 0;
            padding: 15px 0;
            position: relative;
            z-index: 10;
            min-height: 78px;
            clear: both;
            overflow: visible;
            box-sizing: border-box;
        }

        /* Widget de reCAPTCHA */
        .g-recaptcha {
            position: relative;
            z-index: 10;
            display: inline-flex;
            justify-content: center;
            align-items: center;
        }

        /* Evitar superposición - div interno del widget */
        .g-recaptcha > div {
            position: relative !important;
            z-index: 10;
            overflow: visible;
        }

        /* Iframe del reCAPTCHA */
        .g-recaptcha iframe {
            position: relative;
            z-index: 10;
            border: none;
            display: block;
        }

        /* Espaciado del botón de submit */
        .auth-login-form button[type="submit"] {
            margin-top: 25px;
            position: relative;
            z-index: 1;
        }

        /* ========================================
           RESPONSIVE - TABLETS
           ======================================== */
        @media (max-width: 576px) {
            .g-recaptcha-container {
                width: 100%;
                margin: 18px 0;
                min-height: 75px;
                padding: 12px 0;
            }

            .g-recaptcha {
                transform: scale(0.9);
                transform-origin: center;
            }

            .auth-login-form button[type="submit"] {
                margin-top: 20px;
            }
        }

        /* ========================================
           RESPONSIVE - MÓVILES
           ======================================== */
        @media (max-width: 480px) {
            .g-recaptcha-container {
                min-height: 72px;
                margin: 15px 0;
                padding: 10px 0;
            }

            .g-recaptcha {
                transform: scale(0.85);
                transform-origin: center;
            }
        }

        /* ========================================
           RESPONSIVE - MÓVILES PEQUEÑOS
           ======================================== */
        @media (max-width: 380px) {
            .g-recaptcha {
                transform: scale(0.77);
                transform-origin: center;
            }

            .g-recaptcha-container {
                min-height: 68px;
            }

            .auth-login-form button[type="submit"] {
                margin-top: 18px;
            }
        }

        /* ========================================
           RESPONSIVE - MÓVILES MUY PEQUEÑOS
           ======================================== */
        @media (max-width: 320px) {
            .g-recaptcha {
                transform: scale(0.72);
                transform-origin: center;
            }

            .g-recaptcha-container {
                min-height: 65px;
                padding: 8px 0;
            }
        }
        /* Estilos para el modal de recuperar contraseña */
        .v-dialog {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            backdrop-filter: blur(0px);
            transition: all 0.5s ease;
            /* Optimización para 60 FPS */
            will-change: opacity, backdrop-filter;
            transform: translateZ(0);
            backface-visibility: hidden;
            perspective: 1000px;
        }

        .v-dialog--active {
            display: flex !important;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(3px);
        }

        .modal-content {
            background: #35424a;
            border-radius: 12px;
            padding: 30px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            transform: scale3d(0.3, 0.3, 1) translate3d(0, -50px, 0);
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: 1px solid #4a5568;
            /* Optimización GPU para 60 FPS */
            will-change: transform, opacity;
            transform-style: preserve-3d;
            backface-visibility: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .v-dialog--active .modal-content {
            transform: scale3d(1, 1, 1) translate3d(0, 0, 0);
            opacity: 1;
        }

        /* Animación de entrada optimizada para 60 FPS */
        @keyframes modalFadeIn60fps {
            0% {
                opacity: 0;
                transform: scale3d(0.3, 0.3, 1) translate3d(0, -50px, 0) rotateX(10deg);
                filter: blur(2px);
            }
            16.67% {
                opacity: 0.1;
                transform: scale3d(0.4, 0.4, 1) translate3d(0, -40px, 0) rotateX(8deg);
                filter: blur(1.8px);
            }
            33.33% {
                opacity: 0.3;
                transform: scale3d(0.6, 0.6, 1) translate3d(0, -25px, 0) rotateX(5deg);
                filter: blur(1.2px);
            }
            50% {
                opacity: 0.8;
                transform: scale3d(1.05, 1.05, 1) translate3d(0, -10px, 0) rotateX(2deg);
                filter: blur(0.5px);
            }
            66.67% {
                opacity: 0.95;
                transform: scale3d(1.02, 1.02, 1) translate3d(0, -3px, 0) rotateX(0deg);
                filter: blur(0.2px);
            }
            83.33% {
                opacity: 1;
                transform: scale3d(1.01, 1.01, 1) translate3d(0, -1px, 0) rotateX(0deg);
                filter: blur(0px);
            }
            100% {
                opacity: 1;
                transform: scale3d(1, 1, 1) translate3d(0, 0, 0) rotateX(0deg);
                filter: blur(0px);
            }
        }

        .v-dialog--active .modal-content {
            animation: modalFadeIn60fps 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        /* Animación de salida optimizada */
        @keyframes modalFadeOut60fps {
            0% {
                opacity: 1;
                transform: scale3d(1, 1, 1) translate3d(0, 0, 0) rotateX(0deg);
                filter: blur(0px);
            }
            16.67% {
                opacity: 0.9;
                transform: scale3d(0.98, 0.98, 1) translate3d(0, -2px, 0) rotateX(1deg);
                filter: blur(0.1px);
            }
            33.33% {
                opacity: 0.7;
                transform: scale3d(0.85, 0.85, 1) translate3d(0, -10px, 0) rotateX(3deg);
                filter: blur(0.5px);
            }
            50% {
                opacity: 0.5;
                transform: scale3d(0.6, 0.6, 1) translate3d(0, -25px, 0) rotateX(6deg);
                filter: blur(1px);
            }
            66.67% {
                opacity: 0.3;
                transform: scale3d(0.4, 0.4, 1) translate3d(0, -35px, 0) rotateX(8deg);
                filter: blur(1.5px);
            }
            83.33% {
                opacity: 0.1;
                transform: scale3d(0.25, 0.25, 1) translate3d(0, -45px, 0) rotateX(10deg);
                filter: blur(2px);
            }
            100% {
                opacity: 0;
                transform: scale3d(0.1, 0.1, 1) translate3d(0, -60px, 0) rotateX(15deg);
                filter: blur(3px);
            }
        }

        .v-dialog--closing .modal-content {
            animation: modalFadeOut60fps 0.5s cubic-bezier(0.55, 0.055, 0.675, 0.19) forwards;
        }

        /* Optimización del fondo para 60 FPS */
        @keyframes backdropFadeIn60fps {
            0% {
                background-color: rgba(0, 0, 0, 0);
                backdrop-filter: blur(0px);
            }
            20% {
                background-color: rgba(0, 0, 0, 0.1);
                backdrop-filter: blur(0.6px);
            }
            40% {
                background-color: rgba(0, 0, 0, 0.25);
                backdrop-filter: blur(1.2px);
            }
            60% {
                background-color: rgba(0, 0, 0, 0.4);
                backdrop-filter: blur(1.8px);
            }
            80% {
                background-color: rgba(0, 0, 0, 0.55);
                backdrop-filter: blur(2.4px);
            }
            100% {
                background-color: rgba(0, 0, 0, 0.6);
                backdrop-filter: blur(3px);
            }
        }

        .v-dialog--active {
            animation: backdropFadeIn60fps 0.5s ease-out forwards;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #4a5568;
        }

        .modal-title {
            color: #1a7a3e;
            font-size: 20px;
            font-weight: 600;
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            color: #9ca3af;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
        }

        .modal-close:hover {
            color: #ef4444;
            background-color: rgba(239, 68, 68, 0.1);
        }

        .modal-body {
            margin-bottom: 25px;
        }

        .modal-text {
            color: #d1d5db;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .modal-form-group {
            margin-bottom: 20px;
        }

        .modal-label {
            color: #1a7a3e;
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }

        .modal-input {
            width: 100%;
            padding: 12px 15px;
            background: #2d3748;
            border: 1px solid #4a5568;
            border-radius: 8px;
            color: #ffffff;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .modal-input:focus {
            outline: none;
            border-color: #1a7a3e;
            box-shadow: 0 0 0 3px rgba(26, 122, 62, 0.1);
        }

        .modal-input::placeholder {
            color: #9ca3af;
        }

        .modal-footer {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .modal-btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            font-size: 14px;
        }

        .modal-btn-cancel {
            background: #6b7280;
            color: #ffffff;
        }

        .modal-btn-cancel:hover {
            background: #9ca3af;
        }

        .modal-btn-primary {
            background: #1a7a3e;
            color: #ffffff;
        }

        .modal-btn-primary:hover {
            background: #16a34a;
        }

        .modal-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .modal-btn:disabled:hover {
            background: #6b7280;
        }

        .modal-btn-primary:disabled:hover {
            background: #1a7a3e;
        }

        /* Loading spinner */
        .fa-spin {
            animation: fa-spin 1s infinite linear;
        }

        @keyframes fa-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Success message styles */
        .modal-success {
            color: #10b981;
            background-color: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            display: none;
        }

        .modal-error {
            color: #ef4444;
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            display: none;
        }

        @media (max-width: 576px) {
            .modal-content {
                width: 95%;
                padding: 20px;
                margin: 20px;
            }
            
            .modal-footer {
                flex-direction: column;
            }
            
            .modal-btn {
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    <div class="position-relative" style="z-index: 1">
        <div class="position-absolute top-0 start-50 translate-middle-x col-12">
            {{-- Muestra mensajes de éxito o advertencia según la sesión --}}
            @if (session('success'))
                <div role="alert" class="alert alert-success col-10 m-auto">
                    <div class="alert-body text-center">
                        <span>{{ session('success') }}!</span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            @elseif(session('warning'))
                <div role="alert" class="alert alert-warning col-10 m-auto">
                    <div class="alert-body text-center">
                        <span>{{ session('warning') }}!</span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="auth-wrapper auth-v1 px-2" style="background: #0b0f12ee">
        <div class="auth-inner py-2 back-gris" style="background: #35424a">
            <!-- Tarjeta de Inicio de Sesión -->
            <div class="card mb-0 back-gris">
                <div class="card-body">

                    <span class="brand-logo">
                        {{-- Logo de la aplicación --}}
                        <img src="{{ asset('images/logo/promolider_logo.png') }}" style="min-width: 130px;width:50%">
                    </span>

                    <h4 class="card-title mb-1 color-text-green">¡Bienvenido a Promolíder! 👋</h4>
                    <p class="card-text mb-2 color-text-white">Inicia sesión con tu cuenta y comienza la aventura</p>

                    <form class="auth-login-form mt-2" method="POST" action="{{ route('main-login') }}">
                        @csrf
                        {{-- Campo oculto para capturar la zona horaria del usuario --}}
                        <input type="hidden" name="user_timezone" id="user_timezone" />

                        <div class="form-group {{ $errors->has('username') ? 'has-error' : '' }}">
                            <label for="login-user" class="form-label color-text-green">Usuario</label>
                            {{-- Entrada de nombre de usuario con manejo de errores --}}
                            <input type="text" autocomplete="off"
                                class="form-control input-back @error('username') is-invalid @enderror" id="login-user"
                                name="username" aria-describedby="login-user" tabindex="1" autofocus
                                value="{{ old('username') }}" placeholder="Ingrese su nombre de usuario" />
                            @error('username')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group password-container {{ $errors->has('password') ? 'has-error' : '' }}">
                            <label for="login-password" class="form-label color-text-green">Contraseña</label>
                            {{-- Entrada de contraseña con opción de mostrar/ocultar --}}
                            <input type="password" class="form-control input-back @error('password') is-invalid @enderror"
                                id="login-password" name="password" tabindex="2" aria-describedby="login-password"
                                placeholder="Ingrese su contraseña" />
                            <span class="toggle-password" onclick="togglePasswordVisibility()">
                                <i class="fas fa-eye-slash" id="eye-icon"></i>
                            </span>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="d-flex justify-content-between">
                                <div class="custom-control custom-checkbox">
                                    {{-- Casilla de "Recuérdame" --}}
                                    <input class="custom-control-input input-back" type="checkbox" id="remember-me"
                                        name="remember-me" tabindex="3" {{ old('remember-me') ? 'checked' : '' }} />
                                    <label class="custom-control-label color-text-white" for="remember-me"> Recuérdame </label>
                                </div>
                                <div>
                                    {{-- Enlace de recuperar contraseña --}}
                                    <a href="#" onclick="openForgotPasswordModal()" class="color-text-green" style="text-decoration: none; font-size: 14px;">
                                        <i class="fas fa-key mr-1"></i>¿Olvidaste tu contraseña?
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="form-group {{ $errors->has('g-recaptcha-response') ? 'has-error' : '' }}">
                            {{-- Widget de Google reCAPTCHA --}}
                            <div class="g-recaptcha-container">
                                {!! htmlFormSnippet() !!}
                            </div>
                            @error('g-recaptcha-response')
                                <span class="invalid-feedback text-center" role="alert" style="display: block">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        {{-- Botón para enviar el formulario --}}
                        <button type="submit" class="btn back-green btn-block color-text-white" tabindex="4">Iniciar
                            sesión</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Recuperar Contraseña --}}
    <div id="forgotPasswordModal" class="v-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-key mr-2"></i>Recuperar Contraseña
                </h3>
                <button type="button" class="modal-close" onclick="closeForgotPasswordModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-body">
                <!-- Mensaje de éxito -->
                <div id="modal-success" class="modal-success">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>Se ha enviado un enlace de recuperación a tu correo electrónico.</span>
                </div>
                
                <!-- Mensaje de error -->
                <div id="modal-error" class="modal-error">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span id="error-message">Ha ocurrido un error. Inténtalo de nuevo.</span>
                </div>
                
                <p class="modal-text">
                    Ingresa tu dirección de correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
                </p>
                
                <form id="forgotPasswordForm">
                    <div class="modal-form-group">
                        <label for="forgot-email" class="modal-label">Correo electrónico</label>
                        <input 
                            type="email" 
                            id="forgot-email" 
                            class="modal-input" 
                            placeholder="Ingresa tu correo electrónico"
                            required
                        />
                    </div>
                </form>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeForgotPasswordModal()">
                    Cancelar
                </button>
                <button type="button" class="modal-btn modal-btn-primary" onclick="sendRecoveryEmail()">
                    <i class="fas fa-paper-plane mr-1"></i>Enviar enlace
                </button>
            </div>
            
            <!-- Enlace para ir a la página completa de reset -->
            <div style="text-align: center; margin-top: 15px; padding: 0 30px 20px;">
                <p style="color: #9ca3af; font-size: 12px; margin-bottom: 8px;">
                    ¿Tienes problemas con el modal?
                </p>
                <a href="{{ route('password.request') }}" style="color: #1a7a3e; text-decoration: none; font-size: 13px;">
                    <i class="fas fa-external-link-alt mr-1"></i>Usar página completa de recuperación
                </a>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        // Configura la zona horaria del usuario al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            var userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            document.getElementById('user_timezone').value = userTimezone;
        });

        // Alternar visibilidad de la contraseña
        function togglePasswordVisibility() {
            var passwordInput = document.getElementById('login-password');
            var eyeIcon = document.getElementById('eye-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            }
        }

        // Funciones del modal de recuperar contraseña optimizadas para 60 FPS
        function openForgotPasswordModal() {
            const modal = document.getElementById('forgotPasswordModal');
            
            // Forzar repaint para optimización
            modal.style.display = 'flex';
            modal.offsetHeight; // Trigger reflow
            
            document.body.style.overflow = 'hidden';
            
            // Usar requestAnimationFrame para sincronizar con refresh rate
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    modal.classList.add('v-dialog--active');
                });
            });
            
            // Focus optimizado después de la animación
            setTimeout(() => {
                const emailInput = document.getElementById('forgot-email');
                emailInput.focus();
                // Optimizar el input field
                emailInput.style.willChange = 'auto';
            }, 500);
        }

        function closeForgotPasswordModal() {
            const modal = document.getElementById('forgotPasswordModal');
            
            // Agregar clase de closing para animación de salida
            modal.classList.add('v-dialog--closing');
            modal.classList.remove('v-dialog--active');
            
            // Cleanup después de la animación con precisión de 60 FPS
            setTimeout(() => {
                modal.style.display = 'none';
                modal.classList.remove('v-dialog--closing');
                document.body.style.overflow = 'auto';
                
                // Limpiar formulario y mensajes
                const emailInput = document.getElementById('forgot-email');
                emailInput.value = '';
                emailInput.style.willChange = 'auto';
                
                // Ocultar mensajes
                document.getElementById('modal-success').style.display = 'none';
                document.getElementById('modal-error').style.display = 'none';
                
                // Restaurar botón
                const sendButton = document.querySelector('.modal-btn-primary');
                sendButton.disabled = false;
                sendButton.innerHTML = '<i class="fas fa-paper-plane mr-1"></i>Enviar enlace';
                
                // Forzar cleanup del GPU
                requestAnimationFrame(() => {
                    modal.style.willChange = 'auto';
                });
            }, 500);
        }

        // Optimización adicional para 60 FPS
        function optimizeForPerformance() {
            const modal = document.getElementById('forgotPasswordModal');
            
            // Pre-optimizar elementos para GPU
            modal.style.willChange = 'transform, opacity';
            
            // Detectar soporte de GPU
            const supportsGPU = CSS.supports('will-change', 'transform') && 
                               CSS.supports('transform', 'translateZ(0)');
            
            if (supportsGPU) {
                modal.style.transform = 'translateZ(0)';
            }
        }

        function sendRecoveryEmail() {
            const email = document.getElementById('forgot-email').value;
            const modalSuccess = document.getElementById('modal-success');
            const modalError = document.getElementById('modal-error');
            const errorMessage = document.getElementById('error-message');
            // Ocultar mensajes previos
            modalSuccess.style.display = 'none';
            modalError.style.display = 'none';

            if (!email) {
                // Push tipo warning (amarillo)
                if (window.Vue && window.Vue.prototype && window.Vue.prototype.$message) {
                    window.Vue.prototype.$message.warning('Debes ingresar tu correo electrónico.');
                } else if (window.toastr) {
                    toastr.warning('Debes ingresar tu correo electrónico.');
                } else {
                    errorMessage.innerText = 'Debes ingresar tu correo electrónico.';
                    modalError.style.display = 'block';
                }
                return;
            }
            if (!isValidEmail(email)) {
                // Push tipo warning (amarillo)
                if (window.Vue && window.Vue.prototype && window.Vue.prototype.$message) {
                    window.Vue.prototype.$message.warning('Por favor, ingresa un correo electrónico válido.');
                } else if (window.toastr) {
                    toastr.warning('Por favor, ingresa un correo electrónico válido.');
                } else {
                    errorMessage.innerText = 'Por favor, ingresa un correo electrónico válido.';
                    modalError.style.display = 'block';
                }
                return;
            }

            // Deshabilitar botón y mostrar loading
            const sendButton = document.querySelector('.modal-btn-primary');
            const originalText = sendButton.innerHTML;
            sendButton.disabled = true;
            sendButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Enviando...';

            // Enviar petición AJAX para no recargar la página
            fetch('{{ route('password.email') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ email })
            })
            .then(async response => {
                sendButton.disabled = false;
                sendButton.innerHTML = originalText;
                if (response.ok) {
                    // Push tipo success (verde)
                    if (window.Vue && window.Vue.prototype && window.Vue.prototype.$message) {
                        window.Vue.prototype.$message.success('¡Se ha enviado un correo para recuperar la contraseña!');
                    } else if (window.toastr) {
                        toastr.success('¡Se ha enviado un correo para recuperar la contraseña!');
                    } else {
                        modalSuccess.style.display = 'block';
                    }
                    // Limpiar input
                    document.getElementById('forgot-email').value = '';
                } else {
                    const data = await response.json().catch(() => ({}));
                    let msg = data.message || 'Ha ocurrido un error. Inténtalo de nuevo.';
                    // Manejo especial para passwords.throttled
                    if (data.errors && data.errors.email && data.errors.email.includes('passwords.throttled')) {
                        msg = 'Has realizado demasiados intentos. Por favor, espera unos minutos antes de volver a intentarlo.';
                    }
                    // Push tipo error (rojo)
                    if (window.Vue && window.Vue.prototype && window.Vue.prototype.$message) {
                        window.Vue.prototype.$message.error(msg);
                    } else if (window.toastr) {
                        toastr.error(msg);
                    } else {
                        errorMessage.innerText = msg;
                        modalError.style.display = 'block';
                    }
                }
            })
            .catch(error => {
                sendButton.disabled = false;
                sendButton.innerHTML = originalText;
                // Push tipo error (rojo)
                if (window.Vue && window.Vue.prototype && window.Vue.prototype.$message) {
                    window.Vue.prototype.$message.error('Error de red. Inténtalo de nuevo.');
                } else if (window.toastr) {
                    toastr.error('Error de red. Inténtalo de nuevo.');
                } else {
                    errorMessage.innerText = 'Error de red. Inténtalo de nuevo.';
                    modalError.style.display = 'block';
                }
            });
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Cerrar modal al hacer clic fuera del contenido
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('forgotPasswordModal');
            
            // Inicializar optimizaciones para 60 FPS
            optimizeForPerformance();
            
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeForgotPasswordModal();
                }
            });
            
            // Cerrar modal con tecla Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('v-dialog--active')) {
                    closeForgotPasswordModal();
                }
            });
            
            // Enviar formulario al presionar Enter
            document.getElementById('forgot-email').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    sendRecoveryEmail();
                }
            });
            
            // Optimizar rendimiento en dispositivos de gama baja
            if (navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4) {
                // Reducir complejidad de animaciones en dispositivos lentos
                document.documentElement.style.setProperty('--animation-duration', '0.3s');
            }
        });
    </script>
@endsection
