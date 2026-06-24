<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CertificateTemplate;

class CertificateTemplateSeeder extends Seeder
{
    public function run()
    {
        CertificateTemplate::create([
            'name' => 'Plantilla Sofisticada',
            'html_template' => <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado</title>
</head>
<body>
    <div style="width: 850px; height: 600px; padding: 40px; border: 8px solid #1e293b; background-color: #ffffff; font-family: 'Montserrat', sans-serif; text-align: center; margin: auto; position: relative; box-shadow: 0 8px 18px rgba(0,0,0,0.15);">

        <!-- Logo -->
        <div style="margin-bottom: 20px;">
            <img src="https://promolider-storage-user.s3.sa-east-1.amazonaws.com/images/promolider_logo_email.png" alt="Logo Promolider" style="max-height: 80px;">
        </div>

        <!-- Título -->
        <h1 style="font-family: 'Playfair Display', serif; font-size: 54px; color: #1e293b; margin-top: 10px; margin-bottom: 20px; font-weight: 700; text-transform: uppercase;">
            Certificado
        </h1>

        <p style="font-size: 18px; color: #4a5568; margin: 0;">
            Otorgado a:
        </p>

        <!-- Nombre del alumno -->
        <h2 style="font-family: 'Montserrat', sans-serif; font-size: 36px; color: #0056b3; margin: 10px 0 25px 0; font-weight: 700; border-bottom: 3px solid #1e293b; display: inline-block; padding-bottom: 5px;">
            {{recipient_name}}
        </h2>

        <p style="font-size: 18px; color: #374151; line-height: 1.6; margin: 0 auto 10px auto; max-width: 85%;">
            Por haber completado satisfactoriamente el curso:
        </p>

        <!-- Nombre del curso -->
        <h3 style="font-size: 24px; color: #111827; margin: 0 0 25px 0; font-weight: 600;">
            {{course_name}}
        </h3>

        <p style="font-size: 16px; color: #6b7280; margin-bottom: 20px;">
            Emitido el {{completion_date}}
        </p>

        <!-- Firmas -->
        <div style="position: absolute; bottom: 60px; left: 0; right: 0; width: 100%; padding: 0 50px; box-sizing: border-box;">
            <div style="display: flex; justify-content: space-around; align-items: flex-end; width: 100%;">
                
                <!-- Firma Instructor -->
                <div style="text-align:center;width:45%;">
                    <div style="height:60px;margin-bottom:10px;">
                        <img src="{{instructor_signature_url}}" alt="Firma Instructor" style="max-height:50px;max-width:150px;">
                    </div>
                    <div style="border-bottom:1px solid #333;height:1px;margin:0 auto 10px auto;width:80%;"></div>
                    <p style="margin:0;font-size:14px;color:#333;">
                        <strong>{{instructor_name}}</strong><br>
                        <span style="font-size:12px;color:#555;">Instructor Principal</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Código -->
        <p style="position: absolute; bottom: 15px; left: 20px; font-size: 10px; color: #9ca3af;">
            Código: {{certificate_code}}
        </p>
    </div>
</body>
</html>
HTML,
            'preview_image' => 'certificates/previews/sofisticada.png',
            'is_active' => 1,
            'design_data' => json_encode([]), 
        ]);
    }
}