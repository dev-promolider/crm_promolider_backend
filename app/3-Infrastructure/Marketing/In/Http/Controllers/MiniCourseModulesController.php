<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Minicourse;
use App\Models\MiniCourseClass;
use App\Models\MiniCourseModule;
use App\Models\MiniCourseDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MiniCourseModulesController extends Controller
{
    // ==================== MODULES ====================

    public function modulesIndex(int $miniCourseId): JsonResponse
    {
        try {
            $modules = MiniCourseModule::where('mini_course_id', $miniCourseId)
                ->with('classes')
                ->orderBy('id')
                ->get();

            // Load module-level documents (those with mini_course_class_id = 0)
            $moduleDocs = MiniCourseDocument::where('mini_course_id', $miniCourseId)
                ->where('mini_course_class_id', 0)
                ->get();

            // Attach documents to each module
            $modules->each(function ($module) use ($moduleDocs) {
                $module->setRelation('documents', $moduleDocs);
            });

            return response()->json(['success' => true, 'data' => $modules]);
        } catch (\Exception $e) {
            Log::error('Error listing mini-course modules: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al listar módulos'], 500);
        }
    }

    public function modulesStore(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'mini_course_id' => 'required|integer|exists:mini_courses,id',
                'title' => 'required|string|max:255',
                'content' => 'nullable|string',
                'duration' => 'nullable|integer|min:1',
            ]);

            $module = MiniCourseModule::create([
                'mini_course_id' => $validated['mini_course_id'],
                'title' => $validated['title'],
                'content' => $validated['content'] ?? '',
                'duration' => $validated['duration'] ?? null,
            ]);

            return response()->json(['success' => true, 'data' => $module], 201);
        } catch (\Exception $e) {
            Log::error('Error creating mini-course module: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear módulo'], 500);
        }
    }

    public function modulesUpdate(Request $request, int $id): JsonResponse
    {
        try {
            $module = MiniCourseModule::find($id);
            if (!$module) {
                return response()->json(['success' => false, 'message' => 'Módulo no encontrado'], 404);
            }

            $rules = [
                'title' => 'sometimes|string|max:255',
                'content' => 'nullable|string',
                'duration' => 'nullable|integer|min:1',
            ];

            // Handle file uploads if present
            if ($request->hasFile('documents')) {
                $rules['documents'] = 'array';
                $rules['documents.*'] = 'file|mimes:doc,docx,pdf,xls,xlsx,txt|max:5120';
            }

            $validated = $request->validate($rules);

            $updateData = [];
            if (isset($validated['title'])) $updateData['title'] = $validated['title'];
            if (isset($validated['content'])) $updateData['content'] = $validated['content'];
            if (isset($validated['duration'])) $updateData['duration'] = $validated['duration'];

            if (!empty($updateData)) {
                $module->update($updateData);
            }

            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store('mini-course-documents/' . $module->id, 'public');
                    MiniCourseDocument::create([
                        'mini_course_id' => $module->mini_course_id,
                        'mini_course_class_id' => 0, // Documento a nivel de módulo (usa id 0 como placeholder)
                        'document' => $path,
                    ]);
                }
            }

            $module->load('classes');

            return response()->json(['success' => true, 'data' => $module]);
        } catch (\Exception $e) {
            Log::error('Error updating mini-course module: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar módulo'], 500);
        }
    }

    public function modulesDestroy(int $id): JsonResponse
    {
        try {
            $module = MiniCourseModule::with('classes')->find($id);
            if (!$module) {
                return response()->json(['success' => false, 'message' => 'Módulo no encontrado'], 404);
            }

            // Delete related documents from storage
            $moduleDocs = MiniCourseDocument::where('mini_course_id', $module->mini_course_id)
                ->where('mini_course_class_id', 0)
                ->get();
            foreach ($moduleDocs as $doc) {
                if ($doc->document && Storage::disk('public')->exists($doc->document)) {
                    Storage::disk('public')->delete($doc->document);
                }
                $doc->delete();
            }

            // Delete related classes (soft delete or cascade handled by FK)
            $module->classes()->delete();
            $module->documents()->delete();
            $module->delete();

            return response()->json(['success' => true, 'message' => 'Módulo eliminado']);
        } catch (\Exception $e) {
            Log::error('Error deleting mini-course module: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar módulo'], 500);
        }
    }

    // ==================== CLASSES ====================

    public function classesIndex(int $moduleId): JsonResponse
    {
        try {
            $classes = MiniCourseClass::where('module_id', $moduleId)
                ->orderBy('order')
                ->orderBy('id')
                ->get();

            return response()->json(['success' => true, 'data' => $classes]);
        } catch (\Exception $e) {
            Log::error('Error listing mini-course classes: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al listar clases'], 500);
        }
    }

    public function classesStore(Request $request): JsonResponse
    {
        try {
            $rules = [
                'mini_course_id' => 'required|integer|exists:mini_courses,id',
                'mini_course_module_id' => 'required|integer|exists:mini_course_modules,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'duration' => 'nullable|integer|min:1',
                'order' => 'nullable|integer|min:1',
                'video' => 'nullable|file|mimes:mp4,mov,avi,webm|max:512000',
            ];

            $validated = $request->validate($rules);

            $data = [
                'mini_course_id' => $validated['mini_course_id'],
                'module_id' => $validated['mini_course_module_id'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? '',
                'duration' => $validated['duration'] ?? null,
                'order' => $validated['order'] ?? 0,
            ];

            // Handle video upload
            if ($request->hasFile('video')) {
                $path = $request->file('video')->store('mini-course-videos/' . $validated['mini_course_module_id'], 'public');
                $data['video_url'] = $path;
            }

            $class = MiniCourseClass::create($data);

            return response()->json(['success' => true, 'data' => $class], 201);
        } catch (\Exception $e) {
            Log::error('Error creating mini-course class: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear clase: ' . $e->getMessage()], 500);
        }
    }

    public function classesUpdate(Request $request, int $id): JsonResponse
    {
        try {
            $class = MiniCourseClass::find($id);
            if (!$class) {
                return response()->json(['success' => false, 'message' => 'Clase no encontrada'], 404);
            }

            $rules = [
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'duration' => 'nullable|integer|min:1',
                'order' => 'nullable|integer|min:1',
                'video' => 'nullable|file|mimes:mp4,mov,avi,webm|max:512000',
            ];

            $validated = $request->validate($rules);

            $updateData = [];
            if (isset($validated['title'])) $updateData['title'] = $validated['title'];
            if (isset($validated['description'])) $updateData['description'] = $validated['description'];
            if (isset($validated['duration'])) $updateData['duration'] = $validated['duration'];
            if (isset($validated['order'])) $updateData['order'] = $validated['order'];

            // Handle video upload (replace existing)
            if ($request->hasFile('video')) {
                // Delete old video if exists
                if ($class->video_url && Storage::disk('public')->exists($class->video_url)) {
                    Storage::disk('public')->delete($class->video_url);
                }
                $path = $request->file('video')->store('mini-course-videos/' . $class->module_id, 'public');
                $updateData['video_url'] = $path;
            }

            $class->update($updateData);

            return response()->json(['success' => true, 'data' => $class->fresh()]);
        } catch (\Exception $e) {
            Log::error('Error updating mini-course class: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar clase'], 500);
        }
    }

    public function classesDestroy(int $id): JsonResponse
    {
        try {
            $class = MiniCourseClass::find($id);
            if (!$class) {
                return response()->json(['success' => false, 'message' => 'Clase no encontrada'], 404);
            }

            // Delete video file from storage
            if ($class->video_url && Storage::disk('public')->exists($class->video_url)) {
                Storage::disk('public')->delete($class->video_url);
            }

            $class->delete();

            return response()->json(['success' => true, 'message' => 'Clase eliminada']);
        } catch (\Exception $e) {
            Log::error('Error deleting mini-course class: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar clase'], 500);
        }
    }

    // ==================== DOCUMENTS ====================

    public function documentsDestroy(int $id): JsonResponse
    {
        try {
            $doc = MiniCourseDocument::find($id);
            if (!$doc) {
                return response()->json(['success' => false, 'message' => 'Documento no encontrado'], 404);
            }

            if ($doc->document && Storage::disk('public')->exists($doc->document)) {
                Storage::disk('public')->delete($doc->document);
            }

            $doc->delete();

            return response()->json(['success' => true, 'message' => 'Documento eliminado']);
        } catch (\Exception $e) {
            Log::error('Error deleting mini-course document: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar documento'], 500);
        }
    }
}
