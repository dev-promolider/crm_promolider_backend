@extends('layouts/registerLayout')
@section('title', 'User Membreship - Register')
@section('content')
    <script
        src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.client_id') }}&currency=USD&locale=es_PE">
    </script>
    <div class="container h-100 px-1">
        <div class="row h-100">
            <div class="col-2 d-none d-md-block"></div>
            <div class="col-12 col-md-5 py-5">
                <h1>Pagar Membresia</h1>
                <h3 class="mt-3"> Método de pago</h3>
                <div class="row px-1 mt-2">
                    <div class="col-12">
                        <div id="paypal-button-container"></div>
                    </div>
                    <div class="col-12 mt-2 text-center d-none" id="loader">
                        <span class="spinner-border spinner-border-sm"></span>
                        <p class="mt-1">Procesando su compra..</p>
                    </div>
                    <div class="col-12">
                        <button type="button" class="btn btn-primary d-none" id="btn-modal-success" data-toggle="modal"
                            data-target="#payment-success">
                            <button type="button" class="btn btn-primary d-none" id="btn-modal-error" data-toggle="modal"
                                data-target="#payment-error">
                            </button>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 px-3 bg-light">
                <div class="row">
                    <div class="col-12">
                        <div class="row bg-buy">
                            <div class="col-12">
                                <h3 class="mt-5 pt-1">Resumen</h3>
                            </div>
                            <div class="col-12 mt-1 ">
                                <p class="d-inline-block">Precio origial:</p>
                                <p class="float-right">$ {{ $data[0]->price }}</p>
                            </div>
                            <div class="col-12">
                                <p class="d-inline-block">IGV:</p>
                                <p class="float-right">{{ $data[0]->iva }} %</p>
                                <hr style="border-color: #D1D7DC" />
                            </div>
                            <div class="col-12">
                                <p class="d-inline-block">Total a pagar:</p>
                                <p class="float-right">$ {{ $data[0]->total }}</p>
                            </div>
                            <div class="col-12" id="pagar-paypal">
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <p class="mt-2">Al completar la compra, aceptas estas Condiciones de uso.</p>
                        <p class="my-2">Promolider está obligado por ley a recaudar los impuestos sobre las
                            transacciones de las compras realizadas en determinadas jurisdicciones fiscales.</p>
                    </div>
                    <div class="col-12">
                        <h4 class="mt-2">Resumen de la compra</h4>
                        <div class="row">
                            <div class="col-sm-2 col-md-12 col-lg-2 col-xl-2 col-2">
                                <img src="https://cdn-icons-png.flaticon.com/512/51/51777.png" alt="logo_membership"
                                    width="40" height="auto">
                            </div>
                            <div class="col-sm-7 col-md-12 col-lg-10 col-xl-7 col-7 mmin">
                                <h5>Membresia: {{ $data[0]->account }}</h5>
                            </div>
                            <div class="col-sm-3 col-md-12 col-lg-12 col-xl-3 col-3 mmin">
                                <p>$ {{ $data[0]->price }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-1 d-none d-md-block bg-light">
            </div>
        </div>
        <div class="modal fade" id="payment-success">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-body">
                        <div class="text-center my-3">
                            <h1 class="mb-2 text-primary">Pago Realizado</h1>
                            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="96" height="96"
                                viewBox="0 0 172 172" style=" fill:#000000;">
                                <g fill="none" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt"
                                    stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0"
                                    font-family="none" font-weight="none" font-size="none" text-anchor="none"
                                    style="mix-blend-mode: normal">
                                    <path d="M0,172v-172h172v172z" fill="none"></path>
                                    <g fill="#1abc9c">
                                        <path
                                            d="M86,14.33333c-39.517,0 -71.66667,32.14967 -71.66667,71.66667c0,39.517 32.14967,71.66667 71.66667,71.66667c39.517,0 71.66667,-32.14967 71.66667,-71.66667c0,-8.06967 -1.40478,-15.80821 -3.87728,-23.05371l-11.60384,11.60384c0.7525,3.698 1.14778,7.5297 1.14778,11.44987c0,31.61217 -25.72117,57.33333 -57.33333,57.33333c-31.61217,0 -57.33333,-25.72117 -57.33333,-57.33333c0,-31.61217 25.72117,-57.33333 57.33333,-57.33333c11.70317,0 22.58877,3.53955 31.67611,9.58822l10.26009,-10.26009c-11.81067,-8.557 -26.27703,-13.66146 -41.93619,-13.66146zM152.59961,23.59961l-73.76628,73.76628l-23.59961,-23.59961l-10.13411,10.13411l33.73372,33.73372l83.90039,-83.90039z">
                                        </path>
                                    </g>
                                </g>
                            </svg>

                        </div>
                        <div class="row mt-2">
                            <div class="col-4 text-center">
                                <p>Codigo de pago</p>
                            </div>
                            <div class="col-7">
                                <p id="cod_pago"></p>
                            </div>
                            <div class="col-4 text-center">
                                <p>Estado del pago</p>
                            </div>
                            <div class="col-7">
                                <p id="tipo_pago"></p>
                            </div>
                            <div class="col-4 text-center">
                                <p>Email</p>
                            </div>
                            <div class="col-7">
                                <p id="email"></p>
                            </div>
                            <div class="col-4 text-center">
                                <p><b> Monto pagado</b></p>
                            </div>
                            <div class="col-7">
                                <p><b id="monto"></b></p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        @php
                            $message = 'El usuario se registró correctamente';
                        @endphp
                        <a href="{{ route('redirect-with-message', ['message' => $message]) }}" type="button"
                            class="btn btn-primary">Ir al inicio</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="payment-error">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-body">
                        <div class="text-center my-3">
                            <h1 class="mb-2 text-danger">Pago Fallido</h1>
                            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="96" height="96"
                                viewBox="0 0 172 172" style=" fill:#000000;">
                                <g fill="none" fill-rule="nonzero" stroke="none" stroke-width="1"
                                    stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10"
                                    stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none"
                                    font-size="none" text-anchor="none" style="mix-blend-mode: normal">
                                    <path d="M0,172v-172h172v172z" fill="none"></path>
                                    <g fill="#e74c3c">
                                        <path
                                            d="M86,14.33333c-39.49552,0 -71.66667,32.17115 -71.66667,71.66667c0,39.49552 32.17115,71.66667 71.66667,71.66667c39.49552,0 71.66667,-32.17115 71.66667,-71.66667c0,-39.49552 -32.17115,-71.66667 -71.66667,-71.66667zM86,28.66667c31.74921,0 57.33333,25.58412 57.33333,57.33333c0,31.74921 -25.58412,57.33333 -57.33333,57.33333c-31.74921,0 -57.33333,-25.58412 -57.33333,-57.33333c0,-31.74921 25.58412,-57.33333 57.33333,-57.33333zM62.40039,52.26628l-10.13411,10.13411l23.59961,23.59961l-23.59961,23.59961l10.13411,10.13411l23.59961,-23.59961l23.59961,23.59961l10.13411,-10.13411l-23.59961,-23.59961l23.59961,-23.59961l-10.13411,-10.13411l-23.59961,23.59961z">
                                        </path>
                                    </g>
                                </g>
                            </svg>

                        </div>
                        <div>
                            <div class="text-center">
                                <p>Verifique los datos de su tarjeta y su credito</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-dismiss="modal">Volver a
                                intentarlo</button>
                        </div>
                    </div>
                </div>
            </div>
            <style>
                body {
                    background: white !important;
                }

                h1,
                h2,
                h3,
                h4,
                h5 {
                    color: black !important;
                    font-weight: bold;
                }

                .bg-buy {
                    position: sticky;
                    top: 0;
                }

                .mmin {
                    margin-top: 0.7rem;
                }

                .new-color {
                    color: black !important;
                    font-weight: bold;
                }

                .mmin2 {
                    margin-bottom: 0.5rem
                }

                .form-control {
                    color: black;
                    border: 1px solid black;
                }

                .spinner-border-sm {
                    width: 4rem;
                    height: 4rem;
                }
            </style>

        @endsection
        @section('page-script')
            <script>
                function asc_datos(data) {
                    document.getElementById('cod_pago').innerHTML = data.id;
                    document.getElementById('tipo_pago').innerHTML = data.status
                    document.getElementById('email').innerHTML = data.payer.email_address;
                    document.getElementById('monto').innerHTML = '$ ' + data.purchase_units[0].amount.value;
                    document.getElementById('btn-modal-success').click();
                }

                function error_paypal() {
                    document.getElementById('btn-modal-error').click();
                }
            </script>
            <script>
                loader = document.getElementById('loader');
                paypal.Buttons({
                    createOrder: function(data, actions) {
                        return actions.order.create({
                            application_context: {
                                shipping_preference: "NO_SHIPPING"
                            },
                            payer: {
                                email_address: "{{ $data[1]['email'] }}",
                                name: {
                                    given_name: "{{ $data[1]['name'] }}",
                                    surname: "{{ $data[1]['last_name'] }}"
                                },
                            },
                            purchase_units: [{
                                amount: {
                                    currency_code: "USD",
                                    value: {{ $data[0]->total }}
                                }
                            }]
                        });
                    },
                    onApprove: (data, actions) => {
                        loader.classList.remove('d-none');
                        return fetch('/pay/membership/process/' + data.orderID)
                            .then(res => res.json())
                            .then(function(response) {
                                console.log(response)
                                if (response.success) {
                                    loader.classList.add('d-none');
                                    asc_datos(response.data);
                                } else {
                                    loader.classList.add('d-none');
                                    error_paypal();
                                }
                            })
                    },
                    onError: function(err) {
                        console.log(err);
                        error_paypal();
                    }
                }).render('#paypal-button-container');
            </script>
        @endsection
