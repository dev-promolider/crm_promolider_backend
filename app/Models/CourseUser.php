<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseUser extends Model
{
    use HasFactory;
    protected $table = "course_users";
    
    public function scopeRelated($query){
        return $query->select('courses.id_categories')->distinct()->where('course_users.user_id',auth()->user()->id)->join('courses','course_users.id_course','=','courses.id');
    }
}
