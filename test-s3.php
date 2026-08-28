<?php
try {
    echo \Illuminate\Support\Facades\Storage::disk('s3')->temporaryUrl('any-path.mp4', now()->addHours(2));
} catch (\Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}

