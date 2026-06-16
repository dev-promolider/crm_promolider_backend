@extends('layouts/registerLayout')
@section('title', 'User Membreship - Register')
@section('page-style')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .strength-item {
            margin-bottom: 5px;
        }

        .strength-item i {
            margin-right: 5px;
        }
    </style>
@endsection
@section('content')
    <input type="hidden" value="{{ $purchase_number }}" id="purchase_number">
    <input type="hidden" value="{{ $account_type }}" id="account_types">
    <input type="hidden" value="{{ $key_openpay }}" id="key_openpay">
    <input type="hidden" value="{{ $id_openpay }}" id="id_openpay">

    <div class="row py-5">
        <div class="col-6 col-md-2 d-none d-md-block">
        </div>
        <div class="col-12 col-md-8  mb-2 ">
            <div class="container flex-grow-1 flex-shrink-0">
                <div class="p-3 bg-white shadow-sm rounded-lg">
                    <div>
                        <h3 class="d-inline">Crear nueva cuenta? </h3>
                        <p class="d-inline float-right">
                            Referido por: <b> {{ $sponsor_name }} </b>
                        </p>
                    </div>
                    <script>
                        let tiempoRestanteEnSegundos = 0;
                        const countdownElement = document.getElementById('countdown');
                        let timerInterval;
                        let linkValidationInterval;
                        
                        // === VALIDACIÓN DEL LINK DE REGISTRO ===
                        async function validateLinkStatus() {
                            const currentUrl = window.location.href;
                            const urlParts = currentUrl.split('/');
                            const registerIndex = urlParts.findIndex(part => part === 'register');
                        
                            if (registerIndex === -1 || registerIndex + 3 >= urlParts.length) {
                                console.error('URL no tiene el formato esperado');
                                return;
                            }
                        
                            const userId = urlParts[registerIndex + 1];
                            const code = urlParts[registerIndex + 2];
                            const hash = urlParts[registerIndex + 3];
                        
                            console.log('Validando link - User ID:', userId, 'Code:', code, 'Hash:', hash);
                        
                            try {
                                const response = await fetch('/api/validate-registration-link', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    },
                                    body: JSON.stringify({
                                        user_id: userId,
                                        code: code,
                                        hash: hash
                                    })
                                });
                            
                                const data = await response.json();
                                console.log('Respuesta de validación:', data);
                            
                                if (!data.valid) {
                                    console.log('Link inválido:', data.message);
                                    showLinkSuspendedModal(data.message);
                                    clearInterval(linkValidationInterval);
                                    clearInterval(timerInterval);
                                } else {
                                    console.log('Link válido, tiempo restante:', data.tiempo_restante, 'segundos');
                                    
                                    // Actualizar el contador si difiere significativamente (más de 5 segundos)
                                    if (Math.abs(tiempoRestanteEnSegundos - data.tiempo_restante) > 5) {
                                        tiempoRestanteEnSegundos = data.tiempo_restante;
                                    }
                                
                                    // Si el tiempo restante es 0 o negativo, suspender
                                    if (data.tiempo_restante <= 0) {
                                        showLinkSuspendedModal('El tiempo del enlace ha expirado');
                                        clearInterval(linkValidationInterval);
                                        clearInterval(timerInterval);
                                    }
                                }
                            } catch (error) {
                                console.error('Error validando el link:', error);
                            }
                        }
                    
                        async function obtenerTiempoRestante() {
                            try {
                                const response = await fetch('/config/share-link/obtener-tiempo-restante');
                                const data = await response.json();
                            
                                console.log('Tiempo restante del servidor:', data);
                            
                                // Validar que el tiempo sea razonable (máximo 5 horas = 18000 segundos)
                                if (data.tiempoRestanteEnSegundos > 18000) {
                                    console.warn('Tiempo restante sospechosamente largo:', data.tiempoRestanteEnSegundos);
                                    tiempoRestanteEnSegundos = 18000;
                                } else {
                                    tiempoRestanteEnSegundos = Math.max(0, data.tiempoRestanteEnSegundos);
                                }
                            
                                // Si el tiempo es 0, el enlace ha expirado
                                if (tiempoRestanteEnSegundos === 0) {
                                    console.log('Enlace expirado - tiempo: 0');
                                    showLinkSuspendedModal('El tiempo del enlace ha expirado');
                                    clearInterval(linkValidationInterval);
                                    clearInterval(timerInterval);
                                }
                            } catch (error) {
                                console.error('Error al obtener el tiempo restante:', error);
                                tiempoRestanteEnSegundos = 0;
                            }
                        }
                    
                        function actualizarTiempoRestante() {
                            if (tiempoRestanteEnSegundos > 0) {
                                const horas = Math.floor(tiempoRestanteEnSegundos / 3600);
                                const minutos = Math.floor((tiempoRestanteEnSegundos % 3600) / 60);
                                const segundos = tiempoRestanteEnSegundos % 60;
                                
                                let display = '';
                                if (horas > 0) {
                                    display = `${horas}:${minutos.toString().padStart(2, '0')}:${segundos.toString().padStart(2, '0')}`;
                                } else {
                                    display = `${minutos}:${segundos.toString().padStart(2, '0')}`;
                                }
                                
                                countdownElement.textContent = display;
                                tiempoRestanteEnSegundos--;
                            
                                // Advertencia cuando quedan menos de 5 minutos
                                if (tiempoRestanteEnSegundos === 300) {
                                    Swal.fire({
                                        title: 'Atención',
                                        text: 'Quedan 5 minutos para que expire el enlace de registro',
                                        icon: 'warning',
                                        timer: 5000,
                                        showConfirmButton: false
                                    });
                                }
                            
                                // Advertencia cuando queda 1 minuto
                                if (tiempoRestanteEnSegundos === 60) {
                                    Swal.fire({
                                        title: '¡Último minuto!',
                                        text: 'El enlace está por expirar',
                                        icon: 'error',
                                        timer: 5000,
                                        showConfirmButton: false
                                    });
                                }
                            } else {
                                countdownElement.textContent = '00:00';
                                console.log('Contador llegó a 0 - suspendiendo enlace');
                                
                                if (timerInterval) {
                                    clearInterval(timerInterval);
                                }
                                if (linkValidationInterval) {
                                    clearInterval(linkValidationInterval);
                                }
                                
                                // Mostrar modal de expiración
                                showLinkSuspendedModal('El tiempo del enlace ha expirado');
                            }
                        }
                    
                        function showLinkSuspendedModal(message = 'Este enlace de registro ya no está disponible.') {
                            Swal.fire({
                                title: 'Enlace No Disponible',
                                text: message + ' Por favor, solicita un nuevo enlace al patrocinador.',
                                icon: 'warning',
                                confirmButtonText: 'Entendido',
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then(() => {
                                window.location.href = '/login';
                            });
                        
                            disableRegistrationForm();
                        }
                    
                        function disableRegistrationForm() {
                            const form = document.getElementById('stepperForm');
                            if (form) {
                                const inputs = form.querySelectorAll('input, select, button, textarea');
                                inputs.forEach(input => {
                                    input.disabled = true;
                                });
                            }
                        
                            // Agregar overlay
                            const overlay = document.createElement('div');
                            overlay.id = 'suspension-overlay';
                            overlay.style.cssText = `
                                position: fixed;
                                top: 0;
                                left: 0;
                                width: 100%;
                                height: 100%;
                                background: rgba(0,0,0,0.7);
                                z-index: 9999;
                                display: flex;
                                justify-content: center;
                                align-items: center;
                            `;
                            overlay.innerHTML = `
                                <div style="color: white; font-size: 20px; text-align: center;">
                                    <i class="fas fa-lock fa-3x mb-3"></i>
                                    <br>
                                    <strong>Enlace suspendido</strong>
                                    <br>
                                    <small>Redirigiendo al login...</small>
                                </div>
                            `;
                            
                            // Evitar agregar múltiples overlays
                            if (!document.getElementById('suspension-overlay')) {
                                document.body.appendChild(overlay);
                            }
                        }
                    
                        async function iniciarTemporizador() {
                            console.log('Iniciando temporizador...');
                            
                            // Primero obtener el tiempo del servidor
                            await obtenerTiempoRestante();
                            
                            // Luego validar el enlace
                            await validateLinkStatus();
                            
                            // Solo continuar si el enlace es válido
                            if (tiempoRestanteEnSegundos > 0) {
                                actualizarTiempoRestante();
                            
                                if (timerInterval) {
                                    clearInterval(timerInterval);
                                }
                                if (linkValidationInterval) {
                                    clearInterval(linkValidationInterval);
                                }
                            
                                // Actualizar contador cada segundo
                                timerInterval = setInterval(actualizarTiempoRestante, 1000);
                                
                                // Validar enlace cada 30 segundos
                                linkValidationInterval = setInterval(async () => {
                                    await validateLinkStatus();
                                }, 30000);
                            
                                console.log('Temporizador iniciado correctamente');
                            } else {
                                console.log('Enlace inválido o expirado - no se inicia el temporizador');
                            }
                        }
                    
                        // Iniciar cuando se carga la página
                        document.addEventListener('DOMContentLoaded', function() {
                            console.log('DOM cargado - iniciando validación');
                            iniciarTemporizador();
                        });
                    
                        // También validar si la página estaba en segundo plano
                        document.addEventListener('visibilitychange', function() {
                            if (!document.hidden) {
                                console.log('Página visible de nuevo - revalidando enlace');
                                validateLinkStatus();
                            }
                        });
                    </script>

                    <div id="stepperForm" class="bs-stepper linear">
                        <div class="bs-stepper-header" role="tablist">
                            <div class="step active" data-target="#test-form-1">
                                <button type="button" class="step-trigger" role="tab" id="stepperFormTrigger1"
                                    aria-controls="test-form-1" aria-selected="true">
                                    <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                                        <path fill="currentColor"
                                            d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z" />
                                    </svg>
                                    <span class="bs-stepper-label">Cuenta</span>
                                </button>
                            </div>
                            <div class="bs-stepper-line"></div>
                            <div class="step" data-target="#test-form-2">
                                <button type="button" class="step-trigger" role="tab" id="stepperFormTrigger2"
                                    aria-controls="test-form-2" aria-selected="false" disabled="disabled">
                                    <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                                        <path fill="currentColor"
                                            d="M8,9A2,2 0 0,1 10,11A2,2 0 0,1 8,13A2,2 0 0,1 6,11A2,2 0 0,1 8,9M12,17H4V16C4,14.67 6.67,14 8,14C9.33,14 12,14.67 12,16V17M20,8H14V10H20V8M20,12H14V14H20V12M20,16H14V18H20V16M22,4H14V6H22V20H2V6H10V4H2A2,2 0 0,0 0,6V20A2,2 0 0,0 2,22H22A2,2 0 0,0 24,20V6A2,2 0 0,0 22,4M13,6H11V2H13V6Z" />
                                    </svg>
                                    <span class="bs-stepper-label">Perfil</span>
                                </button>
                            </div>
                            <div class="bs-stepper-line"></div>
                            <div class="step" data-target="#test-form-3">
                                <button type="button" class="step-trigger" role="tab" id="stepperFormTrigger3"
                                    aria-controls="test-form-3" aria-selected="false" disabled="disabled">
                                    <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                                        <path fill="currentColor"
                                            d="M20,10H4V4H20M20,15H4V13H20M20,2H4C2.89,2 2,2.89 2,4V15C2,16.11 2.89,17 4,17H8V22L12,20L16,22V17H20C21.11,17 22,16.11 22,15V4C22,2.89 21.11,2 20,2Z" />
                                    </svg>
                                    <span class="bs-stepper-label">Membresia</span>
                                </button>
                            </div>
                        </div>
                        <div class="bs-stepper-content">
                            <div id="error-container" class="alert alert-danger" role="alert"></div>
                            <div>

                                <div id="test-form-1" role="tabpanel" class="bs-stepper-pane fade active dstepper-block"
                                    aria-labelledby="stepperFormTrigger1">
                                    <div class="row">
                                        <div class="form-group col-12 col-lg-6">
                                            <label for="username">{{ __('locale.' . 'User') }}</label>
                                            <input required type="text" id="username" minlength="3" maxlength="50"
                                                onkeypress="return checkCharactersSpecial(event)" class="form-control"
                                                name="username" value="">
                                            <div id="username_error" class="invalid-feedback"></div>
                                        </div>
                                        <div class="form-group col-12 col-lg-6">
                                            <label for="email">{{ __('locale.' . 'Email') }}</label>
                                            <input type="email" id="email" class="form-control" name="email"
                                                required value="">
                                            <div id="email_error" class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-12 col-lg-6">
                                            <label for="password">Contraseña</label>
                                            <div class="input-group input-group-merge form-password-toggle">
                                                <input required type="password" id="password" class="form-control"
                                                    name="password" value="">
                                                <span class="input-group-text cursor-pointer"
                                                    onclick="mostrarContra('password')">
                                                    <i class="fas fa-eye" id="password-icon"></i>
                                                </span>
                                                <div id="password_error" class="invalid-feedback"></div>
                                            </div>
                                            <div class="password-strength mt-2">
                                                <div class="strength-item" id="length-check">
                                                    <i class="fas fa-times text-danger"></i> Mínimo 8 caracteres
                                                </div>
                                                <div class="strength-item" id="number-check">
                                                    <i class="fas fa-times text-danger"></i> Al menos un digito
                                                </div>
                                                <div class="strength-item" id="special-check">
                                                    <i class="fas fa-times text-danger"></i> Al menos un carácter especial
                                                </div>
                                                <div class="strength-item" id="uppercase-check">
                                                    <i class="fas fa-times text-danger"></i> Al menos una mayúscula
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-12 col-lg-6">
                                            <label for="repassword">{{ __('locale.' . 'Re-Password') }}</label>
                                            <div class="input-group input-group-merge form-password-toggle">
                                                <input required type="password" id="repassword" class="form-control"
                                                    name="password_confirmation" value="">
                                                <span class="input-group-text cursor-pointer"
                                                    onclick="mostrarContra('repassword')">
                                                    <svg style="width:14px;height:14px" viewBox="0 0 24 24">
                                                        <path fill="currentColor" id="repassword-icon"
                                                            d="M12,9A3,3 0 0,1 15,12A3,3 0 0,1 12,15A3,3 0 0,1 9,12A3,3 0 0,1 12,9M12,4.5C17,4.5 21.27,7.61 23,12C21.27,16.39 17,19.5 12,19.5C7,19.5 2.73,16.39 1,12C2.73,7.61 7,4.5 12,4.5M3.18,12C4.83,15.36 8.24,17.5 12,17.5C15.76,17.5 19.17,15.36 20.82,12C19.17,8.64 15.76,6.5 12,6.5C8.24,6.5 4.83,8.64 3.18,12Z" />
                                                    </svg>
                                                </span>
                                                <div id="repassword_error" class="invalid-feedback"></div>
                                            </div>
                                            <div class="mt-2">
                                                <label for="user_type">Tipo de Usuario</label>
                                                <select id="user_type" class="form-control" name="user_type"
                                                    onchange="userTypesChanged({{ $account_type }})">
                                                    <option selected hidden>{{ __('locale.' . 'Select_an_option') }}
                                                    </option>
                                                    @foreach ($user_type as $ut)
                                                        <option value="{{ $ut->name }}">
                                                            {{ __('locale.' . $ut->name) }}</option>
                                                    @endforeach
                                                </select>
                                                <div id="user_type_error" class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <div class="btn btn-primary btn-next-form" onclick="next1();">Siguiente <svg
                                                style="width:24px;height:24px" viewBox="0 0 24 24">
                                                <path fill="currentColor"
                                                    d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    document.getElementById('password').addEventListener('input', validarPassword);

                                    function validarPassword() {
                                        const password = document.getElementById('password').value;
                                        const lengthCheck = document.getElementById('length-check');
                                        const numberCheck = document.getElementById('number-check');
                                        const specialCheck = document.getElementById('special-check');
                                        const uppercaseCheck = document.getElementById('uppercase-check');

                                        updateCheckmark(lengthCheck, password.length >= 8);
                                        updateCheckmark(numberCheck, /\d/.test(password));
                                        updateCheckmark(specialCheck, /[!@#$%^&*(),.?":{}|<>]/.test(password));
                                        updateCheckmark(uppercaseCheck, /[A-Z]/.test(password));
                                    }

                                    function updateCheckmark(element, isValid) {
                                        const icon = element.querySelector('i');
                                        if (isValid) {
                                            icon.classList.remove('fa-times', 'text-danger');
                                            icon.classList.add('fa-check', 'text-success');
                                        } else {
                                            icon.classList.remove('fa-check', 'text-success');
                                            icon.classList.add('fa-times', 'text-danger');
                                        }
                                    }

                                    function mostrarContra(inputId) {
                                        const input = document.getElementById(inputId);
                                        const icon = document.getElementById('password-icon');
                                        if (input.type === "password") {
                                            input.type = "text";
                                            icon.classList.remove('fa-eye');
                                            icon.classList.add('fa-eye-slash');
                                        } else {
                                            input.type = "password";
                                            icon.classList.remove('fa-eye-slash');
                                            icon.classList.add('fa-eye');
                                        }
                                    }
                                </script>

                                <div id="test-form-2" role="tabpanel" class="bs-stepper-pane fade dstepper-none"
                                    aria-labelledby="stepperFormTrigger2">
                                    <div class="row">
                                        <div class="form-group col-12 col-lg-6">
                                            <label for="name">{{ __('locale.' . 'Name') }}</label>
                                            <input required type="text" id="name" class="form-control"
                                                name="name" minlength="2" maxlength="50"
                                                onkeypress="return lettersOnly(event)" value="">
                                            <div id="name_error" class="invalid-feedback"></div>
                                        </div>
                                        <div class="form-group col-12 col-lg-6">
                                            <label for="last_name">{{ __('locale.' . 'Last_Name') }}</label>
                                            <input required type="text" id="last_name" class="form-control"
                                                name="last_name" minlength="3" maxlength="50"
                                                onkeypress="return lettersOnly(event)" value="">
                                            <div id="last_name_error" class="invalid-feedback"></div>
                                        </div>
                                        <div class="form-group col-12 col-lg-6">
                                            <label for="biography">{{ __('locale.' . 'Biography') }}</label>
                                            <textarea name="biography" id="biography" rows="3" class="form-control"></textarea>
                                            <div id="biography_error" class="invalid-feedback"></div>
                                        </div>
                                        <div class="form-group col-12 col-lg-6">
                                            <label for="phone">{{ __('locale.' . 'Phones') }}</label>
                                            <input type="tel" id="phone" oninput="numberOnly(this.id);"
                                                placeholder="Ingrese numero Nacional o internacional" required
                                                maxlength="15" class="form-control" name="phone" value="">
                                            <div id="phone_error" class="invalid-feedback"></div>
                                        </div>
                                        <div class="form-group col-12 col-lg-6">
                                            <label for="date_birth">{{ __('locale.' . 'Date_Birth') }}</label>
                                            <input type="date" id="date_birth" class="form-control" name="date_birth"
                                                min="1950-01-01" max="{{ date('Y-m-d') }}" value="">
                                            <div id="date_birth_error" class="invalid-feedback"></div>
                                        </div>
                                        <div class="form-group col-12 col-lg-6">
                                            <label for="id_document_type">{{ __('locale.' . 'Document_Type') }}</label>
                                            <select id="id_document_type" class="form-control" name="id_document_type">
                                                <option value="0" hidden selected>
                                                    {{ __('locale.' . 'Select_an_option') }}
                                                </option>
                                                @foreach ($document_type as $dt)
                                                    <option value="{{ $dt->id }}">{{ $dt->document }}</option>
                                                @endforeach
                                            </select>
                                            <div id="id_document_type_error" class="invalid-feedback"></div>
                                        </div>
                                        <div class="form-group col-12 col-lg-6">
                                            <label for="nro_document">{{ __('locale.' . 'Nro._Document') }}</label>
                                            <input type="text" id="nro_document" class="form-control"
                                                name="nro_document">
                                            <div id="nro_document_error" class="invalid-feedback"></div>
                                        </div>
                                        <script>
                                            document.addEventListener('DOMContentLoaded', function() {
                                                const documentTypeSelect = document.getElementById('id_document_type');
                                                const nroDocumentInput = document.getElementById('nro_document');
                                                const nroDocumentError = document.getElementById('nro_document_error');

                                                const validationRules = {
                                                    dni: {
                                                        pattern: /^\d{8}$/,
                                                        message: 'DNI debe tener 8 dígitos.'
                                                    },
                                                    passport: {
                                                        pattern: /^[A-Za-z0-9]{9,12}$/,
                                                        message: 'Pasaporte debe tener entre 9 y 12 caracteres alfanuméricos.'
                                                    },
                                                    carneExtranjeria: {
                                                        pattern: /^\d{9}$/,
                                                        message: 'Carné de Extranjería debe tener 9 dígitos.'
                                                    },
                                                    otros: {
                                                        pattern: /^.{5,15}$/,
                                                        message: 'Documento debe tener entre 5 y 15 caracteres.'
                                                    }
                                                };

                                                function validateDocument() {
                                                    const selectedType = documentTypeSelect.value;
                                                    const documentNumber = nroDocumentInput.value.trim();
                                                    let rule;

                                                    switch (selectedType) {
                                                        case '1': // DNI
                                                            rule = validationRules.dni;
                                                            break;
                                                        case '2': // Pasaporte
                                                            rule = validationRules.passport;
                                                            break;
                                                        case '3': // Carné de Extranjería
                                                            rule = validationRules.carneExtranjeria;
                                                            break;
                                                        default:
                                                            rule = validationRules.otros;
                                                    }

                                                    if (!rule.pattern.test(documentNumber)) {
                                                        nroDocumentInput.classList.add('is-invalid');
                                                        nroDocumentError.textContent = rule.message;
                                                        return false;
                                                    } else {
                                                        nroDocumentInput.classList.remove('is-invalid');
                                                        nroDocumentError.textContent = '';
                                                        return true;
                                                    }
                                                }

                                                let validateTimeout;

                                                nroDocumentInput.addEventListener('input', function() {
                                                    clearTimeout(validateTimeout);
                                                    validateTimeout = setTimeout(validateDocument,
                                                        1000);
                                                });

                                                nroDocumentInput.addEventListener('blur', validateDocument); // Validar cuando el campo pierde el foco

                                                documentTypeSelect.addEventListener('change', function() {
                                                    nroDocumentInput.classList.remove('is-invalid');
                                                    nroDocumentError.textContent = '';
                                                });
                                            });
                                        </script>

                                        <div class="form-group col-12 col-lg-6">
                                            <label for="country">{{ __('locale.' . 'Country') }}</label>
                                            <select id="country" class="form-control" name="country"
                                                onchange="countrySelected({{ $payment_methods }})">
                                                <option hidden value="0">--------------------</option>
                                                @foreach ($country as $c)
                                                    <option value="{{ $c->id }}"
                                                        {{ $c->name == 'Perú' ? 'selected' : '' }}>
                                                        {{ $c->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div id="country_error" class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <div class="btn btn-next-form" onclick="previus();">
                                            <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                                                <path fill="currentColor"
                                                    d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z" />
                                            </svg> Atras
                                        </div>

                                        <div class="btn btn-primary btn-next-form" onclick="next2();">Siguiente
                                            <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                                                <path fill="currentColor"
                                                    d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div id="test-form-3" role="tabpanel" class="bs-stepper-pane fade dstepper-none"
                                    aria-labelledby="stepperFormTrigger3">
                                    <div class="row">
                                        <div class="form-group col-12 col-lg-6">
                                            <label for="id_account_type">{{ __('locale.' . 'Account_Type') }}</label>
                                            <select id="id_account_type" class="form-control" name="id_account_type"
                                                onchange="accountTypesChanged({{ $account_type }})">
                                                <option hidden selected value="0">
                                                    {{ __('locale.' . 'Select_an_option') }}
                                                </option>
                                            </select>
                                            <div id="id_account_type_error" class="invalid-feedback"></div>
                                        </div>
                                        <div class="form-group col-12 col-lg-6">
                                            <label for="account_type-price">{{ __('locale.' . 'Price') }}</label>
                                            <input type="text" id="account_type-price" class="form-control" disabled>
                                        </div>
                                        <div class="form-group col-12 col-lg-6">
                                            <label for="account_type-iva" data-toggle="tooltip" data-placement="top"
                                                title="Impuesto">{{ __('locale.' . 'IVA') }}</label>
                                            <input type="text" id="account_type-iva" class="form-control" disabled>
                                        </div>
                                        <div class="form-group col-12 col-lg-6">
                                            <label
                                                for="account_type-total_cost_membreship">{{ __('locale.' . 'Total_cost_of_Membreship') }}</label>
                                            <input type="text" id="account_type-total_cost_membreship"
                                                class="form-control" name="amount" readonly>
                                            <input type="hidden" name="reserved13">
                                        </div>

                                        <div id="payment_method_selector" class="form-group col-12 col-lg-6">
                                            <label for="payment_method_id">Método de Pago</label>
                                            <select id="payment_method_id" name="payment_method_id" class="form-control"
                                                onchange="paymentMethodSelected()">
                                                <option hidden selected value="0">
                                                    {{ __('locale.' . 'Select_an_option') }}
                                                </option>
                                                @foreach ($payment_methods as $c)
                                                    <option value="{{ $c->id }}">
                                                        {{ $c->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div id="payment_method_id_error" class="invalid-feedback"></div>
                                        </div>

                                        <div class="form-group col-12 col-lg-6" id="input_operation">
                                            <label for="nro_operation">{{ __('locale.' . 'Nro._operation') }}</label>
                                            <input type="text" id="operation_number" oninput="numberOnly(this.id);"
                                                class="form-control" name="operation_number">
                                            <div id="operation_number_error" class="invalid-feedback"></div>
                                        </div>

                                        <div class="form-group col-12" id="info_openpay" style="word-break: normal">
                                            Al hacer click en "Pagar", usted está aceptando nuestros <button type="button"
                                                class="btn btn-link" data-toggle="modal"
                                                data-target="#exampleModal">términos y condiciones..</button>
                                        </div>

                                        <div class="form-group col-12" id="info_operation">
                                            <div class="card mb-1 border-0 shadow-sm">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ asset('images/avatars/logo_binance.jpg') }}"
                                                            alt="Logo Binance" class="mr-3"
                                                            style="height: 40px; width: auto;">
                                                        <div>
                                                            <small class="text-muted d-block">Cuenta Binance</small>
                                                            <strong class="text-primary">Promolider org</strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-1">
                                                    <div class="card h-100 border-0 shadow-sm hover-shadow">
                                                        <div class="card-body">
                                                            <h6 class="text-muted mb-1">Correo para transferencia</h6>
                                                            <div
                                                                class="d-flex justify-content-between align-items-center bg-light rounded p-1">
                                                                <div class="email-container"
                                                                    style="max-width: 220px; overflow: hidden; text-overflow: ellipsis;">
                                                                    <span id="binanceEmail"
                                                                        class="font-weight-medium">promoliderp@gmail.com</span>
                                                                </div>
                                                                <button id="copyButton" onclick="copyEmail()"
                                                                    class="btn btn-success btn-sm">
                                                                    <i class="fas fa-copy mr-1"></i> Copiar
                                                                </button>
                                                            </div>

                                                            <div class="mt-1">
                                                                <p class="font-weight-bold mb-1">Escanea el código QR:</p>
                                                                <div class="text-center">
                                                                    <img src="{{ asset('images/avatars/qr.jpg') }}"
                                                                        alt="QR para depósito" class="img-fluid qr-hover"
                                                                        style="width: 180px; cursor: pointer;"
                                                                        onclick="openModal()">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 mb-1">
                                                    <div class="card h-100 border-0 shadow-sm hover-shadow">
                                                        <div class="card-body">
                                                            <h6 class="text-muted mb-1">Comprobante de pago</h6>
                                                            <div class="custom-file mb-1">
                                                                <input type="file" class="custom-file-input"
                                                                    id="receiptImage" name="receipt"
                                                                    accept=".png,.jpg,.jpeg,.webp">
                                                                <label class="custom-file-label"
                                                                    for="receiptImage">Seleccionar archivo</label>
                                                            </div>
                                                            <div id="imagePreview" class="text-center mt-1"
                                                                style="display: none;">
                                                                <img src="#" alt="Vista previa del comprobante"
                                                                    class="img-fluid rounded preview-image">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <style>
                                            .hover-shadow:hover {
                                                box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
                                            }

                                            .qr-hover {
                                                transition: transform 0.3s ease-in-out;
                                            }

                                            .qr-hover:hover {
                                                transform: scale(1.05);
                                            }

                                            .email-container {
                                                background-color: #f8f9fa;
                                                border: 1px solid #dee2e6;
                                                border-radius: 4px;
                                                white-space: nowrap;
                                            }

                                            #copyButton {
                                                min-width: 85px;
                                            }

                                            #copyButton.copied {
                                                background-color: #28a745;
                                                border-color: #28a745;
                                                transform: scale(1.05);
                                            }

                                            .preview-image {
                                                max-width: 200px;
                                                max-height: 200px;
                                                object-fit: contain;
                                                border-radius: 8px;
                                                box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
                                            }

                                            .preview-image:hover {
                                                transform: scale(1.05);
                                            }

                                            @media (max-width: 768px) {
                                                .card {
                                                    margin-bottom: 1rem;
                                                }

                                                .email-container {
                                                    font-size: 0.9rem;
                                                }

                                                #copyButton {
                                                    min-width: 70px;
                                                    font-size: 0.8rem;
                                                }
                                            }
                                        </style>

                                        <div id="qrModal" class="modal"
                                            style="display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
                                            <div class="modal-content"
                                                style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px;">
                                                <span class="close" onclick="closeModal()"
                                                    style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
                                                <img src="{{ asset('images/avatars/qr.jpg') }}" alt="QR para depósito"
                                                    class="img-fluid" style="width: 100%;">
                                            </div>
                                        </div>

                                        <style>
                                            .email-container {
                                                background-color: #f8f9fa;
                                                border: 1px solid #dee2e6;
                                                border-radius: 4px;
                                                white-space: nowrap;
                                            }

                                            #copyButton {
                                                transition: all 0.3s ease;
                                            }

                                            #copyButton.copied {
                                                background-color: #28a745;
                                                border-color: #28a745;
                                                transform: scale(1.05);
                                            }

                                            .card {
                                                border: 1px solid rgba(0, 0, 0, .125);
                                                box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
                                            }

                                            @media (max-width: 576px) {
                                                #copyButton {
                                                    min-width: 60px;
                                                    font-size: 0.875rem;
                                                }

                                                .email-container {
                                                    font-size: 0.875rem !important;
                                                }
                                            }

                                            .payment-receipt-card {
                                                transition: all 0.3s ease;
                                                border: 1px solid rgba(0, 0, 0, .125);
                                                box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
                                            }

                                            .payment-receipt-card:hover {
                                                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                                            }

                                            .payment-title {
                                                color: #000000;
                                                text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
                                                font-size: 1.5rem;
                                                position: relative;
                                                display: inline-block;
                                            }


                                            .custom-file-label::after {
                                                content: "Buscar";
                                                border-radius: 0 5px 5px 0;
                                                background-color: #20e404 !important;
                                                border-color: #20e404;
                                                color: white;
                                            }

                                            .custom-file-label::after:hover {
                                                background-color: #20e404;
                                                border-color: #20e404;
                                            }

                                            .custom-file-input:hover+.custom-file-label {
                                                border-color: #20e404;
                                            }

                                            .custom-file-label {
                                                overflow: hidden;
                                                text-overflow: ellipsis;
                                                white-space: nowrap;
                                                padding-right: 90px;
                                            }


                                            .preview-image {
                                                transition: all 0.3s ease;
                                                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                            }

                                            .preview-image:hover {
                                                transform: scale(1.05);
                                            }

                                            #imagePreview {
                                                transition: opacity 0.3s ease;
                                            }
                                        </style>

                                        <script>
                                            function openModal() {
                                                document.getElementById('qrModal').style.display = "block";
                                            }

                                            function closeModal() {
                                                document.getElementById('qrModal').style.display = "none";
                                            }

                                            function copyEmail() {
                                                var emailText = document.getElementById('binanceEmail').innerText;
                                                var copyButton = document.getElementById('copyButton');

                                                navigator.clipboard.writeText(emailText).then(function() {
                                                    copyButton.innerHTML = 'Copiado';
                                                    copyButton.classList.add('copied');

                                                    setTimeout(function() {
                                                        copyButton.innerHTML = 'Copiar';
                                                        copyButton.classList.remove('copied');
                                                    }, 2000);
                                                }, function(err) {
                                                    console.error('Error al copiar el email: ', err);
                                                });
                                            }

                                            // Cerrar el modal si se hace clic fuera de él
                                            window.onclick = function(event) {
                                                var modal = document.getElementById('qrModal');
                                                if (event.target == modal) {
                                                    modal.style.display = "none";
                                                }
                                            }

                                            // Funcionalidad para previsualizar la imagen
                                            document.getElementById('receiptImage').addEventListener('change', function(e) {
                                                var file = e.target.files[0];
                                                var reader = new FileReader();
                                                reader.onload = function(e) {
                                                    var preview = document.getElementById('imagePreview');
                                                    preview.style.opacity = '0';
                                                    preview.style.display = 'block';
                                                    preview.querySelector('img').src = e.target.result;
                                                    setTimeout(() => {
                                                        preview.style.opacity = '1';
                                                    }, 50);
                                                }
                                                reader.readAsDataURL(file);

                                                // Actualizar el texto del label con el nombre del archivo
                                                var fileName = file.name;
                                                var label = e.target.nextElementSibling;
                                                label.innerHTML = fileName;
                                            });
                                        </script>


                                        <div class="form-group col-12 col-md-6" id="info_operation2">
                                            <p style="word-break: normal" class="text-success">
                                                Podrá ingresar a la plataforma una vez que uno de nuestros colaboradores
                                                haya verificado el número de operación.
                                            </p>
                                        </div>
                                        <div class="d-flex justify-content-end offset-md-6">
                                            <div class="btn btn-next-form" onclick="previus();">
                                                <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                                                    <path fill="currentColor"
                                                        d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z" />
                                                </svg> Atras
                                            </div>

                                            <div id="btnPay" class="btn btn-primary btn-next-form mr-1"
                                                onclick="register('{{ url('/unverified-user/create') }}','{{ $id_referrer_sponsor }}')">
                                                Pagar <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                                                    <path fill="currentColor"
                                                        d="M20,8H4V6H20M20,18H4V12H20M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z" />
                                                </svg>
                                            </div>
                                            <div id="btnFree" class="btn btn-primary btn-next-form mr-1"
                                                onclick="register('{{ url('/users/create-free') }}','{{ $id_referrer_sponsor }}')">
                                                Registrarme gratis <svg style="width:24px;height:24px"
                                                    viewBox="0 0 24 24">
                                                    <path fill="currentColor"
                                                        d="M20,8H4V6H20M20,18H4V12H20M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z" />
                                                </svg>
                                            </div>
                                            <div id="btnUnverified" class="btn btn-primary btn-next-form mr-1"
                                                onclick="register('{{ url('users/create-unverified-user') }}','{{ $id_referrer_sponsor }}')">
                                                Registrar <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                                                    <path fill="currentColor"
                                                        d="M20,8H4V6H20M20,18H4V12H20M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z" />
                                                </svg>
                                            </div>

                                            <div id="niubiz_container">
                                                @csrf
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-2 d-none d-md-block"></div>
        </div>
    </div>
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Términos y condiciones</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @include('modalOpenpayConditions')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        body {
            background-image: url('https://img.freepik.com/foto-gratis/fondo-textura-ruido-digital-efecto-falla-negra_53876-103977.jpg?t=st=1655316480~exp=1655317080~hmac=34afa60ad6e9d0227fd7f478c0c6a1201745ce8a9a3dc6dcc9c76f5844588a39&w=900');
            background-size: cover;
            background-repeat: repeat-y;
        }

        .row {
            margin-right: 0 !important;
            margin-left: 0 !important;
        }
    </style>

@endsection
@section('page-script')
    <!-- <script src="{{ asset('js/api/users-register.js') }}"></script> -->
    <script>
        // === INICIO: Validación del link de registro ===
        let linkValidationInterval;
        
        async function validateLinkStatus() {
            const currentUrl = window.location.href;
            console.log('URL actual:', currentUrl);
        
            // Extraer ID, código y hash de la URL (formato: /register/{id}/{code}/{hash})
            const urlParts = currentUrl.split('/');
            const registerIndex = urlParts.findIndex(part => part === 'register');
        
            if (registerIndex === -1 || registerIndex + 3 >= urlParts.length) {
                console.error('URL no tiene el formato esperado');
                return;
            }
        
            const userId = urlParts[registerIndex + 1];
            const code = urlParts[registerIndex + 2];
            const hash = urlParts[registerIndex + 3]; // ← Agregar esto
        
            console.log('Validando link - User ID:', userId, 'Code:', code, 'Hash:', hash);
        
            try {
                const response = await fetch('/api/validate-registration-link', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        code: code,
                        hash: hash // ← Agregar esto
                    })
                });
            
                const data = await response.json();
                console.log('Respuesta de validación:', data);
            
                if (!data.valid) {
                    console.log('Link inválido:', data.message);
                    showLinkSuspendedModal(data.message);
                    clearInterval(linkValidationInterval);
                } else {
                    console.log('Link válido, tiempo restante:', data.tiempo_restante, 'segundos');
                }
            } catch (error) {
                console.error('Error validando el link:', error);
            }
        }

        // Función para mostrar modal de link suspendido
        function showLinkSuspendedModal(message = 'Este enlace de registro ya no está disponible.') {
            Swal.fire({
                title: 'Enlace No Disponible',
                text: message + ' Por favor, solicita un nuevo enlace al patrocinador.',
                icon: 'warning',
                confirmButtonText: 'Entendido',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(() => {
                // Redirigir a página principal o login
                window.location.href = '/login';
            });

            // Deshabilitar todos los formularios
            disableRegistrationForm();
        }

        // Función para deshabilitar el formulario
        function disableRegistrationForm() {
            const form = document.getElementById('stepperForm');
            const inputs = form.querySelectorAll('input, select, button, textarea');
            inputs.forEach(input => {
                input.disabled = true;
            });

            // Agregar overlay para prevenir interacciones
            const overlay = document.createElement('div');
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 9999;
                display: flex;
                justify-content: center;
                align-items: center;
            `;
            overlay.innerHTML = '<div style="color: white; font-size: 18px;">Enlace suspendido...</div>';
            document.body.appendChild(overlay);
        }

        // Iniciar validación periódica cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            // Validar inmediatamente al cargar
            validateLinkStatus();

            // Validar cada 10 segundos
            linkValidationInterval = setInterval(validateLinkStatus, 10000);
        });

        // === FIN: Validación del link de registro ===
    </script>
    <script>
        const username = document.getElementById('username');
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const repassword = document.getElementById('repassword');
        const user_type = document.getElementById('user_type');
        const firstName = document.getElementById('name');
        const lastName = document.getElementById('last_name');
        const biography = document.getElementById('biography');
        const phone = document.getElementById('phone');
        const date_birth = document.getElementById('date_birth');
        const nro_document = document.getElementById('nro_document');
        const country = document.getElementById('country');
        const operation_number = document.getElementById('operation_number');
        const accountTypes = document.getElementById('id_account_type');
        const accountTypesPrice = document.getElementById('account_type-price');
        const accountTypesIva = document.getElementById('account_type-iva');
        const accountTypesTotalCostMembreship = document.getElementById('account_type-total_cost_membreship');
        const documentTypes = document.getElementById('id_document_type');
        const payment_method = document.getElementById('payment_method_id');
        const purchase_number = document.getElementById('purchase_number');
        const account_types = document.getElementById('account_types');
        const key_openpay = document.getElementById('key_openpay');
        const id_openpay = document.getElementById('id_openpay');
        let stepper = new Stepper(document.querySelector('.bs-stepper'));
        var optionRegisterSelected = 0; // 0 -> registro pagado, 1 -> registro gratis
        var charge_id = null;
        var total_amount = null;
        document.getElementById('btnFree').style.display = "none";
        document.getElementById('input_operation').style.display = "none";
        document.getElementById('info_operation').style.display = "none";
        document.getElementById('info_operation2').style.display = "none";
        document.getElementById('btnUnverified').style.display = "none";
        document.getElementById('checker').style.display = "none";
        document.getElementById('alert').style.display = "none";

        function accountTypesChanged(list_account_type) {
            const option_actual = accountTypes.value

            list_account_type.forEach(element => {
                if (element.id == option_actual) {
                    // Cambiar la condición para verificar el precio
                    if (parseFloat(element.price) === 0) {
                        optionRegisterSelected = 1;
                        document.getElementById('btnPay').style.display = "none";
                        document.getElementById('btnFree').style.display = "block"
                        document.getElementById('payment_method_selector').style.visibility = "hidden";
                    } else {
                        document.getElementById('btnFree').style.display = "none";
                        document.getElementById('btnPay').style.display = "block";
                        document.getElementById('payment_method_selector').style.visibility = "visible";
                    }

                    if (option_actual === '5') {
                        payment_method.value = "0"
                    }
                    accountTypesPrice.value = '$ ' + Number.parseFloat(element.price).toFixed(2);
                    accountTypesIva.value = element.iva + '%';
                    let amount = Number.parseFloat(element.total).toFixed(2);
                    accountTypesTotalCostMembreship.value = '$ ' + amount;
                    total_amount = amount;
                }
            });
        }

        //If Perú is selected, show transfer method pay
        function countrySelected(payment_methods) {
            limpiarSelect(payment_method);
            payment_methods.forEach(element => {
                var option = document.createElement("option");
                option.innerHTML = element.name;
                option.value = element.id;
                payment_method.appendChild(option);
            });
        }

        function paymentMethodSelected() {
            if (payment_method.value == "3") {
                document.getElementById('input_operation').style.display = "block";
                document.getElementById('info_operation').style.display = "block";
                document.getElementById('info_operation2').style.display = "block";
                document.getElementById('btnFree').style.display = "none";
                document.getElementById('btnPay').style.display = "none";
                document.getElementById('btnUnverified').style.display = "block";
                document.getElementById('info_openpay').style.display = "none";
            }
            if (payment_method.value == "1") {
                document.getElementById('input_operation').style.display = "none";
                document.getElementById('info_operation').style.display = "none";
                document.getElementById('info_operation2').style.display = "none";
                document.getElementById('btnFree').style.display = "none";
                document.getElementById('btnPay').style.display = "block";
                document.getElementById('btnUnverified').style.display = "none";
                document.getElementById('info_openpay').style.display = "none";
            }
        }

        //show account type after select user type
        function userTypesChanged(list_account_type) {
            limpiarSelect(accountTypes);
            const option_actual = user_type.value
            indice_basic = list_account_type.findIndex(array => array.account == 'Basic')
            indice_guest = list_account_type.findIndex(array => array.account == 'Guest')
            if (option_actual == 'Producer') {
                if (indice_guest != -1) {
                    list_account_type.splice(indice_guest, 1)
                }

            } else if (option_actual == 'Distributor') {
                if (indice_basic != -1) {
                    list_account_type.splice(indice_basic, 1)
                }

            }
            list_account_type.forEach(element => {
                var option = document.createElement("option");
                option.innerHTML = element.account
                option.value = element.id
                accountTypes.appendChild(option)
            });

            accountTypesChanged(list_account_type)
        };

        function limpiarSelect(object) {
            for (let i = object.options.length; i >= 0; i--) {
                object.options[i] = null;
            }
        }

        // Function Validation
        function validDocumentType(list_document_type) {
            nro_document.value = ""
            const option_actual = documentTypes.options[documentTypes.selectedIndex].text;
            nro_document.setAttribute('class', 'form-control is-invalid')
            list_document_type.forEach(element => {
                if (option_actual == "DNI") {
                    nro_document.setAttribute("maxlength", "18")
                    nro_document.setAttribute("minlength", "1")
                } else {
                    nro_document.setAttribute("maxlength", "18")
                    nro_document.setAttribute("minlength", "1")
                }
            })
        }

        function validDocumentTypeStyles(id) {
            let element = document.getElementById(id);
            const option_actual = documentTypes.options[documentTypes.selectedIndex].text;
            tamaño = element.value;
            if (option_actual == "DNI" && tamaño.length < 8) {
                element.setAttribute('class', 'form-control is-invalid');
            } else {
                if (option_actual != "DNI" && tamaño.length < 12) {
                    element.setAttribute('class', 'form-control is-invalid');
                } else {
                    element.setAttribute('class', 'form-control');
                }
            }
        }

        function numberOnly(id) {
            let element = document.getElementById(id);
            element.value = element.value.replace(/[^0-9]/gi, "");

            if (id == "nro_document") {
                validDocumentTypeStyles('nro_document')
            }
        }

        function lettersOnly(e) {
            tecla = (document.all) ? e.keyCode : e.which;

            if (tecla == 8 || tecla == 32) {
                return true;
            }

            patron = /[A-Za-z]/;
            tecla_final = String.fromCharCode(tecla);
            return patron.test(tecla_final);
        }

        function checkCharactersSpecial(e) {
            tecla = (document.all) ? e.keyCode : e.which;

            if (tecla == 8) {
                return true;
            }

            patron = /[A-Za-z0-9]/;
            tecla_final = String.fromCharCode(tecla);
            return patron.test(tecla_final);
        }

        function validDateBirth() {
            const date_now = new Date();
            let year_now = date_now.getFullYear();
            let minLegalAge = year_now - 18;
            date_birth.setAttribute('max', "" + minLegalAge + "-01-01")
        }

        function mostrarContra(id_input) {
            const eyes_close =
                'M2,5.27L3.28,4L20,20.72L18.73,22L15.65,18.92C14.5,19.3 13.28,19.5 12,19.5C7,19.5 2.73,16.39 1,12C1.69,10.24 2.79,8.69 4.19,7.46L2,5.27M12,9A3,3 0 0,1 15,12C15,12.35 14.94,12.69 14.83,13L11,9.17C11.31,9.06 11.65,9 12,9M12,4.5C17,4.5 21.27,7.61 23,12C22.18,14.08 20.79,15.88 19,17.19L17.58,15.76C18.94,14.82 20.06,13.54 20.82,12C19.17,8.64 15.76,6.5 12,6.5C10.91,6.5 9.84,6.68 8.84,7L7.3,5.47C8.74,4.85 10.33,4.5 12,4.5M3.18,12C4.83,15.36 8.24,17.5 12,17.5C12.69,17.5 13.37,17.43 14,17.29L11.72,15C10.29,14.85 9.15,13.71 9,12.28L5.6,8.87C4.61,9.72 3.78,10.78 3.18,12Z'
            const eyes_open =
                'M12,9A3,3 0 0,1 15,12A3,3 0 0,1 12,15A3,3 0 0,1 9,12A3,3 0 0,1 12,9M12,4.5C17,4.5 21.27,7.61 23,12C21.27,16.39 17,19.5 12,19.5C7,19.5 2.73,16.39 1,12C2.73,7.61 7,4.5 12,4.5M3.18,12C4.83,15.36 8.24,17.5 12,17.5C15.76,17.5 19.17,15.36 20.82,12C19.17,8.64 15.76,6.5 12,6.5C8.24,6.5 4.83,8.64 3.18,12Z'
            let lblPassword = document.getElementById(id_input);
            let icon = document.getElementById(id_input + "-icon");

            if (lblPassword.type == 'password') {
                lblPassword.type = 'text';
                icon.setAttribute('d', eyes_close)
            } else {
                lblPassword.type = 'password';
                icon.setAttribute('d', eyes_open)
            }
        }

        //
        function printError(listaErrores) {
            clearError();
            if (listaErrores.username) {
                username.setAttribute('class', 'form-control is-invalid');
                document.getElementById('username_error').innerHTML = listaErrores.username;
            }
            if (listaErrores.email) {
                email.setAttribute('class', 'form-control is-invalid');
                document.getElementById('email_error').innerHTML = listaErrores.email;
            }
            if (listaErrores.password) {
                password.setAttribute('class', 'form-control is-invalid');
                document.getElementById('password_error').innerHTML = listaErrores.password;
            }
            if (listaErrores.user_type) {
                user_type.setAttribute('class', 'form-control is-invalid');
                document.getElementById('user_type_error').innerHTML = listaErrores.user_type;
            }
            if (listaErrores.id_account_type) {
                accountTypes.setAttribute('class', 'form-control is-invalid');
                document.getElementById('id_account_type_error').innerHTML = listaErrores.id_account_type;
            }
            if (listaErrores.id_document_type) {
                documentTypes.setAttribute('class', 'form-control is-invalid');
                document.getElementById('id_document_type_error').innerHTML = listaErrores.id_document_type;
            }
            if (listaErrores.name) {
                firstName.setAttribute('class', 'form-control is-invalid');
                document.getElementById('name_error').innerHTML = listaErrores.name;
            }
            if (listaErrores.last_name) {
                lastName.setAttribute('class', 'form-control is-invalid');
                document.getElementById('last_name_error').innerHTML = listaErrores.last_name;
            }
            if (listaErrores.biography) {
                biography.setAttribute('class', 'form-control is-invalid');
                document.getElementById('biography_error').innerHTML = listaErrores.biography;
            }
            if (listaErrores.phone) {
                phone.setAttribute('class', 'form-control is-invalid');
                document.getElementById('phone_error').innerHTML = listaErrores.phone;
            }
            if (listaErrores.date_birth) {
                date_birth.setAttribute('class', 'form-control is-invalid');
                document.getElementById('date_birth_error').innerHTML = listaErrores.date_birth;
            }
            if (listaErrores.nro_document) {
                nro_document.setAttribute('class', 'form-control is-invalid');
                document.getElementById('nro_document_error').innerHTML = listaErrores.nro_document;
            }

            if (listaErrores.payment_method) {
                payment_method.setAttribute('class', 'form-control is-invalid');
                document.getElementById('payment_method_id_error').innerHTML = listaErrores.payment_method;
            }

            if (listaErrores.operation_number) {
                operation_number.setAttribute('class', 'form-control is-invalid');
                document.getElementById('operation_number_error').innerHTML = listaErrores.operation_number;
            }
        }

        function clearError() {
            username.setAttribute('class', 'form-control');
            document.getElementById('username_error').innerHTML = "";
            email.setAttribute('class', 'form-control');
            document.getElementById('email_error').innerHTML = "";
            password.setAttribute('class', 'form-control');
            document.getElementById('password_error').innerHTML = "";
            user_type.setAttribute('class', 'form-control');
            document.getElementById('user_type_error').innerHTML = "";
            accountTypes.setAttribute('class', 'form-control');
            document.getElementById('id_account_type_error').innerHTML = "";
            documentTypes.setAttribute('class', 'form-control');
            document.getElementById('id_document_type_error').innerHTML = "";
            firstName.setAttribute('class', 'form-control');
            document.getElementById('name_error').innerHTML = "";
            lastName.setAttribute('class', 'form-control');
            document.getElementById('last_name_error').innerHTML = "";
            biography.setAttribute('class', 'form-control');
            document.getElementById('biography_error').innerHTML = "";
            phone.setAttribute('class', 'form-control');
            document.getElementById('phone_error').innerHTML = "";
            date_birth.setAttribute('class', 'form-control');
            document.getElementById('date_birth_error').innerHTML = "";
            nro_document.setAttribute('class', 'form-control');
            document.getElementById('nro_document_error').innerHTML = "";
            operation_number.setAttribute('class', 'form-control');
            document.getElementById('operation_number_error').innerHTML = "";
        }

        function viewError(listaErrores) {
            if (listaErrores.username) {
                stepper.to(1)
            } else if (listaErrores.email) {
                stepper.to(1)
            } else if (listaErrores.password) {
                stepper.to(1)
            } else if (listaErrores.user_type) {
                stepper.to(1)
            } else if (listaErrores.id_document_type) {
                stepper.to(2)
            } else if (listaErrores.name) {
                stepper.to(2)
            } else if (listaErrores.last_name) {
                stepper.to(2)
            } else if (listaErrores.biography) {
                stepper.to(2)
            } else if (listaErrores.phone) {
                stepper.to(2)
            } else if (listaErrores.date_birth) {
                stepper.to(2)
            } else if (listaErrores.nro_document) {
                stepper.to(2)
            } else if (listaErrores.id_account_type) {
                stepper.to(3)
            } else if (listaErrores.operation_number) {
                stepper.to(3)
            }
        }

        function useRegex(input) {
            let regex = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z])((?=.*\W)|(?=.*_))^[^ ]+$/g;
            return regex.test(input);
        }

        function validPassword(new_pas, conf_pas) {
            if (new_pas == '' || new_pas.length <= 0) {
                alert('Ingrese una contraseña');
                return false;
            }

            if (new_pas != conf_pas) {
                alert('Las contraseñas no coinciden');
                return false;
            }
            if (new_pas.length < 8) {
                alert('La contraseña debe tener como mínimo 8 caracteres');
                return false;
            }
            if (!this.useRegex(new_pas)) {
                password.setAttribute('class', 'form-control is-invalid');
                document.getElementById('password_error').innerHTML =
                    "La contraseña debe tener como mínimo 1 letra minúscula,una letra mayúscula, 1 digito y 1 caracter especial!";
                return false;
            }
            return true;
        }

        var order_id;
        var payment;
        var openpayOrder;
        var redirection;

        async function register(url, sponsor) {
            // Validar link antes de proceder
            await validateLinkStatus();
            console.log('Inicio del registro');
            console.log('URL:', url);
            console.log('Sponsor:', sponsor);
        
            document.getElementById('btnPay').style.display = "none";
            let date_birth = document.getElementById('date_birth');
        
            let getBirth = new Date(date_birth.value);
            let dateB = getBirth.getFullYear();
        
            let year_now = new Date().getFullYear();
            let getAge = year_now - dateB;
        
            console.log('Edad calculada:', getAge);
        
            if (getAge < 18) {
                console.log('No cumple con la edad requerida');
                return alert('No cumple con la edad requerida');
            }
        
            if (optionRegisterSelected == 1) {
                payment = 'free';
                console.log('Registro gratuito');
            } else {
                console.log('Método de pago seleccionado:', payment_method.value);
            
                if (payment_method.value == 0) {
                    payment_method.setAttribute('class', 'form-control is-invalid');
                    document.getElementById('payment_method_id_error').innerHTML =
                        "Selecciona un método de pago.";
                } else {
                    username.setAttribute('class', 'form-control');
                    document.getElementById('username_error').innerHTML = "";
                }
            
                if (payment_method.value == 1) {
                    payment = 'openpay';
                }
            
                if (payment_method.value == 2) {
                    return alert('metodo de pago aun no implementado');
                }
                if (payment_method.value == 3) {
                    payment = 'binance';
                    if (operation_number.value.length <= 4) {
                        console.log('Número de operación inválido!');
                        return alert('Ingrese un número de operación válido');
                    }
                }
            
                if (payment_method.value == 4) {
                    payment = 'paypal';
                }
            }
        
            if (payment_method.value == 1) {
                let account_type_list = JSON.parse(account_types.value);
                let account_selected = account_type_list.filter(obj => obj.id == accountTypes.value)[0];
                let description = "Membresía " + account_selected.account;
                var myHeaders = new Headers();
                console.log(key_openpay, id_openpay);
            
                myHeaders.append("Content-Type", "application/json");
            
                let fechaActual = new Date();
                let anio = fechaActual.getFullYear();
                let mes = (fechaActual.getMonth() + 1).toString().padStart(2, '0');
                let dia = fechaActual.getDate().toString().padStart(2, '0');
                let hora = fechaActual.getHours().toString().padStart(2, '0');
                let minuto = fechaActual.getMinutes().toString().padStart(2, '0');
                let segundo = fechaActual.getSeconds().toString().padStart(2, '0');
                let fechaFormateada = `${anio}-${mes}-${dia}T${hora}:${minuto}:${segundo}`;
            
                var raw = JSON.stringify({
                    "method": "card",
                    "amount": total_amount,
                    "currency": "USD",
                    "description": description,
                    "confirm": "false",
                    "send_email": "false",
                    "redirect_url": "{{ env('APP_URL') }}login",
                    "due_date": fechaFormateada,
                    "customer": {
                        "name": firstName.value,
                        "last_name": lastName.value,
                        "phone_number": phone.value,
                        "email": email.value
                    }
                });
                var requestOptions = {
                    method: 'POST',
                    headers: myHeaders,
                    body: raw,
                };
            
                await fetch(`/pay/register`, requestOptions).then(r => r.json())
                    .then(r => {
                        charge_id = r.charge_id;
                        redirection = r.payment_url;
                    });
            }
        
            // Preparar datos según el método de pago
            const formData = new FormData();
            formData.append('id_referrer_sponsor', sponsor);
            formData.append('username', username.value);
            formData.append('email', email.value);
            formData.append('password', password.value);
            formData.append('password_confirmation', repassword.value);
            formData.append('user_type', user_type.value);
            formData.append('name', firstName.value);
            formData.append('last_name', lastName.value);
            formData.append('biography', biography.value);
            formData.append('phone', phone.value);
            formData.append('date_birth', date_birth.value);
            formData.append('id_document_type', documentTypes.value);
            formData.append('nro_document', nro_document.value);
            formData.append('id_country', country.value);
            formData.append('id_account_type', accountTypes.value);
            formData.append('purchase_number', purchase_number.value);
            formData.append('payment_method_id', payment_method_id.value);
            formData.append('payment_method', payment);
            formData.append('order_id', charge_id);
        
            // Si es pago con Binance, agregar comprobante
            if (payment_method.value == "3") {
                const receiptImage = document.getElementById('receiptImage').files[0];
                if (!receiptImage) {
                    return alert('Por favor suba el comprobante de pago');
                }
                formData.append('receipt', receiptImage);
                formData.append('operation_number', operation_number.value);
            }
        
            // Log de todos los datos que se van a enviar
            console.log('Datos a enviar:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }
        
            // Deshabilitar botones durante el proceso
            const btnPay = document.getElementById('btnPay');
            const btnFree = document.getElementById('btnFree');
            const btnUnverified = document.getElementById('btnUnverified');
            
            if (btnPay) btnPay.disabled = true;
            if (btnFree) btnFree.disabled = true;
            if (btnUnverified) btnUnverified.disabled = true;
        
            // Envío de datos
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                    },
                });
            
                console.log('Respuesta del servidor:', response);
            
                const data = await response.json();
                console.log('Datos de respuesta:', data);
            
                const errorContainer = document.getElementById('error-container');
                errorContainer.innerHTML = '';
            
                if (data.errors) {
                    console.error('Errores en la respuesta:', data.errors);
                    errorContainer.classList.add('alert', 'alert-danger');
                
                    Object.values(data.errors).forEach(errorMessages => {
                        errorMessages.forEach(errorMessage => {
                            const errorDiv = document.createElement('div');
                            errorDiv.classList.add('mb-1');
                            errorDiv.textContent = errorMessage;
                            errorContainer.appendChild(errorDiv);
                        });
                    });
                    
                    // Re-habilitar botones si hay errores
                    if (btnPay) btnPay.disabled = false;
                    if (btnFree) btnFree.disabled = false;
                    if (btnUnverified) btnUnverified.disabled = false;
                } else {
                    console.log('Registro exitoso');
                    
                    // Si hay URL de redirección desde el backend, usarla
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                        return;
                    } 
                    // Obtener el elemento correctamente para evitar errores de referencia
                    const paymentSelect = document.getElementById('payment_method_id');
                    const paymentVal = paymentSelect ? paymentSelect.value : 0;
                                    
                    if (paymentVal == "3") {
                        alerta();
                    } 
                    else if (redirection) {
                        window.location.href = redirection;
                    }
                }
            } catch (error) {
                console.error('Error completo:', error);
                console.log('Detalles del error:', {
                    name: error.name,
                    message: error.message,
                    stack: error.stack
                });
                
                // Re-habilitar botones en caso de error
                if (btnPay) btnPay.disabled = false;
                if (btnFree) btnFree.disabled = false;
                if (btnUnverified) btnUnverified.disabled = false;
            }
        }

        async function stepperValidations1() {
            let value = true;
            let clearUsernameError = true;
            if (username.value == "") {
                username.setAttribute('class', 'form-control is-invalid');
                document.getElementById('username_error').innerHTML =
                    "El nombre de usuario debe tener más de 3 caracteres";
                value = false;
                clearUsernameError = false;
            }
            if (username.value.length >= 50) {
                username.setAttribute('class', 'form-control is-invalid');
                document.getElementById('username_error').innerHTML =
                    "Selecciona un método de pago.";
                value = false;
                clearUsernameError = false;
            }

            let usernameExists = false;
            await axios.post('/verify-username', {
                username: username.value
            }).then((r) => {
                console.log(r)
                usernameExists = r.data
            });
            if (usernameExists) {
                username.setAttribute('class', 'form-control is-invalid');
                document.getElementById('username_error').innerHTML = "El nombre de usuario ya existe";
                value = false;
                clearUsernameError = false;
            }

            if (clearUsernameError) {
                username.setAttribute('class', 'form-control');
                document.getElementById('username_error').innerHTML = "";
            }

            let clearEmailError = true;

            let emailRegex = /^[-\w.%+]{1,64}@(?:[A-Z0-9-]{1,63}\.){1,125}[A-Z]{2,63}$/i;
            if (!emailRegex.test(email.value)) {
                email.setAttribute('class', 'form-control is-invalid');
                document.getElementById('email_error').innerHTML = "Ingrese un correo válido";
                value = false;
                clearEmailError = false;
            }
            let emailExists = false;
            await axios.post('/verify-email', {
                email: email.value
            }).then((r) => {
                console.log(r)
                emailExists = r.data
            });
            if (emailExists) {
                email.setAttribute('class', 'form-control is-invalid');
                document.getElementById('email_error').innerHTML = "El correo ya se registró.";

                value = false;
                clearEmailError = false;
            }
            if (clearEmailError) {
                email.setAttribute('class', 'form-control');
                document.getElementById('email_error').innerHTML = "";
            }

            let clearPasswordError = true;
            let regex = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z])((?=.*\W)|(?=.*_))^[^ ]+$/g;
            if (password.value <= 5) {
                password.setAttribute('class', 'form-control is-invalid');
                document.getElementById('password_error').innerHTML = "La contraseña debe tener más de 5 caracteres";
                value = false;
                clearPasswordError = false;
            }
            if (!regex.test(password.value)) {
                password.setAttribute('class', 'form-control is-invalid');
                document.getElementById('password_error').innerHTML =
                    "La contraseña debe tener como mínimo 1 letra minúscula,una letra mayúscula, 1 digito y 1 caracter especial";
                value = false;
                clearPasswordError = false;
            }
            if (password.value != repassword.value) {
                password.setAttribute('class', 'form-control is-invalid');
                document.getElementById('password_error').innerHTML = "Las contraseñas no coinciden";
                value = false;
                clearPasswordError = false;
            }
            if (clearPasswordError) {
                password.setAttribute('class', 'form-control');
                document.getElementById('password_error').innerHTML = "";
            }

            let userTypeClearError = true;
            if (user_type.value == "Seleccione una opción") {
                user_type.setAttribute('class', 'form-control is-invalid');
                document.getElementById('user_type_error').innerHTML = "Seleccione un tipo de usuario";
                value = false;
                userTypeClearError = false;
            }
            if (userTypeClearError) {
                user_type.setAttribute('class', 'form-control');
                document.getElementById('user_type_error').innerHTML = "";
            }

            return value;
        }

        async function stepperValidations2() {
            let value = true;
            let clearFirstNameError = true;
            if (firstName.value.length <= 3) {
                firstName.setAttribute('class', 'form-control is-invalid');
                document.getElementById('name_error').innerHTML = "Ingrese su nombre correctamente";
                value = false;
                clearFirstNameError = false;
            }
            if (firstName.value.length >= 50) {
                firstName.setAttribute('class', 'form-control is-invalid');
                document.getElementById('name_error').innerHTML = "Solo se aceptan hasta 50 caracteres";
                value = false;
                clearFirstNameError = false;
            }
            if (clearFirstNameError) {
                firstName.setAttribute('class', 'form-control');
                document.getElementById('name_error').innerHTML = "";
            }

            let clearLastNameError = true;
            if (lastName.value.length <= 3) {
                lastName.setAttribute('class', 'form-control is-invalid');
                document.getElementById('last_name_error').innerHTML = "Ingrese su apellido correctamente";
                value = false;
                clearLastNameError = false;
            }
            if (lastName.value.length >= 50) {
                lastName.setAttribute('class', 'form-control is-invalid');
                document.getElementById('last_name_error').innerHTML = "Solo se aceptan hasta 50 caracteres";
                value = false;
                clearLastNameError = false;
            }
            if (clearLastNameError) {
                lastName.setAttribute('class', 'form-control');
                document.getElementById('last_name_error').innerHTML = "";
            }

            let clearPhoneError = true;
            if (phone.value.length <= 7) {
                phone.setAttribute('class', 'form-control is-invalid');
                document.getElementById('phone_error').innerHTML = "Ingrese su teléfono correctamente";
                value = false;
                clearPhoneError = false;
            }
            if (clearPhoneError) {
                phone.setAttribute('class', 'form-control');
                document.getElementById('phone_error').innerHTML = "";
            }

            let clearDateBirthError = true;
            if (date_birth.value == "") {
                date_birth.setAttribute('class', 'form-control is-invalid');
                document.getElementById('date_birth_error').innerHTML = "Ingrese su fecha de nacimiento";
                value = false;
                clearDateBirthError = false;
            }
            if (clearDateBirthError) {
                date_birth.setAttribute('class', 'form-control');
                document.getElementById('date_birth_error').innerHTML = "";
            }

            let clearDocumentTypeError = true;
            if (documentTypes.value == 0) {
                documentTypes.setAttribute('class', 'form-control is-invalid');
                document.getElementById('id_document_type_error').innerHTML = "Seleccione su tipo de documento";
                value = false;
                clearDocumentTypeError = false;
            }
            if (clearDocumentTypeError) {
                documentTypes.setAttribute('class', 'form-control');
                document.getElementById('id_document_type_error').innerHTML = "";
            }

            let clearNroDocumentError = true;

            if (nro_document.value == "") {
                nro_document.setAttribute('class', 'form-control is-invalid');
                document.getElementById('nro_document_error').innerHTML = "Ingrese su número de documento";
                value = false;
                clearNroDocumentError = false;
            }
            let documentExists = false;
            await axios.post('/verify-document', {
                documentType: documentTypes.value,
                documentNumber: nro_document.value
            }).then((r) => {
                console.log(r)
                documentExists = r.data
            });
            if (documentExists) {
                nro_document.setAttribute('class', 'form-control is-invalid');
                document.getElementById('nro_document_error').innerHTML = "El número de documento ya se registró.";
                value = false;
                clearNroDocumentError = false;
            }
            if (clearNroDocumentError) {
                nro_document.setAttribute('class', 'form-control');
                document.getElementById('nro_document_error').innerHTML = "";
            }
            return value;
        }

        async function next1() {
            let validation1 = await stepperValidations1();

            if (validation1) {
                clearError();
                stepper.next()
            }
        }

        function alerta() {
            let timerInterval;
            Swal.fire({
                title: "Procesando",
                html: "Su solicitud está siendo procesada <b></b>.",
                timer: 2000,
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                    const timer = Swal.getPopup().querySelector("b");
                    timerInterval = setInterval(() => {
                        timer.textContent = `${Swal.getTimerLeft()}`;
                    }, 100);
                },
                willClose: () => {
                    clearInterval(timerInterval);
                }
            }).then((result) => {
                if (result.dismiss === Swal.DismissReason.timer) {
                    window.location.href = redirection;
                    console.log("Close");
                }
            });
        }

        async function next2() {
            let validation2 = await stepperValidations2()
            if (validation2) {
                clearError();
                stepper.next()
            }
        }

        function previus() {
            stepper.previous()
        }
    </script>
@endsection
