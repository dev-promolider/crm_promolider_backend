<?php

namespace Promolider\Application\Marketing\Exceptions;

use RuntimeException;

class CourseRatingCourseNotFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('El curso no fue encontrado.');
    }
}
