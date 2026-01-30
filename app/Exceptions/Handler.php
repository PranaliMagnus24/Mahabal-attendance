<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof TokenMismatchException) {
            return redirect()
                ->route('login')
                ->with('error', 'Your session has expired. Please login again.');
        }

        return parent::render($request, $e);
    }
}
