<?php

namespace Promolider\Application\Infoproducts\Exceptions;

use Exception;

class InfoproductNotOwnedException extends Exception
{
    public function __construct()
    {
        parent::__construct("No tienes autorización para acceder a este recurso.");
    }
}
