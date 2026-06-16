<?php

namespace App\Http\Controllers\Templates;

use App\Http\Controllers\Controller;
use App\Models\MongoTemplate\Template;
use App\Models\MongoTemplate\UserTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class UserTemplateController extends Controller
{
    public function store(Request $request){
        
        $request->validate([
            'userId'=>'exists:mysql.users,id',
            'templateId'=>'exists:mongodb.templates,_id',
            'type' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        
        $template = UserTemplate::create([
            'userId'=>$request->input('userId'),
            'templateId'=>$request->input('templateId'),
            'type' => $request->input('type'),
            'content' => $request->input('content'),
        ]);

        
        return response()->json($template, 201);
    }

    public function getContent($id){
        $userTemplates = UserTemplate::where("_id", $id)->first();
        Log::info($userTemplates);
        return response()->json($userTemplates->content, 200);
    }

    public function list($userId){
        $userTemplates = UserTemplate::where("userId", (int)$userId)->get();
        return response()->json($userTemplates, 200);
    }

    public function update(Request $request, $id)
    {
        
        $request->validate([
            'type' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|array', 
        ]);

      
        $template = UserTemplate::find($id);
        if (!$template) {
            return response()->json(['message' => 'Template not found'], 404);
        }

        
        $template->update($request->all());

        
        return response()->json($template, 200);
    }

    public function delete($id)
    {
        
        $template = UserTemplate::find($id);
        if (!$template) {
            return response()->json(['message' => 'Template not found'], 404);
        }

       
        $template->delete();

        return response()->json(['message' => 'Template deleted successfully'], 200);
    }
}
