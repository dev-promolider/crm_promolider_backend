<?php
namespace Promolider\Infrastructure\Registration\Out\Persistence;

use Promolider\Domain\Registration\Entities\RegistrationUser;
use Promolider\Domain\Registration\Ports\Out\RegistrationRepositoryInterface;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Payment;
use App\Models\Classified;
use App\Models\Notifications;
use App\Models\Option;
use App\Models\SharedLink;
use App\Models\UserDailyQuizz;
use App\Models\UserClassroomPoint;
use App\Models\AccountType;
use App\Models\Country;
use App\Models\DocumentType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EloquentRegistrationRepository implements RegistrationRepositoryInterface
{
    public function createUser(RegistrationUser $userData): int
    {
        $user = new User();
        $user->username              = $userData->username;
        $user->password              = $userData->password;
        $user->name                  = $userData->name;
        $user->last_name             = $userData->lastName;
        $user->phone                 = $userData->phone;
        $user->date_birth            = $userData->dateBirth;
        $user->email                 = $userData->email;
        $user->id_referrer_sponsor   = $userData->idReferrerSponsor;
        $user->id_country            = $userData->idCountry;
        $user->city                  = $userData->city ?? 'ciudad';
        $user->id_document_type      = $userData->idDocumentType;
        $user->id_account_type       = $userData->idAccountType;
        $user->nro_document          = $userData->nroDocument;
        $user->biography             = $userData->biography;
        $user->request               = $userData->getRequestStatus();
        $user->expiration_date       = date('Y-m-d H:i:s', $userData->getExpirationTimestamp());
        $user->photo                 = $userData->photo;

        $membershipExp = $userData->getMembershipExpirationTimestamp();
        if ($membershipExp) {
            $user->expiration_membership_date = date('Y-m-d H:i:s', $membershipExp);
        }

        $user->save();

        return $user->id;
    }

    public function createWallet(int $userId): void
    {
        Wallet::create([
            'user_id' => $userId,
            'status'  => 1,
        ]);
    }

    public function createPayment(array $paymentData): int
    {
        $payment = Payment::create($paymentData);
        return $payment->id;
    }

    public function createClassified(array $data): void
    {
        Classified::create($data);
    }

    public function createNotification(array $data): void
    {
        $notification = new Notifications();
        $notification->id_generator = $data['id_generator'];
        $notification->id_receiver  = $data['id_receiver'];
        $notification->title        = $data['title'];
        $notification->body         = $data['body'];
        $notification->type         = $data['type'];
        $notification->save();
    }

    public function findReferrer(int $id): ?array
    {
        $user = User::find($id);
        if (!$user) return null;

        return [
            'id'       => $user->id,
            'username' => $user->username,
            'position' => $user->position,
        ];
    }

    public function updateUserPosition(int $userId, int $position): bool
    {
        $user = User::find($userId);
        if ($user) {
            $user->position = $position;
            return $user->save();
        }
        return false;
    }

    public function deleteSharedLink(int $userId): void
    {
        SharedLink::where('user_id', $userId)->delete();
    }

    public function getLastUserBeforeEmpty(int $referrerId, string $position): ?int
    {
        // Busca recursivamente hacia abajo en el árbol binario
        // hasta encontrar la posición vacía por la EXTREMA elegida
        $current = $referrerId;
        $targetPosition = $position === 'user_position_left' ? 0 : 1;

        while (true) {
            // Buscamos quién ocupa la posición $targetPosition debajo de $current
            $childClassified = Classified::where('user_above', (string)$current)
                ->where('position', $targetPosition)
                ->first();

            if (!$childClassified) {
                // Posición vacía encontrada
                return $current;
            }

            // Continuamos bajando por esa extrema
            $current = $childClassified->user_id;
        }
    }

    public function sendWelcomeEmail(string $email, string $username, string $password): void
    {
        // TODO: Implement email sending when mail setup is complete
    }

    public function assignRole(int $userId, string $role): void
    {
        $user = User::find($userId);
        if ($user) {
            $user->assignRole($role);
        }
    }

    public function getDefaultAvatar(): ?string
    {
        $option = Option::where('description', 'default_avatar')->select('value')->first();
        return $option ? $option->value : null;
    }

    public function createDailyQuizz(int $userId): void
    {
        $quizz = new UserDailyQuizz();
        $quizz->id_user = $userId;
        $quizz->passed_quizz = 0;
        $quizz->save();
    }

    public function createClassroomPoints(int $userId): void
    {
        $point = new UserClassroomPoint();
        $point->id_user = $userId;
        $point->total_points = 0;
        $point->save();
    }

    public function saveMembershipExpiration(int $userId, int $accountTypeId): void
    {
        // Si existe el modelo, crear registro de expiración
        if (class_exists('App\Models\UserMembershipExpiration')) {
            $accountType = AccountType::find($accountTypeId);
            if ($accountType) {
                \App\Models\UserMembershipExpiration::create([
                    'user_id'         => $userId,
                    'id_account_type' => $accountTypeId,
                    'expiration_date' => strtotime('+365 days'),
                ]);
            }
        }
    }

    public function findSponsorByUsername(string $username): ?array
    {
        $user = User::where('username', $username)->first();
        if (!$user) return null;

        return [
            'id' => $user->id,
            'username' => $user->username,
        ];
    }

    public function resolveAccountType(): array
    {
        $configuredId = config('services.preregistro.account_type_id');

        if ($configuredId) {
            $accountType = AccountType::where('id', $configuredId)->where('status', '1')->first();
            if ($accountType) return ['id' => $accountType->id, 'price' => $accountType->price, 'iva' => $accountType->iva];
        }

        $accountType = AccountType::where('status', '1')->where('price', 53.10)->first()
            ?? AccountType::where('status', '1')->where('account', 'Guest')->first()
            ?? AccountType::where('status', '1')->where('price', '>', 0)->orderBy('price')->firstOrFail();

        return ['id' => $accountType->id, 'price' => $accountType->price, 'iva' => $accountType->iva];
    }

    public function resolveCountry(?string $countryName): array
    {
        if ($countryName) {
            $country = Country::where('name', $countryName)->first()
                ?? Country::where('name', 'like', '%' . $countryName . '%')->first();
            if ($country) return ['id' => $country->id];
        }

        $country = Country::where('name', 'Perú')->first()
            ?? Country::where('name', 'Peru')->first()
            ?? Country::firstOrFail();

        return ['id' => $country->id];
    }

    public function resolveDocumentType(string $documentType): array
    {
        $normalized = strtolower(trim($documentType));

        $doc = DocumentType::whereRaw('LOWER(document) = ?', [$normalized])->first()
            ?? DocumentType::where('document', 'like', '%' . $documentType . '%')->first()
            ?? DocumentType::firstOrFail();

        return ['id' => $doc->id];
    }

    public function validateSponsorLink(int $userId, string $code): ?array
    {
        $user = User::find($userId);
        if (!$user) return null;

        $userLink = SharedLink::where('user_id', $user->id)
            ->where('url', 'like', '%/' . $userId . '/' . $code . '%')
            ->where('fecha_fin', '>', now())
            ->where('estado', true)
            ->first();

        if (!$userLink || now()->gt($userLink->fecha_fin)) {
            return null;
        }

        return [
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'link_id' => $userLink->id,
        ];
    }

    public function getRegistrationFormData(): array
    {
        return [
            'document_types' => DocumentType::select('id', 'document')->get()->toArray(),
            'account_types' => AccountType::select('id', 'account', 'price', 'iva')->where('status', '1')->get()->toArray(),
            'countries' => Country::select('id', 'name')->get()->toArray(),
            'payment_methods' => \App\Models\PaymentMethod::select('id', 'name')
                ->where('status', 1)
                ->whereIn('name', ['Binance', 'Tarjeta crédito / débito'])
                ->get()->toArray(),
            'user_types' => \Spatie\Permission\Models\Role::where('name', '!=', 'Admin')->get()->toArray(),
        ];
    }

    public function checkAvailability(string $field, string $value, ?int $documentType = null): bool
    {
        $query = User::query();
        if ($field === 'email' || $field === 'username') {
            return !$query->where($field, $value)->exists();
        }
        
        if ($field === 'nro_document') {
            if ($documentType) {
                $query->where('id_document_type', $documentType);
            }
            return !$query->where('nro_document', $value)->exists();
        }

        return false;
    }

    public function registerMinicourseParticipant(int $userId): void
    {
        if (class_exists('App\Models\MinicourseParticipant')) {
            \App\Models\MinicourseParticipant::firstOrCreate([
                'user_id' => $userId,
            ]);
        }
    }

    public function registerEbookParticipant(int $userId, int $ebookId = null): void
    {
        if (class_exists('App\Models\EbookParticipant') && $ebookId) {
            \App\Models\EbookParticipant::firstOrCreate([
                'user_id' => $userId,
                'ebook_id' => $ebookId,
            ]);
        }
    }

    public function registerMasterclassParticipant(int $userId): void
    {
        if (class_exists('App\Models\MasterclassParticipant')) {
        }
    }

    public function createSponsorLink(int $userId, string $url, \DateTime $start, \DateTime $end): array
    {
        $link = \App\Models\SharedLink::create([
            'user_id' => $userId,
            'url' => $url,
            'estado' => true,
            'fecha_inicio' => $start->format('Y-m-d H:i:s'),
            'fecha_fin' => $end->format('Y-m-d H:i:s')
        ]);
        return $link->toArray();
    }

    public function getActiveSponsorLink(int $userId): ?array
    {
        $now = \Carbon\Carbon::now('UTC');
        $link = \App\Models\SharedLink::where('user_id', $userId)
            ->where('estado', true)
            ->where('fecha_fin', '>', $now)
            ->latest('created_at')
            ->first();

        return $link ? $link->toArray() : null;
    }

    public function suspendSponsorLink(int $linkId, int $userId): bool
    {
        $link = \App\Models\SharedLink::where('id', $linkId)
            ->where('user_id', $userId)
            ->first();
            
        if ($link) {
            $link->estado = false;
            return $link->save();
        }
        return false;
    }

    public function deleteExpiredSponsorLinks(int $userId): void
    {
        $now = \Carbon\Carbon::now('UTC');
        // Marcar como inactivos los que pasaron su fecha
        \App\Models\SharedLink::where('user_id', $userId)
            ->where('fecha_fin', '<', $now)
            ->where('estado', true)
            ->update(['estado' => false]);

        // Eliminar los muy antiguos (más de 24 horas)
        \App\Models\SharedLink::where('user_id', $userId)
            ->where('fecha_fin', '<', $now->copy()->subHours(24))
            ->delete();
    }

    /**
     * Directos del patrocinador.
     *
     * El panel de Registro los separa por perfil (productor / distribuidor) y, para los
     * productores, muestra cuántos infoproductos publicaron y cuál es el más vendido. Por eso
     * la respuesta lleva el rol, el nombre y el apellido por separado y los datos de cursos.
     * Se conserva `nombre` con el nombre completo para no romper a quien ya lo consumía.
     */
    public function getRegisteredDirects(int $userId): array
    {
        $users = User::where('id_referrer_sponsor', $userId)
            ->with('roles:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($users->isEmpty()) {
            return [];
        }

        $ids = $users->pluck('id')->all();

        $publicados = DB::table('courses')
            ->select('user_id', DB::raw('COUNT(*) as total'))
            ->whereIn('user_id', $ids)
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        // Ventas por curso, de mayor a menor: al agrupar después por autor, el primero de
        // cada grupo es su curso más vendido. Una sola consulta para todos los directos.
        $masVendidos = DB::table('purchased_courses as pc')
            ->join('courses as c', 'c.id', '=', 'pc.course_id')
            ->select('c.user_id', 'c.title', DB::raw('COUNT(pc.id) as ventas'))
            ->whereIn('c.user_id', $ids)
            ->groupBy('c.user_id', 'c.id', 'c.title')
            ->orderByDesc('ventas')
            ->get()
            ->groupBy('user_id')
            ->map(fn($cursos) => $cursos->first());

        $directs = $users->map(function ($u) use ($publicados, $masVendidos) {
            $top = $masVendidos->get($u->id);

            return [
                'id'                => $u->id,
                'nombre'            => trim(($u->name ?? '') . ' ' . ($u->last_name ?? '')),
                'nombres'           => $u->name ?? '',
                'apellidos'         => $u->last_name ?? '',
                'roles'             => $u->roles->pluck('name')->all(),
                'lado'              => $u->binary_position == 0 ? 'izquierda' : 'derecha',
                'whatsapp'          => $u->phone ?? '',
                'correo'            => $u->email ?? '',
                'cursos_publicados' => (int) ($publicados[$u->id] ?? 0),
                'curso_mas_vendido' => $top ? ['titulo' => $top->title, 'ventas' => (int) $top->ventas] : null,
                'fecha_registro'    => $u->created_at ? $u->created_at->toDateTimeString() : null,
                'origen'            => 'registro',
                'pago_estado'       => 'pagado',
            ];
        });

        return $directs->toArray();
    }
}
