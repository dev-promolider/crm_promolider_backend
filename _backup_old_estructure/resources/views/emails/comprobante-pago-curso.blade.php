@php
    // Variables para el header
    $titulo_principal = '¡Pago Confirmado!';
    $subtitulo = 'Comprobante de pago de curso';
@endphp

@include('emails.layouts.header', ['titulo_principal' => $titulo_principal, 'subtitulo' => $subtitulo])

<!-- Contenido principal -->
<div class="u-row-container" style="padding: 0; background: linear-gradient(135deg, #f5f7fa 0%, #ffffff 100%);">
    <div class="u-row" style="margin: 0 auto; min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #ffffff;">
        <div style="border-collapse: collapse; display: table; width: 100%; background-color: transparent;">
            <div class="u-col u-col-100" style="max-width: 320px; min-width: 640px; display: table-cell; vertical-align: top;">
                <div style="width: 100% !important;">
                    <div style="padding: 50px 40px;">
                        
                        <!-- Saludo personalizado -->
                        <p style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; font-size: 28px; color: #1a1a1a; margin: 0 0 15px 0; font-weight: 700;">Hola, {{ $student_name }}</p>
                        <p style="font-family: 'Inter', sans-serif; color: #555555; margin: 0 0 40px 0; font-size: 15px; line-height: 1.7;">
                            Tu pago ha sido procesado exitosamente. Ya puedes acceder al curso <span style="color: #1a1a1a; font-weight: 600;">{{ $course_title }}</span>.
                        </p>

                        <!-- Banner de confirmación -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: linear-gradient(135deg, rgb(0, 200, 0) 0%, rgb(0, 160, 0) 100%); margin: 40px 0; border-radius: 8px; overflow: hidden;">
                            <tr>
                                <td align="center" style="padding: 45px 30px;">
                                    <div style="width: 72px; height: 72px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 40px;">
                                        ✓
                                    </div>
                                    <h2 style="font-family: 'Inter', sans-serif; color: #ffffff; font-size: 26px; margin: 0 0 12px 0; font-weight: 700;">Pago Confirmado</h2>
                                    <p style="font-family: 'Inter', sans-serif; color: rgba(255, 255, 255, 0.9); font-size: 14px; margin: 0; line-height: 1.6;">
                                        Tu inscripción al curso ha sido activada
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <!-- Comprobante de pago -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #ffffff; margin: 35px 0; border: 2px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
                            <tr>
                                <td style="background: #1f2937; padding: 20px 35px; border-bottom: 2px solid #e5e7eb;">
                                    <h3 style="font-family: 'Inter', sans-serif; color: #ffffff; font-size: 16px; margin: 0; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">COMPROBANTE DE PAGO</h3>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 35px;">
                                    <!-- Número de transacción -->
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 25px; background: #f9fafb; padding: 20px; border-radius: 6px;">
                                        <tr>
                                            <td style="font-family: 'Inter', sans-serif; color: #6b7280; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">
                                                Número de Comprobante
                                            </td>
                                            <td style="font-family: 'Courier New', monospace; color: #dc3545; font-size: 20px; font-weight: 700; text-align: right;">
                                                #{{ $transaction_id }}
                                            </td>
                                        </tr>
                                    </table>

                                    <!-- Detalles del pago -->
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 25px;">
                                        <tr>
                                            <td style="font-family: 'Inter', sans-serif; color: #6b7280; font-size: 13px; padding: 14px 0; border-bottom: 1px solid #e5e7eb;">
                                                Fecha de pago
                                            </td>
                                            <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; padding: 14px 0; text-align: right; border-bottom: 1px solid #e5e7eb; font-weight: 600;">
                                                {{ $payment_date }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-family: 'Inter', sans-serif; color: #6b7280; font-size: 13px; padding: 14px 0; border-bottom: 1px solid #e5e7eb;">
                                                Método de pago
                                            </td>
                                            <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; padding: 14px 0; text-align: right; border-bottom: 1px solid #e5e7eb; font-weight: 600;">
                                                {{ $payment_method }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-family: 'Inter', sans-serif; color: #6b7280; font-size: 13px; padding: 14px 0; border-bottom: 1px solid #e5e7eb;">
                                                Usuario
                                            </td>
                                            <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; padding: 14px 0; text-align: right; border-bottom: 1px solid #e5e7eb; font-weight: 600;">
                                                {{ $student_name }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-family: 'Inter', sans-serif; color: #6b7280; font-size: 13px; padding: 14px 0; border-bottom: 1px solid #e5e7eb;">
                                                Email
                                            </td>
                                            <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; padding: 14px 0; text-align: right; border-bottom: 1px solid #e5e7eb; font-weight: 600;">
                                                {{ $student_email }}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- Información del curso -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #f9fafb; margin: 35px 0; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
                            <tr>
                                <td style="padding: 35px;">
                                    <h3 style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; margin: 0 0 18px 0; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Detalles del Curso Adquirido</h3>
                                    
                                    <h2 style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 22px; font-weight: 700; margin: 0 0 22px 0;">{{ $course_title }}</h2>

                                    @if(!empty($course_image))
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 25px 0;">
                                        <tr>
                                            <td align="center">
                                                <img src="{{ $course_image }}" 
                                                     alt="{{ $course_title }}" 
                                                     width="500" 
                                                     style="display: block; width: 100%; max-width: 500px; height: auto; border: 1px solid #e5e7eb; border-radius: 4px;">
                                            </td>
                                        </tr>
                                    </table>
                                    @endif

                                    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 20px;">
                                        <tr>
                                            <td style="font-family: 'Inter', sans-serif; color: #6b7280; font-size: 13px; padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                                                Categoría
                                            </td>
                                            <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; padding: 12px 0; text-align: right; border-bottom: 1px solid #e5e7eb; font-weight: 600;">
                                                {{ $course_category }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-family: 'Inter', sans-serif; color: #6b7280; font-size: 13px; padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                                                Instructor
                                            </td>
                                            <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; padding: 12px 0; text-align: right; border-bottom: 1px solid #e5e7eb; font-weight: 600;">
                                                {{ $instructor_name }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-family: 'Inter', sans-serif; color: #6b7280; font-size: 13px; padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                                                Módulos
                                            </td>
                                            <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; padding: 12px 0; text-align: right; border-bottom: 1px solid #e5e7eb; font-weight: 600;">
                                                {{ $total_modules }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-family: 'Inter', sans-serif; color: #6b7280; font-size: 13px; padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                                                Lecciones
                                            </td>
                                            <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; padding: 12px 0; text-align: right; border-bottom: 1px solid #e5e7eb; font-weight: 600;">
                                                {{ $total_lessons }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-family: 'Inter', sans-serif; color: #6b7280; font-size: 13px; padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                                                Duración estimada
                                            </td>
                                            <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; padding: 12px 0; text-align: right; border-bottom: 1px solid #e5e7eb; font-weight: 600;">
                                                {{ $course_duration }}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- Resumen de pago -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #ffffff; margin: 35px 0; border: 2px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
                            <tr>
                                <td style="padding: 35px;">
                                    <h3 style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; margin: 0 0 25px 0; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Resumen de Pago</h3>
                                    
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                        <tr>
                                            <td style="font-family: 'Inter', sans-serif; color: #6b7280; font-size: 14px; padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                                                Precio del curso
                                            </td>
                                            <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 14px; padding: 12px 0; text-align: right; border-bottom: 1px solid #e5e7eb; font-weight: 600;">
                                                ${{ number_format($course_price, 2) }}
                                            </td>
                                        </tr>
                                        @if($discount_amount > 0)
                                        <tr>
                                            <td style="font-family: 'Inter', sans-serif; color: #6b7280; font-size: 14px; padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                                                Descuento aplicado ({{ $discount_percentage }}%)
                                            </td>
                                            <td style="font-family: 'Inter', sans-serif; color: rgb(0, 200, 0); font-size: 14px; padding: 12px 0; text-align: right; border-bottom: 1px solid #e5e7eb; font-weight: 600;">
                                                -${{ number_format($discount_amount, 2) }}
                                            </td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 18px; padding: 20px 0 0 0; font-weight: 700;">
                                                Total Pagado
                                            </td>
                                            <td style="font-family: 'Inter', sans-serif; color: rgb(0, 200, 0); font-size: 24px; padding: 20px 0 0 0; text-align: right; font-weight: 700;">
                                                ${{ number_format($amount_paid, 2) }}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- Botón de acceso al curso -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 40px 0;">
                            <tr>
                                <td align="center">
                                    <a href="{{ $course_url }}" 
                                       style="display: inline-block; padding: 18px 50px; background: linear-gradient(135deg, rgb(0, 200, 0) 0%, rgb(0, 160, 0) 100%); color: #ffffff; text-decoration: none; font-family: 'Inter', sans-serif; font-size: 16px; font-weight: 700; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 200, 0, 0.3);">
                                        Acceder al Curso Ahora
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <!-- Información adicional -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #f0fdf4; margin: 35px 0; border-left: 4px solid rgb(0, 200, 0); border-radius: 4px;">
                            <tr>
                                <td style="padding: 25px 30px;">
                                    <p style="font-family: 'Inter', sans-serif; color: #065f46; font-size: 13px; margin: 0; line-height: 1.7;">
                                        <strong>💡 Consejo:</strong> Puedes acceder a tu curso en cualquier momento desde tu panel de estudiante. Todas tus compras están disponibles en la sección "Mis Cursos".
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <!-- Datos fiscales (si aplica) -->
                        @if(!empty($include_billing_info) && $include_billing_info)
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #f9fafb; margin: 35px 0; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
                            <tr>
                                <td style="padding: 35px;">
                                    <h3 style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; margin: 0 0 18px 0; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Datos de Facturación</h3>
                                    
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                        <tr>
                                            <td style="font-family: 'Inter', sans-serif; color: #6b7280; font-size: 13px; padding: 10px 0;">
                                                Razón Social:
                                            </td>
                                            <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; padding: 10px 0; text-align: right; font-weight: 600;">
                                                {{ $billing_name ?? 'N/A' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-family: 'Inter', sans-serif; color: #6b7280; font-size: 13px; padding: 10px 0;">
                                                Dirección:
                                            </td>
                                            <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; padding: 10px 0; text-align: right; font-weight: 600;">
                                                {{ $billing_address ?? 'N/A' }}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        @endif

                        <!-- Mensaje de agradecimiento -->
                        <p style="font-family: 'Inter', sans-serif; color: #6b7280; font-size: 14px; margin: 40px 0 20px 0; line-height: 1.7; text-align: center;">
                            Gracias por confiar en <strong style="color: #1a1a1a;">Promolíder</strong> para tu formación profesional. Si tienes alguna pregunta sobre tu compra, no dudes en contactarnos.
                        </p>

                        <!-- Soporte -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 30px 0;">
                            <tr>
                                <td align="center">
                                    <p style="font-family: 'Inter', sans-serif; color: #9ca3af; font-size: 12px; margin: 0 0 10px 0;">
                                        ¿Necesitas ayuda?
                                    </p>
                                    <a href="mailto:soporte@promolider.info" 
                                       style="font-family: 'Inter', sans-serif; color: rgb(0, 200, 0); font-size: 13px; text-decoration: none; font-weight: 600;">
                                        soporte@promolider.info
                                    </a>
                                </td>
                            </tr>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('emails.layouts.footer')
