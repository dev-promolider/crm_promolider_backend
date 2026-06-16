<?php

namespace App\Exceptions\Auth;

use App\Exceptions\CustomException;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ForbiddenException extends CustomException
{
    protected $message;
    protected $code;
    protected $previous;

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $this->message = $message ?: __('http.forbidden');
        $this->code = $code ?: Response::HTTP_FORBIDDEN;
        $this->previous = $previous ?: $this;

        parent::__construct( $this->message, $this->code, $this->previous);
    }

    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
    }
}
