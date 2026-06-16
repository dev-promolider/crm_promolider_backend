<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ClassroomCartDetail;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassroomCart extends Model
{
    use HasFactory;
    protected $table = "classroom_cart";
    protected $fillable = ['user_id','status'];
    protected $guarded = ['id'];

    /**
     * Get all of the cartDetails for the ShoppingCart
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cartDetails(): HasMany
    {
        return $this->hasMany(ClassroomCartDetail::class, 'classroom_cart_id');
    }
    public function scopeSltData($query){
        return $query->select('classroom_cart.id','classroom_cart.user_id','classroom_cart.status','classroom_cart.updated_at');
    }
    public function scopeCartUser($query){
        return $query->where('user_id',auth()->user()->id)->where('status','<>','NO ACTION')->orderBy('updated_at','DESC');
    }
    public function scopeCartWaiting($query){
        return $query->where('user_id',auth()->user()->id)->where('status','WAITING')->orderBy('updated_at','DESC');
    }
}
