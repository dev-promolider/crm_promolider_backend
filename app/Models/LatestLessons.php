<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LatestLessons extends Model
{
    use HasFactory;

    protected $table = 'latest_lessons';

    protected $fillable = [
        'users_id',
        'class_id',
    ];

    public function scopeGetClass($query)
    {
        return $query->select('id', 'class_id', 'users_id', 'updated_at')
            ->where('users_id', auth()->user()->id)
            ->orderBy('updated_at', 'DESC');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(Clas::class, 'class_id')->select('id', 'id_modules', 'name');
    }

    public function module(): HasMany
    {
        return $this->hasMany(Module::class, 'id_modules');
    }

    public function scopeCountLesson($query)
    {
        return $query->where('users_id', auth()->user()->id)->count();
    }

    public function scopeLastLesson($query)
    {
        return $query->where('users_id', auth()->user()->id)->orderBy('updated_at', 'ASC')->first();
    }
}
