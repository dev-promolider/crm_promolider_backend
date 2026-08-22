<?php

namespace App\Models\Infoproduct\Book;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookFile extends Model
{
    use HasFactory;

    protected $table = 'book_files';

    protected $fillable = [
        'course_id',
        'file_type',
        'file_name',
        'file_path',
        'mime_type',
        'size',
        'is_preview',
    ];

    protected $casts = [
        'size' => 'integer',
        'is_preview' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
