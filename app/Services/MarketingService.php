<?php

namespace App\Services;

use App\Models\{
    Masterclass, Ebook, MiniCourse,
    MasterclassUser, MiniCourseUser, EbookUser,
    User, Dinamica
};
use Illuminate\Support\Facades\{DB, Log};
use Carbon\Carbon;

class MarketingService
{
    /**
     * Obtiene todas las herramientas de un usuario
     */
    public function getToolsByUser($userId)
    {
        try {
            // Mini Cursos
            $miniCourses = $this->getMiniCoursesForUser($userId);
            
            // E-books
            $ebooks = $this->getEbooksForUser($userId);
            
            // Masterclasses
            $masterclasses = $this->getMasterclassesForUser($userId);
            
            // Dinámicas - TEMPORALMENTE DESHABILITADO
            // $dinamicas = $this->getDinamicasForUser($userId);

            // Combinar y ordenar
            $allTools = collect()
                ->concat($miniCourses)
                ->concat($ebooks)
                ->concat($masterclasses)
                // ->concat($dinamicas)
                ->sortByDesc('fecha')
                ->values();

            return response()->json([
                'data' => $allTools,
                'message' => 'Listado de herramientas de marketing'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener herramientas', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener herramientas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene campañas activas (status = 2)
     */
    public function getCampaigns()
    {
        try {
            $user = auth()->user();
            
            if ($user->getRoleNames()->first() !== 'Producer') {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para acceder a esta sección'
                ], 403);
            }

            $miniCourses = $this->getMiniCoursesWithStatus(2);
            $ebooks = $this->getEbooksWithStatus(2);
            $masterclasses = $this->getMasterclassesWithStatus(2);

            $allCampaigns = collect()
                ->concat($miniCourses)
                ->concat($ebooks)
                ->concat($masterclasses)
                ->sortByDesc('fecha')
                ->values();

            $stats = [
                'total' => $allCampaigns->count(),
                'mini_cursos' => $miniCourses->count(),
                'ebooks' => $ebooks->count(),
                'masterclasses' => $masterclasses->count(),
                'total_distribuidores' => $allCampaigns->sum('distribuidores')
            ];

            return response()->json([
                'success' => true,
                'data' => $allCampaigns,
                'stats' => $stats,
                'message' => 'Campañas activas obtenidas correctamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener campañas', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las campañas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene campañas por tipo
     */
    public function getCampaignsByType($type)
    {
        try {
            $user = auth()->user();
            
            if ($user->getRoleNames()->first() !== 'Producer') {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos'
                ], 403);
            }

            $data = match($type) {
                'minicurso' => $this->getMiniCoursesWithStatus(2),
                'ebook' => $this->getEbooksWithStatus(2),
                'masterclass' => $this->getMasterclassesWithStatus(2),
                default => throw new \Exception('Tipo no válido')
            };

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => "Campañas de {$type} obtenidas"
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Obtiene lista de masterclasses futuras
     */
    public function getMasterclassList()
    {
        try {
            $now = Carbon::now();

            $masterclasses = Masterclass::with('images')
                ->join('categories', 'masterclasses.id_categories', '=', 'categories.id')
                ->select('masterclasses.*', 'categories.name as category_name')
                ->where('masterclasses.status', 1)
                ->where(DB::raw("CONCAT(masterclasses.date, ' ', masterclasses.hour)"), '>', $now)
                ->get();

            $this->formatImages($masterclasses, 'images');

            return response()->json([
                'success' => true,
                'data' => $masterclasses,
                'message' => 'Listado de Masterclass futuras'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener masterclasses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene masterclasses paginadas
     */
    public function getMasterclassesPaginated($pageNumber, $pageSize)
    {
        return $this->getPaginatedContent(Masterclass::class, 'masterclasses', $pageNumber, $pageSize);
    }

    /**
     * Obtiene detalles de una masterclass
     */
    public function getMasterclassDetails($id)
    {
        $masterclass = Masterclass::with('images', 'documents')
            ->join('categories', 'masterclasses.id_categories', '=', 'categories.id')
            ->select('masterclasses.*', 'categories.name as category_name')
            ->where('masterclasses.id', $id)
            ->first();

        $this->formatImages($masterclass->images, null);
        $this->formatDocuments($masterclass->documents);

        return $masterclass;
    }

    /**
     * Obtiene lista de ebooks
     */
    public function getEbooksList()
    {
        $ebooks = Ebook::with('images', 'documents', 'chapters')
            ->join('categories', 'ebooks.category_id', '=', 'categories.id')
            ->select('ebooks.*', 'categories.name as category_name')
            ->where('ebooks.status', 1)
            ->get();

        $this->formatImages($ebooks, 'images');

        return response()->json([
            'data' => $ebooks,
            'message' => 'Listado de E-books'
        ], 200);
    }

    /**
     * Obtiene ebooks paginados
     */
    public function getEbooksPaginated($pageNumber, $pageSize)
    {
        return $this->getPaginatedContent(Ebook::class, 'ebooks', $pageNumber, $pageSize, [
            'relations' => ['images', 'documents', 'chapters'],
            'categoryField' => 'category_id'
        ]);
    }

    /**
     * Obtiene detalles de un ebook
     */
    public function getEbookDetails($id)
    {
        $ebook = Ebook::with('images', 'documents', 'chapters')
            ->join('categories', 'ebooks.category_id', '=', 'categories.id')
            ->select('ebooks.*', 'categories.name as category_name')
            ->where('ebooks.id', $id)
            ->first();

        $this->formatImages($ebook->images, null);
        $this->formatDocuments($ebook->documents);

        return $ebook;
    }

    /**
     * Obtiene lista de mini cursos
     */
    public function getMiniCoursesList()
    {
        try {
            $miniCourses = MiniCourse::with('images', 'modules')
                ->join('categories', 'mini_courses.category_id', '=', 'categories.id')
                ->select('mini_courses.*', 'categories.name as category_name')
                ->where('mini_courses.status', 1)
                ->get();

            $this->formatImages($miniCourses, 'images');

            return response()->json([
                'data' => $miniCourses,
                'message' => 'Listado de Mini Cursos'
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Error en miniCoursesList', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error interno'
            ], 500);
        }
    }

    /**
     * Obtiene mini cursos paginados
     */
    public function getMiniCoursesPaginated($pageNumber, $pageSize)
    {
        return $this->getPaginatedContent(MiniCourse::class, 'mini_courses', $pageNumber, $pageSize, [
            'relations' => ['images', 'modules'],
            'categoryField' => 'category_id'
        ]);
    }

    /**
     * Obtiene detalles de un mini curso
     */
    public function getMiniCourseDetails($id)
    {
        $miniCourse = MiniCourse::with('images', 'modules')
            ->join('categories', 'mini_courses.category_id', '=', 'categories.id')
            ->select('mini_courses.*', 'categories.name as category_name')
            ->where('mini_courses.id', $id)
            ->first();

        $this->formatImages($miniCourse->images, null);

        return $miniCourse;
    }

    /**
     * Obtiene participantes pendientes
     */
    public function getPendingParticipants($id, $type)
    {
        try {
            $students = match($type) {
                'masterclass' => $this->getMasterclassStudents($id, 0),
                'minicourse' => $this->getMiniCourseStudents($id, 0),
                'ebook' => $this->getEbookStudents($id, 0),
                default => throw new \Exception('Tipo no válido')
            };

            return response()->json([
                'success' => true,
                'data' => $students
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Obtiene los distribuidores de una masterclass
     */
    public function getMasterclassDistributors($masterclassId)
    {
        try {
            $distributors = DB::table('masterclass_distributor as md')
                ->join('users as u', 'md.user_id', '=', 'u.id')
                ->where('md.masterclass_id', $masterclassId)
                ->select(
                    'u.id as user_id',
                    'u.name',
                    'u.email',
                    'u.phone',
                    'md.id as distributor_id',
                    'md.created_at as fecha_asociacion'
                )
                ->distinct()
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $distributors,
                'total' => $distributors->count(),
                'message' => 'Distribuidores de masterclass obtenidos correctamente'
            ], 200);
        
        } catch (\Exception $e) {
            Log::error('Error al obtener distribuidores de masterclass', [
                'masterclass_id' => $masterclassId,
                'error' => $e->getMessage()
            ]);
        
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener distribuidores',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtiene los distribuidores de un mini curso
     */
    public function getMiniCourseDistributors($minicourseId)
    {
        try {
            $distributors = DB::table('mini_course_distributors as mcd')
                ->join('users as u', 'mcd.user_id', '=', 'u.id')
                ->where('mcd.mini_course_id', $minicourseId)
                ->select(
                    'u.id as user_id',
                    'u.name',
                    'u.email',
                    'u.phone',
                    'mcd.id as distributor_id',
                    'mcd.created_at as fecha_asociacion'
                )
                ->distinct()
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $distributors,
                'total' => $distributors->count(),
                'message' => 'Distribuidores de mini curso obtenidos correctamente'
            ], 200);
        
        } catch (\Exception $e) {
            Log::error('Error al obtener distribuidores de mini curso', [
                'minicourse_id' => $minicourseId,
                'error' => $e->getMessage()
            ]);
        
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener distribuidores',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtiene los distribuidores de un ebook
     */
    public function getEbookDistributors($ebookId)
    {
        try {
            $distributors = DB::table('ebook_distributor as ed')
                ->join('users as u', 'ed.user_id', '=', 'u.id')
                ->where('ed.ebook_id', $ebookId)
                ->select(
                    'u.id as user_id',
                    'u.name',
                    'u.email',
                    'u.phone',
                    'ed.id as distributor_id',
                    'ed.created_at as fecha_asociacion'
                )
                ->distinct()
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $distributors,
                'total' => $distributors->count(),
                'message' => 'Distribuidores de ebook obtenidos correctamente'
            ], 200);
        
        } catch (\Exception $e) {
            Log::error('Error al obtener distribuidores de ebook', [
                'ebook_id' => $ebookId,
                'error' => $e->getMessage()
            ]);
        
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener distribuidores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lista estudiantes de un contenido
     */
    public function listStudents($id, $type, $distributorUserId)
    {
        $students = match($type) {
            'masterclass' => $this->getMasterclassStudents($id, null, $distributorUserId),
            'minicourse' => $this->getMiniCourseStudents($id, null, $distributorUserId),
            'ebook' => $this->getEbookStudents($id, null, $distributorUserId),
            default => []
        };

        return response()->json(['data' => $students]);
    }

    /**
     * Obtiene lista consolidada de estudiantes
     */
    public function getStudentsList($userId)
    {
        try {
            $masterclassStudents = $this->getStudentsByType('masterclass', $userId);
            $minicourseStudents = $this->getStudentsByType('minicourse', $userId);
            $ebookStudents = $this->getStudentsByType('ebook', $userId);

            $allStudents = collect()
                ->concat($masterclassStudents)
                ->concat($minicourseStudents)
                ->concat($ebookStudents)
                ->sortBy('name')
                ->values();

            return response()->json([
                'success' => true,
                'data' => $allStudents,
                'message' => 'Lista de estudiantes obtenida',
                'total' => $allStudents->count()
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener estudiantes', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estudiantes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene todos los participantes de un usuario
     */
    public function getAllParticipantsByUser($userId, $isParticipant = null)
    {
        try {
            $condition = $isParticipant !== null ? "AND mu.isParticipant = {$isParticipant}" : "";

            Log::info("Buscando participantes", [
                'user_id' => $userId,
                'condition' => $condition
            ]);

            // Masterclass
            Log::info("Ejecutando consulta MASTERCLASS", ['user_id' => $userId]);
            $masterclassStudents = $this->getAllStudentsByType('masterclass', $userId, $condition);
            Log::info("Masterclass result count", ['count' => count($masterclassStudents)]);

            // Minicourse
            Log::info("Ejecutando consulta MINICOURSE", ['user_id' => $userId]);
            $minicourseStudents = $this->getAllStudentsByType('minicourse', $userId, $condition);
            Log::info("Minicourse result count", ['count' => count($minicourseStudents)]);

            // Ebook
            Log::info("Ejecutando consulta EBOOK", ['user_id' => $userId]);
            $ebookStudents = $this->getAllStudentsByType('ebook', $userId, $condition);
            Log::info("Ebook result count", ['count' => count($ebookStudents)]);

            // Merge all
            $allStudents = collect()
                ->concat($masterclassStudents)
                ->concat($minicourseStudents)
                ->concat($ebookStudents)
                ->sortByDesc('date')
                ->values();

            Log::info("Conteo final de participantes", [
                'total' => $allStudents->count(),
                'masterclass' => count($masterclassStudents),
                'minicourse' => count($minicourseStudents),
                'ebook' => count($ebookStudents),
            ]);

            $statusMessage = match($isParticipant) {
                0 => 'participantes pendientes',
                1 => 'participantes confirmados',
                2 => 'participantes con estado 2',
                default => 'todos los participantes'
            };

            return response()->json([
                'success' => true,
                'data' => $allStudents,
                'summary' => [
                    'masterclass' => count($masterclassStudents),
                    'minicourse' => count($minicourseStudents),
                    'ebook' => count($ebookStudents),
                    'total' => $allStudents->count()
                ],
                'filter' => [
                    'user_id' => $userId,
                    'isParticipant' => $isParticipant,
                    'status_description' => $statusMessage
                ],
                'message' => "Lista de {$statusMessage} obtenida"
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener participantes', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener participantes',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Valida un distribuidor
     */
    public function validateDistributor($userId, $distributorName)
    {
        try {
            $user = User::find($userId);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            $isValid = strtolower(trim($user->name)) === strtolower(trim($distributorName));

            return response()->json(['success' => $isValid]);
        } catch (\Exception $e) {
            Log::error('Error al validar distribuidor', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al validar'
            ], 500);
        }
    }

    // ============= MÉTODOS PRIVADOS =============

    private function getMiniCoursesForUser($userId)
    {
        return MiniCourse::with('images', 'modules', 'distributors')
            ->where('mini_courses.user_id', $userId)
            ->leftJoin('categories', 'mini_courses.category_id', '=', 'categories.id')
            ->select(
                'mini_courses.id',
                'mini_courses.title as nombre',
                'mini_courses.created_at as fecha',
                'mini_courses.status as estado',
                DB::raw('COALESCE(categories.name, "Sin categoría") as category_name'),
                DB::raw("'Mini Curso' as tipo")
            )
            ->get()
            ->each(fn($item) => $item->distribuidores = $item->distributors->count());
    }

    private function getEbooksForUser($userId)
    {
        return Ebook::with('images', 'documents', 'chapters', 'distributors')
            ->where('ebooks.user_id', $userId)
            ->leftJoin('categories', 'ebooks.category_id', '=', 'categories.id')
            ->select(
                'ebooks.id',
                'ebooks.title as nombre',
                'ebooks.created_at as fecha',
                'ebooks.status as estado',
                DB::raw('COALESCE(categories.name, "Sin categoría") as category_name'),
                DB::raw("'E-book' as tipo")
            )
            ->get()
            ->each(fn($item) => $item->distribuidores = $item->distributors->count());
    }

    private function getMasterclassesForUser($userId)
    {
        return Masterclass::with('images', 'documents', 'distributors')
            ->where('masterclasses.user_id', $userId)
            ->leftJoin('categories', 'masterclasses.id_categories', '=', 'categories.id')
            ->select(
                'masterclasses.id',
                'masterclasses.title as nombre',
                'masterclasses.created_at as fecha',
                'masterclasses.status as estado',
                DB::raw('COALESCE(categories.name, "Sin categoría") as category_name'),
                DB::raw("'Masterclass' as tipo")
            )
            ->get()
            ->each(fn($item) => $item->distribuidores = $item->distributors->count());
    }

    private function getDinamicasForUser($userId)
    {
        return Dinamica::with('premios')
            ->where('dinamicas.user_id', $userId)
            ->leftJoin('categories', 'dinamicas.category_id', '=', 'categories.id')
            ->select(
                'dinamicas.id',
                'dinamicas.nombre',
                'dinamicas.created_at as fecha',
                DB::raw('0 as estado'),
                DB::raw('COALESCE(categories.name, "Sin categoría") as category_name'),
                DB::raw("'Dinámica' as tipo")
            )
            ->get()
            ->each(fn($item) => $item->distribuidores = 0);
    }

    private function getMiniCoursesWithStatus($status)
    {
        return MiniCourse::with(['images', 'modules', 'distributors'])
            ->where('mini_courses.status', $status)
            ->leftJoin('categories', 'mini_courses.category_id', '=', 'categories.id')
            ->leftJoin('users', 'mini_courses.user_id', '=', 'users.id')
            ->select(
                'mini_courses.id',
                'mini_courses.title as nombre',
                'mini_courses.description as descripcion',
                'mini_courses.created_at as fecha',
                'mini_courses.status as estado',
                'mini_courses.user_id',
                'users.name as productor',
                DB::raw('COALESCE(categories.name, "Sin categoría") as category_name'),
                DB::raw("'Mini Curso' as tipo")
            )
            ->get()
            ->each(function ($item) {
                $item->distribuidores = $item->distributors->count();
                $item->modulos = $item->modules->count();
                $item->imagen = $item->images->isNotEmpty() 
                    ? asset($item->images->first()->image) 
                    : null;
            });
    }

    private function getEbooksWithStatus($status)
    {
        return Ebook::with(['images', 'documents', 'chapters', 'distributors'])
            ->where('ebooks.status', $status)
            ->leftJoin('categories', 'ebooks.category_id', '=', 'categories.id')
            ->leftJoin('users', 'ebooks.user_id', '=', 'users.id')
            ->select(
                'ebooks.id',
                'ebooks.title as nombre',
                'ebooks.description as descripcion',
                'ebooks.created_at as fecha',
                'ebooks.status as estado',
                'ebooks.user_id',
                'users.name as productor',
                DB::raw('COALESCE(categories.name, "Sin categoría") as category_name'),
                DB::raw("'E-book' as tipo")
            )
            ->get()
            ->each(function ($item) {
                $item->distribuidores = $item->distributors->count();
                $item->capitulos = $item->chapters->count();
                $item->imagen = $item->images->isNotEmpty() 
                    ? asset($item->images->first()->image) 
                    : null;
            });
    }

    private function getMasterclassesWithStatus($status)
    {
        return Masterclass::with(['images', 'documents', 'distributors'])
            ->where('masterclasses.status', $status)
            ->leftJoin('categories', 'masterclasses.id_categories', '=', 'categories.id')
            ->leftJoin('users', 'masterclasses.user_id', '=', 'users.id')
            ->select(
                'masterclasses.id',
                'masterclasses.title as nombre',
                'masterclasses.description as descripcion',
                'masterclasses.date as fecha_evento',
                'masterclasses.hour as hora_evento',
                'masterclasses.created_at as fecha',
                'masterclasses.status as estado',
                'masterclasses.user_id',
                'users.name as productor',
                DB::raw('COALESCE(categories.name, "Sin categoría") as category_name'),
                DB::raw("'Masterclass' as tipo")
            )
            ->get()
            ->each(function ($item) {
                $item->distribuidores = $item->distributors->count();
                if ($item->fecha_evento && $item->hora_evento) {
                    $item->fecha_hora_evento = Carbon::parse("{$item->fecha_evento} {$item->hora_evento}")
                        ->format('d/m/Y H:i');
                }
                $item->imagen = $item->images->isNotEmpty() 
                    ? asset($item->images->first()->image) 
                    : null;
            });
    }

    private function getPaginatedContent($model, $table, $pageNumber, $pageSize, $options = [])
    {
        try {
            $pageNumber = max((int) $pageNumber, 1);
            $pageSize = max((int) $pageSize, 1);

            $relations = $options['relations'] ?? ['images'];
            $categoryField = $options['categoryField'] ?? 'id_categories';

            $items = $model::with($relations)
                ->join('categories', "{$table}.{$categoryField}", '=', 'categories.id')
                ->select("{$table}.*", 'categories.name as category_name')
                ->skip(($pageNumber - 1) * $pageSize)
                ->take($pageSize)
                ->get();

            $total = $model::count();

            $this->formatImages($items, 'images');

            return response()->json([
                'success' => true,
                'data' => $items,
                'pagination' => [
                    'pageNumber' => $pageNumber,
                    'pageSize' => $pageSize,
                    'total' => $total,
                    'totalPages' => ceil($total / $pageSize),
                ],
                'message' => 'Listado paginado'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al paginar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function formatImages($items, $relation)
    {
        if ($items instanceof \Illuminate\Support\Collection) {
            $items->each(function ($item) use ($relation) {
                $images = $relation ? $item->$relation : $item;
                if ($images) {
                    $images->each(fn($img) => $img->image = asset($img->image));
                }
            });
        } elseif ($items) {
            $items->each(fn($img) => $img->image = asset($img->image));
        }
    }

    private function formatDocuments($documents)
    {
        $documents?->each(fn($doc) => $doc->document = asset($doc->document));
    }

    private function getMasterclassStudents($id, $isParticipant = null, $distributorId = null)
    {
        $query = DB::table('masterclass_user as mu')
            ->join('masterclass_distributor as md', 'mu.masterclass_distributor_id', '=', 'md.id')
            ->join('masterclasses as m', 'md.masterclass_id', '=', 'm.id')
            ->where('m.id', $id);

        if ($isParticipant !== null) {
            $query->where('mu.isParticipant', $isParticipant);
        }

        if ($distributorId) {
            $query->where('md.user_id', $distributorId);
        }

        return $query->select(
            'mu.id', 'mu.name', 'mu.lastname', 'mu.email', 'mu.phone',
            'mu.created_at as date', 'mu.age as birthdate', 'mu.isParticipant as participant',
            'md.masterclass_id as content_id', DB::raw('"masterclass" as content_type')
        )->get();
    }

    private function getMiniCourseStudents($id, $isParticipant = null, $distributorId = null)
    {
        $query = DB::table('mini_course_users as mcu')
            ->join('mini_course_distributors as mcd', 'mcu.mini_course_distributors_id', '=', 'mcd.id')
            ->join('mini_courses as mc', 'mcd.mini_course_id', '=', 'mc.id')
            ->where('mc.id', $id);

        if ($isParticipant !== null) {
            $query->where('mcu.isParticipant', $isParticipant);
        }

        if ($distributorId) {
            $query->where('mcd.user_id', $distributorId);
        }

        return $query->select(
            'mcu.id', 'mcu.name', 'mcu.lastname', 'mcu.email', 'mcu.phone',
            'mcu.created_at as date', 'mcu.age as birthdate', 'mcu.isParticipant as participant',
            'mcd.mini_course_id as content_id', DB::raw('"minicourse" as content_type')
        )->get();
    }

    private function getEbookStudents($id, $isParticipant = null, $distributorId = null)
    {
        $query = DB::table('ebook_users as eu')
            ->join('ebook_distributor as ed', 'eu.ebook_distributor_id', '=', 'ed.id')
            ->join('ebooks as e', 'ed.ebook_id', '=', 'e.id')
            ->where('e.id', $id);

        if ($isParticipant !== null) {
            $query->where('eu.isParticipant', $isParticipant);
        }

        if ($distributorId) {
            $query->where('ed.user_id', $distributorId);
        }

        return $query->select(
            'eu.id', 'eu.name', 'eu.lastname', 'eu.email', 'eu.phone',
            'eu.created_at as date', 'eu.age as birthdate', 'eu.isParticipant as participant',
            'ed.ebook_id as content_id', DB::raw('"ebook" as content_type')
        )->get();
    }

    private function getStudentsByType($type, $userId)
    {
        $config = [
            'masterclass' => [
                'table' => 'masterclass_user',
                'distributor' => 'masterclass_distributor',
                'content' => 'masterclasses',
                'content_id' => 'masterclass_id',
                'label' => 'masterclass'
            ],
            'minicourse' => [
                'table' => 'mini_course_users',
                'distributor' => 'mini_course_distributorss',
                'content' => 'mini_courses',
                'content_id' => 'mini_course_id',
                'label' => 'minicurso'
            ],
            'ebook' => [
                'table' => 'ebook_users',
                'distributor' => 'ebook_distributor',
                'content' => 'ebooks',
                'content_id' => 'ebook_id',
                'label' => 'ebook'
            ]
        ][$type];

        return DB::table("{$config['table']} as u")
            ->join("{$config['distributor']} as d", 'u.' . substr($config['distributor'], 0, -1) . '_id', '=', 'd.id')
            ->join("{$config['content']} as c", "d.{$config['content_id']}", '=', 'c.id')
            ->where('d.user_id', $userId)
            ->select(
                'u.id', 'u.name', 'u.lastname', 'u.phone',
                DB::raw("'{$config['label']}' as contenttype"),
                'c.title', 'u.isParticipant'
            )
            ->get();
    }

    private function getAllStudentsByType($type, $userId, $condition)
    {
        $config = [
            'masterclass' => [
                'table' => 'masterclass_user',
                'distributor' => 'masterclass_distributor',
                'content' => 'masterclasses',
                'content_id' => 'masterclass_id',
                'category_field' => 'id_categories'
            ],
            'minicourse' => [
                'table' => 'mini_course_users',
                'distributor' => 'mini_course_distributors',
                'content' => 'mini_courses',
                'content_id' => 'mini_course_id',
                'category_field' => 'category_id'
            ],
            'ebook' => [
                'table' => 'ebook_users',
                'distributor' => 'ebook_distributor',
                'content' => 'ebooks',
                'content_id' => 'ebook_id',
                'category_field' => 'category_id'
            ]
        ][$type];

        return DB::select("
            SELECT 
                mu.id,
                mu.name,
                mu.lastname,
                mu.email,
                mu.phone,
                mu.created_at as date,
                mu.age as birthdate,
                mu.isParticipant,
                mu.observation,
                c.id as content_id,
                c.title as content_title,
                '{$type}' as content_type,
                cat.name as category_name
            FROM {$config['table']} mu
            JOIN {$config['distributor']} md ON mu.{$config['distributor']}_id = md.id
            JOIN {$config['content']} c ON md.{$config['content_id']} = c.id
            LEFT JOIN categories cat ON c.{$config['category_field']} = cat.id
            WHERE md.user_id = ? {$condition}
        ", [$userId]);
    }

    /**
     * Verifica si una herramienta pertenece al usuario (como productor o distribuidor)
     */
    public function verifyToolOwnership($userId, $type, $contentId)
    {
        $config = match($type) {
            'masterclass' => [
                'content_table' => 'masterclasses',
            ],
            'minicourse' => [
                'content_table' => 'mini_courses',
            ],
            'ebook' => [
                'content_table' => 'ebooks',
            ],
            default => throw new \Exception('Tipo de contenido no válido')
        };
    
        $query = "
            SELECT 
                c.id,
                c.title,
                c.user_id,
                CASE 
                    WHEN c.user_id = ? THEN 1
                    ELSE 0
                END AS has_access
            FROM {$config['content_table']} c
            WHERE c.id = ?
            LIMIT 1
        ";
    
        $result = DB::selectOne($query, [$userId, $contentId]);
    
        if (!$result) {
            return [
                'success' => false,
                'has_access' => false,
                'message' => 'Contenido no encontrado'
            ];
        }
    
        $hasAccess = (bool) $result->has_access;
    
        return [
            'success' => true,
            'has_access' => $hasAccess,
            'is_owner' => $hasAccess,
            'content_id' => $result->id,
            'content_title' => $result->title,
            'message' => $hasAccess ? 'Tienes acceso a este contenido' : 'No eres el propietario de este contenido'
        ];
    }

}