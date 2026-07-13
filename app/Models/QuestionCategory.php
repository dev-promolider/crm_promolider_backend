<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class QuestionCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'question_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'questions_count',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'questions_count' => 'integer',
    ];

    protected $appends = [
        'creator_name',
        'updater_name',
    ];

    protected static function booted()
    {
        static::creating(function (self $category) {
            if (empty($category->slug)) {
                $category->slug = static::generateUniqueSlug($category->name);
            }
        });

        static::updating(function (self $category) {
            if ($category->isDirty('name') && !$category->isDirty('slug')) {
                $category->slug = static::generateUniqueSlug($category->name, $category->id);
            }
        });
    }

    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (true) {
            $query = static::where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
            if (!$query->exists()) {
                break;
            }
            $slug = $original . '-' . $counter++;
        }

        return $slug;
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function questions()
    {
        return $this->hasMany(QuestionItem::class, 'question_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getCreatorNameAttribute()
    {
        return $this->relationLoaded('creator') && $this->creator
            ? $this->creator->name
            : null;
    }

    public function getUpdaterNameAttribute()
    {
        return $this->relationLoaded('updater') && $this->updater
            ? $this->updater->name
            : null;
    }
}
