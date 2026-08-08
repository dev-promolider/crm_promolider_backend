<?php
function replaceInDir($dir) {
    $files = glob($dir . '/*');
    foreach($files as $file) {
        if(is_dir($file)) {
            replaceInDir($file);
        } else {
            if(pathinfo($file, PATHINFO_EXTENSION) == 'php') {
                $content = file_get_contents($file);
                $content = str_replace('App\1_Domain', 'Promolider\Domain', $content);
                $content = str_replace('App\2_Application', 'Promolider\Application', $content);
                $content = str_replace('App\3_Infrastructure', 'Promolider\Infrastructure', $content);
                file_put_contents($file, $content);
            }
        }
    }
}
replaceInDir(__DIR__ . '/app/1-Domain/Requests');
replaceInDir(__DIR__ . '/app/2-Application/Requests');
replaceInDir(__DIR__ . '/app/3-Infrastructure/Requests');
echo "Done";
