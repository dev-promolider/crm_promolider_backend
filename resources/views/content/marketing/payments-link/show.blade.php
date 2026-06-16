<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $paymentLink->name }} - Pago</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding-top: 40px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .btn-pay {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 25px;
            padding: 12px 40px;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card p-4">
                <div class="text-center mb-4">
                    <h2 class="text-primary">{{ $paymentLink->name }}</h2>
                    <p class="text-muted">Completa tu pago de forma segura</p>
                </div>

                <div class="row">
                    <div class="col-md-7">
                        <h5 class="mb-3">📋 Detalles del Pago</h5>
                        <div class="bg-light p-3 rounded mb-4">
                            <p class="mb-0"><strong>Descripción:</strong></p>
                            <p class="text-muted mb-0">{{ $paymentLink->description }}</p>
                        </div>

                        <h6 class="mb-3">👤 Tus Datos</h6>
                        <div class="form-group">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input type="text" id="buyer_name" class="form-control" placeholder="Tu nombre" required>
                        </div>
                        <div class="form-group">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" id="buyer_email" class="form-control" placeholder="tu@email.com" required>
                        </div>

                    </div>

                    <div class="col-md-5">
                        <div class="card bg-dark text-white text-center p-4">
                            <h6 class="text-uppercase mb-3">💰 Resumen de Pago</h6>
                            <hr class="bg-light">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Monto:</span>
                                <span>${{ number_format($paymentLink->amount, 2) }}</span>
                            </div>
                            <hr class="bg-light">
                            <div class="d-flex justify-content-between">
                                <strong class="h6">Total a Pagar:</strong>
                                <strong class="h4 text-success">${{ number_format($paymentLink->amount, 2) }} USD</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="errorDiv" class="alert alert-danger mt-3" style="display:none;"></div>

                <div class="text-center mt-4">
                    <button id="payButton" class="btn btn-pay btn-lg text-white">
                        <i class="fas fa-credit-card"></i> Pagar con Tarjeta
                    </button>
                    <p class="text-muted mt-3 small">🔒 Pago seguro procesado por OpenPay</p>
                </div>

                <div id="loadingDiv" class="text-center mt-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-primary">Redirigiendo a la pasarela de pago...</p>
                    <p class="text-muted small">Por favor, espere mientras procesamos su solicitud</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<script>
document.getElementById('payButton').addEventListener('click', function() {
    const name  = document.getElementById('buyer_name').value.trim();
    const email = document.getElementById('buyer_email').value.trim();
    const phone = '';
    const errorDiv = document.getElementById('errorDiv');

    errorDiv.style.display = 'none';

    if (!name) {
        errorDiv.textContent = 'Por favor ingresa tu nombre.';
        errorDiv.style.display = 'block';
        return;
    }
    if (!email || !email.includes('@')) {
        errorDiv.textContent = 'Por favor ingresa un email válido.';
        errorDiv.style.display = 'block';
        return;
    }

    this.disabled = true;
    this.style.display = 'none';
    document.getElementById('loadingDiv').style.display = 'block';

    sendOpenpayData(name, email, phone);
});

async function sendOpenpayData(name, email, phone) {
    try {
        let fechaFormateada = generateDatetime();

        const raw = JSON.stringify({
            "method": "card",
            "amount": "{{ $paymentLink->amount }}",
            "currency": "USD",
            "description": "{{ $paymentLink->description }}",
            "confirm": "false",
            "send_email": "false",
            "redirect_url": "{{ env('APP_URL') }}payment-success",
            "due_date": fechaFormateada,
            "customer": {
                "name": name,
                "last_name": "",
                "phone_number": phone,
                "email": email
            },
            "payment_link_id": "{{ $paymentLink->id }}",
            "product_name": "{{ $paymentLink->name }}"
        });

        const response = await fetch('/payment-links/pay/recharge', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: raw
        });

        const data = await response.json();

        if (data.success && data.payment_url) {
            window.location.href = data.payment_url;
        } else {
            throw new Error(data.error || 'Error al procesar el pago');
        }

    } catch (error) {
        const errorDiv = document.getElementById('errorDiv');
        errorDiv.textContent = 'Error: ' + error.message;
        errorDiv.style.display = 'block';
        document.getElementById('payButton').disabled = false;
        document.getElementById('payButton').style.display = 'inline-block';
        document.getElementById('loadingDiv').style.display = 'none';
    }
}

function generateDatetime() {
    let d = new Date();
    return d.getFullYear() + '-' +
        String(d.getMonth()+1).padStart(2,'0') + '-' +
        String(d.getDate()).padStart(2,'0') + 'T' +
        String(d.getHours()).padStart(2,'0') + ':' +
        String(d.getMinutes()).padStart(2,'0') + ':' +
        String(d.getSeconds()).padStart(2,'0');
}
</script>
</body>
</html>
