<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\CertificateTemplate;
use App\Http\Controllers\BadgeController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CertificateTemplateController extends Controller
{
    public function index()
    {
        return CertificateTemplate::where('is_active', true)->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'design_data' => 'required|array',
            'html_template' => 'required|string',
        ]);

        $template = CertificateTemplate::create($request->all());
        
        return response()->json($template, 201);
    }

    public function update(Request $request, CertificateTemplate $template)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'design_data' => 'required|array',
            'html_template' => 'required|string',
        ]);

        $template->update($request->all());
        
        return response()->json($template);
    }
}