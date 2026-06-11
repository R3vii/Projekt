<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\TaskFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Handle active project
        $activeProjectId = session('active_project_id');
        if (!$activeProjectId) {
            $activeProject = $user->isAdmin() ? \App\Models\Project::first() : $user->projects()->first();
            if ($activeProject) {
                $activeProjectId = $activeProject->id;
                session(['active_project_id' => $activeProjectId]);
            }
        }

        // Base query for tasks the user has access to, restricted to the active project
        $tasksQuery = Task::query();
        if ($activeProjectId) {
            $tasksQuery->where('project_id', $activeProjectId);
        } else {
            // No active project (e.g. user assigned to 0 projects), they see 0 tasks.
            $tasksQuery->where('id', '<', 0);
        }

        if ($user->isLeader()) {
            $tasksQuery->where(function ($q) use ($user) {
                $q->where('profession', $user->specialization)
                  ->orWhereHas('assignees', fn($sq) => $sq->where('users.id', $user->id));
            });
        } elseif ($user->isUser()) {
            $tasksQuery->whereHas('assignees', fn($sq) => $sq->where('users.id', $user->id));
        }

        $totalTasks = (clone $tasksQuery)->count();

        $tasksByStatus = [];
        foreach (Task::STATUSES as $status => $label) {
            $tasksByStatus[$status] = [
                'label' => $label,
                'count' => (clone $tasksQuery)->where('status', $status)->count(),
            ];
        }

        // Overdue tasks
        $overdueTasks = (clone $tasksQuery)->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->whereNotIn('status', ['done'])
            ->count();

        // Notifications
        $unreadNotifications = $user->unreadNotifications;

        // Admin/Leader only stats
        $totalUsers = $totalLeaders = $pendingFilesCount = 0;
        $pendingFiles = collect();
        if ($user->isAdmin() || $user->isLeader()) {
            $totalUsers = User::where('role', 'user')->count();
            $totalLeaders = User::where('role', 'leader')->count();
            
            $pfQuery = TaskFile::where('status', 'pending')->with(['task', 'uploader']);
            if ($activeProjectId) {
                $pfQuery->whereHas('task', fn($q) => $q->where('project_id', $activeProjectId));
            }
            if ($user->isLeader()) {
                $pfQuery->whereHas('task', fn($q) => $q->where('profession', $user->specialization));
            }
            $pendingFilesCount = $pfQuery->count();
            $pendingFiles = $pfQuery->latest()->take(5)->get();
        }

        $recentTasks = (clone $tasksQuery)->with(['assignees', 'creator'])->latest()->take(5)->get();

        $priorityCounts = [
            'high' => (clone $tasksQuery)->where('priority', 'high')->count(),
            'medium' => (clone $tasksQuery)->where('priority', 'medium')->count(),
            'low' => (clone $tasksQuery)->where('priority', 'low')->count(),
        ];

        return view('dashboard', compact(
            'totalTasks', 'tasksByStatus', 'totalUsers', 'totalLeaders',
            'pendingFiles', 'pendingFilesCount', 'recentTasks', 'priorityCounts', 'overdueTasks', 'unreadNotifications'
        ));
    }

    public function switchProject(Request $request)
    {
        $request->validate(['project_id' => 'required|exists:projects,id']);
        
        $user = Auth::user();
        if (!$user->isAdmin()) {
            if (!$user->projects()->where('projects.id', $request->project_id)->exists()) {
                return back()->with('error', 'Odmowa dostępu do tego projektu.');
            }
        }
        
        session(['active_project_id' => $request->project_id]);
        return back()->with('success', 'Projekt został zmieniony.');
    }

    public function downloadProject(\App\Models\Project $project)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->projects()->where('projects.id', $project->id)->exists()) {
            return back()->with('error', 'Odmowa dostępu do tego projektu.');
        }

        if (!$project->github_repo) {
            return back()->with('error', 'Ten projekt nie ma powiązanego repozytorium GitHub.');
        }

        $repo = $project->github_repo;

        if ($project->github_token) {
            try {
                $response = \Illuminate\Support\Facades\Http::withToken($project->github_token)
                    ->withoutVerifying()
                    ->withOptions(['allow_redirects' => false])
                    ->get("https://api.github.com/repos/{$repo}/zipball/main");

                // POPRAWKA: Laravel Http nie ma isRedirect() — sprawdzamy status ręcznie
                if (in_array($response->status(), [301, 302, 303, 307, 308])) {
                    $location = $response->header('Location');
                    if ($location) {
                        return redirect()->away($location);
                    }
                }

                if ($response->status() === 200) {
                    return response($response->body(), 200, [
                        'Content-Type'        => 'application/zip',
                        'Content-Disposition' => 'attachment; filename="' . str_replace('/', '-', $repo) . '.zip"',
                    ]);
                }

                if ($response->status() === 404) {
                    return back()->with('error', 'Nie znaleziono repozytorium na GitHubie. Sprawdź nazwę i token.');
                }

                if ($response->status() === 401 || $response->status() === 403) {
                    return back()->with('error', 'Brak uprawnień do repozytorium. Sprawdź token GitHub.');
                }

                return back()->with('error', 'Błąd API GitHub: ' . $response->json('message', 'Nieznany błąd'));

            } catch (\Exception $e) {
                return back()->with('error', 'Błąd połączenia: ' . $e->getMessage());
            }
        }

        // Redirect directly for public repos
        return redirect()->away("https://github.com/{$repo}/archive/refs/heads/main.zip");
    }
}