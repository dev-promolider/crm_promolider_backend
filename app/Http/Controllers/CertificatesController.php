<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Certificates;
use App\Models\UserConfiguration;
use App\Models\Course;
use App\Models\User;
use App\Models\UserCertificate;
use App\Http\Controllers\BadgeController;
use Illuminate\Support\Facades\Storage;
use App\Models\CourseConfiguration;
use App\Models\PurchasedCourse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CertificatesController extends Controller
{
    public function index()
    {
        return view('content.config.certificates-config');
    }

    public function showAll()
    {
        $user = auth()->user();
        //signature
        $signature = UserConfiguration::where(
            ['configuration_id' => 2, 'user_id' => $user->id]
        )->get(); //template
        $templateUserConfig = UserConfiguration::where(
            ['configuration_id' => 1, 'user_id' => $user->id]
        )->get();

        $certificates = Certificates::get();

        $data = [$certificates, $signature, $templateUserConfig];

        return JsonResource::collection($data);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function addCertificate(Request $request)
    {

        $cambioImagen = $request->hasFile('signature') ? 0 : 1;
        $id = $request->id;
        $user = auth()->user();

        $signature = UserConfiguration::where(
            ['configuration_id' => 2, 'user_id' => $user->id]
        )->get();

        if (count($signature) != 0 || $cambioImagen == 0) {
            if (is_null($id)) {
                $certificate = new Certificates();
                $certificate->name = $request->name;
                $certificate->template = $request->template;
                $certificate->id_user = auth()->user()->id;
                $certificate->save();
                if ($cambioImagen == 1) {
                    return response('ok', 200);
                }
            } elseif (!is_null($id)) {
                $certificate = Certificates::find($request->id);
                $certificate->name = $request->name;
                $certificate->template = $request->template;
                $certificate->save();
                if ($cambioImagen == 1) {
                    return response('ok', 200);
                }
            }
            if ($cambioImagen == 0) {
                //$nameImg = time() . '_' . $request->file('signature')->getClientOriginalName();
                $nameImg = $this->saveSignatureS3($user, $request);

                UserConfiguration::updateOrCreate(
                    ['user_id' => $user->id, 'configuration_id' => 2],
                    [
                        'value' => $nameImg,
                    ]
                );
                //$request->file('signature')->storeAs('certificates/signatures', $nameImg);
                return response('ok', 200);
            } else {
                return response('error ', 200);
            }
        } else {
            return response('error_img', 200);
        }
    }
    public function saveSignatureS3($user, $request)
    {
        //no se va eliminara la firma anterior, porque se esta utilizando en algun certificado html
        $path_photo = 'user_photos/' . $user->id;
        $name_photo = $request->file('signature')->getClientOriginalName();

        Storage::disk('s3')->putFileAs($path_photo, $request->file('signature'), $name_photo,  'public');
        $nameImg = $path_photo . '/' . $name_photo;
        return $nameImg;
    }
    public function destroyCertificate($id)
    {

        $certificate = Certificates::find($id);
        if ($certificate->delete()) {
            return response('ok', 200);
        } else {
            return response('error', 200);
        }
    }
    public function saveConfigCertificate(Request $request)
    {

        $validateImagen = $request->hasFile('signature') ? 0 : 1;
        $user = auth()->user();
        //si existe imagen y plantilla
        if ($validateImagen == 0 && $request->id != -1) {

            $nameImg = $this->saveSignatureS3($user, $request);

            UserConfiguration::updateOrCreate(
                ['user_id' => $user->id, 'configuration_id' => 1],
                [
                    'value' => $request->id,
                ]
            );
            UserConfiguration::updateOrCreate(
                ['user_id' => $user->id, 'configuration_id' => 2],
                [
                    'value' => $nameImg,
                ]
            );

            return response('ok', 200);
        } //si no hay imagen -  si solo hay plantilla
        else if ($validateImagen == 1) {
            UserConfiguration::updateOrCreate(
                ['user_id' => $user->id, 'configuration_id' => 1],
                [
                    'value' => $request->id,
                ]
            );
            return response('ok', 200);
        } //si solo hay imagen
        else if ($request->id == -1) {

            $nameImg = $this->saveSignatureS3($user, $request);

            UserConfiguration::updateOrCreate(
                ['user_id' => $user->id, 'configuration_id' => 2],
                [
                    'value' => $nameImg,
                ]
            );

            return response('ok', 200);
        } else {
            return response('error', 200);
        }
    }
    
    public function getCertificateUser($course_id)
    {

        $user = auth()->user();
        $is_paid = UserCertificate::where(['id_user' => $user->id, 'id_course' =>  $course_id, 'is_paid' => 1])->exists();
        $Certificate = PurchasedCourse::where('user_id', $user->id)
            ->where('course_id', $course_id)
            ->first();
        $Certificate->certificate_path = config('global_variables.storage_domain').'/'.$Certificate->certificate_url;
        $data = [
            "Certificate" => $Certificate,
            'is_paid' => $is_paid
        ];
        return response($data, 200);
    }

    public function getCertificateUserList()
    {

        $user = auth()->user();
        $Certificate = UserCertificate::select('courses.title', 'user_certificates.id AS user_certificate_id', 'courses.id', 'courses.portada', 'courses.url_portada','user_certificates.is_paid','course_configuration.data')
            ->join('courses', 'courses.id', '=', 'user_certificates.id_course')
            ->join('course_configuration', 'user_certificates.id_course', '=', 'course_configuration.course_id')
            ->where(['user_certificates.id_user' => $user->id])
            ->distinct('courses.id')
            ->get();

        $congratulation = false;
        $congratulation_certificate_url = "";
        foreach ($Certificate as $cert) {
            $cert->url_portada = config('global_variables.storage_domain') . '/' . $cert->url_portada;
            $congratulation_pending = PurchasedCourse::where('course_id', $cert->id)
                ->where('completed_course', 1)
                ->where('certificate_delivered', 1)
                ->where('certificate_seen', 0)
                ->where('user_id', $user->id)
                ->exists();
            if($congratulation_pending){
                $congratulation = true;
                $congratulation_certificate_url = PurchasedCourse::where('course_id', $cert->id)
                    ->where('completed_course', 1)
                    ->where('certificate_delivered', 1)
                    ->where('certificate_seen', 0)
                    ->where('user_id', $user->id)
                    ->first()
                    ->certificate_url;
                $congratulation_certificate_url = config('global_variables.storage_domain').$congratulation_certificate_url;
            }
        }
        $data = [
            'Certificate' => $Certificate,
            'congratulation' => $congratulation,
            'congratulation_certificate_url' => $congratulation_certificate_url
        ];
        return response($data, 200);
    }

    public function createCertificate($user, $course_id)
    {
        \Log::info("Iniciando creación de certificado", [
            'user_id'   => $user->id,
            'course_id' => $course_id
        ]);

        $course = Course::find($course_id);
        \Log::info("Curso encontrado", ['course' => $course]);

        $plantillaProductor = UserConfiguration::select('id', 'value')
            ->where(['user_id' => $course->user_id, 'configuration_id' => 1])
            ->first();
        \Log::info("Plantilla productor obtenida", ['plantillaProductor' => $plantillaProductor]);

        $signatureProductor = UserConfiguration::select('id', 'value')
            ->where(['user_id' => $course->user_id, 'configuration_id' => 2])
            ->first();
        \Log::info("Firma productor obtenida", ['signatureProductor' => $signatureProductor]);

        $certificate = Certificates::find(optional($plantillaProductor)->value);
        \Log::info("Plantilla de certificado encontrada", ['certificate' => $certificate]);

        $user_productor = User::find($course->user_id);
        \Log::info("Usuario productor encontrado", ['user_productor' => $user_productor]);

        $user_admin = User::find(optional($certificate)->id_user);
        \Log::info("Usuario admin creador de plantilla encontrado", ['user_admin' => $user_admin]);

        $signatureAdmin = UserConfiguration::select('id', 'value')
            ->where(['user_id' => optional($certificate)->id_user, 'configuration_id' => 2])
            ->first();
        \Log::info("Firma admin obtenida", ['signatureAdmin' => $signatureAdmin]);

        // reemplazar valores en plantilla
        $resultado = str_replace("@usuario", ($user->name . " " . $user->last_name), $certificate->template ?? '');
        $resultado = str_replace("@curso", $course->title, $resultado);

        $img_admin = '<img crossorigin="anonymous" class="signatureImg"  src="https://promolider-storage-user.s3-accelerate.amazonaws.com/' . ($signatureAdmin->value ?? '') . '" height="50px"/>';
        $resultado = str_replace("@firma_administrador", $img_admin, $resultado);
        $resultado = str_replace("@administrador", $user_admin->name ?? '', $resultado);

        $img = '<img crossorigin="anonymous" class="signatureImg" src="https://promolider-storage-user.s3-accelerate.amazonaws.com/' . ($signatureProductor->value ?? '') . '" height="50px"/>';
        $resultado = str_replace("@firma_productor", $img, $resultado);
        $resultado = str_replace("@productor", $user_productor->name ?? '', $resultado);

        \Log::info("Plantilla final procesada", ['resultado_preview' => substr($resultado, 0, 200)]); // solo los primeros 200 chars

        $this->screenshot($course_id, $user);
        \Log::info("Screenshot ejecutado", ['course_id' => $course_id, 'user_id' => $user->id]);

        $courseConfig = CourseConfiguration::where('course_id', $course_id)->first();
        $isPaid = 1;
        if ($courseConfig && $courseConfig->type_certificate == 0) {
            $isPaid = 0;
        }
        \Log::info("Configuración de curso para certificado", [
            'courseConfig' => $courseConfig,
            'isPaid'       => $isPaid
        ]);

        $cert = UserCertificate::updateOrCreate(
            ['id_user' => $user->id, 'id_course' => $course_id],
            [
                'certificate' => $resultado,
                'is_paid'     => $isPaid
            ]
        );
        \Log::info("Certificado guardado/actualizado en UserCertificate", ['certificate_record' => $cert]);

        // CREAR INSIGNIA
        app(BadgeController::class)->validateBadgesCertificates($user->id);
        \Log::info("Validación de insignias ejecutada", ['user_id' => $user->id]);

        return $resultado;
    }

    public function screenshot($course_id, $user)
    {
        \Log::info("Iniciando screenshot", [
            'course_id' => $course_id,
            'user_id'   => $user->id
        ]);

        $verify = PurchasedCourse::where([
                "course_id" => $course_id,
                "user_id" => $user->id,
                "completed_course" => 1
            ])->exists();

        \Log::info("¿Curso completado?", ['verify' => $verify]);

        if ($verify) {
            $purchased = PurchasedCourse::where([
                    "course_id" => $course_id,
                    "user_id" => $user->id,
                ])->first();

            $token = env('ACCESS_CONVERSOR');
            $url = urlencode("http://crm.promolider.info/get-certificado?course_id=$course_id&user_id=$user->id");

            $width = 1100;
            $height = 580;
            $delay = 10000;
            $output = 'image';

            $query = "https://shot.screenshotapi.net/screenshot";
            $query .= "?token=$token&url=$url&width=$width&height=$height&output=$output&delay=$delay";

            \Log::info("Generando screenshot con API", [
                'api_url'   => $query,
                'course_id' => $course_id,
                'user_id'   => $user->id
            ]);

            $date = date("YmdHis_");
            $path = 'certificates/';
            $name = $date . $course_id . "_" . $user->id . ".png";

            try {
                $content = file_get_contents($query);

                // Guardar en S3
                Storage::disk('s3')->put($path . $name, $content, 'public');

                // Guardar también en el proyecto (storage/app/public/certificates)
                Storage::disk('public')->put($path . $name, $content);

                // Actualizar PurchasedCourse con la ruta de S3 (puedes cambiarlo a la local si prefieres)
                $purchased->certificate_url = $path . $name;
                $purchased->update();

                \Log::info("Screenshot guardado en S3 y local", [
                    's3_url'    => Storage::disk('s3')->url($path . $name),
                    'local_url' => asset("storage/$path$name")
                ]);
            } catch (\Exception $e) {
                \Log::error("Error generando screenshot", [
                    'message'   => $e->getMessage(),
                    'course_id' => $course_id,
                    'user_id'   => $user->id
                ]);
            }
        }
    }

    public function downloadCertificate($course_id)
    {
        \Log::info("=== INICIANDO DESCARGA DE CERTIFICADO ===", [
            'course_id' => $course_id,
            'timestamp' => now()
        ]);

        \Log::info("Usuario autenticado obtenido", [
            'user_id' => Auth::id(),
        ]);

        // Buscar el curso comprado y completado
        \Log::info("Buscando curso comprado con criterios:", [
            'course_id' => $course_id,
            'user_id' => Auth::id(),
            'completed_course' => 1,
            'certificate_delivered' => 1
        ]);

        $purchased = PurchasedCourse::where([
            "course_id" => $course_id,
            "user_id" => Auth::id(),
            "completed_course" => 1,
        ])->first();
        
        \Log::info("Resultado de búsqueda de curso comprado", [
            'purchased_found' => $purchased ? true : false,
            'purchased_data' => $purchased ? $purchased->toArray() : null
        ]);

        if (!$purchased) {
            \Log::warning("No se encontró curso comprado que cumpla criterios", [
                'course_id' => $course_id,
                'user_id' => Auth::id()
            ]);
            return response()->json(['error' => 'Curso no encontrado o no completado'], 404);
        }

        if (!$purchased->certificate_url) {
            \Log::warning("Curso encontrado pero sin certificate_url", [
                'course_id' => $course_id,
                'user_id' => Auth::id(),
                'purchased_id' => $purchased->id
            ]);
            return response()->json(['error' => 'Certificado no generado'], 404);
        }

        \Log::info("Certificate_url encontrado", [
            'certificate_url_raw' => $purchased->certificate_url
        ]);

        // Verificar si la URL ya tiene el dominio completo
        $certificateUrl = $purchased->certificate_url;
        $hasHttpPrefix = str_starts_with($certificateUrl, 'http');

        \Log::info("Verificando formato de URL", [
            'original_url' => $certificateUrl,
            'has_http_prefix' => $hasHttpPrefix
        ]);

        if (!$hasHttpPrefix) {
            // Si no tiene el dominio, agregarlo
            $domain = config('global_variables.storage_domain');
            $certificateUrl = $domain . '/' . $certificateUrl;

            \Log::info("URL completada con dominio", [
                'domain' => $domain,
                'final_url' => $certificateUrl
            ]);
        }

        // Extraer el nombre del archivo de la URL
        $parsedUrl = parse_url($certificateUrl, PHP_URL_PATH);
        $filename = basename($parsedUrl);
        $fallbackFilename = 'certificado_' . $course_id . '.png';

        \Log::info("Procesando nombre de archivo", [
            'parsed_path' => $parsedUrl,
            'extracted_filename' => $filename,
            'fallback_filename' => $fallbackFilename,
            'final_filename' => $filename ?: $fallbackFilename
        ]);

        $finalResponse = [
            'success' => true,
            'download_url' => $certificateUrl,
            'filename' => $filename ?: $fallbackFilename
        ];

        \Log::info("=== DESCARGA DE CERTIFICADO EXITOSA ===", [
            'response_data' => $finalResponse
        ]);

        return response()->json($finalResponse);
    }

    public function getCertificado(Request $request)
    {
        // ⚠️ Usamos el user_id que llega por parámetro, no el auth()
        $user = User::findOrFail($request->user_id);
    
        $verify = PurchasedCourse::where([
                "course_id" => $request->course_id,
                "user_id" => $user->id,
                "completed_course" => 1
            ])
            ->exists();
        
        if ($verify) {
            $course = Course::findOrFail($request->course_id);
        
            $plantillaProductor = UserConfiguration::select('id', 'value')
                ->where(['user_id' => $course->user_id, 'configuration_id' => 1])
                ->first();
        
            $signatureProductor = UserConfiguration::select('id', 'value')
                ->where(['user_id' => $course->user_id, 'configuration_id' => 2])
                ->first();
        
            $certificate = Certificates::find(optional($plantillaProductor)->value);
            $user_productor = User::find($course->user_id);
            $user_admin = User::find(optional($certificate)->id_user);
        
            $signatureAdmin = UserConfiguration::select('id', 'value')
                ->where(['user_id' => optional($certificate)->id_user, 'configuration_id' => 2])
                ->first();
        
            $img_admin = '<img crossorigin="anonymous" class="signatureImg" src="https://promolider-storage-user.s3-accelerate.amazonaws.com/' . ($signatureAdmin->value ?? '') . '" height="50px"/>';
            $img = '<img crossorigin="anonymous" class="signatureImg" src="https://promolider-storage-user.s3-accelerate.amazonaws.com/' . ($signatureProductor->value ?? '') . '" height="50px"/>';
        
            $usuario = $user->name . " " . $user->last_name;
            $curso = $course->title;
            $firma_administrador = $img_admin;
            $administrador = $user_admin->name ?? '';
            $firma_productor = $img;
            $productor = $user_productor->name ?? '';
        
            return view('content.certificado', compact(
                'usuario', 'curso',
                'firma_administrador', 'administrador',
                'firma_productor', 'productor'
            ));
        }
    
        // ⚠️ Devuelve algo válido si no hay verificación
        return response("<h1>Certificado no disponible</h1>", 200);
    }
}