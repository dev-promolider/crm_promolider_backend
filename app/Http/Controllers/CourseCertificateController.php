<?php

namespace App\Http\Controllers;

use App\Models\CourseCertificate;
use App\Models\CertificateTemplate;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CourseCertificateController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        Log::info("Listando certificados para el usuario {$userId}", [
            'filters' => $request->only(['course_id', 'status'])
        ]);

        $query = CourseCertificate::with(['template', 'user', 'course'])
            ->where('user_id', $userId);

        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $certificates = $query->paginate(15);

        Log::info("Se encontraron {$certificates->total()} certificados para el usuario {$userId}");

        return $certificates;
    }

    public function store(Request $request)
    {
        $userId = Auth::id();

        // Log del request para debug
        Log::info("Request de certificado recibido", [
            'user_id' => $userId,
            'data' => $request->all()
        ]);

        try {
            $validatedData = $request->validate([
                'template_id'     => 'required|integer|exists:certificate_templates,id',
                'course_id'       => 'required|integer|exists:courses,id',
                'completion_date' => 'required|date|before_or_equal:today',
                'custom_data'     => 'sometimes|array',
                'auto_issue'      => 'sometimes|boolean'
            ], [
                'template_id.required' => 'La plantilla de certificado es obligatoria',
                'template_id.exists' => 'La plantilla seleccionada no existe',
                'course_id.required' => 'El curso es obligatorio',
                'course_id.exists' => 'El curso seleccionado no existe',
                'completion_date.required' => 'La fecha de finalización es obligatoria',
                'completion_date.date' => 'La fecha de finalización debe ser una fecha válida',
                'completion_date.before_or_equal' => 'La fecha de finalización no puede ser futura',
            ]);

        } catch (ValidationException $e) {
            Log::warning("Errores de validación", [
                'user_id' => $userId,
                'errors' => $e->errors()
            ]);
            
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        }

        Log::info("Intento de creación de certificado", [
            'user_id'     => $userId,
            'course_id'   => $validatedData['course_id'],
            'template_id' => $validatedData['template_id'],
        ]);

        // Verificar si el usuario tiene acceso al curso
        $course = Course::findOrFail($validatedData['course_id']);
        
        // Aquí podrías agregar lógica adicional para verificar si el usuario completó el curso
        // Por ejemplo: verificar enrollment, progreso, etc.

        $existingCertificate = CourseCertificate::where('user_id', $userId)
            ->where('course_id', $validatedData['course_id'])
            ->first();

        if ($existingCertificate) {
            Log::warning("El usuario {$userId} ya tiene un certificado para el curso {$validatedData['course_id']}", [
                'existing_certificate_id' => $existingCertificate->id
            ]);

            return response()->json([
                'message' => 'Ya tienes un certificado para este curso',
                'existing_certificate' => $existingCertificate->load(['template', 'user', 'course'])
            ], 422);
        }

        try {
            $certificate = CourseCertificate::create([
                'template_id'      => $validatedData['template_id'],
                'user_id'          => $userId,
                'course_id'        => $validatedData['course_id'],
                'completion_date'  => $validatedData['completion_date'],
                'custom_data'      => $validatedData['custom_data'] ?? null,
                'certificate_code' => strtoupper(uniqid('CERT-')),
                'status'           => 'draft'
            ]);

            // NUEVO: Si el curso no tiene plantilla asignada, asignarla automáticamente
            $course = Course::find($validatedData['course_id']);
            if (!$course->certificate_template_id && $course->user_id === $userId) {
                $course->update(['certificate_template_id' => $validatedData['template_id']]);

                Log::info("Plantilla auto-asignada al curso", [
                    'course_id' => $course->id,
                    'template_id' => $validatedData['template_id']
                ]);
            }
        
            Log::info("Certificado creado exitosamente", [
                'certificate_id'   => $certificate->id,
                'certificate_code' => $certificate->certificate_code,
            ]);
        
            // 🔽 NUEVO BLOQUE: traer firma del instructor desde el curso
            $instructorSignature = $course->instructor_signature_path ?? null;
            if ($instructorSignature) {
                $certificate->update([
                    'instructor_signature_path' => $instructorSignature
                ]);
                Log::info("Firma de instructor añadida al certificado", [
                    'certificate_id' => $certificate->id,
                    'signature'      => $instructorSignature
                ]);
            }
        
            if ($request->get('auto_issue', false)) {
                $certificate->issue();
                Log::info("Certificado {$certificate->id} auto-emitido");
            }
        
            $this->generatePDF($certificate);
            Log::info("PDF generado para certificado {$certificate->id}");
        
            return response()->json($certificate->load(['template', 'user', 'course']), 201);
        
        } catch (\Exception $e) {
            Log::error("Error al crear certificado", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        
            return response()->json([
                'message' => 'Error interno del servidor al crear el certificado'
            ], 500);
        }
    }

    public function show(CourseCertificate $certificate)
    {
        if ($certificate->user_id !== Auth::id()) {
            Log::warning("Acceso denegado al certificado {$certificate->id} por usuario " . Auth::id());
            abort(403, 'No tienes permiso para ver este certificado');
        }

        Log::info("Mostrando certificado {$certificate->id} al usuario " . Auth::id());

        return $certificate->load(['template', 'user', 'course']);
    }

    public function update(Request $request, CourseCertificate $certificate)
    {
        if ($certificate->user_id !== Auth::id()) {
            Log::warning("Intento no autorizado de actualización del certificado {$certificate->id} por usuario " . Auth::id());
            abort(403, 'No tienes permiso para actualizar este certificado');
        }

        $validatedData = $request->validate([
            'template_id'     => 'sometimes|integer|exists:certificate_templates,id',
            'completion_date' => 'sometimes|date|before_or_equal:today',
            'custom_data'     => 'sometimes|array',
            'status'          => 'sometimes|in:draft,issued,revoked'
        ]);

        $certificate->update($validatedData);

        Log::info("Certificado {$certificate->id} actualizado", [
            'changes' => $validatedData
        ]);

        if (isset($validatedData['template_id'])) {
            $this->generatePDF($certificate);
            Log::info("PDF regenerado para certificado {$certificate->id}");
        }

        return response()->json($certificate->load(['template', 'user', 'course']));
    }

    public function issue(CourseCertificate $certificate)
    {
        if ($certificate->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para emitir este certificado');
        }

        if ($certificate->isIssued()) {
            return response()->json([
                'message' => 'El certificado ya ha sido emitido'
            ], 422);
        }

        $certificate->issue();
        $this->generatePDF($certificate); // Regenerar con estado final

        return response()->json([
            'message'     => 'Certificado emitido exitosamente',
            'certificate' => $certificate->load(['template', 'user', 'course'])
        ]);
    }

    public function download($certificateCode)
    {
        $certificate = CourseCertificate::where('certificate_code', $certificateCode)
            ->with(['template', 'user', 'course'])
            ->firstOrFail();

        if ($certificate->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para descargar este certificado');
        }

        if (!$certificate->isIssued()) {
            abort(403, 'Este certificado aún no ha sido emitido');
        }

        if ($certificate->pdf_path && Storage::exists($certificate->pdf_path)) {
            return Storage::download($certificate->pdf_path, 
                "certificado_{$certificate->certificate_code}.pdf");
        }

        // Si no existe el PDF, generarlo
        $this->generatePDF($certificate);
        return Storage::download($certificate->pdf_path, 
            "certificado_{$certificate->certificate_code}.pdf");
    }

    public function downloadStudentCertificate($courseId)
    {
        $userId = Auth::id();

        // Obtener o crear certificado
        $certificateResponse = $this->getStudentCertificate($courseId);
        if ($certificateResponse->getStatusCode() !== 200) {
            return $certificateResponse; // Retornar error si no puede obtener certificado
        }

        $certificate = CourseCertificate::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->firstOrFail();

        if (!$certificate->isIssued()) {
            return response()->json([
                'message' => 'Tu certificado aún no está disponible'
            ], 403);
        }

        // Usar el método de descarga existente
        return $this->download($certificate->certificate_code);
    }

    public function downloadStudentCertificateModule($moduleId)
    {
        $userId = Auth::id();
    
        // Obtener o crear certificado
        $certificateResponse = $this->getStudentCertificateModule($moduleId);
        if ($certificateResponse->getStatusCode() !== 200) {
            return $certificateResponse; // Retornar error si no puede obtener certificado
        }
    
        // Obtener el módulo para conseguir el course_id
        $module = Module::findOrFail($moduleId);
        
        $certificate = CourseCertificate::where('user_id', $userId)
            ->where('module_id', $moduleId) // Usar el course_id del módulo
            ->firstOrFail();
    
        if (!$certificate->isIssued()) {
            return response()->json([
                'message' => 'Tu certificado aún no está disponible'
            ], 403);
        }
    
        // Usar el método de descarga existente
        return $this->download($certificate->certificate_code);
    }

    public function uploadInstructorSignature(Request $request, $courseId)
    {
        Log::info("Intento de subida de firma", [
            'user_id'  => Auth::id(),
            'course_id' => $courseId,
            'input'    => $request->all()
        ]);

        try {
            $request->validate([
                'signature' => 'required|image|mimes:png,jpg,jpeg|max:2048'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("Validación fallida al subir firma", [
                'user_id' => Auth::id(),
                'course_id' => $courseId,
                'errors' => $e->errors()
            ]);
            throw $e; // deja que Laravel devuelva el 422 normal
        }

        $course = Course::find($courseId);

        if (!$course) {
            Log::error("Curso no encontrado al subir firma", [
                'user_id' => Auth::id(),
                'course_id' => $courseId,
            ]);
            abort(404, 'Curso no encontrado');
        }

        // Verificar que el usuario sea el instructor
        if ($course->user_id !== Auth::id()) {
            Log::warning("Acceso denegado al subir firma", [
                'user_id'       => Auth::id(),
                'course_id'     => $course->id,
                'instructor_id' => $course->instructor_id,
            ]);
            abort(403, 'No tienes permiso para subir firma a este curso');
        }

        // Eliminar firma anterior si existe
        if ($course->instructor_signature_path) {
            Log::info("Eliminando firma anterior", [
                'path' => $course->instructor_signature_path
            ]);
            Storage::delete($course->instructor_signature_path);
        }

        $path = $request->file('signature')->store('signatures', 'public');

        $course->update(['instructor_signature_path' => $path]);

        Log::info("Firma subida exitosamente", [
            'user_id' => Auth::id(),
            'course_id' => $course->id,
            'path' => $path
        ]);

        return response()->json([
            'message' => 'Firma subida exitosamente',
            'signature_url' => Storage::url($path)
        ]);
    }

    public function getStudentCertificate($courseId)
    {
        $userId = Auth::id();
        $course = Course::findOrFail($courseId);

        // Buscar certificado existente
        $certificate = CourseCertificate::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        // Si no existe, crear automáticamente
        if (!$certificate) {
            $certificate = $this->createStudentCertificate($course, $userId);
        }

        return response()->json($certificate->load(['template', 'user', 'course']));
    }

    public function getStudentCertificateModule($moduleId)
    {
        $userId = Auth::id();
        $module = Module::findOrFail($moduleId);

        // Buscar certificado existente
        $certificate = CourseCertificate::where('user_id', $userId)
            ->where('module_id', $moduleId)
            ->first();

        // Si no existe, crear automáticamente
        if (!$certificate) {
            $certificate = $this->createStudentCertificateModule($module, $userId);
        }

        return response()->json($certificate->load(['template', 'user', 'course']));
    }

    private function createStudentCertificateModule($module, $userId)
    {
        // Buscar el curso asociado al módulo
        $course = Course::find($module->id_courses);

        if (!$course) {
            throw new \Exception('El curso asociado al módulo no existe');
        }

        // Usar la plantilla asignada al curso, o la primera disponible como fallback
        $template = null;

        if ($course->certificate_template_id) {
            $template = CertificateTemplate::find($course->certificate_template_id);
        }

        if (!$template) {
            $template = CertificateTemplate::where('is_active', 1)->first();
        }

        if (!$template) {
            throw new \Exception('No hay plantillas de certificado disponibles para este curso');
        }

        $certificate = CourseCertificate::create([
            'template_id' => $template->id,
            'user_id' => $userId,
            'module_id' => $module->id,
            'completion_date' => now(),
            'status' => 'issued',
            'issued_at' => now(),
            'certificate_code' => strtoupper(uniqid('CERT-')),
            'instructor_signature_path' => $course->instructor_signature_path
        ]);

        // ✅ Enviar el id del módulo directamente
        $this->generatePDFModule($certificate, $module->id);

        Log::info("Certificado auto-generado para estudiante", [
            'student_id' => $userId,
            'course_id' => $course->id,
            'module_id' => $module->id, // lo agrego al log para mejor trazabilidad
            'certificate_id' => $certificate->id
        ]);

        return $certificate;
    }

    private function createStudentCertificate($course, $userId)
    {
        // Usar la plantilla asignada al curso, o la primera disponible como fallback
        $template = null;

        if ($course->certificate_template_id) {
            $template = CertificateTemplate::find($course->certificate_template_id);
        }

        if (!$template) {
            $template = CertificateTemplate::where('is_active', 1)->first();
        }

        if (!$template) {
            throw new \Exception('No hay plantillas de certificado disponibles para este curso');
        }

        $certificate = CourseCertificate::create([
            'template_id' => $template->id,
            'user_id' => $userId,
            'course_id' => $course->id,
            'completion_date' => now(), // ✅ antes usaba $enrollment->completed_at
            'status' => 'issued',
            'issued_at' => now(),
            'certificate_code' => strtoupper(uniqid('CERT-')),
            'instructor_signature_path' => $course->instructor_signature_path
        ]);

        $this->generatePDF($certificate);

        Log::info("Certificado auto-generado para estudiante", [
            'student_id' => $userId,
            'course_id' => $course->id,
            'certificate_id' => $certificate->id
        ]);

        return $certificate;
    }

    private function generatePDFModule(CourseCertificate $certificate, $moduleId)
    {
        try {
            // Buscar el módulo asociado
            $module = Module::find($moduleId);

            if (!$module) {
                throw new \Exception("El módulo con ID {$moduleId} no existe");
            }

            $templateData = [
                'recipient_name'   => $certificate->user->name,
                // ✅ Usar el nombre del módulo en lugar del título del curso
                'course_name'      => $module->name,
                'completion_date'  => $certificate->completion_date->format('d/m/Y'),
                'certificate_code' => $certificate->certificate_code,
                'issue_date'       => $certificate->issued_at ? $certificate->issued_at->format('d/m/Y') : null,
                'instructor_name'  => $certificate->course->instructor->name ?? 'Productor',
                'instructor_signature_url' => $certificate->instructor_signature_path 
                    ? Storage::url($certificate->instructor_signature_path) 
                    : null,
                'director_signature_url' => null, // Si también necesitas firma del director
            ];

            $htmlContent = $certificate->template->html_template;
            foreach ($templateData as $key => $value) {
                $htmlContent = str_replace("{{{$key}}}", $value, $htmlContent);
            }

            if ($certificate->custom_data) {
                foreach ($certificate->custom_data as $key => $value) {
                    $htmlContent = str_replace("{{{$key}}}", $value, $htmlContent);
                }
            }

            $pdf = Pdf::loadView('certificates.pdf', [
                'certificate' => $certificate,
                'htmlContent' => $htmlContent
            ])->setPaper('a4', 'landscape');

            $filename = 'certificate_' . $certificate->certificate_code . '.pdf';
            $path = 'certificates/' . $filename;

            Storage::put($path, $pdf->output());
            $certificate->update(['pdf_path' => $path]);

            Log::info("PDF generado exitosamente para certificado {$certificate->id}", [
                'path' => $path,
                'module_id' => $module->id,
                'module_name' => $module->name
            ]);

        } catch (\Exception $e) {
            Log::error("Error al generar PDF para certificado {$certificate->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function generatePDF(CourseCertificate $certificate)
    {
        try {
            $templateData = [
                'recipient_name'   => $certificate->user->name,
                'course_name'      => $certificate->course->title,
                'completion_date'  => $certificate->completion_date->format('d/m/Y'),
                'certificate_code' => $certificate->certificate_code,
                'issue_date'       => $certificate->issued_at ? $certificate->issued_at->format('d/m/Y') : null,
                'instructor_name'  => $certificate->course->instructor->name ?? 'Productor',
                'instructor_signature_url' => $certificate->instructor_signature_path 
                    ? Storage::url($certificate->instructor_signature_path) 
                    : null,
                'director_signature_url' => null, // Si también necesitas firma del director
            ];

            $htmlContent = $certificate->template->html_template;
            foreach ($templateData as $key => $value) {
                $htmlContent = str_replace("{{{$key}}}", $value, $htmlContent);
            }

            if ($certificate->custom_data) {
                foreach ($certificate->custom_data as $key => $value) {
                    $htmlContent = str_replace("{{{$key}}}", $value, $htmlContent);
                }
            }

            $pdf = Pdf::loadView('certificates.pdf', [
                'certificate' => $certificate,
                'htmlContent' => $htmlContent
            ])->setPaper('a4', 'landscape'); // 👈 aquí la magia

            $filename = 'certificate_' . $certificate->certificate_code . '.pdf';
            $path = 'certificates/' . $filename;

            Storage::put($path, $pdf->output());
            $certificate->update(['pdf_path' => $path]);

            Log::info("PDF generado exitosamente para certificado {$certificate->id}", [
                'path' => $path
            ]);

        } catch (\Exception $e) {
            Log::error("Error al generar PDF para certificado {$certificate->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function bulkGenerate(Request $request)
    {
        $validatedData = $request->validate([
            'course_id'       => 'required|integer|exists:courses,id',
            'template_id'     => 'required|integer|exists:certificate_templates,id',
            'user_ids'        => 'required|array',
            'user_ids.*'      => 'integer|exists:users,id',
            'completion_date' => 'required|date|before_or_equal:today'
        ]);

        $certificates = [];

        foreach ($validatedData['user_ids'] as $userId) {
            $existing = CourseCertificate::where('user_id', $userId)
                ->where('course_id', $validatedData['course_id'])
                ->first();

            if (!$existing) {
                try {
                    $certificate = CourseCertificate::create([
                        'template_id'     => $validatedData['template_id'],
                        'user_id'         => $userId,
                        'course_id'       => $validatedData['course_id'],
                        'completion_date' => $validatedData['completion_date'],
                        'status'          => 'issued',
                        'issued_at'       => now(),
                        'certificate_code'=> strtoupper(uniqid('CERT-')),
                    ]);

                    $this->generatePDF($certificate);
                    $certificates[] = $certificate->load(['template', 'user', 'course']);

                } catch (\Exception $e) {
                    Log::error("Error al crear certificado para usuario {$userId}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return response()->json([
            'message'      => count($certificates) . ' certificados generados exitosamente',
            'certificates' => $certificates
        ]);
    }

    public function preview(CourseCertificate $certificate)
    {
        if ($certificate->user_id !== Auth::id()) {
            Log::warning("Acceso denegado a la vista previa del certificado {$certificate->id} por usuario " . Auth::id());
            abort(403, 'No tienes permiso para ver este certificado');
        }
    
        Log::info("Generando vista previa del certificado {$certificate->id} para usuario " . Auth::id());
    
        try {
            $templateData = [
                'recipient_name'   => $certificate->user->name,
                'course_name'      => $certificate->course->title,
                'completion_date'  => $certificate->completion_date->format('d/m/Y'),
                'certificate_code' => $certificate->certificate_code,
                'issue_date'       => $certificate->issued_at ? $certificate->issued_at->format('d/m/Y') : null,
                'instructor_name'  => $certificate->course->instructor->name ?? 'N/A'
            ];
        
            $htmlContent = '';
            
            // Si tiene plantilla personalizada, usarla
            if ($certificate->template && $certificate->template->html_template) {
                $htmlContent = $certificate->template->html_template;
                
                // Reemplazar variables de plantilla
                foreach ($templateData as $key => $value) {
                    $htmlContent = str_replace("{{{$key}}}", $value ?? '', $htmlContent);
                }
            
                // Agregar datos personalizados si existen
                if ($certificate->custom_data) {
                    foreach ($certificate->custom_data as $key => $value) {
                        $htmlContent = str_replace("{{{$key}}}", $value ?? '', $htmlContent);
                    }
                }
            } else {
                // Generar plantilla por defecto
                $htmlContent = $this->generateDefaultPreviewTemplate($templateData);
            }
        
            return response()->json([
                'html_content' => $htmlContent,
                'template_data' => $templateData,
                'certificate' => $certificate->load(['template', 'user', 'course'])
            ]);
        
        } catch (\Exception $e) {
            Log::error("Error al generar vista previa del certificado {$certificate->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        
            return response()->json([
                'message' => 'Error al generar la vista previa del certificado'
            ], 500);
        }
    }
    
    private function generateDefaultPreviewTemplate($templateData)
    {
        return '
            <div style="
                width: 794px;
                height: 600px;
                margin: 20px auto;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                text-align: center;
                position: relative;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                border-radius: 10px;
                overflow: hidden;
                font-family: Arial, sans-serif;
            ">
                <!-- Código del certificado -->
                <div style="
                    position: absolute;
                    top: 20px;
                    right: 20px;
                    font-size: 12px;
                    opacity: 0.7;
                    background: rgba(255,255,255,0.1);
                    padding: 5px 10px;
                    border-radius: 15px;
                ">
                    ' . $templateData['certificate_code'] . '
                </div>
                
                <!-- Decoraciones -->
                <div style="
                    position: absolute;
                    top: 20px;
                    left: 20px;
                    width: 80px;
                    height: 80px;
                    border: 3px solid rgba(255,255,255,0.2);
                    border-radius: 50%;
                "></div>
                
                <div style="
                    position: absolute;
                    bottom: 30px;
                    left: 30px;
                    width: 60px;
                    height: 60px;
                    border: 2px solid rgba(255,255,255,0.2);
                    border-radius: 10px;
                    transform: rotate(45deg);
                "></div>
                
                <!-- Encabezado -->
                <div style="padding: 60px 40px 30px; border-bottom: 3px solid rgba(255,255,255,0.3);">
                    <h1 style="
                        font-size: 48px;
                        font-weight: bold;
                        margin: 0 0 20px 0;
                        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
                    ">CERTIFICADO</h1>
                    <p style="
                        font-size: 18px;
                        opacity: 0.9;
                        font-weight: 300;
                        margin: 0;
                    ">de Finalización de Curso</p>
                </div>
                
                <!-- Cuerpo -->
                <div style="padding: 40px; line-height: 1.8;">
                    <p style="font-size: 16px; margin: 15px 0;">Por medio del presente se certifica que</p>
                    
                    <div style="
                        font-size: 26px;
                        font-weight: bold;
                        margin: 25px 0;
                        padding: 15px;
                        border: 2px solid rgba(255,255,255,0.3);
                        border-radius: 10px;
                        background: rgba(255,255,255,0.1);
                    ">
                        ' . $templateData['recipient_name'] . '
                    </div>
                    
                    <p style="font-size: 16px; margin: 15px 0;">ha completado satisfactoriamente el curso</p>
                    
                    <div style="
                        font-size: 20px;
                        font-weight: 600;
                        margin: 20px 0;
                        color: #ffd700;
                    ">
                        "' . $templateData['course_name'] . '"
                    </div>
                    
                    <p style="font-size: 14px; margin: 15px 0;">
                        Finalizado el ' . $templateData['completion_date'] . '
                    </p>
                    
                    ' . ($templateData['issue_date'] ? '
                        <div style="font-size: 12px; margin-top: 20px; opacity: 0.8;">
                            Emitido el ' . $templateData['issue_date'] . '
                        </div>
                    ' : '') . '
                    
                    <div style="
                        position: absolute;
                        bottom: 40px;
                        left: 50%;
                        transform: translateX(-50%);
                        text-align: center;
                    ">
                        <div style="
                            width: 150px;
                            height: 2px;
                            background: rgba(255,255,255,0.5);
                            margin: 0 auto 10px auto;
                        "></div>
                        <p style="margin: 0; font-size: 12px; opacity: 0.8;">Instructor</p>
                        <p style="margin: 0; font-size: 14px; font-weight: bold;">
                            ' . $templateData['instructor_name'] . '
                        </p>
                    </div>
                </div>
            </div>
        ';
    }
}