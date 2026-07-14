<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class MarketplaceRegistrationController extends Controller
{
    private function getDistributorModel(string $type): string
    {
        return match ($type) {
            'masterclass' => \App\Models\MasterclassDistributor::class,
            'ebook' => \App\Models\EbookDistributor::class,
            'mini-course', 'minicourse' => \App\Models\MiniCourseDistributor::class,
            default => throw new \InvalidArgumentException("Invalid type: {$type}"),
        };
    }

    private function getProductForeignKey(string $type): string
    {
        return match ($type) {
            'masterclass' => 'masterclass_id',
            'ebook' => 'ebook_id',
            'mini-course', 'minicourse' => 'mini_course_id',
            default => throw new \InvalidArgumentException("Invalid type: {$type}"),
        };
    }

    private function getFrontendBaseUrl(): string
    {
        return rtrim(env('MARKETING_FRONTEND_URL', env('FRONTEND_URL', 'http://localhost:5173')), '/');
    }

    private function getRegistrationRoute(string $type): string
    {
        return '/invitacion';
    }

    private function getProductModel(string $type): string
    {
        return match ($type) {
            'masterclass' => \App\Models\Masterclass::class,
            'ebook' => \App\Models\Ebook::class,
            'mini-course', 'minicourse' => \App\Models\Minicourse::class,
            default => throw new \InvalidArgumentException("Invalid type: {$type}"),
        };
    }

    private function resolveProductId(Request $request, string $type, int|string|null $productId = null): ?int
    {
        if ($productId !== null && $productId !== '') {
            return (int) $productId;
        }

        $bodyField = match ($type) {
            'masterclass' => 'masterclass_id',
            'ebook' => 'ebook_id',
            'mini-course', 'minicourse' => 'mini_course_id',
            default => 'product_id',
        };

        $bodyValue = $request->input($bodyField);
        return $bodyValue ? (int) $bodyValue : null;
    }

    // ──────────────────────────────────────────────
    //  MÉTODOS WRAPPER
    // ──────────────────────────────────────────────

    public function registerAsMasterclassDistributor(Request $request, int|string|null $productId = null): JsonResponse
    {
        return $this->registerAsDistributor($request, 'masterclass', $productId);
    }

    public function checkMasterclassRegistration(Request $request, int|string|null $productId = null): JsonResponse
    {
        return $this->checkRegistration($request, 'masterclass', $productId);
    }

    public function checkMasterclassInvitation(Request $request, int|string|null $productId = null): JsonResponse
    {
        return $this->checkInvitation($request, 'masterclass', $productId);
    }

    public function createMasterclassInvitation(Request $request, int|string|null $productId = null): JsonResponse
    {
        return $this->createInvitation($request, 'masterclass', $productId);
    }

    public function registerAsEbookDistributor(Request $request, int|string|null $productId = null): JsonResponse
    {
        return $this->registerAsDistributor($request, 'ebook', $productId);
    }

    public function checkEbookRegistration(Request $request, int|string|null $productId = null): JsonResponse
    {
        return $this->checkRegistration($request, 'ebook', $productId);
    }

    public function checkEbookInvitation(Request $request, int|string|null $productId = null): JsonResponse
    {
        return $this->checkInvitation($request, 'ebook', $productId);
    }

    public function createEbookInvitation(Request $request, int|string|null $productId = null): JsonResponse
    {
        return $this->createInvitation($request, 'ebook', $productId);
    }

    public function registerAsMinicourseDistributor(Request $request, int|string|null $productId = null): JsonResponse
    {
        return $this->registerAsDistributor($request, 'mini-course', $productId);
    }

    public function checkMinicourseRegistration(Request $request, int|string|null $productId = null): JsonResponse
    {
        return $this->checkRegistration($request, 'mini-course', $productId);
    }

    public function checkMinicourseInvitation(Request $request, int|string|null $productId = null): JsonResponse
    {
        return $this->checkInvitation($request, 'mini-course', $productId);
    }

    public function createMinicourseInvitation(Request $request, int|string|null $productId = null): JsonResponse
    {
        return $this->createInvitation($request, 'mini-course', $productId);
    }

    // ──────────────────────────────────────────────
    //  MÉTODOS PRINCIPALES
    // ──────────────────────────────────────────────

    public function registerAsDistributor(Request $request, string $type, int|string|null $productId = null): JsonResponse
    {
        try {
            $userId = $request->user()->id ?? $request->input('user_id');
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Usuario no autenticado'], 401);
            }

            if (!in_array($type, ['masterclass', 'ebook', 'mini-course', 'minicourse'])) {
                return response()->json(['success' => false, 'message' => 'Tipo inválido'], 400);
            }

            $productId = $this->resolveProductId($request, $type, $productId);
            if (!$productId) {
                return response()->json(['success' => false, 'message' => 'ID del producto requerido'], 400);
            }

            $modelClass = $this->getDistributorModel($type);
            $foreignKey = $this->getProductForeignKey($type);

            $existing = $modelClass::where('user_id', $userId)
                ->where($foreignKey, $productId)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ya estás registrado como distribuidor',
                    'isRegistered' => true,
                    'isPurchased' => true,
                ]);
            }

            $modelClass::create([
                'user_id' => $userId,
                $foreignKey => $productId,
                'code' => '0',
                'expires_at' => now()->addDays(7),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registrado exitosamente como distribuidor',
                'isRegistered' => true,
                'isPurchased' => true,
            ]);
        } catch (Throwable $e) {
            Log::error("Error registering as distributor for {$type}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al registrar'], 500);
        }
    }

    public function checkRegistration(Request $request, string $type, int|string|null $productId = null): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }

            $productId = $this->resolveProductId($request, $type, $productId);
            if (!$productId) {
                return response()->json(['success' => false, 'message' => 'ID del producto requerido'], 400);
            }

            $modelClass = $this->getDistributorModel($type);
            $foreignKey = $this->getProductForeignKey($type);

            $isRegistered = $modelClass::where('user_id', $userId)
                ->where($foreignKey, $productId)
                ->exists();

            return response()->json([
                'success' => true,
                'isRegistered' => $isRegistered,
                'isPurchased' => $isRegistered,
                'message' => $isRegistered ? 'Registrado' : 'No registrado',
            ]);
        } catch (Throwable $e) {
            Log::error("Error checking registration for {$type}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al verificar'], 500);
        }
    }

    public function createInvitation(Request $request, string $type, int|string|null $productId = null): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }

            $productId = $this->resolveProductId($request, $type, $productId);
            if (!$productId) {
                return response()->json(['success' => false, 'message' => 'ID del producto requerido'], 400);
            }

            $modelClass = $this->getDistributorModel($type);
            $foreignKey = $this->getProductForeignKey($type);
            $route = $this->getRegistrationRoute($type);

            $code = $userId . Str::random(10);

            $distributor = $modelClass::where('user_id', $userId)
                ->where($foreignKey, $productId)
                ->first();

            if ($distributor) {
                $distributor->update([
                    'code' => $code,
                    'expires_at' => now()->addDays(7),
                ]);
            } else {
                $distributor = $modelClass::create([
                    'user_id' => $userId,
                    $foreignKey => $productId,
                    'code' => $code,
                    'expires_at' => now()->addDays(7),
                ]);
            }

            $frontendUrl = $this->getFrontendBaseUrl();
            $invitationLink = "{$frontendUrl}{$route}?invitation_code={$code}";

            return response()->json([
                'success' => true,
                'link' => $invitationLink,
                'code' => $code,
                'expires_at' => $distributor->expires_at->toIso8601String(),
                'message' => 'Link de invitación creado exitosamente',
            ]);
        } catch (Throwable $e) {
            Log::error("Error creating invitation for {$type}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear link'], 500);
        }
    }

    // ──────────────────────────────────────────────
    //  ENDPOINTS PÚBLICOS
    // ──────────────────────────────────────────────

    /**
     * GET /api/v1/products/invitation/{code}
     */
    public function getInvitationInfo(string $code): JsonResponse
    {
        try {
            $tables = [
                'masterclass' => [\App\Models\MasterclassDistributor::class, \App\Models\Masterclass::class, 'masterclass_id'],
                'ebook' => [\App\Models\EbookDistributor::class, \App\Models\Ebook::class, 'ebook_id'],
                'mini-course' => [\App\Models\MiniCourseDistributor::class, \App\Models\Minicourse::class, 'mini_course_id'],
            ];

            $foundType = null;
            $distributor = null;
            $product = null;

            foreach ($tables as $type => [$distModel, $prodModel, $fk]) {
                $record = $distModel::where('code', $code)->first();
                if ($record) {
                    $foundType = $type;
                    $distributor = $record;
                    $product = $prodModel::with('images')->find($record->{$fk});
                    break;
                }
            }

            if (!$distributor || !$product) {
                return response()->json(['success' => false, 'message' => 'Código de invitación no válido'], 404);
            }

            if ($distributor->expires_at && $distributor->expires_at < now()) {
                return response()->json(['success' => false, 'message' => 'El enlace de invitación ha expirado'], 410);
            }

            $productData = $product->toArray();
            if (method_exists($product, 'images') && $product->images->count() > 0) {
                $productData['image'] = asset($product->images->first()->image);
            }

            return response()->json([
                'success' => true,
                'type' => $foundType,
                'product' => $productData,
                'distributor_id' => $distributor->id,
                'expires_at' => $distributor->expires_at?->toIso8601String(),
            ]);
        } catch (Throwable $e) {
            Log::error('Error getting invitation info: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al validar invitación'], 500);
        }
    }

    /**
     * POST /api/v1/products/register
     * Registra un estudiante, suscribe como participante (masterclass),
     * genera token (mini-cursos), notifica al distribuidor y envía email.
     */
    public function registerStudent(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string',
                'name' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'country' => 'required|string|max:100',
                'age' => 'required|integer|min:1|max:120',
                'user_type' => 'nullable|in:Guest,Affiliate',
            ]);

            $tables = [
                'masterclass' => [\App\Models\MasterclassDistributor::class, \App\Models\MasterclassUser::class, 'masterclass_distributor_id', 'masterclass_id'],
                'ebook' => [\App\Models\EbookDistributor::class, \App\Models\EbookUser::class, 'ebook_distributor_id', 'ebook_id'],
                'mini-course' => [\App\Models\MiniCourseDistributor::class, \App\Models\MiniCourseUser::class, 'mini_course_distributors_id', 'mini_course_id'],
            ];

            $foundType = null;
            $distributor = null;
            $userModelClass = null;
            $foreignKey = null;
            $productForeignKey = null;

            foreach ($tables as $type => [$distModel, $userModel, $fk, $productFk]) {
                $record = $distModel::where('code', $validated['code'])->first();
                if ($record) {
                    $foundType = $type;
                    $distributor = $record;
                    $userModelClass = $userModel;
                    $foreignKey = $fk;
                    $productForeignKey = $productFk;
                    break;
                }
            }

            if (!$distributor) {
                return response()->json(['success' => false, 'message' => 'Código de invitación no válido'], 404);
            }

            // Check duplicate email
            $existing = $userModelClass::where($foreignKey, $distributor->id)
                ->where('email', $validated['email'])
                ->exists();
            if ($existing) {
                return response()->json(['success' => false, 'message' => 'Este correo ya está registrado en este curso'], 409);
            }

            // ─── 1. CREAR ESTUDIANTE ─────────────────────────
            $student = $userModelClass::create([
                $foreignKey => $distributor->id,
                'name' => $validated['name'],
                'lastname' => $validated['lastname'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'nationality' => $validated['country'],
                'age' => $validated['age'],
                'user_type' => $validated['user_type'] ?? 'Guest',
            ]);

            // ─── 2. (MASTERCLASS) NOTA: Ya no se crea MasterClassParticipant
            //      porque masterclass_user ya registra al estudiante.
            //      La tabla master_class_participants es legacy y su FK
            //      apunta a master_class_video.id, no a masterclasses.id.
            //      En la nueva arquitectura, masterclass_user es suficiente.

            // ─── 3. (MINI-CURSO) GENERAR TOKEN DE ACCESO ─────
            $accessLink = null;
            if ($foundType === 'mini-course') {
                $accessToken = Str::random(32);
                $student->update([
                    'access_token' => $accessToken,
                    'token_expires_at' => now()->addDays(30),
                ]);
                $frontendUrl = $this->getFrontendBaseUrl();
                $accessLink = "{$frontendUrl}/mini-course/access/{$distributor->{$productForeignKey}}?token={$accessToken}";
            }

            // ─── 4. NOTIFICAR AL DISTRIBUIDOR ────────────────
            try {
                $studentName = $validated['name'] . ' ' . $validated['lastname'];
                $typeLabel = match ($foundType) {
                    'masterclass' => 'masterclass',
                    'ebook' => 'ebook',
                    'mini-course' => 'mini curso',
                    default => 'producto',
                };

                $productModel = $this->getProductModel($foundType);
                $product = $productModel::find($distributor->{$productForeignKey});

                \App\Models\Notifications::create([
                    'id_receiver' => $distributor->user_id,
                    'type' => 0,
                    'title' => 'Nuevo Estudiante Registrado',
                    'body' => "{$studentName} se ha inscrito en tu {$typeLabel}: {$product->title}",
                ]);
            } catch (\Exception $e) {
                Log::warning('Error al notificar al distribuidor: ' . $e->getMessage());
            }

            // ─── 5. ENVIAR EMAIL DE CONFIRMACIÓN ─────────────
            try {
                $this->sendConfirmationEmail($student, $foundType, $distributor, $productForeignKey, $accessLink);
            } catch (\Exception $e) {
                Log::warning('Error al enviar email de confirmación: ' . $e->getMessage());
            }

            $responseData = [
                'success' => true,
                'message' => 'Registro exitoso',
                'data' => $student,
            ];
            if ($accessLink) {
                $responseData['access_link'] = $accessLink;
            }

            return response()->json($responseData);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Datos inválidos', 'errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            Log::error('Error registering student: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al registrar'], 500);
        }
    }

    /**
     * Enviar email de confirmacion al estudiante segun el tipo de producto.
     */
    private function sendConfirmationEmail($student, string $type, $distributor, string $productForeignKey, ?string $accessLink = null): void
    {
        $productModel = $this->getProductModel($type);
        $product = $productModel::with('images')->find($distributor->{$productForeignKey});

        if (!$product) {
            Log::warning('Producto no encontrado para enviar email', ['type' => $type, 'id' => $distributor->{$productForeignKey}]);
            return;
        }

        $userName = $student->name . ' ' . $student->lastname;
        $studentEmail = $student->email;

        try {
            $mailService = new \Promolider\Infrastructure\Marketing\Out\Services\PHPMailerService();

            switch ($type) {
                case 'masterclass':
                    $mailService->sendEmailWithTemplate(
                        $studentEmail,
                        'Confirmacion de Inscripcion - ' . $product->title,
                        'vendor.marketing.emails.masterclass-confirmation',
                        [
                            'userName' => $userName,
                            'lastname' => $student->lastname,
                            'email' => $studentEmail,
                            'country' => $student->nationality ?? $student->country ?? '',
                            'masterclassTitle' => $product->title,
                            'masterclassDescription' => $product->description,
                            'date' => $product->date ? date('d/m/Y', strtotime($product->date)) : '',
                            'hour' => $product->hour ?? '',
                            'objectives' => $product->objectives ?? $product->description,
                            'meetingLink' => $product->meeting_link ?? null,
                        ]
                    );
                    break;

                case 'ebook':
                    $pdfLink = null;
                    if (method_exists($product, 'documents') && $product->documents->count() > 0) {
                        $pdfLink = asset($product->documents->first()->document);
                    }

                    $mailService->sendEmailWithTemplate(
                        $studentEmail,
                        'Acceso a tu E-book - ' . $product->title,
                        'vendor.marketing.emails.ebook-confirmation',
                        [
                            'userName' => $userName,
                            'ebookTitle' => $product->title,
                            'ebookDescription' => $product->description,
                            'pdfLink' => $pdfLink,
                            'ebook' => $product,
                        ]
                    );
                    break;

                case 'mini-course':
                    if (!$accessLink) {
                        Log::warning('No se genero accessLink para minicurso', ['student_id' => $student->id]);
                        return;
                    }

                    $mailService->sendEmailWithTemplate(
                        $studentEmail,
                        'Acceso a tu Mini Curso - ' . $product->title,
                        'vendor.marketing.emails.minicourse-access',
                        [
                            'userName' => $userName,
                            'courseTitle' => $product->title,
                            'courseDescription' => $product->description,
                            'accessLink' => $accessLink,
                            'mini_course' => $product,
                        ]
                    );
                    break;
            }

            Log::info("Email de confirmacion enviado a {$studentEmail} para {$type}: {$product->title}");
        } catch (\Exception $e) {
            Log::error("Error enviando email de confirmacion de {$type}: " . $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────
    //  HELPERS: MODELOS DE USUARIO
    // ──────────────────────────────────────────────

    private function getUserModel(string $type): string
    {
        return match ($type) {
            'masterclass' => \App\Models\MasterclassUser::class,
            'ebook' => \App\Models\EbookUser::class,
            'mini-course', 'minicourse' => \App\Models\MiniCourseUser::class,
            default => throw new \InvalidArgumentException("Invalid type: {$type}"),
        };
    }

    private function getUserTable(string $type): string
    {
        return match ($type) {
            'masterclass' => 'masterclass_user',
            'ebook' => 'ebook_users',
            'mini-course', 'minicourse' => 'mini_course_users',
            default => throw new \InvalidArgumentException("Invalid type: {$type}"),
        };
    }

    // ──────────────────────────────────────────────
    //  PARTICIPANT STATUS – UPDATE
    // ──────────────────────────────────────────────

    public function updateParticipantStatus(Request $request, string $type, int $userId): JsonResponse
    {
        Log::info("🎯 [updateParticipantStatus] Solicitud recibida", [
            'type' => $type,
            'user_id' => $userId,
            'request' => $request->all()
        ]);

        if (!in_array($type, ['masterclass', 'ebook', 'mini-course', 'minicourse'])) {
            return response()->json(['success' => false, 'message' => 'Tipo inválido'], 400);
        }

        // Validar que el user_id exista en la tabla correspondiente
        $table = $this->getUserTable($type);
        $validator = \Illuminate\Support\Facades\Validator::make(['user_id' => $userId], [
            'user_id' => 'required|integer|exists:' . $table . ',id',
        ]);

        if ($validator->fails()) {
            Log::warning("⚠️ [updateParticipantStatus] Validación fallida", [
                'errors' => $validator->errors()->toArray()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'ID de usuario no válido',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validar body: participant debe ser 0,1,2,3
        $request->validate([
            'participant' => 'required|in:0,1,2,3'
        ]);

        try {
            $modelClass = $this->getUserModel($type);
            $user = $modelClass::find($userId);

            if (!$user) {
                Log::warning("⚠️ [updateParticipantStatus] Usuario no encontrado", [
                    'user_id' => $userId
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            $user->isParticipant = (int) $request->input('participant');
            $user->save();

            Log::info("✅ [updateParticipantStatus] Estado actualizado", [
                'type' => $type,
                'user_id' => $user->id,
                'email' => $user->email,
                'isParticipant' => $user->isParticipant
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado de participante actualizado exitosamente',
                'data' => [
                    'user_id' => $user->id,
                    'name' => $user->name . ' ' . $user->lastname,
                    'email' => $user->email,
                    'isParticipant' => (bool) $user->isParticipant
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ [updateParticipantStatus] Error", [
                'type' => $type,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ──────────────────────────────────────────────
    //  OBSERVATION – UPDATE
    // ──────────────────────────────────────────────

    public function updateObservation(Request $request, string $type, int $userId): JsonResponse
    {
        Log::info("📝 [updateObservation] Solicitud recibida", [
            'type' => $type,
            'user_id' => $userId,
            'request' => $request->all()
        ]);

        if (!in_array($type, ['masterclass', 'ebook', 'mini-course', 'minicourse'])) {
            return response()->json(['success' => false, 'message' => 'Tipo inválido'], 400);
        }

        // Validar que el user_id exista en la tabla correspondiente
        $table = $this->getUserTable($type);
        $validator = \Illuminate\Support\Facades\Validator::make(['user_id' => $userId], [
            'user_id' => 'required|integer|exists:' . $table . ',id',
        ]);

        if ($validator->fails()) {
            Log::warning("⚠️ [updateObservation] Validación fallida", [
                'errors' => $validator->errors()->toArray()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'ID de usuario no válido',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validar body: observation requerido, string, máx 1000
        $request->validate([
            'observation' => 'required|string|max:1000'
        ]);

        try {
            $modelClass = $this->getUserModel($type);
            $user = $modelClass::find($userId);

            if (!$user) {
                Log::warning("⚠️ [updateObservation] Usuario no encontrado", [
                    'user_id' => $userId
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            $user->observation = $request->input('observation');
            $user->save();

            Log::info("✅ [updateObservation] Observación actualizada", [
                'type' => $type,
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Observación actualizada exitosamente',
                'data' => [
                    'user_id' => $user->id,
                    'name' => $user->name . ' ' . $user->lastname,
                    'email' => $user->email,
                    'observation' => $user->observation
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ [updateObservation] Error", [
                'type' => $type,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ──────────────────────────────────────────────
    //  WRAPPERS POR TIPO — PARTICIPANT STATUS
    // ──────────────────────────────────────────────

    public function updateMasterclassParticipantStatus(Request $request, int $userId): JsonResponse
    {
        return $this->updateParticipantStatus($request, 'masterclass', $userId);
    }

    public function updateEbookParticipantStatus(Request $request, int $userId): JsonResponse
    {
        return $this->updateParticipantStatus($request, 'ebook', $userId);
    }

    public function updateMiniCourseParticipantStatus(Request $request, int $userId): JsonResponse
    {
        return $this->updateParticipantStatus($request, 'mini-course', $userId);
    }

    // ──────────────────────────────────────────────
    //  WRAPPERS POR TIPO — OBSERVATION
    // ──────────────────────────────────────────────

    public function updateMasterclassObservation(Request $request, int $userId): JsonResponse
    {
        return $this->updateObservation($request, 'masterclass', $userId);
    }

    public function updateEbookObservation(Request $request, int $userId): JsonResponse
    {
        return $this->updateObservation($request, 'ebook', $userId);
    }

    public function updateMiniCourseObservation(Request $request, int $userId): JsonResponse
    {
        return $this->updateObservation($request, 'mini-course', $userId);
    }

    // ──────────────────────────────────────────────
    //  VALIDATE DISTRIBUTOR
    // ──────────────────────────────────────────────

    /**
     * POST /marketing/marketplace/validate-distributor
     * Valida que el nombre del distribuidor coincida con el del usuario autenticado.
     * Replica el metodo validateDistributor del monolito (MarketingService).
     */
    public function validateDistributor(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $distributorName = $request->input('distributor_name');

            if (!$distributorName) {
                return response()->json(['success' => false, 'message' => 'distributor_name es requerido'], 400);
            }

            $user = \App\Models\User::find($userId);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
            }

            $isValid = strtolower(trim($user->name)) === strtolower(trim($distributorName));

            return response()->json(['success' => $isValid]);
        } catch (\Throwable $e) {
            Log::error('Error al validar distribuidor', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error al validar'], 500);
        }
    }

    // ──────────────────────────────────────────────
    //  ACCESS MINI-COURSE (ENDPOINT PÚBLICO)
    // ──────────────────────────────────────────────

    /**
     * GET /api/v1/minicourse/access/{id}?token=...
     * Valida el token de acceso al mini-curso y devuelve el contenido.
     */
    public function accessMiniCourse(int $id, Request $request): JsonResponse
    {
        try {
            $token = $request->query('token');

            if (!$token) {
                return response()->json(['success' => false, 'message' => 'Token de acceso requerido'], 400);
            }

            // Buscar el usuario registrado con el token válido
            $miniCourseUser = \App\Models\MiniCourseUser::whereHas('distributor', function ($query) use ($id) {
                $query->where('mini_course_id', $id);
            })
            ->where('access_token', $token)
            ->where('token_expires_at', '>', now())
            ->first();

            if (!$miniCourseUser) {
                return response()->json(['success' => false, 'message' => 'Token de acceso inválido o expirado'], 404);
            }

            // Actualizar último acceso
            $miniCourseUser->update(['last_accessed_at' => now()]);

            // Obtener el mini-curso con módulos, clases y documentos
            $miniCourse = \App\Models\Minicourse::with([
                'modules',
                'images',
                'classes.documents',
            ])->find($id);

            if (!$miniCourse) {
                return response()->json(['success' => false, 'message' => 'Mini curso no encontrado'], 404);
            }

            // Formatear rutas de assets
            $miniCourse->images->each(fn($img) => $img->image = asset($img->image));
            $miniCourse->classes->each(function ($class) {
                $class->video = $class->video ? asset($class->video) : null;
                $class->documents->each(fn($doc) => $doc->document = asset($doc->document));
            });

            return response()->json([
                'success' => true,
                'mini_course' => $miniCourse,
                'user' => [
                    'id' => $miniCourseUser->id,
                    'name' => $miniCourseUser->name,
                    'lastname' => $miniCourseUser->lastname,
                    'email' => $miniCourseUser->email,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error accessing mini-course: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al acceder al mini curso'], 500);
        }
    }

    // ──────────────────────────────────────────────
    //  CHECK INVITATION
    // ──────────────────────────────────────────────

    public function checkInvitation(Request $request, string $type, int|string|null $productId = null): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }

            $productId = $this->resolveProductId($request, $type, $productId);
            if (!$productId) {
                return response()->json(['success' => false, 'message' => 'ID del producto requerido'], 400);
            }

            $modelClass = $this->getDistributorModel($type);
            $foreignKey = $this->getProductForeignKey($type);
            $route = $this->getRegistrationRoute($type);

            $invitation = $modelClass::where('user_id', $userId)
                ->where($foreignKey, $productId)
                ->where('code', '!=', '0')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->first();

            $existInvitation = !is_null($invitation);
            $invitationLink = null;

            if ($existInvitation) {
                $frontendUrl = $this->getFrontendBaseUrl();
                $invitationLink = "{$frontendUrl}{$route}?invitation_code={$invitation->code}";
            }

            return response()->json([
                'success' => true,
                'existInvitation' => $existInvitation,
                'invitationLink' => $invitationLink,
                'message' => $existInvitation ? 'Invitacion activa' : 'No hay invitacion activa',
            ]);
        } catch (Throwable $e) {
            Log::error("Error checking invitation for {$type}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al verificar'], 500);
        }
    }
}
