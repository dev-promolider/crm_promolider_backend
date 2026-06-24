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
    protected $guarded = [];
    protected $hidden = ['pivot'];

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'id_payment_method');
    }
    /**
     * Get the user that owns the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }

    // public function user(): HasMany
    // {
    //     return $this->hasMany(User::class,'id');
    // }

    /**
     * Get the CancelledPayment associated with the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cancelledpayment(): HasOne
    {
        return $this->hasOne(CancelledPayment::class);
    }
    /**
     * The products that belong to the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity');;
    }

    /**Scopes  Para comprotbar stado del pago autorizado standby o passed  o rejected **/
    // public function scopeStandby($query)
    // {
    //     return $query->where('authorized', 'standby');
    // }

    // public function scopePassed($query)
    // {
    //     return $query->where('authorized', 'passed');
    // } 
    // public function scopeRejected($query)
    // {
    //     return $query->where('authorized', 'rejected');
    // }
    /**end Socpes autorizado */

    // Scope  payments where Auth::user
    public function scopePaymentAuthSponsor($query)
    {
        return $query->where('id_user_sponsor', auth()->user()->id);
    }

    // public function paymentable()
    // {
    //     return $this->morphTo();
    // }

    /**
     * The coursees that belong to the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'courses_payments', 'payment_id', 'course_id')->withPivot('desc','price');
    }
    //$payment->courses()->syncWithoutDetaching(course_id);

}
