<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchasedCourse;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function getCongratulation(){
        $certificate = PurchasedCourse::join('users', 'purchased_courses.user_id', '=', 'users.id')
            ->join('user_certificates', 'users.id', '=', 'user_certificates.id_user')
            ->where('purchased_courses.certificate_seen', 0)
            ->where('purchased_courses.user_id', auth()->user()->id)
            ->select('user_certificates.certificate')
            ->first();
        return $certificate;
    }
}
