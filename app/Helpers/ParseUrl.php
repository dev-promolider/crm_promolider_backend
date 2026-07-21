<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config as FacadesConfig;

class ParseUrl
{
    public static function method()
    {
        return FacadesConfig::get('app.cipher');
    }

    public static function contacAtrrS3($atrr)
    {
        $storage_domain = config('global_variables.storage_domain') ?: env('STORAGE_DOMAIN');
        $url = rtrim($storage_domain, '/') . '/' . ltrim($atrr, '/');
        return $url;
    }

    public static function contacAtrrS3badges($atrr)
    {
        $storage_domain = config('global_variables.storage_domain') ?: env('STORAGE_DOMAIN');
        $url = rtrim($storage_domain, '/') . '/images/badges/' . ltrim($atrr, '/');
        return $url;
    }
}
