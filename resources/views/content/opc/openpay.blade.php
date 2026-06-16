@extends('layouts/registerLayout')
@section('title', 'Pago de producto')
@section('content')
    <input type="hidden" id="user_id" value="{{ $user_id }}">
    <input type="hidden" id="product_id" value="{{ $product_id }}">
    <input type="hidden" id="product_name" value="{{ $product_name }}">
    <input type="hidden" id="product_price" value="{{ $product_price }}">
    <input type="hidden" id="product_detail" value="{{ $product_detail }}">
    <input type="hidden" id="user_name" value="{{ $user_name }}">
    <input type="hidden" id="user_lastname" value="{{ $user_lastname }}">
    <input type="hidden" id="user_phone" value="{{ $user_phone }}">
    <input type="hidden" id="user_email" value="{{ $user_email }}">
    <input type="hidden" id="key_openpay" value="{{ $key_openpay }}">
    <input type="hidden" id="id_openpay" value="{{ $id_openpay }}">
@endsection

@section('page-script')
    <script>
        const user_id = document.getElementById("user_id").value;
        const product_id = document.getElementById("product_id").value;
        const product_name = document.getElementById("product_name").value;
        const product_price = document.getElementById("product_price").value;
        const product_detail = document.getElementById("product_detail").value;
        const user_name = document.getElementById("user_name").value;
        const user_lastname = document.getElementById("user_lastname").value;
        const user_phone = document.getElementById("user_phone").value;
        const user_email = document.getElementById("user_email").value;
        const key_openpay = document.getElementById("key_openpay").value;
        const id_openpay = document.getElementById("id_openpay").value;

        var order_id, order, charge_id;

        sendOpenpayData();

        //-----------functions------------
        async function generateOrderId() {
            await axios.post('/openpay-order').then((r) => {
                order_id = r.data
            });
            order = "promolider2023-" + order_id;
        }

        async function sendOpenpayData() {
            var myHeaders = new Headers();
            myHeaders.append("Content-Type", "application/json");
            let fechaFormateada = generateDatetime();

            var raw = JSON.stringify({
                "method": "card",
                "amount": product_price,
                "currency": "USD",
                "description": product_detail,
                "confirm": "false",
                "send_email": "false",
                "redirect_url": "{{ route('dashboard-ecommerce') }}?payment=success",
                "due_date": fechaFormateada,
                "customer": {
                    "name": user_name,
                    "last_name": user_lastname,
                    "phone_number": user_phone,
                    "email": user_email
                }
            });

            var requestOptions = {
                method: 'POST',
                headers: myHeaders,
                body: raw,
            };

            await fetch(`/pay/recharge`, requestOptions).then(r => r.json())
                .then(r => {
                    console.log(r)
                    charge_id = r.charge_id;
                    redirection = r.payment_url;
                });
            await storePaymentData();
            window.location.href = redirection;
        }

        function generateDatetime() {
            let fechaActual = new Date();
            let anio = fechaActual.getFullYear();
            let mes = (fechaActual.getMonth() + 1).toString().padStart(2, '0');
            let dia = fechaActual.getDate().toString().padStart(2, '0');
            let hora = fechaActual.getHours().toString().padStart(2, '0');
            let minuto = fechaActual.getMinutes().toString().padStart(2, '0');
            let segundo = fechaActual.getSeconds().toString().padStart(2, '0');
            let fechaFormateada = `${anio}-${mes}-${dia}T${hora}:${minuto}:${segundo}`;
            return fechaFormateada;
        }

        async function storePaymentData() {
            const formData = new FormData();
            // validar metodo de pago vacio y demas
            formData.append('product_id', product_id);
            formData.append('user_id', user_id);
            formData.append('openpay_order_id', charge_id);
            formData.append('product_name', product_name);
            formData.append('product_detail', product_detail);
            formData.append('product_price', product_price);

            await fetch('/unverified-payment/create', {
                    method: 'POST',
                    body: JSON.stringify(Object.fromEntries(formData)),
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                }).then(response => response.json())
                .catch(error => console.error(error));
        }
    </script>
@endsection
