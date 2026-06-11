@extends('layouts.app')

@section('title', 'Zarządzaj zadaniami')
@section('page-title', 'Zarządzanie zadaniami')
@section('breadcrumb', 'Panel administracyjny')

@section('content')
<div class="p-6">

    <!-- Top actions bar -->
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('admin.tasks.create') }}"
           class="px-4 py-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nowe zadanie
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-4 mb-6">
        <form method="GET" action="{{ route('admin.tasks.index') }}">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <div class="col-span-2 lg:col-span-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Szukaj zadania..."
                           class="w-full px-3 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                </div>

                <select name="status" class="px-3 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                    <option value="">Wszystkie statusy</option>
                    @foreach($statuses as $val => $label)
                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="profession" class="px-3 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                    <option value="">Wszystkie profesje</option>
                    <option value="writers" {{ request('profession') === 'writers' ? 'selected' : '' }}>Writerzy</option>
                    <option value="graphics" {{ request('profession') === 'graphics' ? 'selected' : '' }}>Graficy</option>
                    <option value="programmers" {{ request('profession') === 'programmers' ? 'selected' : '' }}>Programiści</option>
                </select>

                <select name="priority" class="px-3 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                    <option value="">Wszystkie priorytety</option>
                    @foreach($priorities as $val => $label)
                    <option value="{{ $val }}" {{ request('priority') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="assignee" class="px-3 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                    <option value="">Wszyscy użytkownicy</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('assignee') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="flex-1 px-3 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                </div>

                <div class="col-span-2 lg:col-span-6 flex gap-2 justify-end">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium transition-colors">
                        Filtruj
                    </button>
                    @if(request()->hasAny(['search', 'status', 'profession', 'priority', 'assignee', 'date_from', 'date_to']))
                    <a href="{{ route('admin.tasks.index') }}" class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-gray-300 text-sm font-medium transition-colors">
                        Wyczyść
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Tasks table -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-800">
                        @php
                        $sortBy = request('sort', 'created_at');
                        $sortDir = request('direction', 'desc');
                        $nextDir = $sortDir === 'asc' ? 'desc' : 'asc';
                        $sortLink = fn($col) => route('admin.tasks.index', array_merge(request()->query(), ['sort' => $col, 'direction' => $sortBy === $col ? $nextDir : 'asc']));
                        @endphp

                        <th class="px-4 py-3 text-left">
                            <a href="{{ $sortLink('title') }}" class="text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-white flex items-center gap-1">
                                Tytuł {{ $sortBy === 'title' ? ($sortDir === 'asc' ? '↑' : '↓') : '' }}
                            </a>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <a href="{{ $sortLink('status') }}" class="text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-white">
                                Status {{ $sortBy === 'status' ? ($sortDir === 'asc' ? '↑' : '↓') : '' }}
                            </a>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <a href="{{ $sortLink('priority') }}" class="text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-white">
                                Priorytet {{ $sortBy === 'priority' ? ($sortDir === 'asc' ? '↑' : '↓') : '' }}
                            </a>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Przypisani</th>
                        <th class="px-4 py-3 text-left">
                            <a href="{{ $sortLink('due_date') }}" class="text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-white">
                                Termin {{ $sortBy === 'due_date' ? ($sortDir === 'asc' ? '↑' : '↓') : '' }}
                            </a>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Pliki / Komentarze</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Akcje</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($tasks as $task)
                    @php
                        $pColors = ['high' => 'text-red-400 bg-red-500/10', 'medium' => 'text-amber-400 bg-amber-500/10', 'low' => 'text-green-400 bg-green-500/10'];
                        $sColors = ['writers' => 'text-purple-300 bg-purple-500/10', 'graphics' => 'text-pink-300 bg-pink-500/10', 'programmers' => 'text-blue-300 bg-blue-500/10', 'in_progress' => 'text-amber-300 bg-amber-500/10', 'done' => 'text-green-300 bg-green-500/10'];
                    @endphp
                    <tr class="hover:bg-gray-800/50 transition-colors cursor-pointer" onclick="if(event.target.closest('a') || event.target.closest('button')) return; window.location='{{ route('tasks.show', $task) }}';">
                        <td class="px-4 py-3">
                            <a href="{{ route('tasks.show', $task) }}" class="font-medium text-white hover:text-brand-400 transition-colors text-sm">{{ $task->title }}</a>
                            <p class="text-xs text-gray-500 truncate max-w-xs">{{ $task->description_short }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-semibold px-2 py-1 rounded {{ $sColors[$task->status] ?? 'text-gray-400 bg-gray-800' }}">{{ $task->status_label }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-semibold px-2 py-1 rounded {{ $pColors[$task->priority] ?? '' }}">{{ $task->priority_label }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex -space-x-1">
                                @foreach($task->assignees->take(3) as $a)
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-brand-400 to-purple-500 flex items-center justify-center text-xs font-bold text-white ring-2 ring-gray-900" title="{{ $a->name }}">{{ $a->initials }}</div>
                                @endforeach
                                @if($task->assignees->count() > 3)
                                <div class="w-6 h-6 rounded-full bg-gray-700 flex items-center justify-center text-xs text-gray-400 ring-2 ring-gray-900">+{{ $task->assignees->count() - 3 }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm {{ $task->due_date && $task->due_date->isPast() && $task->status !== 'done' ? 'text-red-400' : 'text-gray-400' }}">
                            {{ $task->due_date ? $task->due_date->format('d.m.Y') : '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <span title="Pliki">📎 {{ $task->files_count }}</span>
                                <span title="Komentarze">💬 {{ $task->comments_count }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.tasks.edit', $task) }}"
                                   class="p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-700 transition-colors" title="Edytuj">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @can('delete', $task)
                                <form method="POST" action="{{ route('admin.tasks.destroy', $task) }}"
                                      onsubmit="return confirm('Czy na pewno chcesz usunąć to zadanie?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-900/20 transition-colors" title="Usuń">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-600">
                            <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-sm">Brak zadań spełniających kryteria wyszukiwania</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tasks->hasPages())
        <div class="px-4 py-3 border-t border-gray-800">
            {{ $tasks->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
