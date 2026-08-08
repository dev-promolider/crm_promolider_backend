<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Find Luis Angel
$user = Illuminate\Support\Facades\DB::table('role_requests')
    ->where('status', 1)
    ->where('id_user', 989)
    ->first();

echo "role_request for user 989: " . ($user ? "FOUND id={$user->id}" : "NOT FOUND") . "\n";

// Try approve  
try {
    $uc = new Promolider\Application\RoleRequests\UseCases\ApproveRoleRequestUseCase();
    $result = $uc->executeCourseRequest(989);
    echo "APPROVED: " . ($result ? 'true' : 'false') . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
