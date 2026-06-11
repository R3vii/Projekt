<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{


    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'ilike', '%' . $request->search . '%')
                  ->orWhere('email', 'ilike', '%' . $request->search . '%');
            });
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('specialization')) {
            $query->where('specialization', $request->specialization);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');
        $allowedSorts = ['name', 'email', 'role', 'specialization', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $projects = \App\Models\Project::orderBy('name')->get();
        return view('admin.users.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', 'in:leader,user'],
            'specialization' => ['required', 'in:writers,graphics,programmers'],
            'projects' => ['required', 'array', 'min:1'],
            'projects.*' => ['exists:projects,id'],
        ], [
            'name.required' => 'Imię i nazwisko jest wymagane.',
            'email.required' => 'Adres e-mail jest wymagany.',
            'email.unique' => 'Ten adres e-mail jest już zarejestrowany.',
            'password.confirmed' => 'Hasła nie są identyczne.',
            'role.required' => 'Rola jest wymagana.',
            'specialization.required' => 'Specjalizacja jest wymagana.',
            'projects.required' => 'Należy wybrać co najmniej jeden projekt.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'specialization' => $request->specialization,
            'is_active' => true,
        ]);

        $user->projects()->sync($request->projects);

        return redirect()->route('admin.users.index')
            ->with('success', "Konto dla {$request->name} zostało utworzone.");
    }

    public function edit(User $user)
    {
        $projects = \App\Models\Project::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'projects'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:leader,user'],
            'specialization' => ['required', 'in:writers,graphics,programmers'],
            'is_active' => ['boolean'],
            'projects' => ['required', 'array', 'min:1'],
            'projects.*' => ['exists:projects,id'],
        ], [
            'name.required' => 'Imię i nazwisko jest wymagane.',
            'email.required' => 'Adres e-mail jest wymagany.',
            'email.unique' => 'Ten adres e-mail jest już zarejestrowany.',
            'role.required' => 'Rola jest wymagana.',
            'specialization.required' => 'Specjalizacja jest wymagana.',
            'projects.required' => 'Należy wybrać co najmniej jeden projekt.',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'specialization' => $request->specialization,
            'is_active' => $request->boolean('is_active'),
        ]);

        $user->projects()->sync($request->projects);

        return redirect()->route('admin.users.index')
            ->with('success', "Dane użytkownika {$user->name} zostały zaktualizowane.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Nie możesz usunąć własnego konta.');
        }
        if ($user->isAdmin()) {
            return back()->with('error', 'Nie można usunąć konta Administratora.');
        }
        $userName = $user->name;
        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('success', "Konto {$userName} zostało usunięte.");
    }

    public function toggleActive(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Nie możesz dezaktywować własnego konta.');
        }
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'aktywowane' : 'dezaktywowane';
        return back()->with('success', "Konto {$user->name} zostało {$status}.");
    }
}
