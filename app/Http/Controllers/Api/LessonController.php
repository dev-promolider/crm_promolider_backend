<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LessonController extends Controller
{
     /**
     * Show  lessons data 
     * @return $lesson {object}
     */
    public function __invoke()
    {
        $user = User::find(Auth::user()->id);
        $lessons = $user->lessons;
        $lesson = $lessons->last();
        $module = $lesson->module;
        $module->course;
        return $lesson;
    }
}
