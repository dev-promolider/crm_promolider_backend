<?php

namespace App\Models;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClassroomCartDetail extends Model
{
    use HasFactory;
    protected $table = 'classroom_cart_detail';
    protected $fillable = ['classroom_cart_id','courses_id'];
    protected $guarded = ['id'];

    public function courses(): BelongsTo
    {
        return $this->belongsTo(Course::class)->select(array('courses.id','courses.title','courses.price','courses.url_portada','users.name','users.last_name','courses.user_id'))->join('users','courses.user_id','=','users.id');
    }
    public function producer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function scopeSltData($query){
        return $query->select('classroom_cart_detail.id','classroom_cart_detail.courses_id');
    }
}
