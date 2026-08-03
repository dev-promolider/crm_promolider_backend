<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function scopeMyClients($query, $id)
    {
        return $query->where('id_referrer_sponsor', $id);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
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
}
