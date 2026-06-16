<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\PurchasedCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PurchasedCourseController extends Controller
{
    public function deliverCertificate(Request $request){
        $purchased_course = PurchasedCourse::findOrFail($request->purchased_course_id);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $certificate_name = Helper::formatFilename($file->getClientOriginalName());
            $path = "/certificates";
            Storage::disk('s3')->put($path.'/'.$certificate_name, file_get_contents($file), 'public');
            $purchased_course->certificate_url = $path.'/'.$certificate_name;
            $purchased_course->certificate_delivered = 1;
            $purchased_course->update();
        }
    }
}
