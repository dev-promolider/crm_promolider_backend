<?php // Code within app\Helpers\Helper.php

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
        $storage_domain = config('global_variables.storage_domain');
        $url = $storage_domain . '/' . $atrr;
        return $url;
    }

    public static function contacAtrrS3badges($atrr)
    {
        $storage_domain = config('global_variables.storage_domain');
        $url = $storage_domain . '/images/badges/' . $atrr;
        return $url;
    }
}