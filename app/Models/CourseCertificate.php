<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'user_id',
        'course_id',
        'module_id',
        'completion_date',
        'custom_data',
        'certificate_code',
        'pdf_path',
        'status',
        'issued_at',
        'instructor_signature_path',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'custom_data' => 'array',
        'issued_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(CertificateTemplate::class, 'template_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function issue()
    {
        $this->update([
            'status'    => 'issued',
            'issued_at' => now(),
        ]);
    }

    public function isIssued(): bool
    {
        return $this->status === 'issued';
    }
}
