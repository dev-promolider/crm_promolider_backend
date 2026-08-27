<?php

namespace Promolider\Application\Marketing\Exceptions;

use RuntimeException;

class CourseRatingNotFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('No tienes una valoración registrada para este curso.');
    }
}
