<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountTypePointsMoney extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * Get the user that owns the AccountTypePointsMoney
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class);
    }
}
