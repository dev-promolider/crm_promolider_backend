<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ebook;
use App\Models\EbookImage;
use App\Models\EbookDocument;
use App\Models\EbookChapter;
use App\Models\EbookDistributor;
use App\Helpers\CreateNotification;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class EbookController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:marketing.tools')->only(['create', 'store']);
    }

    public function create()
    {
        $categories = Category::all();
        return view('content.marketing.e-book.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        \Log::info('Iniciando registro de e-book', ['user_id' => $user->id]);
        
        try {
            $validated = $request->validate([
                'titulo' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'autor' => 'required|string|max:255',
                'categoria' => 'required|exists:categories,id',
                'paginas' => 'required|integer|min:1',
                'portada' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'archivo_pdf' => 'nullable|mimes:pdf|max:10240',
                'capitulos' => 'required|array|min:1',
                'capitulos.*.titulo' => 'required|string|max:255',
                'capitulos.*.contenido' => 'required|string',
                'capitulos.*.paginas' => 'required|integer|min:1',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Falló validación al registrar e-book', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Datos inválidos.',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();
            \Log::info('Creando instancia de Ebook');
            
            $ebook = new Ebook();
            $ebook->user_id = $user->id;
            $ebook->category_id = $request->categoria;
            $ebook->title = $request->titulo;
            $ebook->description = $request->descripcion;
            $ebook->author = $request->autor;
            $ebook->pages = $request->paginas;
            $ebook->status = 1;

            if ($ebook->save()) {
                \Log::info('E-book guardado con ID ' . $ebook->id);

                // Guardar portada
                if ($request->hasFile('portada')) {
                    $image = $request->file('portada');
                    $path = 'ebooks/' . $user->id . '/' . $ebook->id . '/images/';
                    $storedPath = $image->store($path, 'public');
                    
                    EbookImage::create([
                        'ebook_id' => $ebook->id,
                        'image' => 'storage/' . $storedPath,
                    ]);
                    \Log::info('Portada guardada', ['path' => $storedPath]);
                }

                // Guardar archivo PDF
                if ($request->hasFile('archivo_pdf')) {
                    $pdf = $request->file('archivo_pdf');
                    $path = 'ebooks/' . $user->id . '/' . $ebook->id . '/documents/';
                    $storedPath = $pdf->store($path, 'public');
                    
                    EbookDocument::create([
                        'ebook_id' => $ebook->id,
                        'document' => 'storage/' . $storedPath,
                    ]);
                    \Log::info('PDF guardado', ['path' => $storedPath]);
                }

                // Guardar capítulos
                foreach ($request->capitulos as $capituloData) {
                    EbookChapter::create([
                        'ebook_id' => $ebook->id,
                        'title' => $capituloData['titulo'],
                        'content' => $capituloData['contenido'],
                        'pages' => $capituloData['paginas'],
                    ]);
                }

                DB::commit();
                \Log::info('Registro de e-book finalizado con éxito');

                CreateNotification::saveNotificationDistributors($user->id, $request->titulo, 'ebook');
                
                return response()->json([
                    'message' => 'E-book registrado con éxito.',
                    'data' => $ebook,
                ], 200);
            }

            throw new \Exception('No se pudo guardar el e-book.');
        } catch (\Throwable $th) {
            DB::rollBack();
            \Log::error('Error al guardar el e-book', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ]);
            
            return response()->json([
                'message' => 'Ocurrió un error al registrar el e-book.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function getByUser($id)
    {
        try {
            \Log::info('Obteniendo e-books para usuario:', ['user_id' => $id]);
            
            $ebooks = Ebook::with('images', 'documents', 'chapters')
                ->where('ebooks.user_id', $id)
                ->join('categories', 'ebooks.category_id', '=', 'categories.id')
                ->select(
                    'ebooks.*',
                    'categories.name as category_name'
                )
                ->get();
                
            \Log::info('E-books encontrados:', ['count' => $ebooks->count()]);
                
            $ebooks->each(function ($ebook) {
                $ebook->images->each(function ($image) {
                    $image->image = asset($image->image);
                });
                $ebook->documents->each(function ($document) {
                    $document->document = asset($document->document);
                });
            });
        
            return response()->json([
                'data' => $ebooks,
                'message' => 'Listado de E-books'
            ], 200);
            
        } catch (\Throwable $th) {
            \Log::error('Error al obtener e-books por usuario:', [
                'user_id' => $id,
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ]);
            
            return response()->json([
                'message' => 'Error al obtener los e-books',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
    public function show($id)
    {
        try {
            $ebook = Ebook::with('images', 'documents', 'chapters')
                ->join('categories', 'ebooks.category_id', '=', 'categories.id')
                ->select(
                    'ebooks.*',
                    'categories.name as category_name'
                )
                ->where('ebooks.id', $id)
                ->first();
                
            if (!$ebook) {
                return response()->json([
                    'message' => 'E-book no encontrado'
                ], 404);
            }
        
            // Verificar que el usuario tenga permisos para ver este e-book
            if ($ebook->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'No tienes permisos para ver este e-book'
                ], 403);
            }
        
            $ebook->images->each(function ($image) {
                $image->image = asset($image->image);
            });
            $ebook->documents->each(function ($document) {
                $document->document = asset($document->document);
            });
        
            return response()->json([
                'data' => $ebook,
                'message' => 'E-book obtenido correctamente'
            ], 200);
            
        } catch (\Throwable $th) {
            \Log::error('Error al obtener e-book:', [
                'ebook_id' => $id,
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ]);
            
            return response()->json([
                'message' => 'Error al obtener el e-book',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $ebook = Ebook::with('images', 'documents', 'chapters')->find($id);
        if (!$ebook) {
            return response()->json(['message' => 'E-book no encontrado'], 404);
        }
    
        // Validar que el usuario sea el propietario del e-book
        if ($ebook->user_id !== Auth::id()) {
            return response()->json(['message' => 'No tienes permisos para editar este e-book'], 403);
        }
    
        try {
            $validated = $request->validate([
                'titulo' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'precio' => 'required|numeric|min:0',
                'autor' => 'required|string|max:255',
                'categoria' => 'required|exists:categories,id',
                'paginas' => 'required|integer|min:1',
                'portada' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'archivo_pdf' => 'nullable|mimes:pdf|max:10240',
                'capitulos' => 'required|array|min:1',
                'capitulos.*.titulo' => 'required|string|max:255',
                'capitulos.*.contenido' => 'required|string',
                'capitulos.*.paginas' => 'required|integer|min:1',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Falló validación al actualizar e-book', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Datos inválidos.',
                'errors' => $e->errors(),
            ], 422);
        }
    
        try {
            DB::beginTransaction();
            \Log::info('Actualizando e-book', ['ebook_id' => $ebook->id]);
        
            // Actualizar datos básicos del ebook
            $ebook->update([
                'title' => $request->titulo,
                'description' => $request->descripcion,
                'price' => $request->precio,
                'author' => $request->autor,
                'category_id' => $request->categoria,
                'pages' => $request->paginas,
            ]);
        
            // Actualizar portada si se proporciona
            if ($request->hasFile('portada')) {
                // Eliminar portada anterior
                foreach ($ebook->images as $image) {
                    if (Storage::disk('public')->exists(str_replace('storage/', '', $image->image))) {
                        Storage::disk('public')->delete(str_replace('storage/', '', $image->image));
                    }
                    $image->delete();
                }
                
                // Subir nueva portada
                $imageFile = $request->file('portada');
                $path = 'ebooks/' . $ebook->user_id . '/' . $ebook->id . '/images/';
                $storedPath = $imageFile->store($path, 'public');
                $ebook->images()->create(['image' => 'storage/' . $storedPath]);
                \Log::info('Portada actualizada', ['path' => $storedPath]);
            }
        
            // Actualizar PDF si se proporciona
            if ($request->hasFile('archivo_pdf')) {
                // Eliminar PDF anterior
                foreach ($ebook->documents as $document) {
                    if (Storage::disk('public')->exists(str_replace('storage/', '', $document->document))) {
                        Storage::disk('public')->delete(str_replace('storage/', '', $document->document));
                    }
                    $document->delete();
                }
                
                // Subir nuevo PDF
                $pdfFile = $request->file('archivo_pdf');
                $path = 'ebooks/' . $ebook->user_id . '/' . $ebook->id . '/documents/';
                $storedPath = $pdfFile->store($path, 'public');
                $ebook->documents()->create(['document' => 'storage/' . $storedPath]);
                \Log::info('PDF actualizado', ['path' => $storedPath]);
            }
        
            // Actualizar capítulos
            // Primero eliminar todos los capítulos existentes
            $ebook->chapters()->delete();
            
            // Crear los nuevos capítulos
            foreach ($request->capitulos as $capituloData) {
                EbookChapter::create([
                    'ebook_id' => $ebook->id,
                    'title' => $capituloData['titulo'],
                    'content' => $capituloData['contenido'],
                    'pages' => $capituloData['paginas'],
                ]);
            }
        
            DB::commit();
            \Log::info('E-book actualizado correctamente', ['ebook_id' => $ebook->id]);
        
            // Cargar las relaciones actualizadas
            $updatedEbook = $ebook->load('images', 'documents', 'chapters');
            
            // Procesar URLs de imágenes y documentos para la respuesta
            $updatedEbook->images->each(function ($image) {
                $image->image = asset($image->image);
            });
            $updatedEbook->documents->each(function ($document) {
                $document->document = asset($document->document);
            });
        
            return response()->json([
                'message' => 'E-book actualizado correctamente',
                'data' => $updatedEbook
            ], 200);
        
        } catch (\Throwable $th) {
            DB::rollBack();
            \Log::error('Error al actualizar el e-book', [
                'ebook_id' => $ebook->id,
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ]);
            
            return response()->json([
                'message' => 'Ocurrió un error al actualizar el e-book.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $ebook = Ebook::where('id', $id)->where('user_id', $user->id)->first();

        if (!$ebook) {
            return response()->json(['message' => 'E-book no encontrado'], 404);
        }

        try {
            DB::beginTransaction();

            // IMPORTANTE: Elimina relaciones si no tienes cascada en la DB
            $ebook->images()->delete();
            $ebook->documents()->delete();
            $ebook->chapters()->delete();

            $ebook->delete();

            DB::commit();
            return response()->json([
                'status' => 'ok',
                'ebooks' => Ebook::where('user_id', $user->id)->get()
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            \Log::error('Error al eliminar e-book', [
                'ebook_id' => $id,
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ]);
            return response()->json([
                'message' => 'Error al eliminar el e-book.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function createInvitationLink($id)
    {
        $user = Auth::user();
        $random = Str::random(10);
        $code = $user->id . $random;
        
        // Buscar el registro que necesita actualizarse
        $invitation = EbookDistributor::where('user_id', $user->id)
            ->where('ebook_id', $id)
            ->first();

        if ($invitation) {
            // Actualizar el registro con el 'code' y 'expires_at'
            $invitation->update([
                'code' => $code,
                'expires_at' => now()->addDays(7),
            ]);
        }

        return response()->json([
            'link' => url("/ebook/register?invitation_code={$code}"),
        ]);
    }

    public function checkInvitation($id)
    {
        $user = Auth::user();
        
        $existInvitation = EbookDistributor::where('user_id', $user->id)
            ->where('ebook_id', $id)
            ->where('code', '!=', 0)
            ->exists();

        $data = EbookDistributor::where('user_id', $user->id)
            ->where('ebook_id', $id)
            ->where('code', '!=', 0)
            ->first();

        $invitationLink = $data 
            ? url("/ebook/register?invitation_code={$data->code}")
            : null;

        return response()->json([
            'existInvitation' => $existInvitation, 
            'invitationLink' => $invitationLink
        ]);
    }

    public function purchase($id)
    {
        $user = auth()->user();
        $ebook = Ebook::find($id);
        
        if (!$ebook) {
            return response()->json(['message' => 'E-book no encontrado'], 404);
        }
        
        $alreadyPurchased = EbookDistributor::where('user_id', $user->id)
            ->where('ebook_id', $id)
            ->exists();
            
        if ($alreadyPurchased) {
            return response()->json([
                'message' => 'Ya has comprado este e-book',
                'isPurchased' => true
            ], 200);
        }
        
        EbookDistributor::create([
            'user_id' => $user->id,
            'ebook_id' => $id,
            'code' => Str::uuid(),
            'expires_at' => now()->addDays(7),
        ]);
        
        return response()->json([
            'message' => 'E-book comprado exitosamente',
            'isPurchased' => true
        ], 200);
    }

    public function checkPurchase($id)
    {
        $user = auth()->user();
        $ebook = Ebook::find($id);
        
        if (!$ebook) {
            return response()->json(['message' => 'E-book no encontrado'], 404);
        }
        
        // Lógica de compra (aquí estamos asumiendo que 'distribuir' = 'comprar')
        $hasPurchased = EbookDistributor::where('user_id', $user->id)
            ->where('ebook_id', $id)
            ->exists();
            
        return response()->json([
            'isPurchased' => $hasPurchased
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            \Log::info('📘 [updateStatus] Inicio del proceso', [
                'ebook_id' => $id,
                'user_id' => Auth::id(),
                'input' => $request->all(),
            ]);
        
            // ✅ Validar el estado recibido
            $validated = $request->validate([
                'status' => 'required|in:0,1,2',
            ]);
        
            \Log::info('✅ [updateStatus] Datos validados correctamente', [
                'validated_status' => $validated['status'],
            ]);
        
            $ebook = Ebook::find($id);
        
            if (!$ebook) {
                \Log::warning('⚠️ [updateStatus] Ebook no encontrado', [
                    'ebook_id' => $id,
                ]);
            
                return response()->json([
                    'message' => 'E-book no encontrado'
                ], 404);
            }
        
            \Log::info('📗 [updateStatus] Ebook encontrado', [
                'ebook_id' => $ebook->id,
                'current_status' => $ebook->status,
                'user_id_owner' => $ebook->user_id,
            ]);
        
            // ✅ Verificar que el usuario sea el propietario
            if ($ebook->user_id !== Auth::id()) {
                \Log::warning('🚫 [updateStatus] Usuario sin permisos para modificar este e-book', [
                    'ebook_id' => $ebook->id,
                    'owner_id' => $ebook->user_id,
                    'auth_user_id' => Auth::id(),
                ]);
            
                return response()->json([
                    'message' => 'No tienes permisos para actualizar el estado de este e-book'
                ], 403);
            }
        
            // ✅ Intentar actualizar el estado
            $oldStatus = $ebook->status;
            $ebook->status = $validated['status'];
            $saved = $ebook->save();
        
            \Log::info('📝 [updateStatus] Intento de actualización', [
                'ebook_id' => $ebook->id,
                'old_status' => $oldStatus,
                'new_status' => $validated['status'],
                'save_result' => $saved,
            ]);
        
            if (!$saved) {
                \Log::error('❌ [updateStatus] Fallo al guardar el nuevo estado en la base de datos', [
                    'ebook_id' => $ebook->id,
                    'expected_status' => $validated['status'],
                ]);
            }
        
            return response()->json([
                'message' => 'Estado del e-book actualizado correctamente',
                'data' => [
                    'id' => $ebook->id,
                    'status' => $ebook->status
                ]
            ], 200);
        
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('⚠️ [updateStatus] Validación fallida', [
                'errors' => $e->errors(),
            ]);
        
            return response()->json([
                'message' => 'Datos inválidos.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $th) {
            \Log::error('💥 [updateStatus] Error inesperado', [
                'ebook_id' => $id,
                'user_id' => Auth::id(),
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ]);
        
            return response()->json([
                'message' => 'Error al actualizar el estado del e-book',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    
}
