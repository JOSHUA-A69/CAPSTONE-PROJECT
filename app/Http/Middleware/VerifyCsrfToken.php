<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected function inExceptArray($request): bool
    {
        if (app()->environment('local') && $request->is('dev/chat/unread/debug')) {
            return true;
        }

        return parent::inExceptArray($request);
    }
}
