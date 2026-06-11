<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $activeProjectId = session('active_project_id');

        $query = Task::with(['assignees', 'files', 'creator'])->withCount('comments');

        if ($activeProjectId) {
            $query->where('project_id', $activeProjectId);
        } else {
            $query->where('id', '<', 0);
        }

        // Filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }
        if ($request->filled('assignee')) {
            $query->whereHas('assignees', fn ($q) => $q->where('users.id', $request->assignee));
        }
        if ($request->filled('my_tasks')) {
            $query->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id));
        }

        // Regular users see all tasks on board, but limited details
        $tasks = $query->get();

        // Group by status for kanban
        $allColumns = Task::STATUSES;
        if ($user->isAdmin() || $user->isLeader()) {
            $columns = $allColumns;
        } else {
            // Only their specialization + generic columns
            $allowedKeys = array_filter([$user->specialization, 'in_progress', 'to_approve', 'done']);
            $columns = array_intersect_key($allColumns, array_flip($allowedKeys));
        }

        $tasksByStatus = [];
        foreach ($columns as $status => $label) {
            $tasksByStatus[$status] = $tasks->where('status', $status)->values();
        }

        $usersQuery = User::active()->orderBy('name');
        if ($activeProjectId) {
            $usersQuery->whereHas('projects', fn($q) => $q->where('projects.id', $activeProjectId));
        }
        $users = $usersQuery->get();

        return view('board.index', compact('tasksByStatus', 'columns', 'users', 'tasks'));
    }

    public function show(Task $task)
    {
        $user = Auth::user();

        // Load relationships
        $task->load(['assignees', 'files.uploader', 'files.reviewer', 'comments.user', 'creator']);

        // Determine access level
        $hasFullAccess = $user->isAdmin()
            || ($user->isLeader() && $user->canManageCategory($task->getStatusCategory()))
            || $task->isAssigned($user);

        $canEdit = $task->canBeEditedBy($user);
        $canUploadFile = $user->isLeaderOrAdmin() || $task->isAssigned($user);
        $canChangeStatus = $user->isAdmin()
            || ($user->isLeader() && $user->canManageCategory($task->getStatusCategory()))
            || $task->isAssigned($user);

        return view('board.show', compact('task', 'hasFullAccess', 'canEdit', 'canUploadFile', 'canChangeStatus'));
    }

    public function changeStatus(Request $request, Task $task)
    {
        $this->authorize('changeStatus', $task);

        $request->validate([
            'status' => ['required', 'in:' . implode(',', array_keys(Task::STATUSES))],
        ]);

        $newStatus = $request->status;
        $user = Auth::user();

        // Enforce restriction: users cannot move tasks to other specializations
        $specializations = ['writers', 'graphics', 'programmers'];
        if (in_array($newStatus, $specializations) && !$user->isAdmin()) {
            if ($newStatus !== $user->specialization) {
                return back()->with('error', 'Nie możesz przenieść zadania do działu innej specjalizacji.');
            }
        }

        // Enforce restriction: only Admin or Leader of the category can move to "done"
        if ($newStatus === 'done') {
            if (!$user->isAdmin() && !($user->isLeader() && $user->canManageCategory($task->getStatusCategory()))) {
                return back()->with('error', 'Tylko administrator lub lider odpowiedniej kategorii może przenieść zadanie do kolumny Zrobione.');
            }
        }

        $oldStatus = $task->status;
        $task->update(['status' => $newStatus]);

        if ($newStatus === 'to_approve' && $oldStatus !== 'to_approve') {
            $usersToNotify = User::where('role', 'admin')->get();
            $leader = User::where('role', 'leader')->where('specialization', $task->getStatusCategory())->first();
            if ($leader && $leader->id !== $user->id) {
                $usersToNotify->push($leader);
            }
            $usersToNotify = $usersToNotify->unique('id')->reject(fn($u) => $u->id === $user->id);
            if ($usersToNotify->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($usersToNotify, new \App\Notifications\TaskToApproveNotification($task));
            }
        }

        return back()->with('success', "Status zadania zmieniony z '{$task->getStatusLabelFromValue($oldStatus)}' na '{$task->status_label}'.");
    }

    public function destroy(Task $task)
    {
        if (!Auth::user()->can('delete', $task)) {
            return back()->with('error', 'Brak uprawnień do usunięcia tego zadania.');
        }
        $task->delete();
        return redirect()->route('board.index')->with('success', 'Zadanie zostało usunięte.');
    }

    public function pushToGithub(Request $request, Task $task)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !($user->isLeader() && $user->canManageCategory($task->getStatusCategory()))) {
            return back()->with('error', 'Brak uprawnień do przesłania na GitHub.');
        }

        if ($task->status !== 'done') {
            return back()->with('error', 'Tylko zakończone zadania mogą być przesłane na GitHuba.');
        }

        if (!in_array($task->profession, ['writers', 'graphics', 'programmers'])) {
            return back()->with('error', 'Niedozwolona profesja do przesłania.');
        }

        $project = $task->project;
        if (!$project || !$project->github_repo || !$project->github_token) {
            return back()->with('error', 'Projekt nie ma skonfigurowanego repozytorium GitHub lub brakuje tokenu.');
        }

        $branchName = $task->profession;
        $repo = $project->github_repo;
        $token = $project->github_token;
        $http = \Illuminate\Support\Facades\Http::withToken($token)->withoutVerifying();

        // Check if branch exists
        $refResponse = $http->get("https://api.github.com/repos/{$repo}/git/refs/heads/{$branchName}");
        if (!$refResponse->successful()) {
            // Branch doesn't exist, create it from main or master
            $mainRef = $http->get("https://api.github.com/repos/{$repo}/git/refs/heads/main");
            $mainSha = null;

            if ($mainRef->status() === 409) {
                // Repo is empty, initialize it with a README
                $initResponse = $http->put("https://api.github.com/repos/{$repo}/contents/README.md", [
                    'message' => 'Initial commit',
                    'content' => base64_encode("# " . $project->name . "\n\nRepozytorium automatycznie podłączone do systemu HIKIXMORI Kanban."),
                ]);
                
                if (!$initResponse->successful()) {
                    return back()->with('error', 'Nie udało się zainicjować pustego repozytorium (tworzenie pliku README). Odpowiedź: ' . $initResponse->json('message', 'Nieznany błąd'));
                }
                $mainSha = $initResponse->json('commit.sha');
            } elseif (!$mainRef->successful()) {
                // Try master branch
                $masterRef = $http->get("https://api.github.com/repos/{$repo}/git/refs/heads/master");
                if ($masterRef->successful()) {
                    $mainSha = $masterRef->json('object.sha');
                } else {
                    return back()->with('error', 'Nie można pobrać gałęzi main ani master z GitHuba. Status: ' . $mainRef->status() . ' - ' . $mainRef->json('message', 'Nieznany błąd'));
                }
            } else {
                $mainSha = $mainRef->json('object.sha');
            }

            $createBranch = $http->post("https://api.github.com/repos/{$repo}/git/refs", [
                'ref' => "refs/heads/{$branchName}",
                'sha' => $mainSha,
            ]);
            if (!$createBranch->successful()) {
                return back()->with('error', 'Nie udało się utworzyć gałęzi ' . $branchName . '. Odpowiedź: ' . $createBranch->json('message', 'Nieznany błąd'));
            }
        }

        // Push files
        $approvedFiles = $task->files()->where('status', 'approved')->get();
        if ($approvedFiles->isEmpty()) {
            return back()->with('error', 'Zadanie nie posiada zatwierdzonych plików do przesłania.');
        }

        $pushedCount = 0;
        $slugTitle = \Illuminate\Support\Str::slug($task->title);
        
        foreach ($approvedFiles as $file) {
            $content = \Illuminate\Support\Facades\Storage::disk('local')->get($file->path);
            $base64 = base64_encode($content);
            $filePath = "Zadania/{$task->id}-{$slugTitle}/{$file->original_name}";

            $putResponse = $http->put("https://api.github.com/repos/{$repo}/contents/{$filePath}", [
                'message' => "Add file {$file->original_name} for task #{$task->id}",
                'content' => $base64,
                'branch' => $branchName,
            ]);

            if ($putResponse->successful()) {
                $pushedCount++;
            }
        }

        return back()->with('success', "Pomyślnie przesłano {$pushedCount} plik(ów) na gałąź {$branchName}.");
    }
}
