<?php
$c = app(\Promolider\Infrastructure\Marketing\In\Http\Controllers\CourseController::class);
// wait, the previous tinker failed because the controller wasn't found.
echo get_class(app()->make(\App\Http\Controllers\Controller::class));
