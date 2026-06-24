<!-- filepath: c:\xampp\htdocs\programas\promolider-promolider-info\promolider-promolider-info\resources\views\emails\masterclass_registered.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Registro Masterclass Promolíder</title>
    <style>
        body { background: #f9f9f9; color: #35424a; font-family: 'Montserrat', Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #eee; padding: 0; }
        .header { background: #35424a; padding: 24px 0; border-radius: 8px 8px 0 0; }
        .logo { display: block; margin: 0 auto; max-width: 220px; }
        .content { padding: 32px 32px 0 32px; text-align: center; }
        .icon { margin: 24px auto; display: block; max-width: 120px; }
        .title { font-size: 20px; font-weight: bold; margin-bottom: 12px; color: #35424a; }
        .subtitle { font-size: 15px; color: #35424a; margin-bottom: 24px; }
        .box { background: #fff; border: 1px solid #ccc; border-radius: 8px; padding: 24px; margin: 24px auto; max-width: 400px; }
        .box-title { font-size: 18px; font-weight: bold; margin-bottom: 16px; color: #3e3e3e; }
        .meeting-link-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; padding: 24px; margin: 24px auto; max-width: 400px; text-align: center; }
        .meeting-link-title { font-size: 16px; font-weight: bold; color: #fff; margin-bottom: 12px; }
        .meeting-link-btn { display: inline-block; background: #fff; color: #667eea; padding: 12px 32px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 16px; margin-top: 8px; }
        .meeting-link-btn:hover { background: #f0f0f0; }
        .footer { background: #35424a; color: #fff; text-align: center; font-size: 14px; padding: 24px 0; border-radius: 0 0 8px 8px; margin-top: 32px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img class="logo" src="https://promolider-storage-user.s3-accelerate.amazonaws.com/images/promolider_logo.png" alt="Promolíder">
        </div>
        <div class="content">
            <img class="icon" src="https://promolider-storage-user.s3-accelerate.amazonaws.com/images/message-sent.png" alt="Registro exitoso">
            <div class="title">¡Masterclass Creada Exitosamente!</div>
            <div class="subtitle">
                Hola <strong>{{ $userName }}</strong>, tu masterclass ha sido registrada correctamente en <strong>PromoLíder</strong>.<br>
                Pronto recibirás más información sobre el proceso de aprobación.
            </div>
            <div class="box">
                <div class="box-title">Detalles de tu Masterclass:</div>
                <div style="text-align:left;">
                    <strong>Título:</strong> {{ $masterclassTitle }}<br>
                    <strong>Descripción:</strong> {{ $masterclassDescription }}<br>
                    <strong>Fecha:</strong> {{ $masterclassDate }}<br>
                    <strong>Hora:</strong> {{ $masterclassHour }}<br>
                    <strong>Duración:</strong> {{ $masterclassDuration }}<br>
                    <strong>Email de contacto:</strong> {{ $contactEmail }}<br>
                    <strong>Teléfono de contacto:</strong> {{ $contactPhone }}
                </div>
            </div>
        </div>
        <div class="footer">
            Promolíder<br>
            Av. La Fontana, 440 - C.C. La Rotonda II - Tda. 2086, La Molina, Lima 12, La Molina 12175<br>
            0414-3688809<br>
            &copy; {{ $year }} Promolíder, Todos los Derechos Reservados
        </div>
    </div>
</body>
</html>