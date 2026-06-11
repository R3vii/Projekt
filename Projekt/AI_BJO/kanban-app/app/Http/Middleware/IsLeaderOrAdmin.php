<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsLeaderOrAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isLeaderOrAdmin()) {
            abort(403, 'Dostęp tylko dla Lidera lub Administratora.');
        }
        return $next($request);
    }
}
