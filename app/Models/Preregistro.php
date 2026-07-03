<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Preregistro extends Model
{
    use HasFactory;

    protected $table = 'preregistros';

    protected $fillable = [
        'nombres',
        'apellidos',
        'correo',
        'whatsapp',
        'referrer_username',
        'lado',
        'referrer_nombre',
        'referrer_apellido',
        'referrer_correo',
        'referrer_whatsapp',
        'url_invitacion',
        'access_token',
        'token_expires_at',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
    ];

    /**
     * Genera un token único de acceso con expiración y lo persiste en BD.
     *
     * @param  int  $hours  Horas hasta que expira el token (default 72h)
     * @return $this
     */
    public function generateToken(int $hours = 72): static
    {
        $this->access_token    = Str::random(48);
        $this->token_expires_at = now()->addHours($hours);
        $this->save();

        return $this;
    }

    /**
     * Indica si el token existe y no ha expirado.
     */
    public function hasValidToken(): bool
    {
        return ! empty($this->access_token)
            && $this->token_expires_at !== null
            && $this->token_expires_at->isFuture();
    }

    /**
     * Devuelve la URL de retorno lista para incluir en correos.
     */
    public function retornoUrl(): string
    {
        return url('/preregistro/retorno/' . $this->access_token);
    }
}
