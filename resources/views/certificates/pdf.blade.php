<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificado - {{ $certificate->certificate_code }}</title>
    
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        
        .certificate-container {
            width: 100%;
            height: 100vh;
            position: relative;
            overflow: hidden;
        }
        
        .certificate-content {
            width: 100%;
            height: 100%;
            position: relative;
        }
        
        /* Estilos por defecto para el certificado */
        .default-certificate {
            width: 794px;
            height: 1123px;
            margin: 0 auto;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .certificate-header {
            padding: 60px 40px 40px;
            border-bottom: 3px solid rgba(255,255,255,0.3);
        }
        
        .certificate-title {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .certificate-subtitle {
            font-size: 18px;
            opacity: 0.9;
            font-weight: 300;
        }
        
        .certificate-body {
            padding: 60px 40px;
            line-height: 1.8;
        }
        
        .recipient-name {
            font-size: 36px;
            font-weight: bold;
            margin: 30px 0;
            padding: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 10px;
            background: rgba(255,255,255,0.1);
        }
        
        .course-name {
            font-size: 28px;
            font-weight: 600;
            margin: 20px 0;
            color: #ffd700;
        }
        
        .certificate-text {
            font-size: 16px;
            margin: 20px 0;
            line-height: 1.6;
        }
        
        .certificate-footer {
            position: absolute;
            bottom: 40px;
            left: 0;
            right: 0;
            padding: 0 40px;
        }
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        
        .signature-block {
            text-align: center;
            width: 200px;
        }
        
        .signature-line {
            border-top: 2px solid rgba(255,255,255,0.5);
            margin-bottom: 10px;
            width: 100%;
        }
        
        .certificate-code {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 12px;
            opacity: 0.7;
            background: rgba(255,255,255,0.1);
            padding: 5px 10px;
            border-radius: 15px;
        }
        
        .certificate-date {
            font-size: 14px;
            margin-top: 20px;
            opacity: 0.8;
        }
        
        /* Decoraciones */
        .decoration {
            position: absolute;
            opacity: 0.1;
        }
        
        .decoration-1 {
            top: 20px;
            left: 20px;
            width: 100px;
            height: 100px;
            border: 3px solid white;
            border-radius: 50%;
        }
        
        .decoration-2 {
            bottom: 20px;
            left: 20px;
            width: 80px;
            height: 80px;
            border: 2px solid white;
            border-radius: 10px;
            transform: rotate(45deg);
        }
        
        .decoration-3 {
            top: 50%;
            right: 20px;
            width: 60px;
            height: 60px;
            border: 2px solid white;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="certificate-content">
            @if(isset($htmlContent) && !empty($htmlContent))
                <!-- Contenido HTML personalizado de la plantilla -->
                {!! $htmlContent !!}
            @else
                <!-- Plantilla por defecto -->
                <div class="default-certificate">
                    <div class="certificate-code">
                        {{ $certificate->certificate_code }}
                    </div>
                    
                    <!-- Decoraciones -->
                    <div class="decoration decoration-1"></div>
                    <div class="decoration decoration-2"></div>
                    <div class="decoration decoration-3"></div>
                    
                    <div class="certificate-header">
                        <h1 class="certificate-title">CERTIFICADO</h1>
                        <p class="certificate-subtitle">de Finalización de Curso</p>
                    </div>
                    
                    <div class="certificate-body">
                        <p class="certificate-text">Por medio del presente se certifica que</p>
                        
                        <div class="recipient-name">
                            {{ $certificate->user->name ?? 'N/A' }}
                        </div>
                        
                        <p class="certificate-text">ha completado satisfactoriamente el curso</p>
                        
                        <div class="course-name">
                            "{{ $certificate->course->title ?? 'N/A' }}"
                        </div>
                        
                        <p class="certificate-text">
                            con fecha de finalización el {{ \Carbon\Carbon::parse($certificate->completion_date)->format('d \d\e F \d\e Y') }}
                        </p>
                        
                        @if($certificate->issued_at)
                        <div class="certificate-date">
                            Emitido el {{ \Carbon\Carbon::parse($certificate->issued_at)->format('d \d\e F \d\e Y') }}
                        </div>
                        @endif
                    </div>
                    
                    <div class="certificate-footer">
                        <div class="signature-section" style="display: table; width: 100%;">
                            <div class="signature-block" style="display: table-cell; text-align: center; width: 50%;">
                                <div class="signature-line"></div>
                                <p style="margin: 5px 0; font-size: 12px;">Instructor</p>
                                <p style="margin: 0; font-size: 14px; font-weight: bold;">
                                    {{ $certificate->course->instructor->name ?? 'N/A' }}
                                </p>
                            </div>
                            <div style="display: table-cell; width: 100px;"></div>
                            <div class="signature-block" style="display: table-cell; text-align: center; width: 50%;">
                                <div class="signature-line"></div>
                                <p style="margin: 5px 0; font-size: 12px;">Fecha de emisión</p>
                                <p style="margin: 0; font-size: 14px; font-weight: bold;">
                                    {{ $certificate->issued_at ? \Carbon\Carbon::parse($certificate->issued_at)->format('d/m/Y') : \Carbon\Carbon::now()->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</body>
</html>