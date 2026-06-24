<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Classified extends Model
{
    use HasFactory;
    protected $table = 'classified';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function userSponsor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_sponsor');
    }

    public function scopeIsLeft($query){
        return $query->where('position', 0);
    }
    public function scopeIsRight($query){
        return $query->where('position', 1);
    }

    public function scopeMyClassifieds($query,$id){
        return $query->where('id_user_sponsor', $id);
    }
    
    public function scopeMyLeftClassifieds($query,$id){
        return $query->myClassifieds($id)->isLeft();
    }

    public function scopeMyRightClassifieds($query,$id){
        return $query->myClassifieds($id)->isRight();
    }

    public function scopeMyRightClassifiedsUsers($query,$id){
        return $query->myRightClassifieds($id)->with('User');
    }

    public function scopeMyLeftClassifiedsUsers($query,$id){
        return $query->myLeftClassifieds($id)->with('User');
    }


}
