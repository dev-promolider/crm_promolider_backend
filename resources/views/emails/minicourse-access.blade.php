<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso a tu Mini Curso</title>

    <style type="text/css">
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        @media only screen and (min-width: 620px) {
            .u-row {
                width: 600px !important;
            }
            .u-row .u-col {
                vertical-align: top;
            }
            .u-row .u-col-100 {
                width: 600px !important;
            }
        }

        @media (max-width: 620px) {
            .u-row-container {
                max-width: 100% !important;
                padding-left: 0px !important;
                padding-right: 0px !important;
            }
            .u-row .u-col {
                min-width: 320px !important;
                max-width: 100% !important;
                display: block !important;
            }
            .u-row {
                width: calc(100% - 40px) !important;
            }
            .u-col {
                width: 100% !important;
            }
            .u-col>div {
                margin: 0 auto;
            }
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        table, tr, td {
            vertical-align: top;
            border-collapse: collapse;
        }

        p {
            margin: 0;
        }

        @media only screen and (max-width: 480px) {
            .container {
                width: 100% !important;
                max-width: 100% !important;
            }
            
            .mobile-padding {
                padding: 20px 15px !important;
            }
            
            .mobile-font {
                font-size: 18px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; -webkit-text-size-adjust: 100%; background-color: #f8f9fa; color: #2c3e50;">
    <table style="border-collapse: collapse; table-layout: fixed; border-spacing: 0; vertical-align: top; min-width: 320px; margin: 0 auto; background-color: #f8f9fa; width: 100%;" cellpadding="0" cellspacing="0">
        <tbody>
            <tr style="vertical-align: top">
                <td style="word-break: break-word; border-collapse: collapse !important; vertical-align: top;">
                    
                    <!-- Header -->
                    <div class="u-row-container" style="padding: 0px; background-color: transparent">
                        <div class="u-row" style="margin: 0 auto; min-width: 320px; max-width: 600px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);">
                            <div style="border-collapse: collapse; display: table; width: 100%; background-color: transparent;">
                                <div class="u-col u-col-100" style="max-width: 320px; min-width: 600px; display: table-cell; vertical-align: top;">
                                    <div style="width: 100% !important; border-radius: 0px; -webkit-border-radius: 0px; -moz-border-radius: 0px;">
                                        <div style="padding: 0px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-right: 0px solid transparent; border-bottom: 0px solid transparent; border-radius: 0px; -webkit-border-radius: 0px; -moz-border-radius: 0px;">
                                            <table style="font-family: 'Inter', sans-serif" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                                <tbody>
                                                    <tr>
                                                        <td style="overflow-wrap: break-word; word-break: break-word; padding: 0px; font-family: 'Inter', sans-serif;" align="center">
                                                            <img src="https://promolider-storage-user.s3-accelerate.amazonaws.com/images/promolider_logo.png" alt="Promolíder" style="outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; clear: both; display: inline-block !important; border: none; height: auto; float: none; width: 180px; max-width: 180px;" width="180" />
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="overflow-wrap: break-word; word-break: break-word; padding: 20px 0 30px; font-family: 'Inter', sans-serif;" align="center">
                                                            <h1 style="margin: 0; color: #ffffff; line-height: 1.2; text-align: center; font-weight: 300; font-family: 'Inter', sans-serif; font-size: 28px; letter-spacing: -0.5px;">
                                                                ¡Inscripción Confirmada!
                                                            </h1>
                                                            <p style="margin: 8px 0 0; color: #bdc3c7; font-size: 16px; font-weight: 400; text-align: center;">
                                                                Tu lugar en el mini curso está asegurado
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contenido principal -->
                    <div class="u-row-container" style="padding: 0px; background-color: transparent">
                        <div class="u-row" style="margin: 0 auto; min-width: 320px; max-width: 600px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #ffffff;">
                            <div style="border-collapse: collapse; display: table; width: 100%; background-color: transparent;">
                                <div class="u-col u-col-100" style="max-width: 320px; min-width: 600px; display: table-cell; vertical-align: top;">
                                    <div style="width: 100% !important;">
                                        <div style="padding: 30px 25px;">
                                            
                                            <!-- Saludo -->
                                            <p style="font-size: 18px; color: #2d3748; margin: 0 0 20px 0; font-weight: bold;">¡Hola <strong>{{ $user_name }}</strong>!</p>

                                            <p style="color: #4a5568; margin: 0 0 25px 0; font-size: 15px; line-height: 1.6;">
                                                Te damos la bienvenida y te confirmamos que tu inscripción al mini curso ha sido exitosa. 
                                                Estás a un paso de comenzar tu experiencia de aprendizaje.
                                            </p>

                                            <!-- Información del curso -->
                                            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f7fafc; border-radius: 10px; margin: 25px 0; border-left: 4px solid #6366f1;">
                                                <tr>
                                                    <td style="padding: 20px;">
                                                        <h2 style="color: #2d3748; font-size: 20px; font-weight: bold; margin: 0 0 12px 0; line-height: 1.3;">{{ $course_title }}</h2>

                                                        <!-- Imagen del curso -->
                                                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 15px 0;">
                                                            <tr>
                                                                <td align="center">
                                                                    <img src="{{ asset($mini_course->images->first()->image) }}" 
                                                                         alt="{{ $course_title }}" 
                                                                         width="400" 
                                                                         height="150"
                                                                         style="display: block; width: 100%; max-width: 400px; height: auto; border-radius: 8px; border: 2px solid #e2e8f0;">
                                                                </td>
                                                            </tr>
                                                        </table>

                                                        <p style="color: #4a5568; font-size: 14px; line-height: 1.5; margin: 12px 0 0 0;">
                                                            <strong>Descripción:</strong><br>
                                                            {{ $course_description }}
                                                        </p>
                                                    </td>
                                                </tr>
                                            </table>

                                            <!-- Instrucciones -->
                                            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; margin: 25px 0;">
                                                <tr>
                                                    <td style="padding: 20px;">
                                                        <h3 style="color: #92400e; font-size: 16px; margin: 0 0 12px 0; font-weight: bold;">📋 Instrucciones de acceso</h3>
                                                        
                                                        <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                                            <tr><td style="color: #92400e; font-size: 14px; padding: 4px 0;">• Haz clic en el botón verde para acceder inmediatamente</td></tr>
                                                            <tr><td style="color: #92400e; font-size: 14px; padding: 4px 0;">• El enlace es válido por 30 días completos</td></tr>
                                                            <tr><td style="color: #92400e; font-size: 14px; padding: 4px 0;">• Puedes entrar las veces que necesites</td></tr>
                                                            <tr><td style="color: #92400e; font-size: 14px; padding: 4px 0;">• Guarda este email para futuras referencias</td></tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>

                                            <!-- Botón de acceso -->
                                            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 35px 0;">
                                                <tr>
                                                    <td align="center">
                                                        <table cellpadding="0" cellspacing="0" border="0" style="border-radius: 8px; background-color: #16a34a;">
                                                            <tr>
                                                                <td style="border-radius: 8px; background-color: #16a34a;">
                                                                    <a href="{{ $access_link }}" 
                                                                       style="display: block; color: #ffffff; font-weight: bold; font-size: 16px; text-decoration: none; padding: 16px 32px; border-radius: 8px; text-align: center; line-height: 1.2;">
                                                                        🚀 Acceder al Mini Curso
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>

                                            <!-- Enlace alternativo -->
                                            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f3f4f6; border-radius: 6px; margin: 20px 0;">
                                                <tr>
                                                    <td style="padding: 15px;">
                                                        <p style="font-size: 14px; color: #4b5563; margin: 0 0 8px 0;"><strong>¿No funciona el botón?</strong> Copia y pega este enlace en tu navegador:</p>
                                                        
                                                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #ffffff; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                                                            <tr>
                                                                <td style="font-family: 'Courier New', Courier, monospace; font-size: 12px; color: #374151; line-height: 1.4; word-break: break-all;">
                                                                    {{ $access_link }}
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>

                                            <!-- Detalles del mini curso -->
                                            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #ecfdf5; border-radius: 8px; margin: 25px 0; border-left: 4px solid #16a34a;">
                                                <tr>
                                                    <td style="padding: 20px;">
                                                        <h3 style="color: #166534; font-size: 16px; margin: 0 0 15px 0; font-weight: bold;">📚 Detalles del mini curso</h3>
                                                        
                                                        <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                                            <tr>
                                                                <td style="color: #166534; font-size: 14px; padding: 5px 0;">
                                                                    📖 <strong>Título:</strong> {{ $course_title }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: #166534; font-size: 14px; padding: 5px 0;">
                                                                    ⏱️ <strong>Duración:</strong> {{ $mini_course->duration }} minutos
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: #166534; font-size: 14px; padding: 5px 0;">
                                                                    📈 <strong>Nivel:</strong> {{ ucfirst($mini_course->level) }}
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>

                                            <!-- Footer Simple -->
                                            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f9fafb; border-radius: 8px; margin: 25px 0; padding: 25px; border-top: 1px solid #e5e7eb;">
                                                <tr>
                                                    <td style="text-align: center;">
                                                        <p style="color: #6b7280; font-size: 14px; margin: 0 0 8px 0;">Si tienes alguna pregunta o problema para acceder al mini curso, no dudes en contactarnos.</p>
                                                        <p style="color: #6b7280; font-size: 14px; margin: 0 0 15px 0;"><strong>¡Esperamos que disfrutes tu experiencia de aprendizaje!</strong></p>
                                                        
                                                        <p style="font-size: 12px; color: #9ca3af; margin: 0;">
                                                            Este correo se generó automáticamente. Por favor, no respondas a este mensaje.
                                                        </p>
                                                    </td>
                                                </tr>
                                            </table>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer Promolíder -->
                    <div class="u-row-container" style="padding: 0px; background-color: transparent">
                        <div class="u-row" style="margin: 0 auto; min-width: 320px; max-width: 600px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #35424a;">
                            <div style="border-collapse: collapse; display: table; width: 100%; background-color: transparent;">
                                <div class="u-col u-col-100" style="max-width: 320px; min-width: 600px; display: table-cell; vertical-align: top;">
                                    <div style="width: 100% !important; border-radius: 0px; -webkit-border-radius: 0px; -moz-border-radius: 0px;">
                                        <div style="padding: 0px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-right: 0px solid transparent; border-bottom: 0px solid transparent; border-radius: 0px; -webkit-border-radius: 0px; -moz-border-radius: 0px;">
                                            <table style="font-family: 'Montserrat', sans-serif" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                                <tbody>
                                                    <tr>
                                                        <td style="overflow-wrap: break-word; word-break: break-word; padding: 32px 10px 0px; font-family: 'Montserrat', sans-serif;" align="left">
                                                            <div style="color: #ffffff; line-height: 140%; text-align: center; word-wrap: break-word;">
                                                                <p style="font-size: 14px; line-height: 140%">
                                                                    <span style="font-size: 18px; line-height: 25.2px;"><strong>Promolíder</strong></span>
                                                                </p>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                            <table style="font-family: 'Montserrat', sans-serif" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                                <tbody>
                                                    <tr>
                                                        <td style="overflow-wrap: break-word; word-break: break-word; padding: 10px 15%; font-family: 'Montserrat', sans-serif;" align="left">
                                                            <div style="color: #b0b1b4; line-height: 180%; text-align: center; word-wrap: break-word;">
                                                                <p style="font-size: 14px; line-height: 180%">
                                                                    Av. La Fontana, 440 - C.C. La Rotonda II - Tda. 2086, La Molina, Lima 12, La Molina 12175
                                                                </p>
                                                                <p style="font-size: 14px; line-height: 180%">
                                                                    0414-3688809
                                                                </p>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                            <table style="font-family: 'Montserrat', sans-serif" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                                <tbody>
                                                    <tr>
                                                        <td style="overflow-wrap: break-word; word-break: break-word; padding: 10px 10px 32px; font-family: 'Montserrat', sans-serif;" align="left">
                                                            <div align="center">
                                                                <div style="display: table; max-width: 211px">
                                                                    <table align="left" border="0" cellspacing="0" cellpadding="0" width="32" height="32" style="border-collapse: collapse; table-layout: fixed; border-spacing: 0; mso-table-lspace: 0pt; mso-table-rspace: 0pt; vertical-align: top; margin-right: 21px; background-color: #35424a;">
                                                                        <tbody>
                                                                            <tr style="vertical-align: top">
                                                                                <td align="left" valign="middle" style="word-break: break-word; border-collapse: collapse !important; vertical-align: top;">
                                                                                    <a href="https://www.facebook.com/Promolider-101670701715747" title="Facebook" target="_blank">
                                                                                        <img src="https://promolider-storage-user.s3-accelerate.amazonaws.com/images/facebook+icon.webp" alt="Facebook" title="Facebook" width="32" style="outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; clear: both; display: block !important; border: none; height: auto; float: none; max-width: 32px !important;" />
                                                                                    </a>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>

                                                                    <table align="left" border="0" cellspacing="0" cellpadding="0" width="32" height="32" style="border-collapse: collapse; table-layout: fixed; border-spacing: 0; mso-table-lspace: 0pt; mso-table-rspace: 0pt; vertical-align: top; margin-right: 21px;">
                                                                        <tbody>
                                                                            <tr style="vertical-align: top">
                                                                                <td align="left" valign="middle" style="word-break: break-word; border-collapse: collapse !important; vertical-align: top;">
                                                                                    <a href="https://www.youtube.com/channel/UCj-NmJXOnJt55aDRh1R0LLg/featured" title="Youtube" target="_blank">
                                                                                        <img src="https://promolider-storage-user.s3-accelerate.amazonaws.com/images/youtube+icon.webp" alt="Youtube" title="Youtube" width="32" style="outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; clear: both; display: block !important; border: none; height: auto; float: none; max-width: 32px !important;" />
                                                                                    </a>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>

                                                                    <table align="left" border="0" cellspacing="0" cellpadding="0" width="32" height="32" style="border-collapse: collapse; table-layout: fixed; border-spacing: 0; mso-table-lspace: 0pt; mso-table-rspace: 0pt; vertical-align: top; margin-right: 0px;">
                                                                        <tbody>
                                                                            <tr style="vertical-align: top">
                                                                                <td align="left" valign="middle" style="word-break: break-word; border-collapse: collapse !important; vertical-align: top;">
                                                                                    <a href="#" title="Instagram" target="_blank">
                                                                                        <img src="https://promolider-storage-user.s3-accelerate.amazonaws.com/images/instagram+icon.webp" alt="Instagram" title="Instagram" width="32" style="outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; clear: both; display: block !important; border: none; height: auto; float: none; max-width: 32px !important;" />
                                                                                    </a>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>
