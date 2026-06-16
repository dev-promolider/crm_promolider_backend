<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class QuestionCategory extends Model
{
    use HasFactory;
    use SoftDeletes;

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

    protected static function booted(): void
    {
        static::creating(function (self $category) {
            if (empty($category->slug)) {
                $category->slug = static::generateUniqueSlug($category->name);
            }
        });

        static::updating(function (self $category) {
            if ($category->isDirty('name')) {
                $category->slug = static::generateUniqueSlug($category->name, $category->id);
            }
        });
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

    public function getCreatorNameAttribute(): string
    {
        if ($this->relationLoaded('creator') && $this->creator) {
            return $this->creator->name;
        }

        return $this->attributes['creator_name'] ?? ($this->creator()->value('name') ?? '—');
    }

    public function getUpdaterNameAttribute(): string
    {
        if ($this->relationLoaded('updater') && $this->updater) {
            return $this->updater->name;
        }

        return $this->attributes['updater_name'] ?? ($this->updater()->value('name') ?? '—');
    }

    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (static::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = Str::limit($baseSlug, 150 - strlen((string) $counter), '') . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
