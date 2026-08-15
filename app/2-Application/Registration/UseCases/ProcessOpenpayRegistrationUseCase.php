<?php
namespace Promolider\Application\Registration\UseCases;

use Promolider\Domain\Registration\Ports\Out\PaymentGatewayInterface;
use Promolider\Domain\Registration\Ports\Out\PreregistroRepositoryInterface;
use Promolider\Domain\Registration\Ports\Out\RegistrationRepositoryInterface;
use Exception;

class ProcessOpenpayRegistrationUseCase
{
    public function __construct(
        private PaymentGatewayInterface $paymentGateway,
        private PreregistroRepositoryInterface $preregistroRepository,
        private RegistrationRepositoryInterface $registrationRepository,
        private \App\Services\MLM\BinaryTreeService $binaryTreeService
    ) {}

    /**
     * Procesa el registro con pago Openpay: crea cargo 3D Secure y guarda UnverifiedUser.
     * 
     * Lógica extraída de: PreregistroController::openpay()
     */
    public function execute(array $validatedData): array
    {
        $email = $validatedData['correo'];

        // 1. Limpiar registros pendientes previos del mismo email
        $this->paymentGateway->cleanPreviousUnverified($email);

        // 2. Resolver IDs del negocio usando el repositorio
        $sponsor = $this->registrationRepository->findSponsorByUsername($validatedData['referidor']);
        if (!$sponsor) {
            throw new Exception('No encontramos al usuario que te invitó.', 422);
        }

        $accountType = $this->registrationRepository->resolveAccountType();
        $country = $this->registrationRepository->resolveCountry($validatedData['pais'] ?? null);
        $documentType = $this->registrationRepository->resolveDocumentType($validatedData['tipo_documento']);
        
        $role = strtolower($validatedData['tipo_usuario']);
        $roleName = $role === 'distribuidor' ? 'Distributor' : $validatedData['tipo_usuario'];

        $orderNumber = time();
        $orderId = substr('preregistro-' . $orderNumber, 0, 100);
        $hashedPassword = password_hash($validatedData['password'], PASSWORD_DEFAULT);
        $redirectUrl = config('app.frontend_url') . '/login';

        // 3. Calcular el monto con IVA
        $amount = number_format(
            $accountType['price'] + ($accountType['price'] * ($accountType['iva'] / 100)),
            2, '.', ''
        );

        // 3. Crear cargo en Openpay (3D Secure redirect)
        $chargeData = [
            'order_id'    => $orderId,
            'method'      => 'card',
            'currency'    => 'USD',
            'amount'      => $amount,
            'description' => 'Pago preregistro Promolider',
            'customer'    => [
                'name'         => $validatedData['nombre'],
                'last_name'    => $validatedData['apellido'],
                'phone_number' => $validatedData['telefono'],
                'email'        => $email,
            ],
            'send_email'   => false,
            'confirm'      => false,
            'redirect_url' => $redirectUrl,
        ];

        $chargeResult = $this->paymentGateway->createCharge($chargeData);

        // 4. Preparar datos del usuario no verificado
        if ($validatedData['lado'] === 'automatico') {
            $binaryPosition = $this->binaryTreeService->getWeakerLeg($sponsor['id']);
        } else {
            $binaryPosition = $validatedData['lado'] === 'izquierda' ? 0 : 1;
        }

        $unverifiedData = [
            'username'         => $validatedData['usuario'],
            'password'         => $hashedPassword,
            'openpay_order_id' => $chargeResult['charge_id'],
            'data' => [
                'id_referrer_sponsor' => $sponsor['id'],
                'username'            => $validatedData['usuario'],
                'password'            => $hashedPassword,
                'email'               => $email,
                'user_type'           => $roleName,
                'name'                => $validatedData['nombre'],
                'last_name'           => $validatedData['apellido'],
                'biography'           => 'Registro desde preregistro',
                'phone'               => $validatedData['telefono'],
                'date_birth'          => $validatedData['fecha_nacimiento'],
                'id_document_type'    => $documentType['id'],
                'nro_document'        => $validatedData['numero_documento'],
                'id_country'          => $country['id'],
                'id_account_type'     => $accountType['id'],
                'purchase_number'     => $orderId,
                'payment_method_id'   => 1,
                'payment_method'      => 'openpay',
                'operation_number'    => $chargeResult['charge_id'],
                'openpay'             => true,
                'binary_position'     => $binaryPosition,
                'preregistro_id'      => $validatedData['preregistro_id'] ?? null,
            ],
        ];

        // 5. Guardar usuario no verificado
        $this->paymentGateway->saveUnverifiedUser($unverifiedData);

        return [
            'payment_url' => $chargeResult['payment_url'],
            'charge_id'   => $chargeResult['charge_id'],
        ];
    }
}
