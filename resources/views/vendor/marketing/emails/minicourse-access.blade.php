<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso a tu Mini Curso - Promolíder</title>
    <style>
        body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f8f9fa; color: #2c3e50; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); padding: 40px 30px; text-align: center; }
        .header h1 { color: #ffffff; font-weight: 300; font-size: 28px; margin: 16px 0 8px; }
        .header p { color: #bdc3c7; font-size: 16px; margin: 0; }
        .content { padding: 40px 30px; }
        .info-card { background: #f7fafc; border-left: 4px solid #6366f1; border-radius: 10px; padding: 24px; margin: 20px 0; }
        .alert-box { background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .btn { display: inline-block; background: #16a34a; color: #ffffff; text-decoration: none; padding: 16px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; text-align: center; margin: 20px 0; }
        .footer { background: #35424a; padding: 30px; text-align: center; }
        .footer p { color: #b0b1b4; font-size: 14px; margin: 4px 0; }
    </style>
</head>
<body>
    <table width="100%" cellpadding="0" cellspacing="0"><tr><td align="center">
        <div class="container">
            <div class="header">
                <img src="https://promolider-storage-user.s3-accelerate.amazonaws.com/images/promolider_logo.png" alt="Promolíder" width="160" style="max-width:160px">
                <h1>¡Inscripción Confirmada!</h1>
                <p>Tu lugar en el mini curso está asegurado</p>
            </div>
            <div class="content">
                <h2 style="margin:0 0 20px;font-size:22px;">¡Hola, {{ $userName }}! 🎉</h2>
                <p style="color:#4a5568;font-size:15px;line-height:1.6;margin:0 0 20px;">Te confirmamos que tu inscripción al mini curso ha sido exitosa. Estás a un paso de comenzar tu aprendizaje.</p>

                <div class="info-card">
                    <h2 style="color:#2d3748;font-size:20px;margin:0 0 12px;">{{ $courseTitle }}</h2>
                    <p style="color:#4a5568;font-size:14px;line-height:1.5;margin:0;">{{ $courseDescription }}</p>
                </div>

                <div class="alert-box">
                    <h3 style="color:#92400e;font-size:16px;margin:0 0 12px;">📋 Instrucciones de acceso</h3>
                    <p style="color:#92400e;font-size:14px;margin:4px 0;">• Haz clic en el botón verde para acceder inmediatamente</p>
                    <p style="color:#92400e;font-size:14px;margin:4px 0;">• El enlace es válido por 30 días</p>
                    <p style="color:#92400e;font-size:14px;margin:4px 0;">• Puedes entrar las veces que necesites</p>
                </div>

                <table width="100%" cellpadding="0" cellspacing="0"><tr><td align="center">
                    <a href="{{ $accessLink }}" class="btn" target="_blank">🚀 Acceder al Mini Curso</a>
                </td></tr></table>

                <div style="background:#f3f4f6;border-radius:6px;padding:15px;margin:20px 0;">
                    <p style="font-size:14px;color:#4b5563;margin:0 0 8px;"><strong>¿No funciona el botón?</strong> Copia y pega este enlace:</p>
                    <div style="background:#ffffff;padding:12px;border:1px solid #d1d5db;border-radius:6px;font-family:monospace;font-size:12px;color:#374151;word-break:break-all;">{{ $accessLink }}</div>
                </div>
            </div>
            <div class="footer">
                <p style="font-size:18px;font-weight:700;color:#ffffff;">Promolíder</p>
                <p>Av. La Fontana, 440 - C.C. La Rotonda II, La Molina, Lima</p>
                <p>0414-3688809</p>
            </div>
        </div>
    </td></tr></table>
</body>
</html>
