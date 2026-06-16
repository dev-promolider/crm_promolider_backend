@php
    // Variables para el header
    $titulo_principal = '¡Felicitaciones por Completar el Curso!';
    $subtitulo = 'Has finalizado con éxito tu formación';
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
                        <p style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; font-size: 28px; color: #1a1a1a; margin: 0 0 15px 0; font-weight: 700;">Felicitaciones, {{ $student_name }}</p>
                        <p style="font-family: 'Inter', sans-serif; color: #555555; margin: 0 0 40px 0; font-size: 15px; line-height: 1.7;">
                            Ha completado exitosamente el curso <span style="color: #1a1a1a; font-weight: 600;">{{ $course_title }}</span>. Este logro representa un hito importante en su desarrollo profesional.
                        </p>

                        <!-- Tarjeta de felicitación -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #1f2937; margin: 40px 0; border: 1px solid #e5e7eb;">
                            <tr>
                                <td align="center" style="padding: 45px 30px;">
                                    <div style="width: 72px; height: 72px; background: #374151; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 40px;">
                                        🏆
                                    </div>
                                    <h2 style="font-family: 'Inter', sans-serif; color: #ffffff; font-size: 26px; margin: 0 0 12px 0; font-weight: 700;">Curso Completado</h2>
                                    <p style="font-family: 'Inter', sans-serif; color: #d1d5db; font-size: 14px; margin: 0; line-height: 1.6;">
                                        Su compromiso y dedicación han generado resultados excepcionales
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <!-- Información del curso -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #f9fafb; margin: 35px 0; border: 1px solid #e5e7eb;">
                            <tr>
                                <td style="padding: 35px;">
                                    <h3 style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; margin: 0 0 18px 0; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Detalles del Curso</h3>
                                    
                                    <h2 style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 22px; font-weight: 700; margin: 0 0 22px 0;">{{ $course_title }}</h2>

                                    @if(!empty($course_image))
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 25px 0;">
                                        <tr>
                                            <td align="center">
                                                <img src="{{ $course_image }}" 
                                                     alt="{{ $course_title }}" 
                                                     width="500" 
                                                     style="display: block; width: 100%; max-width: 500px; height: auto; border: 1px solid #e5e7eb;">
                                            </td>
                                        </tr>
                                    </table>
                                    @endif

                                    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 20px;">
                                        <tr>
                                            <td style="font-family: 'Inter', sans-serif; color: #555555; font-size: 13px; padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                                                <strong>Categoría:</strong>
                                            </td>
                                            <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; padding: 12px 0; text-align: right; border-bottom: 1px solid #e5e7eb; font-weight: 600;">
                                                {{ $course_category }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-family: 'Inter', sans-serif; color: #555555; font-size: 13px; padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                                                <strong>Instructor:</strong>
                                            </td>
                                            <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; padding: 12px 0; text-align: right; border-bottom: 1px solid #e5e7eb; font-weight: 600;">
                                                {{ $instructor_name }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-family: 'Inter', sans-serif; color: #555555; font-size: 13px; padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                                                <strong>Fecha de finalización:</strong>
                                            </td>
                                            <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; padding: 12px 0; text-align: right; border-bottom: 1px solid #e5e7eb; font-weight: 600;">
                                                {{ $completion_date }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-family: 'Inter', sans-serif; color: #555555; font-size: 13px; padding: 12px 0;">
                                                <strong>Tiempo invertido:</strong>
                                            </td>
                                            <td style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; padding: 12px 0; text-align: right; font-weight: 600;">
                                                {{ $total_time_spent }}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- Estadísticas del logro -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 35px 0;">
                            <tr>
                                <td style="padding: 0;">
                                    <h3 style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; margin: 0 0 20px 0; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Rendimiento Académico</h3>
                                    
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                        <tr>
                                            <td style="text-align: center; padding: 12px;" width="33.33%">
                                                <div style="background: #ffffff; padding: 24px 16px; border: 1px solid #e5e7eb;">
                                                    <div style="font-family: 'Inter', sans-serif; font-size: 32px; color: #1a1a1a; font-weight: 700;">{{ $lessons_completed }}</div>
                                                    <div style="font-family: 'Inter', sans-serif; font-size: 11px; color: #666666; margin-top: 8px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px;">Lecciones</div>
                                                </div>
                                            </td>
                                            <td style="text-align: center; padding: 12px;" width="33.33%">
                                                <div style="background: #ffffff; padding: 24px 16px; border: 1px solid #e5e7eb;">
                                                    <div style="font-family: 'Inter', sans-serif; font-size: 32px; color: #1a1a1a; font-weight: 700;">{{ $modules_completed }}</div>
                                                    <div style="font-family: 'Inter', sans-serif; font-size: 11px; color: #666666; margin-top: 8px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px;">Módulos</div>
                                                </div>
                                            </td>
                                            <td style="text-align: center; padding: 12px;" width="33.33%">
                                                <div style="background: #ffffff; padding: 24px 16px; border: 1px solid #e5e7eb;">
                                                    <div style="font-family: 'Inter', sans-serif; font-size: 32px; color: #1a1a1a; font-weight: 700;">100%</div>
                                                    <div style="font-family: 'Inter', sans-serif; font-size: 11px; color: #666666; margin-top: 8px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px;">Progreso</div>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- Certificado -->
                        @if($has_certificate)
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #1f2937; margin: 35px 0; border: 1px solid #e5e7eb;">
                            <tr>
                                <td style="padding: 35px;">
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                        <tr>
                                            <td width="60" valign="middle">
                                                <div style="width: 48px; height: 48px; background: #374151; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                                                    🎓
                                                </div>
                                            </td>
                                            <td valign="middle" style="padding-left: 16px;">
                                                <h3 style="font-family: 'Inter', sans-serif; color: #ffffff; font-size: 18px; margin: 0 0 6px 0; font-weight: 600;">Certificado Oficial</h3>
                                                <p style="font-family: 'Inter', sans-serif; color: #d1d5db; font-size: 13px; margin: 0; line-height: 1.5;">
                                                    Descargue y comparta su certificado oficial
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 22px;">
                                        <tr>
                                            <td align="center">
                                                <table cellpadding="0" cellspacing="0" border="0" style="background: #ffffff; border: 1px solid #e5e7eb;">
                                                    <tr>
                                                        <td style="padding: 0;">
                                                            <a href="{{ $certificate_url }}" 
                                                               target="_blank"
                                                               style="display: block; font-family: 'Inter', sans-serif; color: #1a1a1a; font-weight: 600; font-size: 14px; text-decoration: none; padding: 14px 32px; text-align: center;">
                                                                Descargar Certificado
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        @endif

                        <!-- Compartir en redes sociales -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #f9fafb; margin: 35px 0; border: 1px solid #e5e7eb;">
                            <tr>
                                <td style="padding: 35px;">
                                    <h3 style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; margin: 0 0 12px 0; font-weight: 600; text-align: center; text-transform: uppercase; letter-spacing: 0.5px;">Comparta su Logro</h3>
                                    
                                    <p style="font-family: 'Inter', sans-serif; color: #555555; font-size: 13px; margin: 0 0 20px 0; text-align: center; line-height: 1.6;">
                                        Comparta su éxito en sus redes profesionales
                                    </p>

                                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                        <tr>
                                            <td align="center" style="padding: 10px;">
                                                <a href="https://www.linkedin.com/sharing/share-offsite/?url=https://vcr.promolider.info/courses" target="_blank" style="text-decoration: none; display: inline-block;">
                                                    <img src="https://cdn-icons-png.flaticon.com/512/174/174857.png" width="48" height="48" alt="LinkedIn" style="display: block; border: 0;">
                                                </a>
                                                <p style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 11px; margin: 8px 0 0 0; font-weight: 600;">LinkedIn</p>
                                            </td>
                                            <td align="center" style="padding: 10px;">
                                                <a href="https://www.facebook.com/sharer/sharer.php?u=https://vcr.promolider.info/courses" target="_blank" style="text-decoration: none; display: inline-block;">
                                                    <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" width="48" height="48" alt="Facebook" style="display: block; border: 0;">
                                                </a>
                                                <p style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 11px; margin: 8px 0 0 0; font-weight: 600;">Facebook</p>
                                            </td>
                                            <td align="center" style="padding: 10px;">
                                                <a href="https://twitter.com/intent/tweet?text=Acabo%20de%20completar%20un%20curso%20en%20Promolíder&url=https://vcr.promolider.info/courses" target="_blank" style="text-decoration: none; display: inline-block;">
                                                    <img src="https://cdn-icons-png.flaticon.com/512/5968/5968958.png" width="48" height="48" alt="X (Twitter)" style="display: block; border: 0;">
                                                </a>
                                                <p style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 11px; margin: 8px 0 0 0; font-weight: 600;">X (Twitter)</p>
                                            </td>
                                            <td align="center" style="padding: 10px;">
                                                <a href="https://www.instagram.com/" target="_blank" style="text-decoration: none; display: inline-block;">
                                                    <img src="https://cdn-icons-png.flaticon.com/512/2111/2111463.png" width="48" height="48" alt="Instagram" style="display: block; border: 0;">
                                                </a>
                                                <p style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 11px; margin: 8px 0 0 0; font-weight: 600;">Instagram</p>
                                            </td>
                                            <td align="center" style="padding: 10px;">
                                                <a href="https://wa.me/?text=Acabo%20de%20completar%20un%20curso%20en%20Promolíder%20https://vcr.promolider.info/courses" target="_blank" style="text-decoration: none; display: inline-block;">
                                                    <img src="https://cdn-icons-png.flaticon.com/512/733/733585.png" width="48" height="48" alt="WhatsApp" style="display: block; border: 0;">
                                                </a>
                                                <p style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 11px; margin: 8px 0 0 0; font-weight: 600;">WhatsApp</p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- Próximos pasos -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #f9fafb; margin: 35px 0; border: 1px solid #e5e7eb;">
                            <tr>
                                <td style="padding: 35px;">
                                    <h3 style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; margin: 0 0 18px 0; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Próximos Pasos</h3>
                                    
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                        <tr><td style="font-family: 'Inter', sans-serif; color: #444444; font-size: 13px; padding: 10px 0; line-height: 1.6;">■ <strong style="color: #1a1a1a;">Aplique sus conocimientos</strong> — Implemente lo aprendido en proyectos reales</td></tr>
                                        <tr><td style="font-family: 'Inter', sans-serif; color: #444444; font-size: 13px; padding: 10px 0; line-height: 1.6;">■ <strong style="color: #1a1a1a;">Explore nuevos cursos</strong> — Amplíe sus habilidades con nuestra oferta educativa</td></tr>
                                        <tr><td style="font-family: 'Inter', sans-serif; color: #444444; font-size: 13px; padding: 10px 0; line-height: 1.6;">■ <strong style="color: #1a1a1a;">Comparta su opinión</strong> — Deje una reseña para inspirar a otros</td></tr>
                                        <tr><td style="font-family: 'Inter', sans-serif; color: #444444; font-size: 13px; padding: 10px 0; line-height: 1.6;">■ <strong style="color: #1a1a1a;">Promocione su logro</strong> — Comparta su certificado en redes</td></tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- Cursos recomendados
                        @if(!empty($recommended_courses) && count($recommended_courses) > 0)
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #ffffff; margin: 35px 0;">
                            <tr>
                                <td style="padding: 0;">
                                    <h3 style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 13px; margin: 0 0 22px 0; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Cursos Recomendados</h3>
                                    
                                    @foreach($recommended_courses as $course)
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #ffffff; margin-bottom: 16px; border: 1px solid #e5e7eb;">
                                        <tr>
                                            <td width="110" style="padding: 14px;">
                                                <img src="{{ $course['image'] }}" alt="{{ $course['title'] }}" style="width: 100%; height: auto; display: block; border: 1px solid #e5e7eb;">
                                            </td>
                                            <td style="padding: 14px;">
                                                <h4 style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 15px; margin: 0 0 6px 0; font-weight: 600;">{{ $course['title'] }}</h4>
                                                <p style="font-family: 'Inter', sans-serif; color: #888888; font-size: 12px; margin: 0 0 8px 0;">{{ $course['category'] }}</p>
                                                <a href="{{ $course['url'] }}" target="_blank" style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 12px; font-weight: 600; text-decoration: none;">Ver curso →</a>
                                            </td>
                                        </tr>
                                    </table>
                                    @endforeach
                                </td>
                            </tr>
                        </table>
                        @endif -->

                        <!-- Botón principal -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 40px 0;">
                            <tr>
                                <td align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" style="background: #1f2937; border: 1px solid #1f2937;">
                                        <tr>
                                            <td>
                                                <a href="https://vcr.promolider.info/courses" 
                                                   target="_blank"
                                                   style="display: block; font-family: 'Inter', sans-serif; color: #ffffff; font-weight: 600; font-size: 15px; text-decoration: none; padding: 16px 40px; text-align: center;">
                                                    Descubra Más Cursos
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- Mensaje motivacional final -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #f5f7fa; margin: 35px 0; border: 1px solid #e5e7eb; border-left: 4px solid #1a1a1a; padding: 30px;">
                            <tr>
                                <td style="text-align: center;">
                                    <p style="font-family: 'Inter', sans-serif; color: #1a1a1a; font-size: 15px; margin: 0 0 8px 0; font-weight: 600; line-height: 1.6;">
                                        "Cada logro es un paso hacia la excelencia profesional."
                                    </p>
                                    <p style="font-family: 'Inter', sans-serif; color: #666666; font-size: 12px; margin: 0;">
                                        Equipo Promolíder
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <!-- Nota de agradecimiento -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f5f7fa; margin: 35px 0; padding: 24px; border: 1px solid #e5e7eb; text-align: center;">
                            <tr>
                                <td>
                                    <p style="font-family: 'Inter', sans-serif; color: #444444; font-size: 13px; margin: 0 0 6px 0;">
                                        Gracias por elegir Promolíder para su desarrollo profesional.
                                    </p>
                                    <p style="font-family: 'Inter', sans-serif; color: #666666; font-size: 12px; margin: 0;">
                                        ¿Preguntas? Contáctenos en <a href="mailto:soporte@promolider.info" style="color: #1a1a1a; text-decoration: none; font-weight: 600;">soporte@promolider.info</a>
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