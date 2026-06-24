<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\ClassResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClassResourceController extends Controller
{
    public function showResources(Request $request)
    {
        $resources = ClassResource::join('class', 'class_resources.class_id', '=', 'class.id')
            ->where('class.name', '=', $request->name)
            ->select('class_resources.*')
            ->get();
        return $resources;
    }

    public function downloadResources(Request $request)
    {
        $resource = ClassResource::findOrFail($request->id);
        return Storage::disk('s3')->download($resource->resource_file);
    }

    public static function storeClassResources($resources, $user_id, $course_id, $class_id)
    {
        foreach ($resources as $index => $items) {
            $name = Helper::formatFilename($resources[$index]->getClientOriginalName());
            $path = 'courses/' . $user_id . '/' . $course_id  . '/' . $class_id  . '/' . 'resources/';
            $class_resource = new ClassResource();
            $class_resource->class_id = $class_id;
            $class_resource->resource_file = $path . $name;
            $class_resource->filename = $name;
            $class_resource->save();
            Storage::disk('s3')->put($path . $name, file_get_contents($resources[$index]), 'public');
        }
    }

    public static function deleteClassResource($resources)
    {
        if (count($resources) > 0) {
            foreach ($resources as $resource) {
                Storage::disk('s3')->delete($resource->resource_file);
                $resource->delete();
            }
        }
    }

    /**
     * Elimina los recursos que el usuario no desea al actualizar la clase
     */
    public static function destroyClassResource($paths, $class_id)
    {
        foreach ($paths as $index => $items) {
            $prefix = 'https://promolider-storage-user.s3-accelerate.amazonaws.com/';
            $clean_path = str_replace($prefix, '', $paths[$index]);
            $database_path = ltrim($clean_path, '/');
            ClassResource::where('resource_file', $database_path)->delete();
            Storage::disk('s3')->delete($clean_path);
        }
    }

    public static function updateClassResource($resources, $user_id, $course_id, $class_id)
    {
        foreach ($resources as $index => $items) {

            $name = Helper::formatFilename($resources[$index]->getClientOriginalName());
            $path = 'courses/' . $user_id . '/' . $course_id  .   '/' . $class_id . '/' . 'resources/';
            $class_resource = new ClassResource();
            $class_resource->class_id = $class_id;
            $class_resource->resource_file = $path . $name;
            $class_resource->filename = $name;
            $class_resource->save();
            Storage::disk('s3')->put($path . $name, file_get_contents($resources[$index]), 'public');
        }
    }
}
