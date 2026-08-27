<?php

namespace Promolider\Application\Marketing\Exceptions;

use RuntimeException;

class CourseRatingAlreadyExistsException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Ya registraste una valoración para este curso.');
    }
}
