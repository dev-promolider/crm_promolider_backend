<?php

namespace Promolider\Infrastructure\Marketing\Out\Persistence;

use Promolider\Domain\Marketing\Ports\Out\ReportRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentReportRepository implements ReportRepositoryInterface
{
    /**
     * Configuracion de tablas por tipo de contenido.
     * Los nombres de tablas y columnas reflejan el schema real (verificado contra los modelos).
     */
    private function getContentConfig(string $type): ?array
    {
        return match ($type) {
            'masterclass' => [
                'table'            => 'masterclasses',
                'content_singular' => 'masterclass',
                'distributor_table' => 'masterclass_distributor',
                'user_table'       => 'masterclass_user',
                'image_table'      => 'masterclass_images',
                'category_field'   => 'id_categories',
                'date_field'       => 'date',
                'label'            => 'Masterclass',
                'content_fk'       => 'masterclass_id',          // FK en distributor → content.id
                'distributor_fk'   => 'masterclass_distributor_id',
            ],
            'minicourse' => [
                'table'            => 'mini_courses',
                'content_singular' => 'minicourse',
                'distributor_table' => 'mini_course_distributors',
                'user_table'       => 'mini_course_users',
                'image_table'      => 'mini_course_images',
                'category_field'   => 'category_id',
                'date_field'       => 'created_at',
                'label'            => 'Mini Curso',
                'content_fk'       => 'mini_course_id',          // FK en distributor → content.id
                'distributor_fk'   => 'mini_course_distributors_id', // FK en user_table → distributor.id
            ],
            'ebook' => [
                'table'            => 'ebooks',
                'content_singular' => 'ebook',
                'distributor_table' => 'ebook_distributor',
                'user_table'       => 'ebook_users',
                'image_table'      => 'ebook_images',
                'category_field'   => 'category_id',
                'date_field'       => 'created_at',
                'label'            => 'Ebook',
                'content_fk'       => 'ebook_id',                // FK en distributor → content.id
                'distributor_fk'   => 'ebook_distributor_id',    // FK en user_table → distributor.id
            ],
            default => null,
        };
    }

    // ─────────────────────────────────────────────────────────────
    //  MÉTODOS PÚBLICOS (Interface)
    // ─────────────────────────────────────────────────────────────

    public function getContentReport(string $type, string $view, ?int $userId = null): array
    {
        $config = $this->getContentConfig($type);
        if (!$config) return [];

        return match ($view) {
            'admin-m', 'admin'  => $this->buildAdminReport($config),
            'admin-d'           => $this->getDistributorReportByAdmin($type),
            'producer-m'        => $userId ? $this->buildProducerReport($config, $userId) : [],
            'producer-d'        => $userId ? $this->getProducerDistributorReport($type, $userId) : [],
            'distributor'       => $userId ? $this->buildDistributorReport($config, $userId) : [],
            default             => [],
        };
    }

    public function getMasterclassReportByAdmin(): array
    {
        return $this->buildAdminReport($this->getContentConfig('masterclass'));
    }

    public function getMiniCourseReportByAdmin(): array
    {
        return $this->buildAdminReport($this->getContentConfig('minicourse'));
    }

    public function getEbookReportByAdmin(): array
    {
        return $this->buildAdminReport($this->getContentConfig('ebook'));
    }

    public function getProducerReport(string $type, int $producerId): array
    {
        $config = $this->getContentConfig($type);
        return $config ? $this->buildProducerReport($config, $producerId) : [];
    }

    public function getDistributorReport(string $type, int $distributorId): array
    {
        $config = $this->getContentConfig($type);
        return $config ? $this->buildDistributorReport($config, $distributorId) : [];
    }

    public function getDistributors(string $type, int $contentId): array
    {
        $config = $this->getContentConfig($type);
        if (!$config) return [];

        $pivotTable = $config['distributor_table'];
        $contentFk  = $config['content_fk'];

        return DB::table($pivotTable)
            ->join('users', $pivotTable . '.user_id', '=', 'users.id')
            ->where($pivotTable . '.' . $contentFk, $contentId)
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.username',
                $pivotTable . '.*'
            )
            ->get()
            ->toArray();
    }

    public function getStudents(string $type, int $contentId): array
    {
        $config = $this->getContentConfig($type);
        if (!$config) return [];

        $userTable      = $config['user_table'];
        $distributorTable = $config['distributor_table'];
        $contentFk      = $config['content_fk'];

        // Replica el JOIN del monolito: user_table → distributor_table → users
        return DB::table($userTable)
            ->join($distributorTable, $userTable . '.' . $config['distributor_fk'], '=', $distributorTable . '.id')
            ->join('users', $distributorTable . '.user_id', '=', 'users.id')
            ->where($distributorTable . '.' . $contentFk, $contentId)
            ->select(
                $userTable . '.*',
                'users.id as user_id',
                'users.name as user_name',
                'users.email as user_email',
                $distributorTable . '.user_id as distributor_id'
            )
            ->orderBy($userTable . '.created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function getPendingParticipants(string $type, int $contentId): array
    {
        $config = $this->getContentConfig($type);
        if (!$config) return [];

        $userTable      = $config['user_table'];
        $distributorTable = $config['distributor_table'];
        $contentFk      = $config['content_fk'];

        return DB::table($userTable)
            ->join($distributorTable, $userTable . '.' . $config['distributor_fk'], '=', $distributorTable . '.id')
            ->join('users', $distributorTable . '.user_id', '=', 'users.id')
            ->where($distributorTable . '.' . $contentFk, $contentId)
            ->where(function ($q) {
                $q->where('isParticipant', 0)->orWhereNull('isParticipant');
            })
            ->select(
                $userTable . '.*',
                'users.id as user_id',
                'users.name as user_name',
                'users.email as user_email',
                $distributorTable . '.user_id as distributor_id'
            )
            ->orderBy($userTable . '.created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function getPrivateContentReport(): array
    {
        // Union query — replica buildPrivateContentQuery del monolito
        $masterclass = $this->buildPrivateContentQuery($this->getContentConfig('masterclass'));
        $minicourse  = $this->buildPrivateContentQuery($this->getContentConfig('minicourse'));
        $ebook       = $this->buildPrivateContentQuery($this->getContentConfig('ebook'));

        $sql = "SELECT * FROM ({$masterclass}) AS masterclass
                UNION ALL
                SELECT * FROM ({$minicourse}) AS minicourse
                UNION ALL
                SELECT * FROM ({$ebook}) AS ebook
                ORDER BY fecha DESC";

        return DB::select($sql);
    }

    public function getPrivateContentStudents(string $contentType, int $contentId): array
    {
        $config = $this->getContentConfig($contentType);
        if (!$config) return [];

        $query = $this->buildPrivateStudentsQuery($config);
        return DB::select($query, [$contentId]);
    }

    public function getContentByStatus(): array
    {
        $sql = $this->buildContentByStatusQuery();
        return DB::select($sql);
    }

    public function getContentByProducer(): array
    {
        // Metodo NUEVO (no existia en el monolito): agrupa contenido por productor
        $masterclasses = DB::table('masterclasses')
            ->join('users', 'masterclasses.user_id', '=', 'users.id')
            ->select(
                'users.id as user_id',
                'users.name as producer_name',
                'users.email as producer_email',
                DB::raw("COUNT(masterclasses.id) as total"),
                DB::raw("'masterclass' as type")
            );

        $ebooks = DB::table('ebooks')
            ->join('users', 'ebooks.user_id', '=', 'users.id')
            ->select(
                'users.id as user_id',
                'users.name as producer_name',
                'users.email as producer_email',
                DB::raw("COUNT(ebooks.id) as total"),
                DB::raw("'ebook' as type")
            );

        $miniCourses = DB::table('mini_courses')
            ->join('users', 'mini_courses.user_id', '=', 'users.id')
            ->select(
                'users.id as user_id',
                'users.name as producer_name',
                'users.email as producer_email',
                DB::raw("COUNT(mini_courses.id) as total"),
                DB::raw("'minicourse' as type")
            );

        return $masterclasses->unionAll($ebooks)->unionAll($miniCourses)
            ->groupBy('user_id', 'producer_name', 'producer_email', 'type')
            ->orderBy('producer_name')
            ->get()
            ->toArray();
    }

    public function getAllStudentsList(int $userId): array
    {            $masterclass = DB::table('masterclass_user as u')
                ->join('masterclass_distributor as d', 'u.masterclass_distributor_id', '=', 'd.id')
            ->join('masterclasses as c', 'd.masterclass_id', '=', 'c.id')
            ->where('d.user_id', $userId)
            ->select('u.id', 'u.name', 'u.lastname', 'u.phone',
                DB::raw("'masterclass' as contenttype"),
                'c.title', 'u.isParticipant');

        $minicourse = DB::table('mini_course_users as u')
            ->join('mini_course_distributors as d', 'u.mini_course_distributors_id', '=', 'd.id')
            ->join('mini_courses as c', 'd.mini_course_id', '=', 'c.id')
            ->where('d.user_id', $userId)
            ->select('u.id', 'u.name', 'u.lastname', 'u.phone',
                DB::raw("'minicurso' as contenttype"),
                'c.title', 'u.isParticipant');

        $ebook = DB::table('ebook_users as u')
            ->join('ebook_distributor as d', 'u.ebook_distributor_id', '=', 'd.id')
            ->join('ebooks as c', 'd.ebook_id', '=', 'c.id')
            ->where('d.user_id', $userId)
            ->select('u.id', 'u.name', 'u.lastname', 'u.phone',
                DB::raw("'ebook' as contenttype"),
                'c.title', 'u.isParticipant');

        return $masterclass
            ->union($minicourse)
            ->union($ebook)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function getAllParticipantsByUser(int $userId, ?int $isParticipant = null): array
    {
        $condition = $isParticipant !== null ? "AND mu.isParticipant = {$isParticipant}" : '';

        $sql = "
            SELECT mu.id, mu.name, mu.lastname, mu.phone, mu.email,
                   mu.isParticipant, mu.created_at as date,
                   c.title as course_title, 'masterclass' as type
            FROM masterclass_user mu
            JOIN masterclass_distributor md ON mu.masterclass_distributor_id = md.id
            JOIN masterclasses c ON md.masterclass_id = c.id
            WHERE md.user_id = ? {$condition}

            UNION ALL

            SELECT mu.id, mu.name, mu.lastname, mu.phone, mu.email,
                   mu.isParticipant, mu.created_at as date,
                   c.title as course_title, 'minicourse' as type
            FROM mini_course_users mu
            JOIN mini_course_distributors md ON mu.mini_course_distributors_id = md.id
            JOIN mini_courses c ON md.mini_course_id = c.id
            WHERE md.user_id = ? {$condition}

            UNION ALL

            SELECT mu.id, mu.name, mu.lastname, mu.phone, mu.email,
                   mu.isParticipant, mu.created_at as date,
                   c.title as course_title, 'ebook' as type
            FROM ebook_users mu
            JOIN ebook_distributor md ON mu.ebook_distributor_id = md.id
            JOIN ebooks c ON md.ebook_id = c.id
            WHERE md.user_id = ? {$condition}

            ORDER BY date DESC
        ";

        return DB::select($sql, [$userId, $userId, $userId]);
    }

    public function getLastSells(int $userId, int $limit = 5): array
    {
        return \App\Models\PurchasedCourse::join('users', 'purchased_courses.user_id', '=', 'users.id')
            ->join('courses', 'purchased_courses.course_id', '=', 'courses.id')
            ->where('purchased_courses.user_id', $userId)
            ->select('users.id', 'users.photo', 'courses.title', 'courses.price')
            ->take($limit)
            ->get()
            ->toArray();
    }

    public function getGeneralReports(): array
    {
        $totalMasterclasses = \App\Models\Masterclass::count();
        $totalEbooks        = \App\Models\Ebook::count();
        $totalMiniCourses   = \App\Models\Minicourse::count();
        $totalUsers         = \App\Models\User::count();

        // Contenido activo (status=2, equivalente a privado/publicado)
        $activeCampaigns = \App\Models\Masterclass::where('status', 2)->count()
            + \App\Models\Ebook::where('status', 2)->count()
            + \App\Models\Minicourse::where('status', 2)->count();

        return [
            'total_masterclasses' => $totalMasterclasses,
            'total_ebooks'        => $totalEbooks,
            'total_mini_courses'  => $totalMiniCourses,
            'total_content'       => $totalMasterclasses + $totalEbooks + $totalMiniCourses,
            'total_users'         => $totalUsers,
            'active_campaigns'    => $activeCampaigns,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  MÉTODOS PRIVADOS — CONSTRUCCIÓN DE QUERIES (replican monolito)
    // ─────────────────────────────────────────────────────────────

    /**
     * Reporte admin: replica buildAdminReport del monolito.
     * JOIN a 6 tablas + subquery de imagenes + COUNT + GROUP BY.
     */
    private function buildAdminReport(array $config): array
    {
        $s = $config['content_singular'];
        $t = $config['table'];
        $dt = $config['distributor_table'];
        $ut = $config['user_table'];
        $it = $config['image_table'];
        $cf = $config['content_fk'];
        $df = $config['distributor_fk'];
        $cat = $config['category_field'];
        $date = $config['date_field'];

        $sql = "
            SELECT
                c.id AS {$s}_id,
                c.title AS {$s}_nombre,
                cat.name AS categoria_nombre,
                c.{$date} AS fecha,
                p.name AS productor_nombre,
                u.name AS distribuidor_nombre,
                u.email AS distribuidor_email,
                u.phone AS distribuidor_phone,
                COUNT(mu.id) AS usuarios_registrados,
                img.image AS imagen
            FROM {$t} c
            JOIN categories cat ON c.{$cat} = cat.id
            JOIN users p ON c.user_id = p.id
            JOIN {$dt} md ON c.id = md.{$cf}
            JOIN users u ON md.user_id = u.id
            LEFT JOIN {$ut} mu ON md.id = mu.{$df}
            LEFT JOIN (
                SELECT img.{$s}_id, img.image
                FROM {$it} img
                WHERE img.id = (
                    SELECT MIN(img2.id)
                    FROM {$it} img2
                    WHERE img2.{$s}_id = img.{$s}_id
                )
            ) img ON c.id = img.{$s}_id
            GROUP BY
                c.id, c.title, cat.name, c.{$date},
                p.name, u.name, u.email, u.phone, img.image
            ORDER BY c.{$date} DESC
        ";

        return DB::select($sql);
    }

    /**
     * Reporte distribuidores (vista admin): replica getDistributorReportByAdmin del monolito.
     */
    private function getDistributorReportByAdmin(string $type): array
    {
        $config = $this->getContentConfig($type);
        if (!$config) return [];

        $s  = $config['content_singular'];
        $t  = $config['table'];
        $dt = $config['distributor_table'];
        $ut = $config['user_table'];
        $cf = $config['content_fk'];
        $df = $config['distributor_fk'];
        $cat = $config['category_field'];

        $sql = "
            SELECT
                u.id AS distribuidor_id,
                u.name AS distribuidor_nombre,
                cat.name AS categoria_nombre,
                c.title AS {$s}_nombre,
                p.name AS productor_nombre,
                COUNT(mu.id) AS usuarios_registrados
            FROM {$dt} md
            JOIN users u ON md.user_id = u.id
            JOIN {$t} c ON md.{$cf} = c.id
            JOIN categories cat ON c.{$cat} = cat.id
            JOIN users p ON c.user_id = p.id
            LEFT JOIN {$ut} mu ON md.id = mu.{$df}
            GROUP BY u.id, u.name, cat.name, c.title, p.name
        ";

        return DB::select($sql);
    }

    /**
     * Reporte productor: replica buildProducerReport del monolito.
     * Incluye contenido donde el usuario es productor O distribuidor.
     * Incluye estado, status_code y rol_usuario.
     */
    private function buildProducerReport(array $config, int $producerId): array
    {
        $s  = $config['content_singular'];
        $t  = $config['table'];
        $dt = $config['distributor_table'];
        $ut = $config['user_table'];
        $cf = $config['content_fk'];
        $df = $config['distributor_fk'];
        $cat = $config['category_field'];
        $date = $config['date_field'];

        $sql = "
            SELECT
                c.id AS {$s}_id,
                c.title AS {$s}_nombre,
                cat.name AS categoria_nombre,
                c.{$date} AS fecha,
                p.name AS productor_nombre,
                u.name AS distribuidor_nombre,
                u.email AS distribuidor_email,
                u.phone AS distribuidor_phone,
                COUNT(mu.id) AS usuarios_registrados,
                CASE
                    WHEN c.status = 1 THEN 'Público'
                    WHEN c.status = 2 THEN 'Privado'
                    ELSE 'No Publicado'
                END AS estado,
                c.status AS status_code,
                CASE
                    WHEN c.user_id = ? THEN 'Productor'
                    ELSE 'Distribuidor'
                END AS rol_usuario
            FROM {$t} c
            JOIN categories cat ON c.{$cat} = cat.id
            JOIN users p ON c.user_id = p.id
            JOIN {$dt} md ON c.id = md.{$cf}
            JOIN users u ON md.user_id = u.id
            LEFT JOIN {$ut} mu ON md.id = mu.{$df}
            WHERE c.user_id = ? OR md.user_id = ?
            GROUP BY
                c.id, c.title, cat.name, c.{$date},
                p.name, u.name, u.email, u.phone,
                c.status, c.user_id
            ORDER BY c.{$date} DESC
        ";

        return DB::select($sql, [$producerId, $producerId, $producerId]);
    }

    /**
     * Reporte distribuidores por productor: replica getProducerDistributorReport del monolito.
     */
    private function getProducerDistributorReport(string $type, int $producerId): array
    {
        $config = $this->getContentConfig($type);
        if (!$config) return [];

        $s  = $config['content_singular'];
        $t  = $config['table'];
        $dt = $config['distributor_table'];
        $ut = $config['user_table'];
        $cf = $config['content_fk'];
        $df = $config['distributor_fk'];
        $cat = $config['category_field'];

        $sql = "
            SELECT
                u.id AS distribuidor_id,
                u.name AS distribuidor_nombre,
                cat.name AS categoria_nombre,
                c.title AS {$s}_nombre,
                p.name AS productor_nombre,
                COUNT(mu.id) AS usuarios_registrados
            FROM {$dt} md
            JOIN users u ON md.user_id = u.id
            JOIN {$t} c ON md.{$cf} = c.id
            JOIN categories cat ON c.{$cat} = cat.id
            JOIN users p ON c.user_id = p.id
            LEFT JOIN {$ut} mu ON md.id = mu.{$df}
            WHERE c.user_id = ?
            GROUP BY u.id, u.name, cat.name, c.title, p.name
        ";

        return DB::select($sql, [$producerId]);
    }

    /**
     * Reporte distribuidor: replica buildDistributorReport del monolito.
     */
    private function buildDistributorReport(array $config, int $distributorId): array
    {
        $s  = $config['content_singular'];
        $t  = $config['table'];
        $dt = $config['distributor_table'];
        $ut = $config['user_table'];
        $cf = $config['content_fk'];
        $df = $config['distributor_fk'];
        $cat = $config['category_field'];

        $sql = "
            SELECT
                c.id AS {$s}_id,
                u.id AS distribuidor_id,
                u.name AS distribuidor_nombre,
                cat.name AS categoria_nombre,
                c.title AS {$s}_nombre,
                p.name AS productor_nombre,
                COUNT(mu.id) AS usuarios_registrados
            FROM {$dt} md
            JOIN users u ON md.user_id = u.id
            JOIN {$t} c ON md.{$cf} = c.id
            JOIN categories cat ON c.{$cat} = cat.id
            JOIN users p ON c.user_id = p.id
            LEFT JOIN {$ut} mu ON md.id = mu.{$df}
            WHERE md.user_id = ?
            GROUP BY c.id, u.id, u.name, cat.name, c.title, p.name
        ";

        return DB::select($sql, [$distributorId]);
    }

    /**
     * Query para contenido privado: replica buildPrivateContentQuery del monolito.
     * Filtra por status = 2 (Privado).
     */
    private function buildPrivateContentQuery(array $config): string
    {
        $s  = $config['content_singular'];
        $t  = $config['table'];
        $dt = $config['distributor_table'];
        $ut = $config['user_table'];
        $cf = $config['content_fk'];
        $df = $config['distributor_fk'];
        $cat = $config['category_field'];
        $date = $config['date_field'];

        return "
            SELECT
                c.id AS {$s}_id,
                c.title AS nombre,
                '{$config['label']}' AS tipo_contenido,
                'Privado' AS estado,
                2 AS status_code,
                u.name AS productor_nombre,
                u.email AS productor_email,
                u.phone AS productor_telefono,
                cat.name AS categoria_nombre,
                c.{$date} AS fecha,
                COUNT(mu.id) AS total_inscritos,
                SUM(CASE WHEN mu.isParticipant = 1 THEN 1 ELSE 0 END) AS total_participantes
            FROM {$t} c
            JOIN users u ON c.user_id = u.id
            JOIN categories cat ON c.{$cat} = cat.id
            JOIN {$dt} md ON c.id = md.{$cf}
            LEFT JOIN {$ut} mu ON md.id = mu.{$df}
            WHERE c.status = 2
            GROUP BY
                c.id, c.title, u.name, u.email, u.phone,
                cat.name, c.{$date}
            ORDER BY c.{$date} DESC
        ";
    }

    /**
     * Query para estudiantes de contenido privado: replica buildPrivateStudentsQuery del monolito.
     */
    private function buildPrivateStudentsQuery(array $config): string
    {
        $s  = $config['content_singular'];
        $t  = $config['table'];
        $dt = $config['distributor_table'];
        $ut = $config['user_table'];
        $cf = $config['content_fk'];
        $df = $config['distributor_fk'];

        // Campos adicionales solo para minicourse
        $additional = ($s === 'minicourse')
            ? ', mu.access_token, mu.token_expires_at, mu.last_accessed_at'
            : '';

        return "
            SELECT
                mu.id,
                mu.name,
                mu.lastname,
                mu.email,
                mu.phone,
                mu.age,
                mu.nationality,
                mu.isParticipant{$additional},
                mu.created_at AS fecha_registro,
                md.user_id AS distributor_id,
                dist.name AS distribuidor_nombre
            FROM {$ut} mu
            JOIN {$dt} md ON mu.{$df} = md.id
            JOIN users dist ON md.user_id = dist.id
            WHERE md.{$cf} = ?
            ORDER BY mu.created_at DESC
        ";
    }

    /**
     * Query para contenido por estado: replica buildContentByStatusQuery del monolito.
     */
    private function buildContentByStatusQuery(): string
    {
        return "
            SELECT
                m.id,
                'Masterclass' AS tipo_contenido,
                m.title AS nombre,
                CASE
                    WHEN m.status = 1 THEN 'Público'
                    WHEN m.status = 2 THEN 'Privado'
                    ELSE 'No Publicado'
                END AS estado,
                m.status AS status_code,
                u.name AS productor_nombre,
                u.email AS productor_email,
                u.phone AS productor_telefono,
                c.name AS categoria_nombre,
                m.date AS fecha
            FROM masterclasses m
            JOIN users u ON m.user_id = u.id
            JOIN categories c ON m.id_categories = c.id

            UNION ALL

            SELECT
                mc.id,
                'Mini Curso' AS tipo_contenido,
                mc.title AS nombre,
                CASE
                    WHEN mc.status = 1 THEN 'Público'
                    WHEN mc.status = 2 THEN 'Privado'
                    ELSE 'No Publicado'
                END AS estado,
                mc.status AS status_code,
                u.name AS productor_nombre,
                u.email AS productor_email,
                u.phone AS productor_telefono,
                c.name AS categoria_nombre,
                mc.created_at AS fecha
            FROM mini_courses mc
            JOIN users u ON mc.user_id = u.id
            JOIN categories c ON mc.category_id = c.id

            UNION ALL

            SELECT
                e.id,
                'Ebook' AS tipo_contenido,
                e.title AS nombre,
                CASE
                    WHEN e.status = 1 THEN 'Público'
                    WHEN e.status = 2 THEN 'Privado'
                    ELSE 'No Publicado'
                END AS estado,
                e.status AS status_code,
                u.name AS productor_nombre,
                u.email AS productor_email,
                u.phone AS productor_telefono,
                c.name AS categoria_nombre,
                e.created_at AS fecha
            FROM ebooks e
            JOIN users u ON e.user_id = u.id
            JOIN categories c ON e.category_id = c.id

            ORDER BY status_code, tipo_contenido, nombre
        ";
    }
}
