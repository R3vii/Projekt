<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyProjectAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user) {
            $activeProjectId = session('active_project_id');

            // If not set, try to set a default one
            if (!$activeProjectId) {
                $defaultProject = $user->isAdmin() ? \App\Models\Project::first() : $user->projects()->first();
                if ($defaultProject) {
                    session(['active_project_id' => $defaultProject->id]);
                    $activeProjectId = $defaultProject->id;
                }
            }

            // Verify access
            if ($activeProjectId && !$user->isAdmin()) {
                if (!$user->projects()->where('projects.id', $activeProjectId)->exists()) {
                    // Fallback to a valid project or remove invalid session
                    $validProject = $user->projects()->first();
                    if ($validProject) {
                        session(['active_project_id' => $validProject->id]);
                    } else {
                        session()->forget('active_project_id');
                    }
                }
            }
        }

        return $next($request);
    }
}
