<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Inscripción - Masterclass</title>
    <style>
        body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f8f9fa; color: #2c3e50; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); padding: 40px 30px; text-align: center; }
        .header h1 { color: #ffffff; font-weight: 300; font-size: 28px; margin: 16px 0 8px; }
        .header p { color: #bdc3c7; font-size: 16px; margin: 0; }
        .content { padding: 40px 30px; }
        .badge { display: inline-block; background: #e8f5e9; color: #0a5c01; padding: 8px 20px; border-radius: 20px; font-weight: 600; font-size: 14px; margin-bottom: 24px; }
        .details { background: #f8f9fa; border-radius: 12px; padding: 24px; margin: 20px 0; }
        .detail-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #e9ecef; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #6c757d; font-size: 14px; }
        .detail-value { color: #2c3e50; font-size: 14px; font-weight: 600; }
        .btn { display: inline-block; background: linear-gradient(135deg, #1ce501 0%, #17b801 100%); color: #ffffff; text-decoration: none; padding: 16px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; text-align: center; margin: 20px 0; }
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
                <p>Tu lugar en la masterclass está asegurado</p>
            </div>
            <div class="content">
                <h2 style="margin:0 0 8px;font-size:22px;">¡Hola, {{ $userName }}! 🎉</h2>
                <p style="color:#6c757d;font-size:16px;line-height:1.6;margin:0 0 20px;">Tu registro para la masterclass gratuita ha sido exitoso.</p>
                
                <div style="text-align:center"><span class="badge">✅ Inscripción Confirmada</span></div>

                <div style="text-align:center;padding:24px;margin:20px 0;background:linear-gradient(135deg,#1ce501 0%,#17b801 100%);border-radius:12px;">
                    <div style="font-size:14px;color:rgba(255,255,255,0.9);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Masterclass Gratuita</div>
                    <div style="font-size:24px;font-weight:700;color:#ffffff;">{{ $masterclassTitle }}</div>
                </div>

                <div class="details">
                    <h3 style="margin:0 0 16px;font-size:18px;">📅 Detalles del evento</h3>
                    <div class="detail-row"><span class="detail-label">📅 Fecha</span><span class="detail-value">{{ $date }}</span></div>
                    <div class="detail-row"><span class="detail-label">🕐 Hora</span><span class="detail-value">{{ $hour }}</span></div>
                    <div class="detail-row"><span class="detail-label">👤 Participante</span><span class="detail-value">{{ $userName }} {{ $lastname }}</span></div>
                    <div class="detail-row"><span class="detail-label">📧 Correo</span><span class="detail-value">{{ $email }}</span></div>
                    <div class="detail-row"><span class="detail-label">🌍 País</span><span class="detail-value">{{ $country }}</span></div>
                </div>

                @if($meetingLink)
                <div style="background:#e8f5e9;border-left:4px solid #1ce501;padding:20px;border-radius:0 8px 8px 0;margin:20px 0;">
                    <div style="font-size:14px;color:#1ce501;font-weight:600;margin-bottom:8px;">🎯 ¡Link de Acceso Disponible!</div>
                    <p style="font-size:14px;color:#2c3e50;line-height:1.6;margin:0;">Haz clic en el botón para unirte. Te recomendamos ingresar <strong>10 minutos antes</strong>.</p>
                </div>
                @endif

                @if($meetingLink)
                <table width="100%" cellpadding="0" cellspacing="0"><tr><td align="center">
                    <a href="{{ $meetingLink }}" class="btn" target="_blank">🚀 Unirse a la Masterclass</a>
                </td></tr></table>
                @endif

                @if($objectives)
                <div class="details" style="margin-top:24px;">
                    <h3 style="margin:0 0 12px;font-size:18px;">¿Qué aprenderás?</h3>
                    <p style="color:#6c757d;font-size:15px;line-height:1.7;margin:0;">{{ $objectives }}</p>
                </div>
                @endif
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
