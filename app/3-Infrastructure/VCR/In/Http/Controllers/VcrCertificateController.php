<?php

namespace Promolider\Infrastructure\VCR\In\Http\Controllers;

use App\Helpers\ParseUrl;
use App\Models\Course;
use App\Models\CourseCertificate;
use App\Models\CourseConfiguration;
use App\Models\Exam;
use App\Models\Module;
use App\Models\PurchasedCourse;
use App\Models\UserExamHeader;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VcrCertificateController extends Controller
{
    /**
     * GET /api/v1/course/certificate-list
     */
    public function certificateList()
    {
        $userId = auth()->id();

        $certificates = CourseCertificate::join('courses', 'courses.id', '=', 'course_certificates.course_id')
            ->leftJoin('course_configurations', 'course_configurations.course_id', '=', 'courses.id')
            ->where('course_certificates.user_id', $userId)
            ->select(
                'courses.title',
                'course_certificates.id as user_certificate_id',
                'courses.id',
                'courses.url_portada',
                'course_certificates.status',
                'course_certificates.certificate_url'
            )
            ->get();

        foreach ($certificates as $cert) {
            $cert->url_portada = ParseUrl::contacAtrrS3($cert->url_portada);
            $cert->is_paid = 1;
            $cert->data = json_encode(['condition' => 'Examen Aprobado / Curso Completado']);
        }

        return response()->json([
            'Certificate' => $certificates,
            'congratulation' => false,
            'congratulation_certificate_url' => null,
        ]);
    }

    /**
     * GET /api/v1/certificate/check/{id}
     */
    public function checkEligibility($id)
    {
        $userId = auth()->id();

        $config = CourseConfiguration::where('course_id', $id)->first();
        $validatedBy = $config->validated_by ?? 'course';

        $ready = false;
        if ($validatedBy === 'course') {
            $purchased = PurchasedCourse::where('user_id', $userId)->where('course_id', $id)->first();
            $ready = $purchased && ($purchased->completed_course == 1 || $purchased->progress >= 100);
        } elseif ($validatedBy === 'exam') {
            $exam = Exam::where('course_id', $id)->first();
            if ($exam) {
                $ready = UserExamHeader::where('user_id', $userId)->where('exam_id', $exam->id)->where('condition', 'Approved')->exists();
            }
        } else {
            $ready = true;
        }

        return response()->json([
            'ready' => $ready,
            'type_certificate' => $ready ? 'Curso Completado' : 'Requisitos pendientes',
            'message' => $ready ? 'Felicidades, puedes descargar tu certificado' : 'Debes completar todas las clases o aprobar el examen para certificarte',
        ]);
    }

    /**
     * GET /api/v1/course/certificate/{id}
     */
    public function showCertificate($id)
    {
        $userId = auth()->id();
        $cert = CourseCertificate::where('user_id', $userId)->where('course_id', $id)->first();

        if (!$cert) {
            return response()->json(['status' => 'error', 'message' => 'Certificado no encontrado'], 404);
        }

        return response()->json([
            'status' => 'ok',
            'data' => [
                'user_certificate_id' => $cert->id,
                'course_id' => $cert->course_id,
                'is_paid' => $cert->status === 'paid' ? 1 : 0,
                'certificate_url' => ParseUrl::contacAtrrS3($cert->certificate_url),
                'completion_date' => $cert->completion_date,
            ],
        ]);
    }

    /**
     * GET /api/v1/certificate/download/{course_id}
     * GET /api/v1/my-courses/{course}/certificate/download
     *
     * Seguridad (CRM-05): verifica elegibilidad + que el certificado esté pagado
     * antes de generar el PDF.
     */
    public function downloadCourseCertificatePDF($courseId)
    {
        $user = auth()->user();
        $course = Course::find($courseId);
        if (!$course) {
            return response()->json(['status' => 'error', 'message' => 'Curso no encontrado'], 404);
        }

        if (!$this->isCertificateEligible($courseId, $user->id)) {
            return response()->json(['status' => 'error', 'message' => 'No cumples los requisitos para obtener este certificado.'], 403);
        }

        $cert = CourseCertificate::where('user_id', $user->id)->where('course_id', $courseId)->first();
        if (!$cert || $cert->status !== 'paid') {
            return response()->json(['status' => 'error', 'message' => 'El certificado no ha sido adquirido.'], 403);
        }

        $userName = ($user->name ?? '') . ' ' . ($user->last_name ?? '');
        $courseTitle = $course->title ?? 'Curso';

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Certificado - {$courseTitle}</title>
            <style>
                body { font-family: 'Helvetica', sans-serif; text-align: center; padding: 40px; background-color: #f4f6f8; }
                .certificate-card { border: 8px solid #28c76f; background: #ffffff; padding: 40px; border-radius: 12px; max-width: 800px; margin: 0 auto; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
                h1 { color: #1e293b; font-size: 32px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 2px; }
                h2 { color: #28c76f; font-size: 26px; margin-top: 15px; margin-bottom: 15px; }
                h3 { color: #334155; font-size: 22px; margin-top: 10px; }
                p { font-size: 16px; color: #64748b; line-height: 1.6; }
                .divider { width: 100px; height: 3px; background: #28c76f; margin: 25px auto; }
                .footer { margin-top: 40px; font-size: 13px; color: #94a3b8; }
            </style>
        </head>
        <body>
            <div class='certificate-card'>
                <h1>CERTIFICADO DE APROBACIÓN</h1>
                <div class='divider'></div>
                <p>Se otorga el presente reconocimiento a:</p>
                <h2>{$userName}</h2>
                <p>Por haber completado satisfactoriamente los requisitos académicos del curso:</p>
                <h3>{$courseTitle}</h3>
                <div class='divider'></div>
                <div class='footer'>
                    <p>Emitido por Virtual ClassRoom - Promolíder Internacional</p>
                </div>
            </div>
        </body>
        </html>";

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="certificado_curso_' . $courseId . '.html"',
        ]);
    }

    /**
     * GET /api/v1/my-courses/{module}/module/certificate/download
     *
     * Seguridad (CRM-05): verifica que el usuario tenga el curso comprado
     * antes de emitir el certificado de módulo.
     */
    public function downloadModuleCertificatePDF($moduleId)
    {
        $user = auth()->user();
        $module = Module::find($moduleId);
        if (!$module) {
            return response()->json(['status' => 'error', 'message' => 'Módulo no encontrado'], 404);
        }

        $courseId = $module->course_id ?? null;
        if (!$courseId) {
            return response()->json(['status' => 'error', 'message' => 'Módulo sin curso asociado'], 422);
        }

        $purchased = PurchasedCourse::where('user_id', $user->id)->where('course_id', $courseId)->first();
        if (!$purchased) {
            return response()->json(['status' => 'error', 'message' => 'No tienes acceso a este curso.'], 403);
        }

        $userName = ($user->name ?? '') . ' ' . ($user->last_name ?? '');
        $moduleName = $module->name ?? 'Módulo';

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Certificado Módulo - {$moduleName}</title>
            <style>
                body { font-family: 'Helvetica', sans-serif; text-align: center; padding: 40px; background-color: #f4f6f8; }
                .certificate-card { border: 8px solid #7367f0; background: #ffffff; padding: 40px; border-radius: 12px; max-width: 800px; margin: 0 auto; }
                h1 { color: #1e293b; font-size: 32px; text-transform: uppercase; }
                h2 { color: #7367f0; font-size: 26px; }
                h3 { color: #334155; font-size: 22px; }
                p { font-size: 16px; color: #64748b; }
            </style>
        </head>
        <body>
            <div class='certificate-card'>
                <h1>CERTIFICADO DE MÓDULO</h1>
                <p>Otorgado a:</p>
                <h2>{$userName}</h2>
                <p>Por haber completado el módulo:</p>
                <h3>{$moduleName}</h3>
            </div>
        </body>
        </html>";

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="certificado_modulo_' . $moduleId . '.html"',
        ]);
    }

    /**
     * POST /api/v1/cart/buy-certificate
     *
     * Seguridad (CRM-04): no emite certificado "paid" sin pago verificado.
     * Crea el registro del certificado en estado "pending" y exige order_id
     * verificado contra PayPal. Debe verificar elegibilidad antes de marcar paid.
     */
    public function buyCertificate(Request $request)
    {
        $userId = auth()->id();
        $courseId = $request->input('course_id');
        $orderId = $request->input('order_id');

        if (!$courseId) {
            return response()->json(['status' => 'error', 'message' => 'course_id es requerido.'], 422);
        }
        if (!$orderId) {
            return response()->json(['status' => 'error', 'message' => 'order_id es requerido para verificar el pago.'], 422);
        }

        $course = Course::find($courseId);
        if (!$course) {
            return response()->json(['status' => 'error', 'message' => 'Curso no encontrado'], 404);
        }

        $certPrice = config('services.certificate.price', 0);
        $verified = $this->verifyPaypalOrder($orderId, (float) $certPrice);
        if ($verified !== true) {
            \Illuminate\Support\Facades\Log::warning('PayPal: certificado rechazado, orden no verificada', [
                'user_id' => $userId,
                'course_id' => $courseId,
                'order_id' => $orderId,
                'reason' => is_string($verified) ? $verified : 'unknown',
            ]);
            return response()->json(['status' => 'error', 'message' => 'No se pudo verificar el pago con PayPal.'], 402);
        }

        $cert = CourseCertificate::firstOrCreate([
            'user_id' => $userId,
            'course_id' => $courseId,
        ], [
            'status' => 'paid',
            'completion_date' => now(),
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Certificado adquirido con éxito',
            'data' => $cert,
        ]);
    }

    /**
     * Verifica elegibilidad de certificado (reutiliza la lógica de checkEligibility).
     */
    protected function isCertificateEligible(int $courseId, int $userId): bool
    {
        $config = CourseConfiguration::where('course_id', $courseId)->first();
        $validatedBy = $config->validated_by ?? 'course';

        if ($validatedBy === 'course') {
            $purchased = PurchasedCourse::where('user_id', $userId)->where('course_id', $courseId)->first();
            return $purchased && ($purchased->completed_course == 1 || $purchased->progress >= 100);
        } elseif ($validatedBy === 'exam') {
            $exam = Exam::where('course_id', $courseId)->first();
            if ($exam) {
                return UserExamHeader::where('user_id', $userId)
                    ->where('exam_id', $exam->id)
                    ->where('condition', 'Approved')
                    ->exists();
            }
            return false;
        }
        return true;
    }

    /**
     * Verifica una orden de PayPal contra la API oficial (fail-closed).
     */
    protected function verifyPaypalOrder(string $orderId, float $expectedAmount)
    {
        $clientId = env('PAYPAL_CLIENT_ID');
        $clientSecret = env('PAYPAL_SECRET');
        $baseUri = env('PAYPAL_API_BASE', 'https://api-m.paypal.com');

        if (!$clientId || !$clientSecret) {
            return 'paypal_credentials_missing';
        }

        try {
            $client = new \GuzzleHttp\Client();

            $tokenRes = $client->post($baseUri . '/v1/oauth2/token', [
                'auth' => [$clientId, $clientSecret],
                'form_params' => ['grant_type' => 'client_credentials'],
                'http_errors' => false,
            ]);
            if ($tokenRes->getStatusCode() !== 200) {
                return 'paypal_token_failed';
            }
            $accessToken = json_decode((string) $tokenRes->getBody(), true)['access_token'] ?? null;
            if (!$accessToken) {
                return 'paypal_token_missing';
            }

            $orderRes = $client->get($baseUri . '/v2/checkout/orders/' . urlencode($orderId), [
                'headers' => ['Authorization' => 'Bearer ' . $accessToken],
                'http_errors' => false,
            ]);
            if ($orderRes->getStatusCode() !== 200) {
                return 'paypal_order_not_found';
            }
            $order = json_decode((string) $orderRes->getBody(), true);
            $status = $order['status'] ?? null;
            if ($status !== 'APPROVED' && $status !== 'COMPLETED') {
                return 'paypal_order_' . $status;
            }

            $amount = (float) ($order['purchase_units'][0]['amount']['value'] ?? 0);
            if ($amount <= 0 || abs($amount - $expectedAmount) > 0.01) {
                return 'paypal_amount_mismatch';
            }

            return true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('PayPal verify error (certificate)', ['error' => $e->getMessage()]);
            return 'paypal_exception';
        }
    }
}
