<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;

class TaskController extends Controller
{


    public function index(Request $request)
    {
        $user = Auth::user();
        
        $activeProjectId = session('active_project_id');
        $query = Task::with(['assignees', 'creator'])->withCount(['comments', 'files']);

        if ($activeProjectId) {
            $query->where('project_id', $activeProjectId);
        } else {
            $query->where('id', '<', 0);
        }

        // Leaders see all tasks in the list, but policies restrict actions.

        // Filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }
        if ($request->filled('profession')) {
            $query->where('profession', $request->profession);
        }
        if ($request->filled('assignee')) {
            $query->whereHas('assignees', fn ($q) => $q->where('users.id', $request->assignee));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');
        $allowedSorts = ['title', 'status', 'priority', 'created_at', 'due_date'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $tasks = $query->paginate(15)->withQueryString();
        
        $usersQuery = User::active()->orderBy('name');
        if ($activeProjectId) {
            $usersQuery->whereHas('projects', fn($q) => $q->where('projects.id', $activeProjectId));
        }
        $users = $usersQuery->get();

        $statuses = Task::STATUSES;
        $priorities = Task::PRIORITIES;

        return view('admin.tasks.index', compact('tasks', 'users', 'statuses', 'priorities'));
    }

    public function create()
    {
        $user = Auth::user();
        $activeProjectId = session('active_project_id');
        if (!$activeProjectId) {
            return back()->with('error', 'Wybierz projekt, w którym chcesz dodać zadanie.');
        }

        $professions = ['writers' => '✍️ Writerzy', 'graphics' => '🎨 Graficy', 'programmers' => '💻 Programiści'];
        $priorities = Task::PRIORITIES;

        $usersQuery = User::active()->whereHas('projects', fn($q) => $q->where('projects.id', $activeProjectId))->orderBy('name');

        // Leaders can only assign users of their specialization
        if ($user->isLeader()) {
            $users = $usersQuery->bySpecialization($user->specialization)->get();
            $statuses = [$user->specialization => Task::STATUSES[$user->specialization]];
        } else {
            $users = $usersQuery->where('role', 'user')->get();
            $statuses = $professions;
        }

        return view('admin.tasks.create', compact('statuses', 'priorities', 'users'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $activeProjectId = session('active_project_id');
        if (!$activeProjectId) {
            return back()->with('error', 'Wybierz projekt przed dodaniem zadania.');
        }

        $rules = [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['required', 'string', 'min:10'],
            'status' => ['required', 'in:' . implode(',', array_keys(Task::STATUSES))],
            'priority' => ['required', 'in:low,medium,high'],
            'assignees' => ['nullable', 'array'],
            'assignees.*' => ['exists:users,id'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];

        // Leader can only set their category as status
        if ($user->isLeader()) {
            $rules['status'] = ['required', 'in:' . $user->specialization];
        }

        $messages = [
            'title.required' => 'Tytuł zadania jest wymagany.',
            'title.min' => 'Tytuł musi mieć co najmniej 3 znaki.',
            'title.max' => 'Tytuł nie może przekraczać 255 znaków.',
            'description.required' => 'Opis zadania jest wymagany.',
            'description.min' => 'Opis musi mieć co najmniej 10 znaków.',
            'status.required' => 'Status jest wymagany.',
            'priority.required' => 'Priorytet jest wymagany.',
            'due_date.date' => 'Podaj prawidłową datę.',
            'due_date.after_or_equal' => 'Termin nie może być w przeszłości.',
        ];

        $validated = $request->validate($rules, $messages);

        // Generate short description
        $validated['description_short'] = str($validated['description'])->limit(100)->toString();
        $validated['created_by'] = Auth::id();
        $validated['project_id'] = $activeProjectId;
        $validated['profession'] = $validated['status'];

        $task = Task::create($validated);

        if (!empty($validated['assignees'])) {
            $task->assignees()->attach($validated['assignees']);
        }

        return redirect()->route('admin.tasks.index')
            ->with('success', "Zadanie \"{$task->title}\" zostało utworzone.");
    }

    public function edit(Task $task)
    {
        $user = Auth::user();

        // Leader can only edit tasks in their category
        if ($user->isLeader() && !$user->canManageCategory($task->getStatusCategory())) {
            return back()->with('error', 'Brak uprawnień do edycji zadania z innej kategorii.');
        }

        $statuses = Task::STATUSES;
        $priorities = Task::PRIORITIES;

        $usersQuery = User::active()->whereHas('projects', fn($q) => $q->where('projects.id', $task->project_id))->orderBy('name');

        if ($user->isLeader()) {
            $users = $usersQuery->bySpecialization($user->specialization)->get();
        } else {
            $users = $usersQuery->where('role', 'user')->get();
        }

        $task->load('assignees');
        $assignedIds = $task->assignees->pluck('id')->toArray();

        return view('admin.tasks.edit', compact('task', 'statuses', 'priorities', 'users', 'assignedIds'));
    }

    public function update(Request $request, Task $task)
    {
        $user = Auth::user();

        if ($user->isLeader() && !$user->canManageCategory($task->getStatusCategory())) {
            return back()->with('error', 'Brak uprawnień do aktualizacji zadania z innej kategorii.');
        }

        $rules = [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['required', 'string', 'min:10'],
            'status' => ['required', 'in:' . implode(',', array_keys(Task::STATUSES))],
            'priority' => ['required', 'in:low,medium,high'],
            'assignees' => ['nullable', 'array'],
            'assignees.*' => ['exists:users,id'],
            'due_date' => ['nullable', 'date'],
        ];

        $messages = [
            'title.required' => 'Tytuł zadania jest wymagany.',
            'title.min' => 'Tytuł musi mieć co najmniej 3 znaki.',
            'description.required' => 'Opis zadania jest wymagany.',
            'description.min' => 'Opis musi mieć co najmniej 10 znaków.',
            'status.required' => 'Status jest wymagany.',
            'priority.required' => 'Priorytet jest wymagany.',
        ];

        $validated = $request->validate($rules, $messages);
        $validated['description_short'] = str($validated['description'])->limit(100)->toString();
        if (in_array($validated['status'], ['writers', 'graphics', 'programmers'])) {
            $validated['profession'] = $validated['status'];
        }

        $task->update($validated);
        $task->assignees()->sync($validated['assignees'] ?? []);

        return redirect()->route('admin.tasks.index')
            ->with('success', "Zadanie \"{$task->title}\" zostało zaktualizowane.");
    }

    public function destroy(Task $task)
    {
        if (!Auth::user()->can('delete', $task)) {
            return back()->with('error', 'Brak uprawnień do usunięcia tego zadania.');
        }
        $taskTitle = $task->title;
        $task->delete();
        return redirect()->route('admin.tasks.index')
            ->with('success', "Zadanie \"{$taskTitle}\" zostało usunięte.");
    }
}
