@extends('layouts.app')

@section('title', 'Projekty')
@section('page-title', 'Zarządzanie projektami')
@section('breadcrumb', 'Panel administracyjny')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('admin.projects.create') }}" class="px-4 py-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Dodaj projekt
        </a>
    </div>

    <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-800">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Nazwa Projektu</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Zadania</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Użytkownicy</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Utworzono</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Akcje</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($projects as $project)
                <tr class="hover:bg-gray-800/50 transition-colors">
                    <td class="px-4 py-3">
                        <p class="text-sm font-medium text-white">{{ $project->name }}</p>
                        <p class="text-xs text-gray-500">{{ Str::limit($project->description, 50) }}</p>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ $project->tasks_count }}</td>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ $project->users_count }}</td>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ $project->created_at->format('d.m.Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('admin.projects.edit', $project) }}" class="p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-700 transition-colors" title="Edytuj">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" onsubmit="return confirm('Czy na pewno usunąć ten projekt? Zostaną usunięte również wszystkie jego zadania!');">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-900/20 transition-colors" title="Usuń">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-gray-600 text-sm">Brak projektów w systemie.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($projects->hasPages())
        <div class="px-4 py-3 border-t border-gray-800">{{ $projects->links() }}</div>
        @endif
    </div>
</div>
@endsection
