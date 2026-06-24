<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fondos Recibidos - {{ config('app.name') }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
            color: #2c3e50;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            padding: 24px;
            text-align: center;
        }
        
        .logo {
            width: 120px;
            height: auto;
            margin-bottom: 16px;
        }
        
        .main-title {
            color: white;
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }
        
        .subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            margin-top: 8px;
        }
        
        .content {
            padding: 32px;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            background-color: rgba(26, 122, 62, 0.1);
            color: #1a7a3e;
            padding: 8px 16px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 24px;
        }
        
        .status-badge i {
            margin-right: 8px;
        }
        
        .card {
            background-color: #e8f5e8;
            border: 1px solid #c3e6c3;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }
        
        .transaction-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
        }
        
        .detail-label {
            font-size: 14px;
            color: #6c757d;
            font-weight: 500;
            margin-right: 12px;
        }
        
        .detail-value {
            font-size: 16px;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .message-box {
            background: linear-gradient(135deg, #f0f9f4 0%, #f5fbf7 100%);
            border-left: 4px solid #1a7a3e;
            padding: 16px;
            margin-bottom: 24px;
            border-radius: 0 8px 8px 0;
        }
        
        .message-box p {
            margin: 0;
            color: #1a7a3e;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .warning-box {
            background: linear-gradient(135deg, #fef2f2 0%, #fdf5f5 100%);
            border: 1px solid #e74c3c;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
        }
        
        .warning-box p {
            margin: 0;
            color: #e74c3c;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #1a7a3e 0%, #27ae60 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            box-shadow: 0 4px 16px rgba(26, 122, 62, 0.3);
            transition: all 0.3s ease;
        }
        
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(26, 122, 62, 0.4);
        }
        
        .footer {
            background-color: #f1f5f9;
            padding: 24px;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
            border-top: 1px solid #e2e8f0;
        }
        
        .social-links {
            margin-top: 16px;
        }
        
        .social-link {
            display: inline-block;
            margin: 0 8px;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }
        
        .social-link:hover {
            opacity: 1;
        }
        
        .social-link img {
            width: 24px;
            height: 24px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <img src="https://promolider-storage-user.s3-accelerate.amazonaws.com/images/promolider_logo.png" alt="Promolíder" class="logo">
            <h1 class="main-title">¡Fondos Recibidos!</h1>
            <p class="subtitle">Ha llegado dinero a su cuenta</p>
        </div>
        
        <!-- Main Content -->
        <div class="content">
            <!-- Status Badge -->
            <div class="status-badge">
                <i>💰</i>
                Transferencia Recibida
            </div>
            
            <!-- Transaction Card -->
            <div class="card">
                <div class="transaction-details">
                    <div class="detail-item">
                        <span class="detail-label">Destinatario</span>
                        <span class="detail-value">{{ $destinatario_nombre }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Remitente</span>
                        <span class="detail-value">{{ $remite_nombre }} ({{ $remite_usuario }})</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Monto</span>
                        <span class="detail-value">${{ $monto }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Fecha</span>
                        <span class="detail-value">{{ $fecha }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Additional Message -->
            @if(isset($mensaje_adicional) && $mensaje_adicional)
            <div class="message-box">
                <p>{{ $mensaje_adicional }}</p>
            </div>
            @endif
            
            <!-- Warning Box -->
            <div class="warning-box">
                <p><strong>Importante:</strong> Verifique siempre la identidad del remitente antes de interactuar con cualquier enlace o solicitud adicional.</p>
            </div>
            
            <!-- Action Button -->
            <div style="text-align: center;">
                <a href="/reports/mywallet" class="action-button">Ver Mi Billetera</a>
                <p style="margin: 16px 0 0; color: #6c757d; font-size: 14px; text-align: center; line-height: 1.4;">
                    Revise el saldo actualizado y sus movimientos recientes
                </p>
            </div>
        </div>
        
       <!-- Footer -->
                    <div class="u-row-container" style="padding: 0px; background-color: transparent">
                        <div class="u-row" style="
                  margin: 0 auto;
                  min-width: 320px;
                  max-width: 600px;
                  overflow-wrap: break-word;
                  word-wrap: break-word;
                  word-break: break-word;
                  background-color: #35424a;
                ">
                            <div style="
                    border-collapse: collapse;
                    display: table;
                    width: 100%;
                    background-color: transparent;
                  ">
                                <div class="u-col u-col-100" style="
                      max-width: 320px;
                      min-width: 600px;
                      display: table-cell;
                      vertical-align: top;
                    ">
                                    <div style="
                        width: 100% !important;
                        border-radius: 0px;
                        -webkit-border-radius: 0px;
                        -moz-border-radius: 0px;
                      ">
                                        <div style="
                          padding: 0px;
                          border-top: 0px solid transparent;
                          border-left: 0px solid transparent;
                          border-right: 0px solid transparent;
                          border-bottom: 0px solid transparent;
                          border-radius: 0px;
                          -webkit-border-radius: 0px;
                          -moz-border-radius: 0px;
                        ">
                                            <table style="font-family: 'Montserrat', sans-serif" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                                <tbody>
                                                    <tr>
                                                        <td style="
                                  overflow-wrap: break-word;
                                  word-break: break-word;
                                  padding: 32px 10px 0px;
                                  font-family: 'Montserrat', sans-serif;
                                " align="left">
                                                            <div style="
                                                            color: #ffffff;
                                                            line-height: 140%;
                                                            text-align: center;
                                                            word-wrap: break-word;
                                                          ">
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
                                                        <td style="
                                  overflow-wrap: break-word;
                                  word-break: break-word;
                                  padding: 10px 15%;
                                  font-family: 'Montserrat', sans-serif;
                                " align="left">
                                                            <div style="
                                                            color: #b0b1b4;
                                                            line-height: 180%;
                                                            text-align: center;
                                                            word-wrap: break-word;
                                                          ">
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
                                                        <td style="
                                  overflow-wrap: break-word;
                                  word-break: break-word;
                                  padding: 10px;
                                  font-family: 'Montserrat', sans-serif;
															" align="left">
                                                            <div align="center">
                                                                <div style="display: table; max-width: 211px">
                                                                    <table align="left" border="0" cellspacing="0" cellpadding="0" width="32" height="32" style="
                                            border-collapse: collapse;
                                            table-layout: fixed;
                                            border-spacing: 0;
                                            mso-table-lspace: 0pt;
                                            mso-table-rspace: 0pt;
                                            vertical-align: top;
                                            margin-right: 21px;
                                            background-color: #35424a;
                                          ">
                                                                        <tbody>
                                                                            <tr style="vertical-align: top">
                                                                                <td align="left" valign="middle" style="
                                              word-break: break-word;
                                              border-collapse: collapse !important;
                                              vertical-align: top;
                                            ">
                                                                                            <a href="https://www.facebook.com/Promolider-101670701715747" title="Facebook" target="_blank">
                                                                                            <img src="https://promolider-storage-user.s3-accelerate.amazonaws.com/images/facebook+icon.webp" alt="Facebook" title="Facebook" width="32" style="
                                                outline: none;
                                                text-decoration: none;
                                                -ms-interpolation-mode: bicubic;
                                                clear: both;
                                                display: block !important;
                                                border: none;
                                                height: auto;
                                                float: none;
                                                max-width: 32px !important;
                                              " />
                                                                                        </a>
                                                                                    </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>

                                                                    <table align="left" border="0" cellspacing="0" cellpadding="0" width="32" height="32" style="
                                            border-collapse: collapse;
                                            table-layout: fixed;
                                            border-spacing: 0;
                                            mso-table-lspace: 0pt;
                                            mso-table-rspace: 0pt;
                                            vertical-align: top;
                                            margin-right: 21px;
                                          ">
                                                                        <tbody>
                                                                            <tr style="vertical-align: top">
                                                                                <td align="left" valign="middle" style="
                                              word-break: break-word;
                                              border-collapse: collapse !important;
                                              vertical-align: top;
                                            ">
                                                                                    <a href="https://www.youtube.com/channel/UCj-NmJXOnJt55aDRh1R0LLg/featured" title="Youtube" target="_blank">
                                                                                        <img src="https://promolider-storage-user.s3-accelerate.amazonaws.com/images/youtube+icon.webp" alt="Youtube" title="Youtube" width="32" style="
                                                outline: none;
                                                text-decoration: none;
                                                -ms-interpolation-mode: bicubic;
                                                clear: both;
                                                display: block !important;
                                                border: none;
                                                height: auto;
                                                float: none;
                                                max-width: 32px !important;
                                              " />
                                                                                    </a>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>

                                                                    <table align="left" border="0" cellspacing="0" cellpadding="0" width="32" height="32" style="
                                            border-collapse: collapse;
                                            table-layout: fixed;
                                            border-spacing: 0;
                                            mso-table-lspace: 0pt;
                                            mso-table-rspace: 0pt;
                                            vertical-align: top;
                                            margin-right: 21px;
                                          ">
                                                                        <tbody>
                                                                            <tr style="vertical-align: top">
                                                                                <td align="left" valign="middle" style="
                                              word-break: break-word;
                                              border-collapse: collapse !important;
                                              vertical-align: top;
                                            ">
                                                                                    <a href="#" title="Instagram" target="_blank">
                                                                                        <img src="https://promolider-storage-user.s3-accelerate.amazonaws.com/images/instagram+icon.webp" alt="Instagram" title="Instagram" width="32" style="
                                                outline: none;
                                                text-decoration: none;
                                                -ms-interpolation-mode: bicubic;
                                                clear: both;
                                                display: block !important;
                                                border: none;
                                                height: auto;
                                                float: none;
                                                max-width: 32px !important;
                                              " />
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