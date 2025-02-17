<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AccessDeniedHttpException) {
            return response()->view('errors.403', [], 403);
        }

        return parent::render($request, $exception);
    }
}
// Compare this snippet from resources/views/errors/403.blade.php: