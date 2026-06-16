{{-- resources/views/emails/test-template.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correo de Prueba - Promolider</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #007cba; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .success { color: #28a745; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Promolider - Sistema de Email</h1>
        </div>
        <div class="content">
            <h2 class="success">✅ Correo de Prueba Exitoso</h2>
            <p>¡Hola!</p>
            <p>Este es un correo de prueba enviado desde el sistema PHPMailer de <strong>Promolider</strong>.</p>
            
            <div style="background-color: white; padding: 15px; border-left: 4px solid #007cba; margin: 20px 0;">
                <p><strong>Detalles del envío:</strong></p>
                <ul>
                    <li><strong>Email destino:</strong> {{ $email }}</li>
                    <li><strong>Fecha y hora:</strong> {{ $timestamp }}</li>
                    <li><strong>Servidor:</strong> {{ env('MAIL_HOST') }}</li>
                    <li><strong>Estado:</strong> <span class="success">Enviado correctamente</span></li>
                </ul>
            </div>
            
            <p>Si recibiste este correo, significa que el sistema de envío está funcionando correctamente.</p>
            
            <p>¡Gracias por usar nuestro sistema!</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Promolider. Todos los derechos reservados.</p>
            <p>Este es un correo automático generado por el sistema.</p>
        </div>
    </div>
</body>
</html>