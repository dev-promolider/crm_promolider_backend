<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$sponsorId = 868;
$position = 0;
$query = "
            WITH RECURSIVE cte AS (
                SELECT id, user_id, user_above, id_user_sponsor, position, 1 as depth
                FROM classified
                WHERE user_above = CAST(? AS CHAR) AND position = ?
                
                UNION ALL
                
                SELECT c.id, c.user_id, c.user_above, c.id_user_sponsor, c.position, cte.depth + 1
                FROM classified c
                INNER JOIN cte ON c.user_above = CAST(cte.user_id AS CHAR)
                WHERE c.position = ?
            )
            SELECT user_id FROM cte WHERE id_user_sponsor = ? ORDER BY depth ASC LIMIT 1
        ";
try {
    $result = Illuminate\Support\Facades\DB::selectOne($query, [$sponsorId, $position, $position, $sponsorId]);
    print_r($result);
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
