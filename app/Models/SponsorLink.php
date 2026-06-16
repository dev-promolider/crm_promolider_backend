<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsorLink extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    protected $table = "sponsor_link";
    protected $fillable = ['url','fecha_inicio','fecha_fin','estado','user_id'];

    // IMPORTANTE: Definir los casts para que las fechas se conviertan automáticamente a Carbon
    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'estado' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // También puedes usar dates (método alternativo, pero casts es más moderno)
    // protected $dates = ['fecha_inicio', 'fecha_fin'];

    //Relacion uno a muchos inversa
    public function user(){
        return $this->belongsTo(User::class);
    }
}