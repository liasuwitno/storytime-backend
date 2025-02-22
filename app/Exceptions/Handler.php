<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException; // Tambahkan ini
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Handle unauthenticated requests.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json([
            'code' => 401,
            'status' => 'error',
            'message' => 'Anda tidak memiliki akses. Silakan login terlebih dahulu.'
        ], 401);
    }
}
