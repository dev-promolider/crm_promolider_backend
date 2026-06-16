@php
    // Variables para el header
    $titulo_principal = '¡Pago Confirmado!';
    $subtitulo = 'Comprobante de pago de membresía';
@endphp

@include('emails.layouts.header', ['titulo_principal' => $titulo_principal, 'subtitulo' => $subtitulo])

<!-- Contenido principal -->
<div class="u-row-container" style="padding: 0; background: #ffffff;">
    <div class="u-row" style="margin: 0 auto; min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #ffffff;">
        <div style="border-collapse: collapse; display: table; width: 100%; background-color: transparent;">
            <div class="u-col u-col-100" style="max-width: 320px; min-width: 640px; display: table-cell; vertical-align: top;">
                <div style="width: 100% !important;">
                    
                    <!-- Header geométrico mejorado -->
                    <div style="position: relative; height: 140px; overflow: hidden; background: linear-gradient(135deg, #00c800 0%, #00a000 100%);">
                        <!-- Triángulos decorativos con mejor opacidad -->
                        <div style="position: absolute; top: 0; left: 0; width: 0; height: 0; border-left: 180px solid transparent; border-bottom: 140px solid rgba(255,255,255,0.15);"></div>
                        <div style="position: absolute; top: 0; right: 0; width: 0; height: 0; border-right: 200px solid transparent; border-top: 140px solid rgba(0,0,0,0.15);"></div>
                        <div style="position: absolute; bottom: 0; left: 40%; width: 0; height: 0; border-left: 100px solid transparent; border-right: 100px solid transparent; border-bottom: 60px solid rgba(255,255,255,0.12);"></div>
                        <!-- Líneas geométricas sutiles -->
                        <div style="position: absolute; top: 30px; right: 30px; width: 40px; height: 2px; background: rgba(255,255,255,0.3); transform: rotate(45deg);"></div>
                        <div style="position: absolute; top: 50px; right: 25px; width: 30px; height: 2px; background: rgba(255,255,255,0.2); transform: rotate(45deg);"></div>
                    </div>

                    <div style="padding: 50px 40px;">
                        
                        <!-- Título principal mejorado -->
                        <div style="text-align: center; margin: -90px 0 50px 0; position: relative; z-index: 10;">
                            <div style="display: inline-block; background: #ffffff; padding: 28px 50px; box-shadow: 0 12px 32px rgba(0,0,0,0.08), 0 4px 8px rgba(0,0,0,0.04); border-top: 3px solid #00c800;">
                                <p style="font-family: 'Inter', sans-serif; color: #6b7280; margin: 0 0 8px 0; font-size: 13px; font-weight: 600; letter-spacing: 2px; text-transform: uppercase;">Comprobante de</p>
                                <h1 style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; font-size: 42px; color: #00c800; margin: 0; font-weight: 900; letter-spacing: 1px; text-transform: uppercase;">MEMBRESÍA</h1>
                            </div>
                        </div>

                        <!-- Saludo personalizado -->
                        <p style="font-family: 'Inter', sans-serif; font-size: 16px; color: #1a1a1a; margin: 0 0 10px 0; font-weight: 600;">{{ $user_name }}</p>
                        <p style="font-family: 'Inter', sans-serif; color: #6b7280; margin: 0 0 40px 0; font-size: 14px; line-height: 1.6;">
                            Tu pago ha sido procesado exitosamente. Tu membresía <strong style="color: #1a1a1a;">{{ $membership_name }}</strong> ha sido activada.
                        </p>

                        <!-- Línea divisoria -->
                        <div style="height: 2px; background: linear-gradient(90deg, transparent 0%, #00c800 50%, transparent 100%); margin: 40px 0;"></div>

                        <!-- Número de comprobante destacado mejorado -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 40px 0;">
                            <tr>
                                <td style="text-align: center;">
                                    <div style="display: inline-block; padding: 20px 40px; background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); border: 2px solid #e5e7eb;">
                                        <p style="font-family: 'Inter', sans-serif; color: #9ca3af; font-size: 10px; margin: 0 0 10px 0; letter-spacing: 2px; text-transform: uppercase; font-weight: 700;">Comprobante Nº</p>
                                        <p style="font-family: 'Courier New', monospace; color: #00c800; font-size: 32px; font-weight: 700; margin: 0; letter-spacing: 3px; text-shadow: 0 2px 4px rgba(0,200,0,0.1);">#{{ $receipt_number }}</p>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <!-- Línea divisoria -->
                        <div style="height: 1px; background: #e5e7eb; margin: 40px 0;"></div>

                        <!-- Detalles del pago en grid minimalista -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 40px 0;">
                            <tr>
                                <td width="50%" style="vertical-align: top; padding-right: 15px;">
                                    <p style="font-family: 'Inter', sans-serif; color: #9ca3af; font-size: 11px; margin: 0 0 6px 0; letter-spacing: 1px; text-transform: uppercase;">Fecha de pago</p>
                                    <p style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 14px; margin: 0; font-weight: 600;">{{ $payment_date }}</p>
                                </td>
                                <td width="50%" style="vertical-align: top; padding-left: 15px;">
                                    <p style="font-family: 'Inter', sans-serif; color: #9ca3af; font-size: 11px; margin: 0 0 6px 0; letter-spacing: 1px; text-transform: uppercase;">Método de pago</p>
                                    <p style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 14px; margin: 0; font-weight: 600;">{{ $payment_method }}</p>
                                </td>
                            </tr>
                        </table>

                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 25px 0 40px 0;">
                            <tr>
                                <td width="50%" style="vertical-align: top; padding-right: 15px;">
                                    <p style="font-family: 'Inter', sans-serif; color: #9ca3af; font-size: 11px; margin: 0 0 6px 0; letter-spacing: 1px; text-transform: uppercase;">Usuario</p>
                                    <p style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 14px; margin: 0; font-weight: 600;">{{ $user_name }}</p>
                                </td>
                                <td width="50%" style="vertical-align: top; padding-left: 15px;">
                                    <p style="font-family: 'Inter', sans-serif; color: #9ca3af; font-size: 11px; margin: 0 0 6px 0; letter-spacing: 1px; text-transform: uppercase;">Email</p>
                                    <p style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 14px; margin: 0; font-weight: 600;">{{ $user_email }}</p>
                                </td>
                            </tr>
                        </table>

                        <!-- Línea divisoria -->
                        <div style="height: 1px; background: #e5e7eb; margin: 40px 0;"></div>

                        <!-- Información de la membresía -->
                        <div style="margin: 40px 0;">
                            <p style="font-family: 'Inter', sans-serif; color: #9ca3af; font-size: 11px; margin: 0 0 15px 0; letter-spacing: 1.5px; text-transform: uppercase; font-weight: 600;">Detalles de la Membresía</p>
                            
                            <div style="display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #00c800 0%, #00a000 100%); margin-bottom: 30px;">
                                <h2 style="font-family: 'Inter', sans-serif; color: #ffffff; font-size: 24px; font-weight: 700; margin: 0; letter-spacing: 0.5px; text-transform: uppercase;">{{ $membership_name }}</h2>
                            </div>

                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td style="font-family: 'Inter', sans-serif; color: #6b7280; font-size: 13px; padding: 14px 0; border-bottom: 1px solid #f3f4f6;">
                                        Duración
                                    </td>
                                    <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; padding: 14px 0; text-align: right; border-bottom: 1px solid #f3f4f6; font-weight: 600;">
                                        {{ $membership_duration }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-family: 'Inter', sans-serif; color: #6b7280; font-size: 13px; padding: 14px 0; border-bottom: 1px solid #f3f4f6;">
                                        Fecha de activación
                                    </td>
                                    <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; padding: 14px 0; text-align: right; border-bottom: 1px solid #f3f4f6; font-weight: 600;">
                                        {{ $activation_date }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-family: 'Inter', sans-serif; color: #6b7280; font-size: 13px; padding: 14px 0;">
                                        Fecha de vencimiento
                                    </td>
                                    <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; padding: 14px 0; text-align: right; font-weight: 600;">
                                        {{ $expiration_date }}
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Línea divisoria -->
                        <div style="height: 1px; background: #e5e7eb; margin: 40px 0;"></div>

                        <!-- Resumen de pago minimalista -->
                        <div style="margin: 40px 0;">
                            <p style="font-family: 'Inter', sans-serif; color: #9ca3af; font-size: 11px; margin: 0 0 25px 0; letter-spacing: 1.5px; text-transform: uppercase; font-weight: 600;">Resumen de Pago</p>
                            
                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td style="font-family: 'Inter', sans-serif; color: #6b7280; font-size: 14px; padding: 12px 0; border-bottom: 1px solid #f3f4f6;">
                                        Precio base
                                    </td>
                                    <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 14px; padding: 12px 0; text-align: right; border-bottom: 1px solid #f3f4f6; font-weight: 600;">
                                        ${{ number_format($base_price, 2) }}
                                    </td>
                                </tr>
                                @if($iva_amount > 0)
                                <tr>
                                    <td style="font-family: 'Inter', sans-serif; color: #6b7280; font-size: 14px; padding: 12px 0; border-bottom: 1px solid #f3f4f6;">
                                        IVA ({{ $iva_percentage }}%)
                                    </td>
                                    <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 14px; padding: 12px 0; text-align: right; border-bottom: 1px solid #f3f4f6; font-weight: 600;">
                                        ${{ number_format($iva_amount, 2) }}
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 16px; padding: 20px 0 0 0; font-weight: 700; letter-spacing: -0.3px;">
                                        Total Pagado
                                    </td>
                                    <td style="font-family: 'Inter', sans-serif; color: #00c800; font-size: 28px; padding: 20px 0 0 0; text-align: right; font-weight: 700; letter-spacing: -0.5px;">
                                        ${{ number_format($total_paid, 2) }}
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Beneficios de la membresía mejorado -->
                        @if(!empty($membership_benefits))
                        <div style="margin: 50px 0; padding: 0; background: linear-gradient(135deg, #f0fdf4 0%, #f9fafb 100%); border: 2px solid #dcfce7; position: relative; overflow: hidden;">
                            <!-- Icono decorativo -->
                            <div style="position: absolute; top: 15px; right: 15px; width: 50px; height: 50px; border: 3px solid #00c800; opacity: 0.1; transform: rotate(45deg);"></div>
                            <div style="padding: 30px;">
                                <div style="display: inline-block; background: #00c800; color: #ffffff; padding: 8px 20px; margin-bottom: 20px;">
                                    <p style="font-family: 'Inter', sans-serif; font-size: 11px; margin: 0; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;">Beneficios Incluidos</p>
                                </div>
                                <div style="font-family: 'Inter', sans-serif; color: #374151; font-size: 13px; line-height: 1.9;">
                                    {!! $membership_benefits !!}
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Botón de acceso mejorado -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 50px 0;">
                            <tr>
                                <td align="center">
                                    <table cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td align="center" style="background: linear-gradient(135deg, #00c800 0%, #00a000 100%); box-shadow: 0 4px 12px rgba(0,200,0,0.25);">
                                                <a href="{{ $dashboard_url }}" 
                                                   style="display: block; padding: 18px 70px; color: #ffffff; text-decoration: none; font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; border: 2px solid rgba(255,255,255,0.2);">
                                                    ► Acceder al Panel
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- Nota informativa -->
                        <div style="margin: 50px 0; padding: 25px; background: #f9fafb; border-top: 2px solid #e5e7eb; border-bottom: 2px solid #e5e7eb;">
                            <p style="font-family: 'Inter', sans-serif; color: #4b5563; font-size: 12px; margin: 0; line-height: 1.7;">
                                Tu membresía estará activa hasta el <strong style="color: #1a1a1a;">{{ $expiration_date }}</strong>. Podrás renovarla antes de que expire para mantener todos tus beneficios.
                            </p>
                        </div>

                        <!-- Mensaje de agradecimiento -->
                        <p style="font-family: 'Inter', sans-serif; color: #6b7280; font-size: 13px; margin: 50px 0 10px 0; line-height: 1.7; text-align: center;">
                            Gracias por ser parte de
                        </p>
                        <p style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 18px; margin: 0 0 40px 0; text-align: center; font-weight: 700; letter-spacing: 1px;">
                            PROMOLÍDER
                        </p>

                        <!-- Línea divisoria final -->
                        <div style="height: 1px; background: #e5e7eb; margin: 40px 0;"></div>

                        <!-- Soporte -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 30px 0;">
                            <tr>
                                <td align="center">
                                    <p style="font-family: 'Inter', sans-serif; color: #9ca3af; font-size: 11px; margin: 0 0 8px 0; letter-spacing: 1px; text-transform: uppercase;">
                                        Soporte
                                    </p>
                                    <a href="mailto:soporte@promolider.info" 
                                       style="font-family: 'Inter', sans-serif; color: #00c800; font-size: 13px; text-decoration: none; font-weight: 600;">
                                        soporte@promolider.info
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <!-- Footer geométrico mejorado -->
                        <div style="position: relative; height: 80px; overflow: hidden; margin: 50px -40px -50px -40px; background: linear-gradient(135deg, #00c800 0%, #00a000 100%);">
                            <!-- Triángulos decorativos -->
                            <div style="position: absolute; bottom: 0; left: 0; width: 0; height: 0; border-left: 150px solid transparent; border-top: 80px solid rgba(255,255,255,0.15);"></div>
                            <div style="position: absolute; bottom: 0; right: 0; width: 0; height: 0; border-right: 180px solid transparent; border-bottom: 80px solid rgba(0,0,0,0.15);"></div>
                            <!-- Líneas geométricas -->
                            <div style="position: absolute; bottom: 30px; left: 30px; width: 40px; height: 2px; background: rgba(255,255,255,0.3); transform: rotate(-45deg);"></div>
                            <div style="position: absolute; bottom: 50px; left: 25px; width: 30px; height: 2px; background: rgba(255,255,255,0.2); transform: rotate(-45deg);"></div>
                            <!-- Marca de verificación sutil -->
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-family: 'Inter', sans-serif; color: rgba(255,255,255,0.2); font-size: 40px; font-weight: 900;">✓</div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('emails.layouts.footer')