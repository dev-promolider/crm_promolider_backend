<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Validation\ValidationException;
use App\Models\MasterclassUser;
use App\Models\MeetingMasterclass;
use App\Models\User;
use App\Models\Masterclass;
use App\Models\Category;
use App\Helpers\Helper;
use App\Models\MasterclassDocument;
use App\Helpers\CreateNotification;
use App\Models\MasterclassImage;
use App\Models\MasterclassDistributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\PHPMailerService;

class MasterclassController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:masterclass.create')->only('create');
        $this->middleware('can:masterclass.index')->only('index', 'listCoursesProd');
        $this->middleware('can:masterclass.edit')->only('edit');
        $this->middleware('can:masterclass.report')->only('report');
    }

    public function index()
    {
        $user = User::find(auth()->user()->id);
        $permission = $user->hasPermissionTo('masterclass.create');
        return view('content.masterclass.index', compact('user', 'permission'));
    }
    public function masterclassList($id)
    {
        //solo va encontrar las masterclass que pertenecen al usuario
        $masterclasses = Masterclass::with('images', 'documents')
            ->where('masterclasses.user_id', $id)
            ->join('categories', 'masterclasses.id_categories', '=', 'categories.id')
            ->leftJoin('masterclass_distributor', 'masterclasses.id', '=', 'masterclass_distributor.masterclass_id') // LEFT JOIN para incluir masterclasses sin distribuidores
            ->select(
                'masterclasses.id',
                'masterclasses.user_id',
                'masterclasses.id_categories',
                'masterclasses.title',
                'masterclasses.description',
                'masterclasses.objectives',
                'masterclasses.date',
                'masterclasses.hour',
                'masterclasses.duration',
                'masterclasses.email_contact',
                'masterclasses.phone_contact',
                'masterclasses.status',
                'masterclasses.meeting_link',
                'masterclasses.created_at',
                'masterclasses.updated_at',
                'categories.name as category_name',
                DB::raw('COUNT(masterclass_distributor.id) as distributors_count')
            )
            ->groupBy(
                'masterclasses.id',
                'masterclasses.user_id',
                'masterclasses.id_categories',
                'masterclasses.title',
                'masterclasses.description',
                'masterclasses.objectives',
                'masterclasses.date',
                'masterclasses.hour',
                'masterclasses.duration',
                'masterclasses.email_contact',
                'masterclasses.phone_contact',
                'masterclasses.status',
                'masterclasses.meeting_link',
                'masterclasses.created_at',
                'masterclasses.updated_at',
                'categories.id',
                'categories.name'
            )
            ->get();

        $masterclasses->each(function ($masterclass) {
            // Formatear cada imagen con la URL completa
            $masterclass->images->each(function ($image) {
                $image->image = asset($image->image);
            });

            // Formatear cada documento con la URL completa
            $masterclass->documents->each(function ($document) {
                $document->document = asset($document->document);
            });
        });

        return response()->json([
            'data' => $masterclasses,
            'message' => 'Listado de Masterclass'
        ], 200);
    }

    public function show($id)
    {
        try {
            $masterclass = Masterclass::with('images', 'documents')
                ->join('categories', 'masterclasses.id_categories', '=', 'categories.id')
                ->select(
                    'masterclasses.*',
                    'categories.name as category_name'
                )
                ->where('masterclasses.id', $id)
                ->first();
                
            if (!$masterclass) {
                return response()->json([
                    'message' => 'Masterclass no encontrada'
                ], 404);
            }
        
            if ($masterclass->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'No tienes permisos para ver esta masterclass'
                ], 403);
            }
        
            // Formatear URLs de imágenes y documentos
            $masterclass->images->each(function ($image) {
                $image->image = asset($image->image);
            });
        
            $masterclass->documents->each(function ($document) {
                $document->document = asset($document->document);
            });
        
            return response()->json([
                'data' => $masterclass,
                'message' => 'Masterclass obtenida correctamente'
            ], 200);
        } catch (\Throwable $th) {
            \Log::error('Error al obtener masterclass:', [
                'masterclass_id' => $id,
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ]);
        
            return response()->json([
                'message' => 'Error al obtener la masterclass',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $masterclass = Masterclass::with('images', 'documents')->find($id);
        
            if (!$masterclass) {
                return response()->json([
                    'message' => 'Masterclass no encontrada'
                ], 404);
            }
        
            // VALIDACIÓN DE AUTORIZACIÓN - CORRECCIÓN DE VULNERABILIDAD IDOR
            if ($masterclass->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'No tienes permisos para eliminar esta masterclass'
                ], 403);
            }
        
            // Eliminar imágenes asociadas del almacenamiento y base de datos
            foreach ($masterclass->images as $image) {
                Storage::delete($image->image);
                $image->delete();
            }
        
            // Eliminar documentos asociados del almacenamiento y base de datos
            foreach ($masterclass->documents as $document) {
                Storage::delete($document->document);
                $document->delete();
            }
        
            // Eliminar registros relacionados en masterclass_distributor
            // Si es una relación HasMany, usar delete() en lugar de detach()
            DB::table('masterclass_distributor')
                ->where('masterclass_id', $masterclass->id)
                ->delete();
        
            // Eliminar la masterclass
            $masterclass->delete();
        
            return response()->json([
                'message' => 'Masterclass eliminada correctamente'
            ], 200);
        
        } catch (\Throwable $th) {
            \Log::error('Error al eliminar masterclass:', [
                'masterclass_id' => $id,
                'user_id' => Auth::id(),
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ]);
        
            return response()->json([
                'message' => 'Error al eliminar la masterclass',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $masterclass = Masterclass::with('images', 'documents')->find($id);

            if (!$masterclass) {
                return response()->json(['message' => 'Masterclass no encontrada'], 404);
            }

            // VALIDACIÓN DE AUTORIZACIÓN
            if ($masterclass->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'No tienes permisos para actualizar esta masterclass'
                ], 403);
            }

            // validar los datos recibidos
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'id_categories' => 'required|integer|exists:categories,id',
                'description' => 'nullable|string',
                'objective' => 'nullable|string',
                'date' => 'nullable|date',
                'email_contact' => 'nullable|email',
                'phone_contact' => 'nullable|string|max:20',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'documents.*' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            ]);

            $masterclass->update([
                'title' => $validated['title'],
                'id_categories' => $validated['id_categories'],
                'description' => $validated['description'] ?? null,
                'objectives' => $validated['objective'] ?? null,
                'date' => $validated['date'] ?? null,
                'email_contact' => $validated['email_contact'] ?? null,
                'phone_contact' => $validated['phone_contact'] ?? null,
            ]);

            // Procesar imágenes
            if ($request->hasFile('images')) {
                foreach ($masterclass->images as $image) {
                    Storage::disk('public')->delete($image->image);
                    $image->delete();
                }
            
                foreach ($request->file('images') as $file) {
                    // Crear carpeta personalizada
                    $folder = "masterclasses/{$masterclass->user_id}/{$masterclass->id}/images";
                    $relativePath = $file->store($folder, 'public');
                    $path = 'storage/' . $relativePath;
                
                    $masterclass->images()->create(['image' => $path]);
                }
            }

            // Procesar documentos
            if ($request->hasFile('documents')) {
                foreach ($masterclass->documents as $document) {
                    Storage::delete($document->document);
                    $document->delete();
                }

                foreach ($request->file('documents') as $file) {
                    $path = $file->store('masterclasses/documents', 'public');
                    $masterclass->documents()->create(['document' => $path]);
                }
            }

            return response()->json([
                'message' => 'Masterclass actualizada correctamente',
                'data' => $masterclass->load('images', 'documents')
            ], 200);

        } catch (ValidationException $e) {
            // 🚨 Log detallado de validación
            \Log::error('Error de validación al actualizar masterclass:', [
                'masterclass_id' => $id,
                'user_id' => Auth::id(),
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Los datos proporcionados no son válidos',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $th) {
            \Log::error('Error al actualizar masterclass:', [
                'masterclass_id' => $id,
                'user_id' => Auth::id(),
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ]);

            return response()->json([
                'message' => 'Error al actualizar la masterclass',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function masterclassCard()
    {
        $masterclasses = Masterclass::with('images')
            ->join('categories', 'masterclasses.id_categories', '=', 'categories.id')
            ->select('masterclasses.*', 'categories.name as category_name')
            ->get();

        // Formatear cada imagen con la URL completa
        $masterclasses->each(function ($masterclass) {
            $masterclass->images->each(function ($image) {
                $image->image = asset($image->image); // Convierte la ruta en una URL completa
            });
        });
        return response()->json([
            'data' => $masterclasses,
            'message' => 'Listado de Masterclass'
        ], 200);
    }
    public function create()
    {
        $categories = Category::all();
        return view('content.masterclass.create', compact('categories'));

    }
    public function storeMasterclass(Request $request)
    {
        $user = Auth::user();

        \Log::info('Iniciando registro de masterclass', ['user_id' => $user->id]);

        // Validación de datos
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'id_categories' => 'required|exists:categories,id',
                'description' => 'required|string',
                'objective' => 'required|string',
                'date' => 'required|date',
                'hour' => 'required|date_format:H:i',
                'duration' => 'required|integer|min:1',
                'meeting_link' => 'required|url', 
                'email_contact' => 'required|email',
                'phone_contact' => 'required|regex:/^[0-9]{10,15}$/',
                'images.*' => 'file|mimes:jpeg,jpg,png,webp|max:500',
                'documents.*' => 'file|mimes:doc,docx,pdf,xls,xlsx,txt|max:1024',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Falló validación al registrar masterclass', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Datos inválidos.',
                'errors' => $e->errors(),
            ], 422);
        }

        if ($request->date < date('Y-m-d')) {
            \Log::info('Fecha inválida: menor a la actual', ['fecha_ingresada' => $request->date]);

            return response()->json([
                'message' => 'La fecha de la masterclass no puede ser menor a la fecha actual.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            \Log::info('Creando instancia de Masterclass');

            $masterclass = new Masterclass();
            $masterclass->user_id = $user->id;
            $masterclass->id_categories = $request->id_categories;
            $masterclass->title = $request->title;
            $masterclass->description = $request->description;
            $masterclass->objectives = $request->objective;
            $masterclass->date = $request->date;
            $masterclass->hour = $request->hour;
            $masterclass->duration = $request->duration;
            $masterclass->email_contact = $request->email_contact;
            $masterclass->phone_contact = $request->phone_contact;
            $masterclass->status = 1;
            //GUARDAR EL MEETING LINK INGRESADO POR EL CREADOR (Zoom, Meet, etc.)
            $masterclass->meeting_link = $request->meeting_link;

            if ($masterclass->save()) {
                \Log::info('Masterclass guardada con ID ' . $masterclass->id);

                // Imágenes
                if ($request->hasFile('images')) {
                    \Log::info('Procesando imágenes');

                    foreach ($request->file('images') as $image) {
                        $imageName = Helper::formatFilename($image->getClientOriginalName());
                        $path = 'masterclasses/' . $user->id . '/' . $masterclass->id . '/images/';
                        $storedPath = $image->store($path, 'public');

                        MasterclassImage::create([
                            'masterclass_id' => $masterclass->id,
                            'image' => 'storage/' . $storedPath,
                        ]);

                        \Log::info('Imagen guardada', ['path' => $storedPath]);
                    }
                }

                // Documentos
                if ($request->hasFile('documents')) {
                    \Log::info('Procesando documentos');

                    foreach ($request->file('documents') as $document) {
                        $documentName = Helper::formatFilename($document->getClientOriginalName());
                        $path = 'masterclasses/' . $user->id . '/' . $masterclass->id . '/documents/';
                        $storedPath = $document->store($path, 'public');

                        MasterclassDocument::create([
                            'masterclass_id' => $masterclass->id,
                            'document' => 'storage/' . $storedPath,
                        ]);

                        \Log::info('Documento guardado', ['path' => $storedPath]);
                    }
                }

                DB::commit();

                \Log::info('Registro de masterclass finalizado con éxito');

                CreateNotification::saveNotificationDistributors($user->id, $masterclass->title, 'masterclass');

                return response()->json([
                    'message' => 'Masterclass registrada con éxito.',
                    'data' => $masterclass,
                ], 200);
            }

            throw new \Exception('No se pudo guardar la masterclass.');
        } catch (\Throwable $th) {
            DB::rollBack();

            \Log::error('Error al guardar la masterclass', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ]);

            return response()->json([
                'message' => 'Ocurrió un error al registrar la masterclass.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    function details($id)
    {
        $user = User::find(auth()->user()->id);
        $permission = $user->hasPermissionTo('masterclass.marketplace');
        $masterclass = Masterclass::with('images', 'documents')
            ->join('categories', 'masterclasses.id_categories', '=', 'categories.id')
            ->select('masterclasses.*', 'categories.name as category_name')
            ->where('masterclasses.id', $id)
            ->first();

        // Formatear cada imagen con la URL completa
        $masterclass->images->each(function ($image) {
            $image->image = asset($image->image);
        });

        // Formatear cada documento con la URL completa
        $masterclass->documents->each(function ($document) {
            $document->document = asset($document->document);
        });

        return view('content.masterclass.marketplace.details', compact('masterclass', 'user', 'permission'));

    }
    public function registerMasterclass($id_masterclass)
    {
        $user = User::find(auth()->user()->id);

        // Crear el registro sin los campos 'code' y 'expires_at'
        MasterclassDistributor::create([
            'user_id' => $user->id,
            'masterclass_id' => $id_masterclass,
            'code' => Str::uuid(), // ✅ Genera código único
            'expires_at' => now()->addDays(7),
        ]);

        return response()->json([
            'message' => 'Registro creado, espera para generar tu código de invitación.',
        ]);
    }
    public function createInvitationLink($id_masterclass)
    {
        $user = User::find(auth()->user()->id);
        $random = Str::random(10);
        $code = $user->id . $random;

        // Buscar el registro que necesita actualizarse
        $invitation = MasterclassDistributor::where('user_id', $user->id)
            ->where('masterclass_id', $id_masterclass)
            ->first();

        if ($invitation) {
            // Actualizar el registro con el 'code' y 'expires_at'
            $invitation->update([
                'code' => $code,
                'expires_at' => now()->addDays(7),
            ]);
        }

        return response()->json([
            'link' => url("/register-masterclass?invitation_code={$code}"),
        ]);
    }
    public function checkRegistration($id_masterclass)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $isRegistered = MasterclassDistributor::where('user_id', $user->id)
                ->where('masterclass_id', $id_masterclass)
                ->exists();

            return response()->json([
                'isRegistered' => $isRegistered,
                'message' => 'Estado de registro verificado correctamente'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error al verificar registro de masterclass:', [
                'masterclass_id' => $id_masterclass,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al verificar el registro',
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }
    
    public function updateStatus(Request $request, $id)
    {
        try {
            // Validar el campo de estado
            $validated = $request->validate([
                'status' => 'required|in:0,1,2', // puedes ajustar los valores válidos según tu lógica
            ]);

            $masterclass = Masterclass::find($id);

            if (!$masterclass) {
                return response()->json([
                    'message' => 'Masterclass no encontrada'
                ], 404);
            }

            // Verificar que el usuario sea el propietario
            if ($masterclass->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'No tienes permisos para actualizar el estado de esta masterclass'
                ], 403);
            }

            // Actualizar solo el estado
            $masterclass->update([
                'status' => $validated['status']
            ]);

            return response()->json([
                'message' => 'Estado actualizado correctamente',
                'data' => [
                    'id' => $masterclass->id,
                    'status' => $masterclass->status
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Datos inválidos.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $th) {
            \Log::error('Error al actualizar el estado de la masterclass:', [
                'masterclass_id' => $id,
                'user_id' => Auth::id(),
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ]);

            return response()->json([
                'message' => 'Error al actualizar el estado de la masterclass',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function checkInvitation($id_masterclass)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $invitation = MasterclassDistributor::where('user_id', $user->id)
                ->where('masterclass_id', $id_masterclass)
                ->where('code', '!=', 0)
                ->first();

            $existInvitation = !is_null($invitation);
            $invitationLink = null;

            if ($existInvitation) {
                $invitationLink = url("/register-masterclass?invitation_code={$invitation->code}");
            }

            return response()->json([
                'existInvitation' => $existInvitation,
                'invitationLink' => $invitationLink,
                'message' => 'Estado de invitación verificado correctamente'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error al verificar invitación de masterclass:', [
                'masterclass_id' => $id_masterclass,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error al verificar la invitación',
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function calendar()
    {
        $user = User::find(auth()->user()->id);
        $permission = $user->hasPermissionTo('masterclass.calendar'); // Verifica si el usuario tiene permiso para acceder al calendario
        $role = $user->getRoleNames()->first();
        return view('content.masterclass.calendar', compact('user', 'permission', 'role'));
    }
    public function calendarAdmin()
    {
        $masterclasses = Masterclass::select('id', 'title', 'date')
            ->get();

        return response()->json(['data' => $masterclasses]);
    }
    public function calendarProducer($id)
    {
        $masterclasses = Masterclass::where('user_id', $id)
            ->select('id', 'title', 'date')
            ->get();
        return response()->json(['data' => $masterclasses]);
    }
    public function calendarDistributor($id)
    {
        $masterclasses = Masterclass::join('masterclass_distributor', 'masterclasses.id', '=', 'masterclass_distributor.masterclass_id')
            ->where('masterclass_distributor.user_id', $id)
            ->select('masterclasses.id as id', 'masterclasses.title as title', 'masterclasses.date as date')
            ->get();
        return response()->json(['data' => $masterclasses]);
    }
    //obtener datos de las reunione sprogramdas en el calendario
    // join para obetener nomrbes de los estudiantes
    public function getActivities($id)
    {
        $activities = MeetingMasterclass::where('owner_id', $id)
            ->select('id', 'date', 'time', 'comments', 'user_id')
            ->get();

        return response()->json(['data' => $activities]);
    }
    public function getParticipants($id)
    {
        $particpants = DB::select("
        SELECT mu.id, mu.name, mu.lastname
        FROM masterclass_user mu
        JOIN masterclass_distributor md ON mu.masterclass_distributor_id = md.id
        WHERE md.masterclass_id = ?
        ", [$id]);

        return response()->json(['data' => $particpants]);
    }
    public function createMeeting(Request $request)
    {
        // Validar los datos recibidos
        $request->validate([
            'date' => 'required|date',
            'time' => 'required',
            'masterclassId' => 'required',
            'participantId' => 'required',
            'title' => 'required',
            'owner_id' => 'required',
        ]);

        // Crear la reunión
        $meeting = MeetingMasterclass::create([
            'date' => $request->date,
            'time' => $request->time,
            'owner_id' => $request->owner_id,
            'comments' => $request->title,
            'user_id' => $request->participantId,

        ]);

        return response()->json([
            'message' => 'Reunión creada exitosamente',
            'meeting' => $meeting
        ], 201);
    }
    public function listStudents($id)
    {
        $students = MasterclassUser::join(
            'masterclass_distributor',
            'masterclass_user.masterclass_distributor_id',
            '=',
            'masterclass_distributor.id'
        )->join('masterclasses', 'masterclass_distributor.masterclass_id', '=', 'masterclasses.id')
            ->where('masterclasses.id', $id)
            ->select(
                'masterclass_user.id as id',
                'masterclass_user.name as name',
                'masterclass_user.lastname as lastname',
                'masterclass_user.email as email',
                'masterclass_user.phone as phone',
                'masterclass_user.created_at as date',
                //'masterclass_user.country as country',
                'masterclass_user.age as birthdate', // cambiada a age porque la tabla no tiene birthdate"
                'masterclass_distributor.masterclass_id as masterclass_id'
            )
            ->get();
        return response()->json(['data' => $students]);
    }
    //ruta pra visualizar al vista de lsita de alumnos segun id de masterclass
    public function viewStudents($id)
    {
        $user = User::find(auth()->user()->id);
        return view('content.masterclass.students', compact('id', 'user'));

    }
}
