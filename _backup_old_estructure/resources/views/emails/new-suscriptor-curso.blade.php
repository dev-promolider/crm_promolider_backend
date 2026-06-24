@php
    // Variables para el header
    $titulo_principal = '🎓 Nueva Inscripción a Curso';
    $subtitulo = 'Un nuevo estudiante se ha inscrito';
@endphp

@include('emails.layouts.header', ['titulo_principal' => $titulo_principal, 'subtitulo' => $subtitulo])

<!-- Contenido principal -->
<div class="u-row-container" style="padding: 0px; background-color: transparent">
    <div class="u-row" style="margin: 0 auto; min-width: 320px; max-width: 600px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #ffffff;">
        <div style="border-collapse: collapse; display: table; width: 100%; background-color: transparent;">
            <div class="u-col u-col-100" style="max-width: 320px; min-width: 600px; display: table-cell; vertical-align: top;">
                <div style="width: 100% !important;">
                    <div style="padding: 30px 25px;">
                        
                        <!-- Saludo -->
                        <p style="font-size: 18px; color: #2d3748; margin: 0 0 20px 0; font-weight: bold;">¡Hola, Administrador! 👋</p>

                        <p style="color: #4a5568; margin: 0 0 25px 0; font-size: 15px; line-height: 1.6;">
                            Te notificamos que un nuevo estudiante se ha inscrito a uno de tus cursos en la plataforma Promolíder.
                        </p>

                        <!-- Tarjeta de confirmación -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; margin: 25px 0; overflow: hidden; box-shadow: 0 8px 24px rgba(16, 185, 129, 0.2);">
                            <tr>
                                <td align="center" style="padding: 25px 20px;">
                                    <div style="width: 60px; height: 60px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px; font-size: 30px; backdrop-filter: blur(10px);">
                                        ✅
                                    </div>
                                    <h2 style="color: #ffffff; font-size: 22px; margin: 0 0 8px 0; font-weight: bold;">¡Nueva Inscripción Confirmada!</h2>
                                    <p style="color: rgba(255, 255, 255, 0.9); font-size: 14px; margin: 0;">El estudiante ya tiene acceso al curso</p>
                                </td>
                            </tr>
                        </table>

                        <!-- Información del Estudiante -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f0f9ff; border-radius: 10px; margin: 25px 0; border-left: 4px solid #3b82f6;">
                            <tr>
                                <td style="padding: 20px;">
                                    <h3 style="color: #1e40af; font-size: 16px; margin: 0 0 15px 0; font-weight: bold;">👤 Datos del Estudiante</h3>
                                    
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                        <tr>
                                            <td style="color: #1e40af; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #dbeafe;">
                                                <strong>Nombre completo:</strong>
                                            </td>
                                            <td style="color: #1e3a8a; font-size: 14px; padding: 8px 0; text-align: right; border-bottom: 1px solid #dbeafe; font-weight: 600;">
                                                {{ $subscriber_name }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="color: #1e40af; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #dbeafe;">
                                                <strong>Correo electrónico:</strong>
                                            </td>
                                            <td style="color: #1e3a8a; font-size: 14px; padding: 8px 0; text-align: right; border-bottom: 1px solid #dbeafe; font-weight: 600;">
                                                {{ $subscriber_email }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="color: #1e40af; font-size: 14px; padding: 8px 0; border-bottom: 1px solid #dbeafe;">
                                                <strong>Teléfono:</strong>
                                            </td>
                                            <td style="color: #1e3a8a; font-size: 14px; padding: 8px 0; text-align: right; border-bottom: 1px solid #dbeafe; font-weight: 600;">
                                                {{ $subscriber_phone ?? 'No proporcionado' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="color: #1e40af; font-size: 14px; padding: 8px 0;">
                                                <strong>Fecha de inscripción:</strong>
                                            </td>
                                            <td style="color: #1e3a8a; font-size: 14px; padding: 8px 0; text-align: right; font-weight: 600;">
                                                {{ $subscription_date }}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- Información del Curso -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #faf5ff; border-radius: 10px; margin: 25px 0; border-left: 4px solid #8b5cf6;">
                            <tr>
                                <td style="padding: 20px;">
                                    <h3 style="color: #6b21a8; font-size: 16px; margin: 0 0 15px 0; font-weight: bold;">📚 Curso al que se inscribió</h3>
                                    
                                    <h2 style="color: #2d3748; font-size: 20px; font-weight: bold; margin: 0 0 12px 0; line-height: 1.3;">{{ $course_title }}</h2>

                                    @if(!empty($course_image))
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 15px 0;">
                                        <tr>
                                            <td align="center">
                                                <img src="{{ $course_image }}" 
                                                     alt="{{ $course_title }}" 
                                                     width="500" 
                                                     style="display: block; width: 100%; max-width: 500px; height: auto; border-radius: 8px; border: 2px solid #e9d5ff;">
                                            </td>
                                        </tr>
                                    </table>
                                    @endif

                                    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 15px;">
                                        <tr>
                                            <td style="color: #6b21a8; font-size: 14px; padding: 5px 0;">
                                                📂 <strong>Categoría:</strong> {{ $course_category }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="color: #6b21a8; font-size: 14px; padding: 5px 0;">
                                                📊 <strong>Nivel:</strong> {{ $course_level }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="color: #6b21a8; font-size: 14px; padding: 5px 0;">
                                                💰 <strong>Precio:</strong> {{ $course_price }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="color: #6b21a8; font-size: 14px; padding: 5px 0;">
                                                👥 <strong>Total inscritos:</strong> {{ $total_subscribers }} estudiantes
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- Estadísticas rápidas -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); border-radius: 10px; margin: 25px 0;">
                            <tr>
                                <td style="padding: 20px;">
                                    <h3 style="color: #374151; font-size: 16px; margin: 0 0 15px 0; font-weight: bold; text-align: center;">📊 Estadísticas del Curso</h3>
                                    
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                        <tr>
                                            <td style="text-align: center; padding: 10px;" width="33.33%">
                                                <div style="background: #ffffff; border-radius: 8px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                                    <div style="font-size: 24px; color: #10b981; font-weight: bold;">{{ $total_subscribers }}</div>
                                                    <div style="font-size: 12px; color: #6b7280; margin-top: 5px;">Inscritos</div>
                                                </div>
                                            </td>
                                            <td style="text-align: center; padding: 10px;" width="33.33%">
                                                <div style="background: #ffffff; border-radius: 8px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                                    <div style="font-size: 24px; color: #3b82f6; font-weight: bold;">{{ $course_modules }}</div>
                                                    <div style="font-size: 12px; color: #6b7280; margin-top: 5px;">Módulos</div>
                                                </div>
                                            </td>
                                            <td style="text-align: center; padding: 10px;" width="33.33%">
                                                <div style="background: #ffffff; border-radius: 8px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                                    <div style="font-size: 24px; color: #8b5cf6; font-weight: bold;">{{ $course_lessons }}</div>
                                                    <div style="font-size: 12px; color: #6b7280; margin-top: 5px;">Lecciones</div>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- Acciones recomendadas -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; margin: 25px 0;">
                            <tr>
                                <td style="padding: 20px;">
                                    <h3 style="color: #92400e; font-size: 16px; margin: 0 0 12px 0; font-weight: bold;">💡 Acciones recomendadas</h3>
                                    
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                        <tr><td style="color: #92400e; font-size: 14px; padding: 4px 0;">• Puedes dar la bienvenida al estudiante directamente</td></tr>
                                        <tr><td style="color: #92400e; font-size: 14px; padding: 4px 0;">• Revisa el progreso de tus estudiantes en el panel</td></tr>
                                        <tr><td style="color: #92400e; font-size: 14px; padding: 4px 0;">• Mantén actualizado el contenido del curso</td></tr>
                                        <tr><td style="color: #92400e; font-size: 14px; padding: 4px 0;">• Interactúa con tus estudiantes en los foros</td></tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- Botón de acceso al curso -->
                        @if(!empty($course_url))
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 30px 0;">
                            <tr>
                                <td align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" style="border-radius: 10px; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);">
                                        <tr>
                                            <td style="border-radius: 10px; background: transparent;">
                                                <a href="{{ $course_url }}" 
                                                   target="_blank"
                                                   style="display: block; color: #ffffff; font-weight: bold; font-size: 16px; text-decoration: none; padding: 16px 32px; border-radius: 10px; text-align: center; line-height: 1.2;">
                                                    📚 Ver Detalles del Curso
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        @endif

                        <!-- Mensaje de cierre -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f9fafb; border-radius: 8px; margin: 25px 0; padding: 20px; border-top: 1px solid #e5e7eb;">
                            <tr>
                                <td style="text-align: center;">
                                    <p style="color: #6b7280; font-size: 14px; margin: 0 0 8px 0;">
                                        Este es un correo informativo automático generado por el sistema Promolíder.
                                    </p>
                                    <p style="color: #9ca3af; font-size: 12px; margin: 0;">
                                        Puedes gestionar todas tus notificaciones desde tu panel de control.
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

@include('emails.layouts.footer')
