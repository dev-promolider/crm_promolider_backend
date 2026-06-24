<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DinamicaTriviaConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'dinamica_id',
        'registration_config',
        'trivia_config',
        'game_blocks',
    ];

    protected $casts = [
        'registration_config' => 'array',
        'trivia_config' => 'array',
        'game_blocks' => 'array',
    ];

    public function dinamica()
    {
        return $this->belongsTo(Dinamica::class);
    }
}
