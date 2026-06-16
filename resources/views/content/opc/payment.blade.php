@extends('layouts/registerLayout')
@section('title', 'Pago de OPC')
@section('content')

@include('partials/navbar')

<div class="container  px-1">
<input type="hidden" id="ip_address" value="{{$ip}}">
<input type="hidden" id="purchase_number" value="{{$purchase_number}}">
    <div class="row px-2 py-2">
        <div class="col-md-6 col-lg-7 col-sm-12">
            <h2>Finaliza tu compra</h2>

            <div class="card mt-1">
                <div class="card-body">
                    <h5 class="card-title">Costo operacional (OPC) </h5>
                    <p class="card-text">
                    <div id="show2" style="display: none;">
                        <ul>
                            <li>{{$product->descripcion}}</li>
                        </ul>
                    </div>
                    <div id="hide2">
                        <div class="loading-skeleton">
                            <p>.</p>
                        </div>
                        <div class="loading-skeleton">
                            <p>.</p>
                        </div>
                        <div class="loading-skeleton">
                            <p>.</p>
                        </div>
                        <div class="loading-skeleton">
                            <p>.</p>
                        </div>
                        <div class="loading-skeleton">
                            <p>.</p>
                        </div>
                        <div class="loading-skeleton">
                            <p>.</p>
                        </div>
                        <div class="loading-skeleton">
                            <p>.</p>
                        </div>
                    </div>

                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-5 col-sm-12">
            <h2>Resumen de compra</h2>
            <div class="card  mt-1">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-9">
                            <div id="hide">
                                <div class="loading-skeleton">
                                    <p>.</p>
                                </div>
                                <div class="loading-skeleton">
                                    <p>.</p>
                                </div>
                                <div class="loading-skeleton">
                                    <p>.</p>
                                </div>
                                <div class="loading-skeleton">
                                    <p>.</p>
                                </div>
                                <div class="loading-skeleton">
                                    <p>.</p>
                                </div>
                                <div class="loading-skeleton">
                                    <p>.</p>
                                </div>
                                <div class="loading-skeleton">
                                    <p>.</p>
                                </div>
                            </div>
                            <div style="display: none;" id="show">
                                <p>Costo operacional: ${{$product->price}}</p>
                                <p>IGV : ${{$igv}}</p>
                     
                            </div>

                        </div>
                    </div>
                    <hr class="mt-2" />
                    <div class="d-flex justify-content-between total_price">
                        <div>
                            Total
                        </div>
                        <div id="show3" style="display: none;">
                            $ {{$total}}
                        </div>
                        <div class="loading-skeleton" id="hide3" style="min-width: 100px">
                            <p>.</p>
                        </div>

                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6 col-lg-7 text-left">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input " id="terms" required>
                                <a href="/terms-and-conditions" target="_blank" class="form-check-label btn-link" for="terms">Acepto los términos y condiciones</a>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-5 text-right">
                            <div class="spinner-border mr-5 " role="status" id="loading">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <div id="niubiz_container"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


@include('partials/footer')
<style>
    .total_price {
        font-size: 1.5em;
        font-weight: bold;
    }

    .loading-skeleton p,
    .loading-skeleton li,
    .loading-skeleton .btn,
    .loading-skeleton label,
    .loading-skeleton .form-control {
        color: transparent;
        appearance: none;
        -webkit-appearance: none;
        background-color: #eee;
        border-color: #eee;
    }

    .loading-skeleton p::placeholder,
    .loading-skeleton li::placeholder,
    .loading-skeleton .btn::placeholder,
    .loading-skeleton label::placeholder,
    .loading-skeleton .form-control::placeholder {
        color: transparent;
    }

    @keyframes loading-skeleton {
        from {
            opacity: 0.4;
        }

        to {
            opacity: 1;
        }
    }

    .loading-skeleton {
        pointer-events: none;
        animation: loading-skeleton 1s infinite alternate;
    }
</style>

@section('page-script')
<script>
    const email = "{{$user["email"]}}";
    window.onload = function() {
        createNiubizSessionToken({{$total}});
    }

    const niubiz_container = document.getElementById('niubiz_container');
    const niubiz_form = document.createElement('form');

    const ip = document.getElementById('ip_address').value;
    const purchase_number = document.getElementById('purchase_number').value;

    // 1 step we need to create an access token
    async function getAccessToken() {
        const request = await fetch('/get-different-access-token')
        let text = await request.text();
        return text;
    }

    // 2 step create a session token
    async function createNiubizSessionToken(amount) {
        let access_token = await getAccessToken();
        // Prod
        // const MERCHANT_ID = "650216791";
        // const API_URL = `https://apiprod.vnforapps.com/api.ecommerce/v2/ecommerce/token/session/${MERCHANT_ID}`;

        // Test
        const MERCHANT_ID = "456879853"; // Tesst
        const API_URL = `https://apisandbox.vnforappstest.com/api.ecommerce/v2/ecommerce/token/session/${MERCHANT_ID}`;
        let form = {
            "channel": "web",
            "amount": amount,
            "antifraud": {
                "clientIp": `${ip}`,
                "merchantDefineData": {
                    "MDD4": `${email}`,
                    "MDD21": "0",
                    "MDD32": `${purchase_number}`,
                    "MDD75": "Invitado",
                    "MDD77": "1"
                }
            },
            'access_token': access_token
        }

        const response = await fetch(API_URL, {
            method: 'POST',
            body: JSON.stringify(form),
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': access_token
            },
        });

        const data = await response.json();
        createNiubizScript(data, form, MERCHANT_ID, ip);
    }
    // 3 step we need to create a script to configure the button, then we are going to be redirect to /autorizeTransacion route
    function createNiubizScript(data, form, merchant_id) {
        // Crear un registro con la ip del usuario y el transaction id con todos los datos del post

        // const URL = 'https://static-content.vnforapps.com/v2/js/checkout.js'; // Prod
        const URL = 'https://static-content-qas.vnforapps.com/v2/js/checkout.js?qa=true'; // Testing

        niubiz_form.method = "post";
        niubiz_form.action = '/authorizeopc';
        niubiz_form.id = 'niubiz_form';

        niubiz_container.appendChild(niubiz_form);
        let script = document.createElement('script');
        script.id = 'script_niubiz';

        // Button data
        script.setAttribute('type', 'text/javascript');
        script.setAttribute('src', URL);
        script.setAttribute('data-sessiontoken', data.sessionKey);
        script.setAttribute('data-expirationminutes', 5);
        // falta configurar cuando expira el tiempo de la sesion
        script.setAttribute('data-timeouturl', 'https://www.promolider.org');
        script.setAttribute('data-channel', form.channel);
        script.setAttribute('data-merchantid', merchant_id);
        script.setAttribute('data-purchasenumber', purchase_number);
        script.setAttribute('data-amount', form.amount);

        // Merchant info
        script.setAttribute('data-merchantDescription', 'Promolider - Suscripción');
        script.setAttribute('data-merchantContact', '+051 995668600');
        script.setAttribute('data-merchantEmail', 'promoliderorg@gmail.com');
        script.setAttribute('data-merchantPhone', '+51 995 668 600');
        script.setAttribute('data-merchantAddress', 'Av. La Fontana 440');
        script.setAttribute('data-merchantCity', 'Lima');
        script.setAttribute('data-merchantState', 'Lima');
        script.setAttribute('data-merchantCountry', 'Perú');
        script.setAttribute('data-merchantZipCode', '51');
        script.setAttribute('data-merchantCurrency', 'USD');
        // Custom button
        script.setAttribute('data-merchantLanguage', 'es');
        script.setAttribute('data-buttonsize', 'MEDIUM');
        script.setAttribute('data-merchantlogo', 'https://promolider.org/global_images/dark_logo.png');
        script.setAttribute('data-merchantName', 'Promolíder');
        script.setAttribute('data-buttoncolor', '#1ae600');
        script.setAttribute('data-formbuttoncolor', '#1ae600');
        script.setAttribute('data-showamount', 'true');
        niubiz_form.appendChild(script);
        document.getElementById('loading').style.display = 'none';
        document.getElementById('show').style.display = 'inline-block';
        document.getElementById('show2').style.display = 'inline-block';
        document.getElementById('show3').style.display = 'inline-block';
        document.getElementById('hide').style.display = 'none';
        document.getElementById('hide2').style.display = 'none';
        document.getElementById('hide3').style.display = 'none';
    }
</script>
@endsection