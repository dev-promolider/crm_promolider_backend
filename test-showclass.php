<?php
echo json_encode(\App\Models\Clas::where('name', 'like', '%Fundamentos del ciclo de vida%')->first());
