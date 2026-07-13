<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // ==========================================
        // Module: Infoproducts
        // ==========================================

        $this->renderable(function (\Promolider\Application\Infoproducts\Exceptions\InfoproductNotOwnedException $e, $request) {
            return response()->json([
                'error'   => 'Forbidden',
                'message' => $e->getMessage()
            ], 403);
        });

        $this->renderable(function (\Promolider\Application\Infoproducts\Exceptions\InfoproductNotFoundException $e, $request) {
            return response()->json([
                'error'   => 'Not Found',
                'message' => $e->getMessage()
            ], 404);
        });
    }
}
