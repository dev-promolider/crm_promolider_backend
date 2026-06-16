<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function listDocumentType()
    {
        $obj = DocumentType::select('id', 'document')->get();
        return $obj;
    }
}
