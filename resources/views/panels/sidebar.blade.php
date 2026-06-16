@php
    $configData = Helper::applClasses();
    $rol = auth()->user()->id_account_type;
    $userId = auth()->user()->id;
    $expiration_date = \App\Models\User::where('id', auth()->user()->id)->value('expiration_date');
    $expiration_date = $expiration_date ? new \DateTime($expiration_date) : new \DateTime();
    
    // Nueva consulta para verificar el precio del producto según el tipo de cuenta
    $productPrice = \DB::table('product')
        ->where('account_type_id', $rol)
        ->value('price');
    
    // Determinar si se debe mostrar el botón de recompra
    $showRecompraButton = ($rol != 1 && $rol != 5 && $rol != 6) && ($productPrice > 0);
@endphp
<div class="main-menu menu-fixed {{ $configData['theme'] === 'dark' ? 'menu-dark' : 'menu-light' }} menu-accordion menu-shadow"
    data-scroll-to-active="true">
    <div class="navbar-header">
        <ul class="nav navbar-nav flex-row">
            <li class="nav-item mr-auto">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <span class="brand-logo">
                        {{-- Logo normal (expandido) --}}
                        <img id="logo-expanded" 
                             src="{{ asset('images/logo/promolider_logo.png') }}"
                             style="margin-left: 40px; min-width: 130px; width: 100%; transition: opacity 0.3s ease;">

                        {{-- Logo colapsado --}}
                        <img id="logo-collapsed" 
                             src="{{ asset('images/logo/promolider_logo_collapse.png') }}"
                             style="display: none; margin-left: 0; width: 40px; height: auto; transition: opacity 0.3s ease;">
                    </span>
                </a>
            </li>
            {{-- Botón para colapsar el menú --}}
            <li class="nav-item nav-toggle">
                <a class="nav-link modern-nav-toggle pr-0" data-toggle="collapse" id="sidebar-toggle">
                    <i class="d-block d-xl-none text-primary toggle-icon font-medium-4" data-feather="x"></i>
                    <i class="d-none d-xl-block collapse-toggle-icon font-medium-4 text-primary" data-feather="sidebar" data-ticon="sidebar"></i>
                </a>
            </li>
        </ul>
    </div>
    <div></div>
    <div class="main-menu-content">
        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
            @if (isset($menuData[0]))
                @foreach ($menuData[0]->menu as $menu)
                    @if (isset($menu->navheader))
                        <li class="navigation-header">
                            @can($menu->slug)
                                <span>{{ __('locale.' . $menu->navheader) }}</span>
                                <i data-feather="more-horizontal"></i>
                            @endcan
                        </li>
                    @else
                        @php
                            $custom_classes = '';
                            if (isset($menu->classlist)) {
                                $custom_classes = $menu->classlist;
                            }
                        @endphp
                        @can($menu->slug)
                            <li
                                class="nav-item {{ Route::currentRouteName() === $menu->slug ? 'active' : '' }} {{ $custom_classes }}">
                                <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0)' }}"
                                    class="d-flex align-items-center"
                                    target="{{ isset($menu->newTab) ? '_blank' : '_self' }}">

                                    <i data-feather="{{ $menu->icon }}"></i>


                                    <span class="menu-title text-truncate">{{ __('locale.' . $menu->name) }}</span>
                                    @if (isset($menu->badge))
                                        <?php $badgeClasses = 'badge badge-pill badge-light-primary ml-auto mr-1'; ?>
                                        <span
                                            class="{{ isset($menu->badgeClass) ? $menu->badgeClass : $badgeClasses }} ">{{ $menu->badge }}</span>
                                    @endif
                                </a>
                                @if (isset($menu->submenu))
                                    @include('panels/submenu', ['menu' => $menu->submenu])
                                @endif
                            </li>
                        @endcan
                    @endif
                @endforeach
                @if ($showRecompraButton)
                    <div class="px-3 mt-5" id="opc-button-container">
                        <div id="countdown" class="btn btn-primary d-flex justify-content-center align-items-center">
                            <span class="btn-text">Recomprar OPC</span>
                            <span class="btn-icon" style="display: none;">
                                <i data-feather="credit-card" class="font-medium-3"></i>
                            </span>
                        </div>
                    </div>
                @endif
            @endif
            @php
                $current_date = new DateTime();

                if ($current_date < $expiration_date) {
                    $debt_count = 0;
                } else {
                    $diffYears = $current_date->format('Y') - $expiration_date->format('Y');
                    $diffMonths = $current_date->format('m') - $expiration_date->format('m');
                    $totalMonths = ($diffYears * 12) + $diffMonths;
                    
                    if ($current_date->format('d') > $expiration_date->format('d') || 
                       ($current_date->format('d') == $expiration_date->format('d') && $current_date->format('H:i:s') > $expiration_date->format('H:i:s'))) {
                        $totalMonths++;
                    }
                    
                    $debt_count = $totalMonths;
                }
            @endphp
        </ul>

    </div>

</div>
<div id="modal-countdown" class="modal fade modal-primary" tabindex="-1" role="dialog" aria-hidden="true"
    data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <slot name="title">Expiración de OPC</slot>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <slot name="body">
                    <p>Tiempo restante para la expiración de su estado activo</p>
                    <div class="row ml-1">
                        <div class="col-2 p-0">
                            <div class="col-12 p-0">
                                <h1 class="text-center pt-2 pb-2 bg-light" style="font-size: 300%;" id="days">0
                                </h1>
                            </div>
                            <div class="col-12 p-0">
                                <p class="text-center">Días</p>
                            </div>
                        </div>
                        <div class="col-1 p-0">
                            <div class="col-12 p-0">
                                <h1 class="text-center pt-2 pb-2" style="font-size: 300%;">:</h1>
                            </div>
                        </div>
                        <div class="col-2 p-0">
                            <div class="col-12 p-0">
                                <h1 class="text-center pt-2 pb-2 bg-light" style="font-size: 300%;" id="hours">0
                                </h1>
                            </div>
                            <div class="col-12 p-0">
                                <p class="text-center">Horas</p>
                            </div>
                        </div>
                        <div class="col-1 p-0">
                            <div class="col-12 p-0">
                                <h1 class="text-center pt-2 pb-2" style="font-size: 300%;">:</h1>
                            </div>
                        </div>
                        <div class="col-2 p-0">
                            <div class="col-12 p-0">
                                <h1 class="text-center pt-2 pb-2 bg-light" style="font-size: 300%;" id="minutes">0
                                </h1>
                            </div>
                            <div class="col-12 p-0">
                                <p class="text-center">Minutos</p>
                            </div>
                        </div>
                        <div class="col-1 p-0">
                            <div class="col-12 p-0">
                                <h1 class="text-center pt-2 pb-2" style="font-size: 300%;">:</h1>
                            </div>
                        </div>
                        <div class="col-2 p-0">
                            <div class="col-12 p-0">
                                <h1 class="text-center pt-2 pb-2 bg-light" style="font-size: 300%;" id="seconds">0
                                </h1>
                            </div>
                            <div class="col-12 p-0">
                                <p class="text-center">Segundos</p>
                            </div>
                        </div>
                    </div>

                </slot>

                <form style="display:none;" id="payment_method_form" class="mt-2">
                    <div id="payment_method_selector" class="form-group col-12 col-lg-6">
                        <label for="payment_method_id">Seleccione el método de pago</label>
                        <select id="payment_method_id" name="payment_method_id" class="form-control">
                        </select>
                        <div id="payment_method_id_error" class="invalid-feedback"></div>
                    </div>

                    <div id="cuotas_selector" class="form-group col-12 col-lg-6" style="display: {{ $debt_count > 0 ? 'block' : 'none' }};">
                        <label for="cuotas_a_pagar">¿Cuántas cuotas desea pagar?</label>
                        <select id="cuotas_a_pagar" name="cuotas_a_pagar" class="form-control" onchange="getWalletUser()">
                            @if ($debt_count > 0)
                                @for ($i = 1; $i <= $debt_count; $i++)
                                    <option value="{{ $i }}">{{ $i }} {{ $i == 1 ? 'cuota' : 'cuotas' }}</option>
                                @endfor
                            @else
                                <option value="1">1 cuota</option>
                            @endif
                        </select>
                    </div>

                    <div id="salto_total" class="form-group col-12 col-lg-12">
                        <p style="font-weight: bold;">Saldo Billetera: $ <span id="saldo_total_valor"></span></p>
                        <p style="font-weight: bold;">Importe Total: $ <span id="importe_opc"></span> <small class="text-muted" id="detalle_cuotas"></small></p>
                    </div>
                </form>
                <p id="message_conditions" style="display: none;">
                    Al hacer click en "Comprar" usted está aceptando nuestros <button type="button"
                        class="btn btn-link" onclick="showConditions()">términos y condiciones.</button>
                </p>
            </div>
            <div class="modal-footer">
                <p class="font-weight-bold" id="debt_footer_text" style="display: {{ $debt_count > 0 ? 'block' : 'none' }};">Usted debe pagar <span class="text-danger" id="debt_footer_count">{{ $debt_count }}</span> cuotas.</p>
                <slot name="footer">

                    <button class="btn btn-warning" onclick="hideForm()" id="cancel"
                        style="display: none;">Cancelar</button>
                    <button class="btn btn-primary" onclick="payOPC()" id="continue" style="display: none;"
                        type="button">Comprar</button>
                    <button class="btn btn-primary" onclick="showForm()" id="buy">Recompra</button>

                </slot>
            </div>
        </div>
    </div>
</div>
{{-- terminos y condiciones --}}
<div class="modal fade" id="modal_conditions" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document" style="overflow-y: initial !important">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success" id="exampleModalLabel">Términos y condiciones</h5>
            </div>
            <div class="modal-body overflow-auto">
                @include('modalOpenpayConditions')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="hideConditions()"
                    data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- END: Main Menu-->
@if ($rol != 1 && $rol != 5 && $rol != 6)
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // --- Referencias a Elementos del DOM ---
        const select_payment_method = document.getElementById("payment_method_id");
        let form = document.getElementById("payment_method_form");
        let cancel_button = document.getElementById("cancel");
        let buy_button = document.getElementById("buy");
        let continue_button = document.getElementById("continue");
        let message_conditions = document.getElementById("message_conditions");
        var saldoTotalElement = document.getElementById('salto_total');
        var comprarButton = document.getElementById('continue');
        let countdown_button = document.getElementById("countdown");

        // --- Variables de Estado Globales ---
        let payment_id = '';
        let date_target; // La fecha de expiración se obtendrá de la API
        let countdownInterval; // Referencia al intervalo para poder limpiarlo

        // --- Funciones de UI (Sin cambios) ---
        async function showForm() {
            const response = await fetch('/config/payment-method/list-array');
            const data = await response.json();
            // Filtrar Efectivo, Transferencia, Paypal y Binance
            const responseData = data.filter(data => !['Efectivo', 'Transferencia', 'Paypal', 'Binance'].includes(data.name));

            select_payment_method.innerHTML = "";

            responseData.forEach(function(item) {
                const optionObj = document.createElement("option");
                optionObj.textContent = item.name;
                optionObj.value = item.id;
                select_payment_method.appendChild(optionObj);
            });

            form.style.display = "block";
            cancel_button.style.display = "block";
            continue_button.style.display = "block";
            buy_button.style.display = "none";
            message_conditions.style.display = "block";
        }

        function hideForm() {
            form.style.display = "none";
            continue_button.style.display = "none";
            cancel_button.style.display = "none";
            buy_button.style.display = "block";
            message_conditions.style.display = "none";
        }

        function showConditions() {
            if (typeof jQuery !== 'undefined') {
                jQuery('#modal_conditions').modal({ show: true });
                jQuery("#modal_conditions").addClass("overflow-auto");
                jQuery('#modal-countdown').modal('hide');
            }
        }

        function hideConditions() {
            if (typeof jQuery !== 'undefined') {
                jQuery('#modal_conditions').modal('hide');
                jQuery('#modal-countdown').modal({ show: true });
            }
        }


        // --- Lógica de Billetera (Sin cambios) ---
        function getWallet() {
            saldoTotalElement.style.display = 'none';

            select_payment_method.addEventListener("change", function() {
                var selectVal = this.value;
                payment_id = this.value;
                if (selectVal == 5) {
                    getWalletUser();
                    saldoTotalElement.style.display = 'block';
                } else {
                    comprarButton.style.display = 'block';
                    saldoTotalElement.style.display = 'none';
                }
            });
        }

        async function getWalletUser() {
            try {
                const userId = @json($userId);
                const opcResponse = await fetch('/opc_config/get-current-price');

                // Obtener el balance
                const response = await fetch('/reports/wallet/balance');

                // Verificar que la respuesta sea exitosa
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const dataRequest = await response.json();

                // Debug: ver qué datos llegan
                console.log('Respuesta del endpoint:', dataRequest);

                const opcData = await opcResponse.json();
                let importeOpc = opcData.price;

                // VALIDACIÓN: Verificar que los datos existan
                let saldoTotal = 0;
                if (dataRequest && dataRequest.data && typeof dataRequest.data.approved_amount === 'number') {
                    saldoTotal = dataRequest.data.approved_amount;
                } else {
                    console.error('Estructura de datos inesperada:', dataRequest);
                    if (dataRequest && dataRequest.data && typeof dataRequest.data.total_balance === 'number') {
                        saldoTotal = dataRequest.data.total_balance;
                    }
                }
            
                // Obtener número de cuotas a pagar
                const cuotasElement = document.getElementById('cuotas_a_pagar');
                let cuotas = 1;
                if (cuotasElement && cuotasElement.offsetParent !== null) { // Si es visible
                    cuotas = parseInt(cuotasElement.value) || 1;
                }
                
                const importeTotal = importeOpc * cuotas;

                const totalValor = document.getElementById('saldo_total_valor');
                const importeOpcElement = document.getElementById('importe_opc');
                const detalleCuotas = document.getElementById('detalle_cuotas');
            
                totalValor.textContent = saldoTotal.toFixed(2);
                importeOpcElement.textContent = importeTotal.toFixed(2);
                if (cuotas > 1) {
                    detalleCuotas.textContent = `(${cuotas} cuotas de $${importeOpc.toFixed(2)})`;
                } else {
                    detalleCuotas.textContent = '';
                }
            
                if (saldoTotal >= importeTotal && payment_id == 5) {
                    comprarButton.style.display = 'block';
                } else {
                    comprarButton.style.display = 'none';
                }
            
                return { saldoTotal, importeOpc: importeTotal, cuotas };
            
            } catch (error) {
                console.error('Error al obtener la billetera del usuario:', error);
                return { saldoTotal: 0, importeOpc: 0, cuotas: 1 };
            }
        }

        // --- Lógica de Pago (MODIFICADA) ---
        async function savePaymentWallet() {
            try {
                // Mostrar loading mientras se procesa
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Realizando el pago, por favor espere.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            
                const { saldoTotal, importeOpc, cuotas } = await getWalletUser();
                const sendForm = JSON.stringify({ saldoTotal, importeOpc, cuotas });
            
                const response = await fetch('/pay/opc-wallet', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: sendForm
                });
            
                if (response.ok) {
                    // Cerrar el modal primero
                    if (typeof jQuery !== 'undefined') {
                        jQuery('#modal-countdown').modal('hide');
                        
                        // Esperar a que el modal se cierre completamente antes de mostrar el SweetAlert
                        jQuery('#modal-countdown').on('hidden.bs.modal.payment-success', function() {
                            // Remover el listener después de usarlo para evitar múltiples ejecuciones
                            jQuery(this).off('hidden.bs.modal.payment-success');
                        
                        // Mostrar mensaje de éxito
                        Swal.fire({
                            title: "¡Exitoso!",
                            text: "Recompra de OPC Exitosa!",
                            icon: "success",
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            // Recargar la página para que el contador de PHP de cuotas pendientes se actualice
                            window.location.reload();
                        });
                    });
                
                    // Si el modal ya está oculto, ejecutar inmediatamente
                    if (!jQuery('#modal-countdown').hasClass('show')) {
                        Swal.fire({
                            title: "¡Exitoso!",
                            text: "Recompra de OPC Exitosa!",
                            icon: "success",
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                
                } else {
                    // Manejar errores de respuesta
                    const errorData = await response.json().catch(() => ({}));
                    
                    Swal.fire({
                        title: "Error",
                        text: errorData.message || "Hubo un problema al procesar el pago. Por favor, intente nuevamente.",
                        icon: "error",
                        confirmButtonText: 'Aceptar'
                    });
                    
                    console.error('Error en la solicitud:', response.statusText, errorData);
                }
            } catch (error) {
                // Manejar errores de red u otros errores
                Swal.fire({
                    title: "Error",
                    text: "No se pudo conectar con el servidor. Verifique su conexión e intente nuevamente.",
                    icon: "error",
                    confirmButtonText: 'Aceptar'
                });
                
                console.error('Error al registrar el pago por billetera:', error);
            }
        }

        async function payOPC() {
            let value = await select_payment_method.options[select_payment_method.selectedIndex].value;
            let cuotas = document.getElementById('cuotas_a_pagar') ? document.getElementById('cuotas_a_pagar').value : 1;
            switch (value) {
                case '1': window.location.href = "/pay/opc-openpay?cuotas=" + cuotas; break;
                case '2': /* Lógica para opción 2 */ break;
                case '3': /* Lógica para opción 3 */ break;
                case '4': window.location.href = "/pay/recompra"; break;
                case '5': savePaymentWallet(); break;
                default: break;
            }
        }


        // --- Lógica de Cuenta Regresiva (Refactorizada) ---
        function evaluateTime(targetDate) {
            let now = new Date().getTime();
            let distance = targetDate - now;
            let days, hours, minutes, seconds;

            if (distance < 0) {
                days = hours = minutes = seconds = "00";
            } else {
                days = Math.floor(distance / (1000 * 60 * 60 * 24));
                hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                seconds = Math.floor((distance % (1000 * 60)) / 1000);
            }

            if (typeof jQuery !== 'undefined') {
                jQuery('#days').html(addZero(days));
                jQuery('#hours').html(addZero(hours));
                jQuery('#minutes').html(addZero(minutes));
                jQuery('#seconds').html(addZero(seconds));
            }
        }

        function addZero(num) {
            return num <= 9 ? "0" + String(num) : String(num);
        }

        // --- ** NUEVA FUNCIÓN PRINCIPAL PARA ACTUALIZAR ESTADO ** ---
        async function updateExpirationDateAndRestartCountdown() {
            try {
                const response = await fetch('/user/get-status');
                if (!response.ok) {
                    throw new Error('No se pudo obtener el estado del usuario.');
                }
                const data = await response.json();
                
                // Actualizamos la variable global con la fecha fresca del servidor
                date_target = new Date(data.expiration_date);
                
                // Limpiamos cualquier contador que estuviera corriendo antes
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                }

                // Evaluamos el tiempo inmediatamente para que la UI se actualice al instante
                evaluateTime(date_target);

                // Iniciamos un nuevo intervalo con la fecha correcta
                countdownInterval = setInterval(() => evaluateTime(date_target), 1000);

                // ACTUALIZACIÓN DINÁMICA DE LA UI DE DEUDA
                let newDebtCount = 0;
                let now = new Date();
                if (now >= date_target) {
                    let diffMonths = (now.getFullYear() - date_target.getFullYear()) * 12 + (now.getMonth() - date_target.getMonth());
                    
                    // Si el día actual es mayor al día de corte, se debe un mes adicional
                    // Además manejamos el caso donde la hora también haya pasado.
                    if (now.getDate() > date_target.getDate() || (now.getDate() === date_target.getDate() && now.getTime() > date_target.getTime())) {
                        diffMonths++;
                    }
                    
                    newDebtCount = diffMonths;
                }

                const cuotasSelector = document.getElementById('cuotas_selector');
                const cuotasSelect = document.getElementById('cuotas_a_pagar');
                if (cuotasSelect && cuotasSelector) {
                    cuotasSelect.innerHTML = '';
                    if (newDebtCount > 0) {
                        cuotasSelector.style.display = 'block';
                        for (let i = 1; i <= newDebtCount; i++) {
                            const option = document.createElement('option');
                            option.value = i;
                            option.text = i + (i === 1 ? ' cuota' : ' cuotas');
                            cuotasSelect.appendChild(option);
                        }
                    } else {
                        cuotasSelector.style.display = 'none';
                        const option = document.createElement('option');
                        option.value = 1;
                        option.text = '1 cuota';
                        cuotasSelect.appendChild(option);
                    }
                }

                const debtFooterText = document.getElementById('debt_footer_text');
                const debtFooterCount = document.getElementById('debt_footer_count');
                if (debtFooterText && debtFooterCount) {
                    if (newDebtCount > 0) {
                        debtFooterCount.innerText = newDebtCount;
                        debtFooterText.style.display = 'block';
                    } else {
                        debtFooterText.style.display = 'none';
                    }
                }

            } catch (error) {
                console.error("Falló la actualización de la fecha de expiración:", error);
            }
        }


        // --- ** MANEJO DE EVENTOS (MODIFICADO) ** ---

        // 1. Al hacer clic en el botón de la cuenta regresiva
        if (countdown_button) {
            countdown_button.addEventListener("click", function() {
                if (typeof jQuery !== 'undefined') {
                    jQuery('#modal-countdown').modal('show');
                }
                updateExpirationDateAndRestartCountdown();
            });
        }

        // 2. Al cerrar el modal, detenemos el intervalo para ahorrar recursos.
        if (typeof jQuery !== 'undefined') {
            jQuery("#modal-countdown").on('hidden.bs.modal', function() {
            if (countdownInterval) {
                clearInterval(countdownInterval);
            }
            });
        }

        // 3. Al cargar la página (incluyendo al volver desde una página externa)
        window.addEventListener('pageshow', function(event) {
            // event.persisted es true si la página fue restaurada del caché (bfcache)
            // Esta es la solución clave para el problema de "refrescar"
            if (event.persisted) {
                console.log('Página restaurada del caché. Actualizando datos.');
                updateExpirationDateAndRestartCountdown();
            }
        });

        // 4. Inicialización al cargar la página por primera vez
        document.addEventListener('DOMContentLoaded', () => {
            getWallet(); // Configura los listeners de la billetera
            updateExpirationDateAndRestartCountdown(); // Obtiene la fecha y arranca el contador
        });

        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.main-menu');
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const mobileSidebarToggle = document.getElementById('mobile-sidebar-toggle');
            const navbarHeader = document.querySelector('.main-menu .navbar-header'); // Nueva referencia
            const body = document.body;
        
            // Toggle para escritorio (MODIFICADO)
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    sidebar.classList.toggle('menu-collapsed');
                    body.classList.toggle('menu-collapsed');
                
                    // NUEVO: Alternar display del botón después del toggle
                    setTimeout(toggleButtonDisplay, 50);
                });
            }
        
            // Toggle para móvil
            if (mobileSidebarToggle) {
                mobileSidebarToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    sidebar.classList.toggle('menu-open');
                    document.querySelector('.sidenav-overlay').classList.toggle('show');
                });
            }
        
            // Cerrar menú al hacer click en overlay
            const overlay = document.querySelector('.sidenav-overlay');
            if (overlay) {
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('menu-open');
                    this.classList.remove('show');
                });
            }
        
            // MODIFICADO: Detectar hover en el menú colapsado
            if (sidebar && navbarHeader) {
                sidebar.addEventListener('mouseenter', function() {
                    if (this.classList.contains('menu-collapsed')) {
                        this.classList.add('expanded');
                        navbarHeader.classList.add('expanded'); // Agregar clase al navbar-header
                        toggleButtonDisplay();
                    }
                });
            
                sidebar.addEventListener('mouseleave', function() {
                    if (this.classList.contains('menu-collapsed')) {
                        this.classList.remove('expanded');
                        navbarHeader.classList.remove('expanded'); // Remover clase del navbar-header
                        toggleButtonDisplay();
                    }
                });
            }
        
            // Inicializar el display correcto del botón y logo al cargar la página
            toggleButtonDisplay();
            initializeLogoDisplay(); // Nueva función
        
            // El resto de tu código existente...
            getWallet();
            updateExpirationDateAndRestartCountdown();
        });
        
        // Nueva función para inicializar correctamente el logo
        function initializeLogoDisplay() {
            const sidebar = document.querySelector('.main-menu');
            const navbarHeader = document.querySelector('.main-menu .navbar-header');
            
            if (!sidebar || !navbarHeader) return;
            
            // Si el menú NO está colapsado al iniciar, mostrar logo expandido
            if (!sidebar.classList.contains('menu-collapsed')) {
                navbarHeader.classList.remove('expanded');
                // El CSS por defecto ya maneja esto
            }
        }
        
        function toggleButtonDisplay() {
            const sidebar = document.querySelector('.main-menu');
            const btnText = document.querySelector('#countdown .btn-text');
            const btnIcon = document.querySelector('#countdown .btn-icon');
            
            if (!sidebar || !btnText || !btnIcon) return;
            
            // Si el menú está colapsado y NO expandido (hover)
            if (sidebar.classList.contains('menu-collapsed') && !sidebar.classList.contains('expanded')) {
                btnText.style.display = 'none';
                btnIcon.style.display = 'flex';
                btnIcon.style.alignItems = 'center';
                btnIcon.style.justifyContent = 'center';
            } else {
                // Menú normal o expandido por hover
                btnText.style.display = 'block';
                btnIcon.style.display = 'none';
            }
        }

    </script>
@endif
