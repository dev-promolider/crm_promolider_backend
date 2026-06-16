<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Configuración de tablas por tipo de contenido
     */
    private function getContentConfig($type)
    {
        return match($type) {
            'masterclass' => [
                'content_table' => 'masterclasses',
                'content_singular' => 'masterclass',
                'distributor_table' => 'masterclass_distributor',
                'user_table' => 'masterclass_user',
                'image_table' => 'masterclass_images',
                'category_field' => 'id_categories',
                'date_field' => 'date',
                'label' => 'Masterclass',
                // === INICIO DE CORRECCIÓN ===
                'content_fk' => 'masterclass_id', // Llave foránea en la tabla 'distributor'
                'distributor_fk' => 'masterclass_distributor_id' // Llave foránea en la tabla 'user'
                // === FIN DE CORRECCIÓN ===
            ],
            'minicourse' => [
                'content_table' => 'mini_courses',
                'content_singular' => 'minicourse',
                'distributor_table' => 'mini_course_distributors',
                'user_table' => 'mini_course_users',
                'image_table' => 'mini_course_images',
                'category_field' => 'category_id',
                'date_field' => 'created_at',
                'label' => 'Mini Curso',
                // === INICIO DE CORRECCIÓN ===
                'content_fk' => 'mini_course_id', // Llave foránea en la tabla 'distributor'
                'distributor_fk' => 'mini_course_distributors_id' // Llave foránea en la tabla 'user'
                // === FIN DE CORRECCIÓN ===
            ],
            'ebook' => [
                'content_table' => 'ebooks',
                'content_singular' => 'ebook',
                'distributor_table' => 'ebook_distributor',
                'user_table' => 'ebook_users',
                'image_table' => 'ebook_images',
                'category_field' => 'category_id',
                'date_field' => 'created_at',
                'label' => 'Ebook',
                // === INICIO DE CORRECCIÓN ===
                'content_fk' => 'ebook_id', // Llave foránea en la tabla 'distributor'
                'distributor_fk' => 'ebook_distributor_id' // Llave foránea en la tabla 'user'
                // === FIN DE CORRECCIÓN ===
            ],
            default => throw new \Exception('Tipo de contenido no válido')
        };
    }

    /**
     * Reporte de contenido por admin (vista Masterclass)
     */
    public function getMasterclassReportByAdmin($view = 'masterclass')
    {
        if ($view === 'distributor') {
            return $this->getDistributorReportByAdmin('masterclass');
        }

        $config = $this->getContentConfig('masterclass');
        return $this->buildAdminReport($config);
    }

    /**
     * Reporte de mini cursos por admin
     */
    public function getMiniCourseReportByAdmin($view = 'minicourse')
    {
        if ($view === 'distributor') {
            return $this->getDistributorReportByAdmin('minicourse');
        }

        $config = $this->getContentConfig('minicourse');
        return $this->buildAdminReport($config);
    }

    /**
     * Reporte de ebooks por admin
     */
    public function getEbookReportByAdmin($view = 'ebook')
    {
        if ($view === 'distributor') {
            return $this->getDistributorReportByAdmin('ebook');
        }

        $config = $this->getContentConfig('ebook');
        return $this->buildAdminReport($config);
    }

    /**
     * Reporte por productor
     */
    public function getProducerReport($type, $producerId, $view = 'content')
    {
        if ($view === 'distributor') {
            return $this->getProducerDistributorReport($type, $producerId);
        }

        $config = $this->getContentConfig($type);
        return $this->buildProducerReport($config, $producerId);
    }

    /**
     * Reporte por distribuidor
     */
    public function getDistributorReport($type, $distributorId)
    {
        $config = $this->getContentConfig($type);
        return $this->buildDistributorReport($config, $distributorId);
    }

    /**
     * Reporte de contenido privado
     */
    public function getPrivateContentReport($type)
    {
        $config = $this->getContentConfig($type);
        $report = DB::select($this->buildPrivateContentQuery($config));

        return response()->json(['data' => $report]);
    }

    /**
     * Estudiantes de contenido privado
     */
    public function getPrivateContentStudents($type, $contentId)
    {
        $config = $this->getContentConfig($type);
        $students = DB::select($this->buildPrivateStudentsQuery($config), [$contentId]);

        return response()->json(['data' => $students]);
    }

    /**
     * Todo el contenido privado consolidado
     */
    public function getAllPrivateContent()
    {
        $masterclasses = DB::select($this->buildPrivateContentQuery($this->getContentConfig('masterclass')));
        $miniCourses = DB::select($this->buildPrivateContentQuery($this->getContentConfig('minicourse')));
        $ebooks = DB::select($this->buildPrivateContentQuery($this->getContentConfig('ebook')));

        $allContent = array_merge($masterclasses, $miniCourses, $ebooks);

        return response()->json(['data' => $allContent]);
    }

    /**
     * Contenido privado por productor
     */
    public function getPrivateContentByProducer($producerId)
    {
        $report = DB::select($this->buildUnionPrivateQuery(), [$producerId, $producerId, $producerId]);

        return response()->json(['data' => $report]);
    }

    /**
     * Contenido por estado
     */
    public function getContentByStatus()
    {
        $report = DB::select($this->buildContentByStatusQuery());

        return response()->json(['data' => $report]);
    }

    // ============= MÉTODOS PRIVADOS PARA CONSTRUCCIÓN DE QUERIES =============

    /**
     * Construye reporte de admin
     */
    private function buildAdminReport($config)
    {
        $query = "
            SELECT 
                c.id AS {$config['content_singular']}_id,
                c.title AS {$config['content_singular']}_nombre, 
                cat.name AS categoria_nombre, 
                c.{$config['date_field']} AS fecha, 
                p.name AS productor_nombre, 
                u.name AS distribuidor_nombre,
                u.email AS distribuidor_email,
                u.phone AS distribuidor_phone,
                COUNT(mu.id) AS usuarios_registrados,
                img.image AS imagen
            FROM {$config['content_table']} c
            JOIN categories cat ON c.{$config['category_field']} = cat.id
            JOIN users p ON c.user_id = p.id
            -- CAMBIO AQUÍ --
            JOIN {$config['distributor_table']} md ON c.id = md.{$config['content_fk']}
            JOIN users u ON md.user_id = u.id
            -- CAMBIO AQUÍ --
            LEFT JOIN {$config['user_table']} mu ON md.id = mu.{$config['distributor_fk']}
            LEFT JOIN (
                SELECT img.{$config['content_singular']}_id, img.image
                FROM {$config['image_table']} img
                WHERE img.id = (
                    SELECT MIN(img2.id) 
                    FROM {$config['image_table']} img2 
                    WHERE img2.{$config['content_singular']}_id = img.{$config['content_singular']}_id
                )
            ) img ON c.id = img.{$config['content_singular']}_id
            GROUP BY c.id, c.title, cat.name, c.{$config['date_field']}, p.name, u.name, u.email, u.phone, img.image
            ORDER BY c.{$config['date_field']} DESC
        ";

        $report = DB::select($query);

        // Formatear imágenes
        foreach ($report as $item) {
            $item->imagen = $item->imagen ? asset($item->imagen) : null;
        }

        return response()->json(['data' => $report]);
    }

    /**
     * Reporte de distribuidores por admin
     */
    private function getDistributorReportByAdmin($type)
    {
        $config = $this->getContentConfig($type);

        $query = "
            SELECT 
                u.id AS distribuidor_id,
                u.name AS distribuidor_nombre, 
                cat.name AS categoria_nombre, 
                c.title AS {$config['content_singular']}_nombre, 
                p.name AS productor_nombre, 
                COUNT(mu.id) AS usuarios_registrados
            FROM {$config['distributor_table']} md
            JOIN users u ON md.user_id = u.id
            -- CAMBIO AQUÍ --
            JOIN {$config['content_table']} c ON md.{$config['content_fk']} = c.id
            JOIN categories cat ON c.{$config['category_field']} = cat.id
            JOIN users p ON c.user_id = p.id
            -- CAMBIO AQUÍ --
            LEFT JOIN {$config['user_table']} mu ON md.id = mu.{$config['distributor_fk']}
            GROUP BY u.id, u.name, cat.name, c.title, p.name
        ";

        $report = DB::select($query);

        return response()->json(['data' => $report]);
    }

    /**
     * Construye reporte de productor
     */
    private function buildProducerReport($config, $producerId)
    {
        $query = "
            SELECT 
                c.id AS {$config['content_singular']}_id,
                c.title AS {$config['content_singular']}_nombre, 
                cat.name AS categoria_nombre, 
                c.{$config['date_field']} AS fecha, 
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
            FROM {$config['content_table']} c
            JOIN categories cat ON c.{$config['category_field']} = cat.id
            JOIN users p ON c.user_id = p.id
            JOIN {$config['distributor_table']} md ON c.id = md.{$config['content_fk']}
            JOIN users u ON md.user_id = u.id
            LEFT JOIN {$config['user_table']} mu ON md.id = mu.{$config['distributor_fk']}
            WHERE c.user_id = ? OR md.user_id = ?
            GROUP BY 
                c.id, 
                c.title, 
                cat.name, 
                c.{$config['date_field']}, 
                p.name, 
                u.name,
                u.email,
                u.phone,
                c.status,
                c.user_id
            ORDER BY c.{$config['date_field']} DESC
        ";
    
        $report = DB::select($query, [$producerId, $producerId, $producerId]);
    
        return response()->json(['data' => $report]);
    }

    /**
     * Reporte de distribuidores por productor
     */
    private function getProducerDistributorReport($type, $producerId)
    {
        $config = $this->getContentConfig($type);

        $query = "
            SELECT 
                u.id AS distribuidor_id,
                u.name AS distribuidor_nombre, 
                cat.name AS categoria_nombre, 
                c.title AS {$config['content_singular']}_nombre, 
                p.name AS productor_nombre, 
                COUNT(mu.id) AS usuarios_registrados
            FROM {$config['distributor_table']} md
            JOIN users u ON md.user_id = u.id
            -- CAMBIO AQUÍ --
            JOIN {$config['content_table']} c ON md.{$config['content_fk']} = c.id
            JOIN categories cat ON c.{$config['category_field']} = cat.id
            JOIN users p ON c.user_id = p.id
            -- CAMBIO AQUÍ --
            LEFT JOIN {$config['user_table']} mu ON md.id = mu.{$config['distributor_fk']}
            WHERE c.user_id = ?
            GROUP BY u.id, u.name, cat.name, c.title, p.name
        ";

        $report = DB::select($query, [$producerId]);

        return response()->json(['data' => $report]);
    }

    /**
     * Construye reporte de distribuidor
     */
    private function buildDistributorReport($config, $distributorId)
    {
        $query = "
            SELECT 
                c.id AS {$config['content_singular']}_id,
                u.id AS distribuidor_id,
                u.name AS distribuidor_nombre, 
                cat.name AS categoria_nombre, 
                c.title AS {$config['content_singular']}_nombre, 
                p.name AS productor_nombre, 
                COUNT(mu.id) AS usuarios_registrados
            FROM {$config['distributor_table']} md
            JOIN users u ON md.user_id = u.id
            -- CAMBIO AQUÍ --
            JOIN {$config['content_table']} c ON md.{$config['content_fk']} = c.id
            JOIN categories cat ON c.{$config['category_field']} = cat.id
            JOIN users p ON c.user_id = p.id
            -- CAMBIO AQUÍ --
            LEFT JOIN {$config['user_table']} mu ON md.id = mu.{$config['distributor_fk']}
            WHERE md.user_id = ?
            GROUP BY c.id, u.id, u.name, cat.name, c.title, p.name
        ";

        $report = DB::select($query, [$distributorId]);

        return response()->json(['data' => $report]);
    }

    /**
     * Query para contenido privado
     */
    private function buildPrivateContentQuery($config)
    {
        return "
            SELECT 
                c.id AS {$config['content_singular']}_id,
                c.title AS nombre,
                '{$config['label']}' AS tipo_contenido,
                'Privado' AS estado,
                2 AS status_code,
                u.name AS productor_nombre,
                u.email AS productor_email,
                u.phone AS productor_telefono,
                cat.name AS categoria_nombre,
                c.{$config['date_field']} AS fecha,
                COUNT(mu.id) AS total_inscritos,
                SUM(CASE WHEN mu.isParticipant = 1 THEN 1 ELSE 0 END) AS total_participantes
            FROM {$config['content_table']} c
            JOIN users u ON c.user_id = u.id
            JOIN categories cat ON c.{$config['category_field']} = cat.id
            -- CAMBIO AQUÍ --
            JOIN {$config['distributor_table']} md ON c.id = md.{$config['content_fk']}
            -- CAMBIO AQUÍ --
            LEFT JOIN {$config['user_table']} mu ON md.id = mu.{$config['distributor_fk']}
            WHERE c.status = 2
            GROUP BY 
                c.id, 
                c.title, 
                u.name, 
                u.email, 
                u.phone, 
                cat.name, 
                c.{$config['date_field']}
            ORDER BY c.{$config['date_field']} DESC
        ";
    }

    /**
     * Query para estudiantes de contenido privado
     */
    private function buildPrivateStudentsQuery($config)
    {
        $additionalFields = $config['content_singular'] === 'minicourse' 
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
                mu.isParticipant,
                mu.observation{$additionalFields},
                mu.created_at AS fecha_registro,
                md.user_id AS distributor_id,
                dist.name AS distribuidor_nombre
            FROM {$config['user_table']} mu
            -- CAMBIO AQUÍ --
            JOIN {$config['distributor_table']} md ON mu.{$config['distributor_fk']} = md.id
            JOIN users dist ON md.user_id = dist.id
            -- CAMBIO AQUÍ --
            WHERE md.{$config['content_fk']} = ?
            ORDER BY mu.created_at DESC
        ";
    }

    /**
     * Query union para contenido privado de un productor
     */
    private function buildUnionPrivateQuery()
    {
        return "
            SELECT * FROM (
                SELECT 
                    m.id AS content_id,
                    'Masterclass' AS tipo_contenido,
                    m.title AS nombre,
                    'Privado' AS estado,
                    2 AS status_code,
                    u.name AS productor_nombre,
                    u.email AS productor_email,
                    u.phone AS productor_telefono,
                    c.name AS categoria_nombre,
                    m.date AS fecha,
                    COUNT(mu.id) AS total_inscritos,
                    SUM(CASE WHEN mu.isParticipant = 1 THEN 1 ELSE 0 END) AS total_participantes
                FROM masterclasses m
                JOIN users u ON m.user_id = u.id
                JOIN categories c ON m.id_categories = c.id
                JOIN masterclass_distributor md ON m.id = md.masterclass_id
                LEFT JOIN masterclass_user mu ON md.id = mu.masterclass_distributor_id
                WHERE m.status = 2 AND m.user_id = ?
                GROUP BY m.id, m.title, u.name, u.email, u.phone, c.name, m.date

                UNION ALL

                SELECT 
                    mc.id AS content_id,
                    'Mini Curso' AS tipo_contenido,
                    mc.title AS nombre,
                    'Privado' AS estado,
                    2 AS status_code,
                    u.name AS productor_nombre,
                    u.email AS productor_email,
                    u.phone AS productor_telefono,
                    c.name AS categoria_nombre,
                    mc.created_at AS fecha,
                    COUNT(mcu.id) AS total_inscritos,
                    SUM(CASE WHEN mcu.isParticipant = 1 THEN 1 ELSE 0 END) AS total_participantes
                FROM mini_courses mc
                JOIN users u ON mc.user_id = u.id
                JOIN categories c ON mc.category_id = c.id
                JOIN mini_course_distributors mcd ON mc.id = mcd.mini_course_id
                LEFT JOIN mini_course_users mcu ON mcd.id = mcu.mini_course_distributors_id
                WHERE mc.status = 2 AND mc.user_id = ?
                GROUP BY mc.id, mc.title, u.name, u.email, u.phone, c.name, mc.created_at

                UNION ALL

                SELECT 
                    e.id AS content_id,
                    'Ebook' AS tipo_contenido,
                    e.title AS nombre,
                    'Privado' AS estado,
                    2 AS status_code,
                    u.name AS productor_nombre,
                    u.email AS productor_email,
                    u.phone AS productor_telefono,
                    c.name AS categoria_nombre,
                    e.created_at AS fecha,
                    COUNT(eu.id) AS total_inscritos,
                    SUM(CASE WHEN eu.isParticipant = 1 THEN 1 ELSE 0 END) AS total_participantes
                FROM ebooks e
                JOIN users u ON e.user_id = u.id
                JOIN categories c ON e.category_id = c.id
                JOIN ebook_distributor ed ON e.id = ed.ebook_id
                LEFT JOIN ebook_users eu ON ed.id = eu.ebook_distributor_id
                WHERE e.status = 2 AND e.user_id = ?
                GROUP BY e.id, e.title, u.name, u.email, u.phone, c.name, e.created_at
            ) AS combined_private
            ORDER BY fecha DESC
        ";
    }

    /**
     * Query para contenido por estado
     */
    private function buildContentByStatusQuery()
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