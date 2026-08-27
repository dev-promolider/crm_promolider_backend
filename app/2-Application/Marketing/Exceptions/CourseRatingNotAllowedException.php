<?php

namespace Promolider\Application\Marketing\Exceptions;

use RuntimeException;

class CourseRatingNotAllowedException extends RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
