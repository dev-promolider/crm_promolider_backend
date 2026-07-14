<?php

namespace Promolider\Application\Infoproducts\Exceptions;

use Exception;

class InfoproductNotFoundException extends Exception
{
    public function __construct()
    {
        parent::__construct("El infoproducto no fue encontrado.");
    }
}
