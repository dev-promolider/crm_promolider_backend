@extends('layouts/registerLayout')
@section('title', 'User Membreship - Register')
@section('content')

    @include('partials/navbar')

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css"
        integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.0.272/jspdf.debug.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.0/moment.min.js"></script>
    <link href="https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>

    <div class="container mt-3">
        <div class="container-fluid section-to-print" id="section-to-print" style="padding: 0!important;">
            <div class="card">
                <div class="card-body">
                    <div id="invoice">
                        <div class="invoice overflow-auto">
                            <div>
                                <!-- Cabecera -->
                                <header>
                                    <div class="row">
                                        <div class="col">
                                            <a href="javascript:;">
                                                <img src="{{ asset('images/logo/dark_logo.png') }}" width="200"
                                                    alt="Logo de promolíder">
                                            </a>
                                        </div>
                                        <div class="col company-details">
                                            <div>Av. La Fontana, 440 Lima, Perú</div>
                                            <div>+51 995 668 600</div>
                                            <div>promoliderorg@gmail.com</div>
                                        </div>
                                    </div>
                                </header>

                                <!-- Loader-->
                                <div class="container loading-skeleton" id="loader">
                                    <div class="row">
                                        <div class="offset-md-4 col-md-4">
                                            <div class="form-group">
                                                <input type="text" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <div style="min-width: 32%">

                                            <input type="text" class="form-control">
                                        </div>
                                        <div style="min-width: 32%">
                                            <input type="text" class="form-control">

                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <div style="min-width: 42%">

                                            <input type="text" class="form-control">
                                        </div>
                                        <div style="min-width: 38%">
                                            <input type="text" class="form-control">

                                        </div>
                                    </div>

                                    <form class="mt-3">

                                        <div class="form-group">
                                            <textarea class="form-control my-2" style="resize:none" rows="2"></textarea>
                                            <textarea class="form-control my-2" style="resize:none" rows="2"></textarea>
                                            <textarea class="form-control my-2" style="resize:none" rows="2"></textarea>
                                        </div>
                                    </form>
                                </div>

                                <div id="header" style="display: none;">
                                    <div class="row mb-2">
                                        <div class="col text-center">
                                            <p id="status" class="font-weight-bold"></p>
                                        </div>
                                    </div>
                                    <!-- Cuerpo -->
                                    <div class="d-flex justify-content-between mb-2">
                                        <div>COMPROBANTE DE PAGO: <span id="purchaseNumber"></span></div>
                                        <div><strong>Fecha de pago : </strong> <span id="payment_date"></span></div>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <div class="text-gray-light text-uppercase">Tarjeta: <span id="brand"></span>
                                        </div>
                                        <div class="text-gray-light">N° <span id="card"></span></div>
                                    </div>
                                </div>
                            </div>

                            <table class="table table-sm mt-3" id="table" style="display: none;">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th scope="col" class="text-left"><strong>DESCRIPCION</strong></th>
                                        <th scope="col" class="text-right"><strong>PRECIO</strong></th>
                                        <th scope="col" class="text-right"><strong>CANTIDAD</strong></th>
                                        <th scope="col" class="text-right"><strong>Subtotal</strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="no">1</td>
                                        <td class="text-left">
                                            <h3>Membresía {{ $membership }}</h3>
                                        </td>
                                        <td class="unit">$ <span id="unit_price"> </td>
                                        <td class="qty"> 1 </td>
                                        <td class="total">$ <span id="amount"> </span></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td colspan="2">TOTAL</td>
                                        <td>$ <span id="total_amount"> </span></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <div class="alert alert-warning mt-2" role="alert" id="success" style="display: none;">
                                Asegúrese de descargar y guardar el archivo
                            </div>
                            <div class="alert alert-danger mt-2" role="alert" id="error" style="display: none;">
                                Ha ocurrido un error, por favor intente nuevamente
                            </div>
                        </div>

                        <div class="toolbar hidden-print" id="print_button" style="display: none;">
                            <div class="row">
                                <div class="offset-md-6 col-md-6 col-sm-6" style="text-align: right;">
                                    <button type="button" class="btn btn-danger" onclick="download()"><i
                                            class="fa fa-file-pdf fa-lg"></i> Descargar como PDF</button>
                                </div>
                            </div>
                        </div>
                        <hr>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials/footer')
    <style>
        #invoice {
            padding: 0px;
        }

        .invoice {
            position: relative;
            background-color: #FFF;
            min-height: 500px;
            padding: 15px
        }

        .invoice header {
            padding: 10px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #1ae600
        }

        .invoice .company-details {
            text-align: right
        }

        .invoice .company-details .name {
            margin-top: 0;
            margin-bottom: 0
        }

        .invoice .contacts {
            margin-bottom: 20px
        }

        .invoice .invoice-to {
            text-align: left
        }

        .invoice .invoice-to .to {
            margin-top: 0;
            margin-bottom: 0
        }

        .invoice .invoice-details {
            text-align: right
        }

        .invoice .invoice-details .invoice-id {
            margin-top: 0;
            color: #1ae600
        }

        .invoice main {
            padding-bottom: 50px
        }

        .invoice main .thanks {
            margin-top: -100px;
            font-size: 2em;
            margin-bottom: 50px
        }

        .invoice main .notices {
            padding-left: 6px;
            border-left: 6px solid #ff9800;
            background: #e7f2ff;
            padding: 10px;
        }

        .invoice main .notices .notice {
            font-size: 1.2em
        }

        .invoice table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            margin-bottom: 20px
        }

        .invoice table td,
        .invoice table th {
            padding: 15px;
            background: #eee;
            border-bottom: 1px solid #fff
        }

        .invoice table th {
            white-space: nowrap;
            font-weight: 400;
            font-size: 16px
        }

        .invoice table td h3 {
            margin: 0;
            font-weight: 400;
            color: #1ae600;
            font-size: 1.2em
        }

        .invoice table .qty,
        .invoice table .total,
        .invoice table .unit {
            text-align: right;
            font-size: 1.2em
        }

        .invoice table .no {
            color: #fff;
            font-size: 1.6em;
            background: #1ae600
        }

        .invoice table .unit {
            background: #ddd
        }

        .invoice table .total {
            background: #1ae600;
            color: #fff
        }

        .invoice table tbody tr:last-child td {
            border: none
        }

        .invoice table tfoot td {
            background: 0 0;
            border-bottom: none;
            white-space: nowrap;
            text-align: right;
            padding: 10px 20px;
            font-size: 1.2em;
            border-top: 1px solid #aaa
        }

        .invoice table tfoot tr:first-child td {
            border-top: none
        }

        .card {
            position: relative;
            display: flex;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
            background-color: #fff;
            background-clip: border-box;
            border: 0px solid rgba(0, 0, 0, 0);
            border-radius: .25rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 6px 0 rgb(218 218 253 / 65%), 0 2px 6px 0 rgb(206 206 238 / 54%);
        }

        .invoice table tfoot tr:last-child td {
            color: #1ae600;
            font-size: 1.4em;
            border-top: 1px solid #1ae600
        }

        .invoice table tfoot tr td:first-child {
            border: none
        }

        .invoice footer {
            width: 100%;
            text-align: center;
            color: #777;
            border-top: 1px solid #aaa;
            padding: 8px 0
        }

        .loading-skeleton h1,
        .loading-skeleton h2,
        .loading-skeleton h3,
        .loading-skeleton h4,
        .loading-skeleton h5,
        .loading-skeleton h6,
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

        .loading-skeleton h1::placeholder,
        .loading-skeleton h2::placeholder,
        .loading-skeleton h3::placeholder,
        .loading-skeleton h4::placeholder,
        .loading-skeleton h5::placeholder,
        .loading-skeleton h6::placeholder,
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

        .loading-skeleton img {
            filter: grayscale(100) contrast(0%) brightness(1.8);
        }
    </style>
@endsection
@section('page-script')
    <script>
        let section = document.getElementById('section-to-print');
        let nota = document.getElementById('success');

        function download() {
            let purchase_number = document.getElementById('purchaseNumber').innerHTML;
            nota.style.display = "none";
            let pdf = new jsPDF('l', 'pt', 'a4');
            pdf.addHTML(section, function() {
                pdf.save(`COMPROBANTE_PROMOLIDER_${purchase_number}.pdf`);
            });
            nota.style.display = "block";
        }

        window.addEventListener("load", function(event) {
            authorize();
        });

        async function authorize() {
            let purchase_data = {!! json_encode($purchase_data) !!};
            let access_token = {!! json_encode($access_token) !!};
            let data = {!! json_encode($data) !!};

            // Prod
            // const MERCHANT_ID = "650216791";
            // const API_URL = `https://apiprod.vnforapps.com/api.authorization/v3/authorization/ecommerce/${MERCHANT_ID}`;

            // Test
            const MERCHANT_ID = "456879853";
            const API_URL =
                `https://apisandbox.vnforappstest.com/api.authorization/v3/authorization/ecommerce/${MERCHANT_ID}`;

            const response = await fetch(API_URL, {
                method: 'POST',
                body: JSON.stringify(purchase_data),
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': access_token
                },
            })

            if (response.status == 200) {
                let {
                    dataMap,
                    fulfillment,
                    header,
                    order
                } = await response.json();
                document.getElementById('loader').style.display = "none";
                document.getElementById('header').style.display = "block";
                document.getElementById('table').style.display = "table";
                document.getElementById('success').style.display = "block";
                document.getElementById('print_button').style.display = "block";

                let dateTime = moment(header.ecoreTransactionDate).format("DD MMM YYYY hh:mm a");
                document.getElementById('payment_date').innerHTML = dateTime;
                document.getElementById('purchaseNumber').innerHTML = order.purchaseNumber;
                document.getElementById('amount').innerHTML = order.amount.toFixed(2);;
                document.getElementById('unit_price').innerHTML = order.amount.toFixed(2);
                document.getElementById('total_amount').innerHTML = order.amount.toFixed(2);

                document.getElementById('brand').innerHTML = dataMap.BRAND;
                document.getElementById('card').innerHTML = dataMap.CARD;
                document.getElementById('status').innerHTML = dataMap.ACTION_DESCRIPTION;
                // Registrar usuario
                if (dataMap.STATUS == "Authorized") {
                    const request = await fetch('niubiz/process', {
                        method: 'POST',
                        body: JSON.stringify({
                            'purchase_data': purchase_data,
                            'order': order,
                        }),
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                    })

                    if (request.status == 200) {
                        Swal.fire(
                            'Transacción exitosa',
                            'Se ha registrado tu compra',
                            'success'
                        )
                    } else {
                        Swal.fire(
                            'Fallo al crear usuario',
                            'Comuníquese con el administrador para solventar sus dudas',
                            'error'
                        )
                    }

                } else {
                    alert(data.dataMap.STATUS);
                }
            } else if (response.status == 401) {
                alert("Too many request with same payload ");
            } else if (response.status == 400) {
                let {
                    data,
                    header
                } = await response.json();
                let dateTime = moment(header.ecoreTransactionDate).format("DD MMM YYYY hh:mm a");
                document.getElementById('payment_date').innerHTML = dateTime;
                document.getElementById('status').innerHTML = data.ACTION_DESCRIPTION;
                document.getElementById('purchaseNumber').innerHTML = purchase_data.order["purchaseNumber"];
                document.getElementById('brand').innerHTML = data.BRAND;
                document.getElementById('card').innerHTML = data.CARD

                document.getElementById('loader').style.display = "none";
                document.getElementById('header').style.display = "block";
                document.getElementById('error').style.display = "block";
            }
        }
    </script>
@endsection
