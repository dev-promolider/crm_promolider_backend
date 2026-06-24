<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToolPermissionRequest extends Model
{
    use HasFactory;

    protected $table = 'tool_permission_requests';

    protected $fillable = [
        'id_user',
        'status',
        'reason',
    ];

    /**
     * Relación con el usuario que hizo la solicitud.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
