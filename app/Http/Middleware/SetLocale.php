<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        app()->setLocale(session('locale', $request->user()?->preferred_locale ?? 'fr'));

        return $next($request);
    }
}
