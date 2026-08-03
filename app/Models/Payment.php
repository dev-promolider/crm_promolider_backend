<?php

namespace App\Models;

use App\Models\CancelledPayment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;
    protected $table = 'payments';
    protected $guarded = ['id', 'user_id', 'id_user_sponsor', 'status', 'amount', 'created_at', 'updated_at'];
    protected $hidden = ['pivot'];

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'id_payment_method');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cancelledpayment(): HasOne
    {
        return $this->hasOne(CancelledPayment::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity');
    }

    public function scopePaymentAuthSponsor($query)
    {
        return $query->where('id_user_sponsor', auth()->user()->id);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'courses_payments', 'payment_id', 'course_id')->withPivot('desc','price');
    }
}
