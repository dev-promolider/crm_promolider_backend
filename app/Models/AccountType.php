<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountType extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = "account_type";

    /**
     * Get the user associated with the AccountType
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function pointMoney(): HasOne
    {
        return $this->hasOne(AccountTypePointsMoney::class);
    }


}
