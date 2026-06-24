<?php

namespace App\Models\Infoproduct\Book;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookObservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'analyst_id',
        'course_id',
        'observations',
        'status'
    ];

    public function analyst()
    {
        return $this->belongsTo(User::class, 'analyst_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
