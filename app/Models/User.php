<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function binanceAccounts()
    {
        return $this->hasMany(BinanceAccount::class);
    }

    public function paypalAccounts()
    {
        return $this->hasMany(PaypalAccount::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'id_country');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    public function accountType()
    {
        return $this->belongsTo(AccountType::class, 'id_account_type');
    }

    /**
     * Su fila en el arbol binario.
     */
    public function classified(): HasOne
    {
        return $this->hasOne(Classified::class, 'user_id');
    }

    /**
     * Las filas del arbol de la gente que el patrocino directamente.
     */
    public function classifiedSponsor(): HasMany
    {
        return $this->hasMany(Classified::class, 'id_user_sponsor', 'id');
    }

    /**
     * Los puntos que ha acumulado. Ojo: los puntos de alguien son las filas donde
     * figura como sponsor_id, no como user_id (ahi va quien los genero).
     */
    public function binaryPoints(): HasMany
    {
        return $this->hasMany(Point::class, 'sponsor_id');
    }

    /**
     * Las columnas expiration_date y expiration_membership_date son varchar y traen
     * valores mezclados: fechas 'Y-m-d H:i:s' del sistema nuevo y marcas de tiempo UNIX
     * que sigue escribiendo el monolito. Carbon::parse revienta con las segundas, y eso
     * era lo que tumbaba el corte binario entero.
     */
    public static function parseExpiration($value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::createFromTimestamp((int) $value);
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Solo las cuentas de pago cuentan para la red. Las gratuitas (precio 0) no activan
     * a nadie ni califican a su patrocinador.
     */
    private function hasPaidAccountType(): bool
    {
        $paidTypes = Cache::remember('valid_account_types', 86400, function () {
            return AccountType::where('price', '>', 0)->pluck('id')->toArray();
        });

        return in_array($this->id_account_type, $paidTypes);
    }

    /**
     * OPC al dia y solicitud aprobada. Sin fecha de OPC se considera vigente,
     * que es como se comportaba el monolito.
     */
    public function getActiveAttribute(): bool
    {
        if (!$this->hasPaidAccountType()) {
            return false;
        }

        $expiration = self::parseExpiration($this->expiration_date);
        $vigente = $expiration === null ? true : $expiration->isFuture();

        return $vigente && (string) $this->request === '2';
    }

    /**
     * Membresia anual vigente y solicitud aprobada.
     */
    public function getMembershipActiveAttribute(): bool
    {
        if (!$this->hasPaidAccountType()) {
            return false;
        }

        $expiration = self::parseExpiration($this->expiration_membership_date);

        return $expiration !== null && $expiration->isFuture() && (string) $this->request === '2';
    }

    /**
     * Calificado: tiene al menos un patrocinado directo activo a cada lado.
     * Es la condicion para cobrar el bono binario.
     *
     * La regla vive en QualificationService para que el panel, el arbol y el corte
     * usen exactamente la misma; antes cada uno tenia la suya.
     */
    public function getQualifiedAttribute(): bool
    {
        return app(\App\Services\MLM\QualificationService::class)->isQualified($this);
    }
}
