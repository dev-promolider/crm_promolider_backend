<?php
namespace Promolider\Infrastructure\Registration\In\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Registration\UseCases\CreateRegisteredUserUseCase;
use Promolider\Domain\Registration\Entities\RegistrationUser;
use Exception;

class RegistrationController extends Controller
{
    public function __construct(
        private CreateRegisteredUserUseCase $createRegisteredUserUseCase,
        private \Promolider\Application\Registration\UseCases\ValidateSponsorLinkUseCase $validateSponsorLinkUseCase,
        private \Promolider\Application\Registration\UseCases\GetRegistrationFormDataUseCase $getRegistrationFormDataUseCase,
        private \Promolider\Application\Registration\UseCases\CheckUserAvailabilityUseCase $checkUserAvailabilityUseCase
    ) {}

    /**
     * GET /registration/sponsor-link/{id}/{code}
     * Valida si un enlace de patrocinador es correcto y activo.
     */
    public function validateSponsorLink($id, $code)
    {
        $result = $this->validateSponsorLinkUseCase->execute((int)$id, $code);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Enlace expirado o invÃ¡lido',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * GET /registration/form-data
     * Retorna los listados para llenar el formulario de registro (PaÃ­ses, Documentos, etc).
     */
    public function getFormData()
    {
        return response()->json([
            'success' => true,
            'data' => $this->getRegistrationFormDataUseCase->execute(),
        ]);
    }

    /**
     * POST /registration/check-availability
     * Verifica si un email, username o documento ya estÃ¡ en uso.
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'field' => 'required|string|in:email,username,nro_document',
            'value' => 'required|string',
            'document_type' => 'nullable|integer'
        ]);

        $isAvailable = $this->checkUserAvailabilityUseCase->execute(
            $request->input('field'),
            $request->input('value'),
            $request->input('document_type')
        );

        return response()->json([
            'success' => true,
            'available' => $isAvailable,
        ]);
    }

    /**
     * POST /registration/create
     * Crea un usuario completo (pago o gratuito).
     */
    public function create(Request $request)
    {
        $request->validate([
            'username'            => 'required|string|unique:users,username',
            'password'            => 'required|string|min:6',
            'name'                => 'required|string|max:255',
            'last_name'           => 'required|string|max:255',
            'phone'               => 'required|string|max:20',
            'date_birth'          => 'required|date',
            'email'               => 'required|email|unique:users,email',
            'id_referrer_sponsor' => 'required|integer|exists:users,id',
            'id_country'          => 'required|integer',
            'id_document_type'    => 'required|integer',
            'id_account_type'     => 'required|integer',
            'nro_document'        => 'required|string|unique:users,nro_document',
            'biography'           => 'nullable|string',
            'user_type'           => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $user = new RegistrationUser(
                username:          $request->input('username'),
                email:             $request->input('email'),
                name:              $request->input('name'),
                lastName:          $request->input('last_name'),
                phone:             $request->input('phone'),
                dateBirth:         $request->input('date_birth'),
                idCountry:         (int) $request->input('id_country'),
                idDocumentType:    (int) $request->input('id_document_type'),
                nroDocument:       $request->input('nro_document'),
                idAccountType:     (int) $request->input('id_account_type'),
                idReferrerSponsor: (int) $request->input('id_referrer_sponsor'),
                password:          Hash::make($request->input('password')),
                biography:         $request->input('biography', ''),
                userType:          $request->input('user_type'),
            );

            $paymentData = [];
            if (!$user->isFreeTier()) {
                $paymentData = [
                    'id_user_sponsor'  => $request->input('id_referrer_sponsor'),
                    'amount'           => $request->input('amount', 0),
                    'operation_number' => $request->input('operation_number', 0),
                    'id_payment_method' => $request->input('id_payment_method', 1),
                ];
            }

            $result = $this->createRegisteredUserUseCase->execute(
                $user,
                $paymentData,
                $request->input('password')
            );

            DB::commit();

            Log::info('Usuario registrado exitosamente', [
                'user_id'  => $result['user_id'],
                'username' => $result['username'],
                'is_free'  => $result['is_free'],
            ]);

            return response()->json([
                'success'      => true,
                'message'      => 'El usuario se registrÃ³ correctamente.',
                'data'         => $result,
                'redirect_url' => env('FRONTEND_URL', 'http://localhost:5173') . '/login',
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error en registro de usuario', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el usuario. Intente nuevamente.',
            ], 500);
        }
    }
}
