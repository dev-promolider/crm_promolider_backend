<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use App\Models\Clas;
use App\Models\Country;
use App\Models\Traits\Pointable;
use App\Traits\EncryptationId;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Traits\HasRoles;
use App\Helpers\ParseUrl;
use App\Models\WalletPaymetMethod;
use Illuminate\Support\Facades\Log;
use App\Helpers\SecurityHelper;
use App\Models\Infoproduct\Book\BookObservation;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Pointable, EncryptationId, HasRoles;
    
    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
        'pivot'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'expiration_date' => 'datetime',
        'expiration_membership_date' => 'datetime',
        'credits' => 'decimal:2', // AGREGADO: Cast para créditos
    ];

    protected $appends = [
        'fullName',
        'active',
        'encid',
        'membershipActive',
    ];

    protected $table = 'users';

    public function getPhotoAttribute($value)
    {
        return ParseUrl::contacAtrrS3($value);
    }

    // MUTADORES PARA SANITIZAR ENTRADA
    public function setNameAttribute($value)
    {
        $sanitized = strip_tags(trim($value));
        $this->attributes['name'] = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');
    }

    public function setLastNameAttribute($value)
    {
        $sanitized = strip_tags(trim($value));
        $this->attributes['last_name'] = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');
    }

    public function setPhotoAttribute($value)
    {
        if (
            empty($value) ||
            filter_var($value, FILTER_VALIDATE_URL) ||
            str_starts_with($value, 'images/')
        ) {
            $this->attributes['photo'] = $value;
        } else {
            $this->attributes['photo'] = null;
        }
    }

    public function getfullNameAttribute()
    {
        $name = htmlspecialchars($this->name ?? '', ENT_QUOTES, 'UTF-8');
        $lastName = htmlspecialchars($this->last_name ?? '', ENT_QUOTES, 'UTF-8');
        return trim($name . ' ' . $lastName);
    }

    public static function validateUserInput($data)
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-ZÀ-ÿ\u00f1\u00d1\s]+$/',
                'not_regex:/<[^>]*>/',
            ],
            'last_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-ZÀ-ÿ\u00f1\u00d1\s]+$/',
                'not_regex:/<[^>]*>/',
            ],
        ];

        return validator($data, $rules);
    }

    public function getActiveAttribute()
    {
        $valid_types = \Illuminate\Support\Facades\Cache::remember('valid_account_types', 86400, function () {
            return \App\Models\AccountType::where('price', '>', 0)->pluck('id')->toArray();
        });

        if (!in_array($this->id_account_type, $valid_types)) {
            return false;
        }

        if (is_null($this->expiration_date)) {
            $expiro = true;
        } else {
            $expiro = $this->expiration_date > now();
        }

        $aceptado = $this->request == 2;
        return $expiro && $aceptado;
    }

    public function getMembershipActiveAttribute()
    {
        $valid_types = \Illuminate\Support\Facades\Cache::remember('valid_account_types', 86400, function () {
            return \App\Models\AccountType::where('price', '>', 0)->pluck('id')->toArray();
        });

        if (!in_array($this->id_account_type, $valid_types)) {
            return false;
        }

        $expiration = $this->expiration_membership_date > now();
        $accepted = $this->request == 2;
    
        return $expiration && $accepted;
    }

    public function getQualifiedAttribute(): bool
    {
        // Optimizamos verificando si ya fue precargado
        if ($this->relationLoaded('classifiedSponsor')) {
            $sponsored = $this->classifiedSponsor;
        } else {
            $sponsored = clone $this->classifiedSponsor()->with('user.accountType')->get();
        }

        $left = false;
        $right = false;

        foreach ($sponsored as $key) {
            if ($key->user && $key->user->active && $key->user->membershipActive && $key->user->id_account_type != 5 && $key->user->id_account_type != 6) {
                if ($key->position == 0) $left = true;
                if ($key->position == 1) $right = true;
            }
            if ($left && $right) break;
        }

        return $left && $right;
    }

    public function scopeIsActive($query)
    {
        return $query->where('expiration_date', '>', now())->where('request', 2);
    }

    // ============================================
    // RELACIONES EXISTENTES
    // ============================================

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'id_country');
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_referrer_sponsor');
    }

    public function paymentsClient(): HasOne
    {
        return $this->hasOne(Payment::class, 'user_id');
    }

    public function paymentsSponsor(): HasMany
    {
        return $this->hasMany(Payment::class, 'id_user_sponsor');
    }

    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class, 'id_account_type');
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'id_document_type');
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    public function classifiedSponsor(): HasMany
    {
        return $this->hasMany(Classified::class, 'id_user_sponsor', 'id');
    }

    public function classifiedClients(): HasMany
    {
        return $this->hasMany(Classified::class, 'user_id', 'id');
    }

    public function scopeMyClients($query, $id)
    {
        return $query->where('id_referrer_sponsor', $id);
    }

    public function SponsorLink()
    {
        return $this->hasMany(SponsorLink::class);
    }

    public function scopeQualifiedsAndActive($query)
    {
        return $query->with('accountType')->whereNotIn('id_account_type', ['5', '6', '1'])->get()->filter(function ($key) {
            return $key->qualified == true && $key->active == true && $key->membershipActive == true;
        });
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function scopeMyCourses()
    {
        return $this->courses()->select('users.name', 'courses.id', 'courses.title', 'courses.price', 'courses.status', 'courses.id_categories', 'courses.created_at', 'courses.description', 'courses.portada', 'courses.url_portada')->join("users", "courses.user_id", "=", "users.id")->orderBy('courses.created_at', 'ASC');
    }

    public function lessons(): BelongsToMany
    {
        return $this->belongsToMany(Clas::class, 'class_users');
    }

    public function purchaseds(): HasMany
    {
        return $this->hasMany(PurchasedCourse::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'id_user_receiver');
    }

    public function directReferrals(): HasMany
    {
        return $this->hasMany(User::class, 'id_referrer_sponsor', 'id');
    }

    public function allDescendants()
    {
        return $this->directReferrals()->with('allDescendants');
    }

    public function binanceAccounts()
    {
        return $this->hasMany(BinanceAccount::class);
    }
    
    public function paypalAccounts()
    {
        return $this->hasMany(PaypalAccount::class);
    }

    public function WalletPaymetMethods()
    {
        return $this->belongsToMany(WalletPaymetMethod::class, 'wallet_payment_method_user');
    }

    public function bookObservations(): HasMany
    {
        return $this->hasMany(BookObservation::class, 'analyst_id');
    }

    // ============================================
    // NUEVAS RELACIONES - SISTEMA DE CRÉDITOS
    // ============================================

    /**
     * Relación con los canjes de premios realizados por el usuario
     */
    public function rewardRedemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }

    /**
     * Relación con los canjes procesados por el usuario (admin)
     */
    public function processedRedemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class, 'processed_by');
    }

    // ============================================
    // MÉTODOS DEL SISTEMA DE CRÉDITOS
    // ============================================

    /**
     * Agregar créditos al usuario
     * 
     * @param float $amount Cantidad de créditos a agregar
     * @param string|null $reason Razón del incremento (para logs)
     * @param array $metadata Datos adicionales para el log
     * @return bool
     */
    public function addCredits(float $amount, ?string $reason = null, array $metadata = []): bool
    {
        if ($amount <= 0) {
            Log::warning('Intento de agregar créditos con cantidad inválida', [
                'user_id' => $this->id,
                'amount' => $amount
            ]);
            return false;
        }

        try {
            $previousBalance = $this->credits ?? 0;
            $this->credits = $previousBalance + $amount;
            $this->save();

            Log::info('Créditos agregados exitosamente', array_merge([
                'user_id' => $this->id,
                'user_name' => $this->fullName,
                'amount' => $amount,
                'reason' => $reason,
                'previous_balance' => $previousBalance,
                'new_balance' => $this->credits
            ], $metadata));

            return true;
        } catch (\Exception $e) {
            Log::error('Error al agregar créditos', [
                'user_id' => $this->id,
                'amount' => $amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Descontar créditos del usuario
     * 
     * @param float $amount Cantidad de créditos a descontar
     * @param string|null $reason Razón del descuento (para logs)
     * @param array $metadata Datos adicionales para el log
     * @return bool
     */
    public function deductCredits(float $amount, ?string $reason = null, array $metadata = []): bool
    {
        if ($amount <= 0) {
            Log::warning('Intento de descontar créditos con cantidad inválida', [
                'user_id' => $this->id,
                'amount' => $amount
            ]);
            return false;
        }

        if (!$this->hasCredits($amount)) {
            Log::warning('Intento de descontar créditos sin saldo suficiente', [
                'user_id' => $this->id,
                'amount' => $amount,
                'current_balance' => $this->credits
            ]);
            return false;
        }

        try {
            $previousBalance = $this->credits;
            $this->credits = $previousBalance - $amount;
            $this->save();

            Log::info('Créditos descontados exitosamente', array_merge([
                'user_id' => $this->id,
                'user_name' => $this->fullName,
                'amount' => $amount,
                'reason' => $reason,
                'previous_balance' => $previousBalance,
                'new_balance' => $this->credits
            ], $metadata));

            return true;
        } catch (\Exception $e) {
            Log::error('Error al descontar créditos', [
                'user_id' => $this->id,
                'amount' => $amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Verificar si el usuario tiene suficientes créditos
     * 
     * @param float $amount Cantidad a verificar
     * @return bool
     */
    public function hasCredits(float $amount): bool
    {
        return ($this->credits ?? 0) >= $amount;
    }

    /**
     * Obtener el balance de créditos formateado
     * 
     * @return string
     */
    public function getFormattedCreditsAttribute(): string
    {
        return number_format($this->credits ?? 0, 2, '.', ',');
    }

    /**
     * Transferir créditos a otro usuario
     * 
     * @param User $recipient Usuario que recibirá los créditos
     * @param float $amount Cantidad a transferir
     * @param string|null $reason Razón de la transferencia
     * @return bool
     */
    public function transferCredits(User $recipient, float $amount, ?string $reason = null): bool
    {
        if ($amount <= 0) {
            return false;
        }

        if (!$this->hasCredits($amount)) {
            return false;
        }

        if ($this->id === $recipient->id) {
            Log::warning('Intento de transferir créditos a uno mismo', [
                'user_id' => $this->id
            ]);
            return false;
        }

        DB::beginTransaction();
        try {
            // Descontar del emisor
            $deducted = $this->deductCredits($amount, $reason, [
                'transfer_to' => $recipient->id,
                'transfer_to_name' => $recipient->fullName
            ]);

            if (!$deducted) {
                throw new \Exception('No se pudieron descontar los créditos del emisor');
            }

            // Agregar al receptor
            $added = $recipient->addCredits($amount, $reason, [
                'transfer_from' => $this->id,
                'transfer_from_name' => $this->fullName
            ]);

            if (!$added) {
                throw new \Exception('No se pudieron agregar los créditos al receptor');
            }

            DB::commit();

            Log::info('Transferencia de créditos exitosa', [
                'from_user_id' => $this->id,
                'from_user_name' => $this->fullName,
                'to_user_id' => $recipient->id,
                'to_user_name' => $recipient->fullName,
                'amount' => $amount,
                'reason' => $reason
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en transferencia de créditos', [
                'from_user_id' => $this->id,
                'to_user_id' => $recipient->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    // ============================================
    // MÉTODOS EXISTENTES
    // ============================================

    public function getAllPaymentMethods()
    {
        $binance = $this->binanceAccounts()->active()->get()->map(function ($account) {
            return [
                'id' => $account->id,
                'type' => 'binance',
                'method' => 'Binance',
                'email' => $account->email,
                'account_name' => $account->account_name,
                'account_number' => $account->binance_id,
                'extra_info' => [
                    'network' => $account->network,
                    'phone' => $account->phone
                ],
                'created_at' => $account->created_at
            ];
        });
    
        $paypal = $this->paypalAccounts()->active()->get()->map(function ($account) {
            return [
                'id' => $account->id,
                'type' => 'paypal',
                'method' => 'PayPal',
                'email' => $account->email,
                'account_name' => $account->account_name,
                'account_number' => $account->email,
                'extra_info' => [
                    'country_code' => $account->country_code,
                    'currency' => $account->currency,
                    'account_type' => $account->account_type,
                    'is_verified' => $account->is_verified
                ],
                'created_at' => $account->created_at
            ];
        });
    
        return $binance->concat($paypal)->sortByDesc('created_at')->values();
    }

    public function jsPermissions()
    {
        return json_encode([
            'roles' => $this->getRoleNames(),
            'permissions' => $this->getAllPermissions()->pluck('name'),
        ]);
    }
    
    /**
     * Enviar notificación personalizada de recuperación de contraseña
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \Illuminate\Auth\Notifications\ResetPassword($token));
    }
}