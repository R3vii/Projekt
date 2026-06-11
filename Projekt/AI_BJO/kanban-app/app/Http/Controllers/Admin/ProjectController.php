<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::withCount('tasks', 'users')->paginate(15);
        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('admin.projects.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'github_token' => 'nullable|string',
            'github_repo' => 'nullable|string',
        ]);

        if (!empty($validated['github_repo'])) {
            $repo = preg_replace('/^(https?:\/\/)?(www\.)?github\.com\//', '', $validated['github_repo']);
            $repo = preg_replace('/\.git$/', '', $repo);
            $validated['github_repo'] = trim($repo, '/');
        }

        if ($request->auto_create_repo && $request->github_token && $request->github_repo) {
            $response = \Illuminate\Support\Facades\Http::withToken($request->github_token)
                ->post('https://api.github.com/user/repos', [
                    'name' => $request->github_repo,
                    'private' => false,
                    'auto_init' => true,
                ]);

            if ($response->successful()) {
                $validated['github_repo'] = $response->json('full_name');
            } else {
                return back()->withInput()->withErrors(['github_repo' => 'Błąd GitHub API: ' . $response->json('message', 'Nieznany błąd')]);
            }
        }

        Project::create($validated);

        return redirect()->route('admin.projects.index')
            ->with('success', 'Projekt został utworzony.');
    }

    public function edit(Project $project)
    {
        return view('admin.projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'github_token' => 'nullable|string',
            'github_repo' => 'nullable|string',
        ]);

        if (!empty($validated['github_repo'])) {
            $repo = preg_replace('/^(https?:\/\/)?(www\.)?github\.com\//', '', $validated['github_repo']);
            $repo = preg_replace('/\.git$/', '', $repo);
            $validated['github_repo'] = trim($repo, '/');
        }

        if (!$request->filled('github_token')) {
            unset($validated['github_token']);
        }

        $project->update($validated);

        return redirect()->route('admin.projects.index')
            ->with('success', 'Projekt został zaktualizowany.');
    }

    public function destroy(Project $project)
    {
        if ($project->id === session('active_project_id')) {
            session()->forget('active_project_id');
        }
        $project->delete();

        return redirect()->route('admin.projects.index')
            ->with('success', 'Projekt został usunięty.');
    }
}
