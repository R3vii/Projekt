@extends('layouts.app')

@section('title', 'Tablica Kanban')
@section('page-title', 'Tablica Kanban')
@section('breadcrumb', 'HIKIXMORI — Zarządzanie zadaniami')

@section('content')
<div class="p-6" x-data="kanbanBoard()">

    <!-- Filters bar -->
    <div class="mb-6 bg-gray-900 rounded-xl border border-gray-800 p-4">
        <form method="GET" action="{{ route('board.index') }}" class="flex flex-wrap gap-3 items-end">
            <!-- Search -->
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-400 mb-1">Szukaj zadania</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Wpisz tytuł lub opis..."
                           class="w-full pl-9 pr-3 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm transition-all">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Priority filter -->
            <div class="min-w-36">
                <label class="block text-xs font-medium text-gray-400 mb-1">Priorytet</label>
                <select name="priority" class="w-full px-3 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                    <option value="">Wszystkie</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>🔴 Wysoki</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>🟡 Średni</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>🟢 Niski</option>
                </select>
            </div>

            <!-- Assignee filter -->
            <div class="min-w-44">
                <label class="block text-xs font-medium text-gray-400 mb-1">Przypisany</label>
                <select name="assignee" class="w-full px-3 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                    <option value="">Wszyscy</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('assignee') == $u->id ? 'selected' : '' }}>
                        {{ $u->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- My tasks filter -->
            <div class="flex items-center ml-2 mb-2 lg:mb-0">
                <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer">
                    <input type="checkbox" name="my_tasks" value="1" {{ request('my_tasks') ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-700 bg-gray-800 text-brand-500 focus:ring-brand-500">
                    Tylko moje zadania
                </label>
            </div>

            <!-- Buttons -->
            <div class="flex gap-2">
                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                    </svg>
                    Filtruj
                </button>
                @if(request()->hasAny(['search', 'priority', 'assignee']))
                <a href="{{ route('board.index') }}"
                   class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-gray-300 text-sm font-medium transition-colors">
                    Wyczyść
                </a>
                @endif
            </div>

            @if(auth()->user()->isLeaderOrAdmin())
            <a href="{{ route('admin.tasks.create') }}"
               class="ml-auto px-4 py-2 rounded-lg bg-green-600 hover:bg-green-500 text-white text-sm font-medium transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nowe zadanie
            </a>
            @endif
        </form>
    </div>

    <!-- Kanban Columns -->
    <div class="grid gap-4 min-w-max lg:min-w-0" style="grid-template-columns: repeat({{ count($columns) }}, minmax(0, 1fr));">
        @php
        $columnConfig = [
            'writers'     => ['color' => 'purple', 'icon' => '✍️', 'gradient' => 'from-purple-900/30'],
            'graphics'    => ['color' => 'pink',   'icon' => '🎨', 'gradient' => 'from-pink-900/30'],
            'programmers' => ['color' => 'blue',   'icon' => '💻', 'gradient' => 'from-blue-900/30'],
            'in_progress' => ['color' => 'amber',  'icon' => '⚡', 'gradient' => 'from-amber-900/30'],
            'to_approve'  => ['color' => 'teal',   'icon' => '⏳', 'gradient' => 'from-teal-900/30'],
            'done'        => ['color' => 'green',  'icon' => '✅', 'gradient' => 'from-green-900/30'],
        ];
        $colorMap = [
            'purple' => ['header' => 'text-purple-300 bg-purple-500/10 border-purple-500/30', 'badge' => 'bg-purple-500/20 text-purple-300', 'dot' => 'bg-purple-400'],
            'pink'   => ['header' => 'text-pink-300 bg-pink-500/10 border-pink-500/30', 'badge' => 'bg-pink-500/20 text-pink-300', 'dot' => 'bg-pink-400'],
            'blue'   => ['header' => 'text-blue-300 bg-blue-500/10 border-blue-500/30', 'badge' => 'bg-blue-500/20 text-blue-300', 'dot' => 'bg-blue-400'],
            'amber'  => ['header' => 'text-amber-300 bg-amber-500/10 border-amber-500/30', 'badge' => 'bg-amber-500/20 text-amber-300', 'dot' => 'bg-amber-400'],
            'teal'   => ['header' => 'text-teal-300 bg-teal-500/10 border-teal-500/30', 'badge' => 'bg-teal-500/20 text-teal-300', 'dot' => 'bg-teal-400'],
            'green'  => ['header' => 'text-green-300 bg-green-500/10 border-green-500/30', 'badge' => 'bg-green-500/20 text-green-300', 'dot' => 'bg-green-400'],
        ];
        @endphp

        @foreach($columns as $status => $label)
        @php
            $cfg = $columnConfig[$status];
            $clr = $colorMap[$cfg['color']];
            $colTasks = $tasksByStatus[$status] ?? collect();
        @endphp
        <div class="flex flex-col w-72 lg:w-auto">
            <!-- Column header -->
            <div class="flex items-center justify-between px-3 py-2.5 rounded-t-xl border {{ $clr['header'] }} border-b-0">
                <div class="flex items-center gap-2">
                    <span class="text-base">{{ $cfg['icon'] }}</span>
                    <h2 class="font-semibold text-sm">{{ $label }}</h2>
                </div>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $clr['badge'] }}">
                    {{ $colTasks->count() }}
                </span>
            </div>

            <!-- Column body (droppable) -->
            <div class="kanban-column flex-1 bg-gray-900/50 border border-gray-800 border-t-0 rounded-b-xl p-2 space-y-2"
                 id="col-{{ $status }}"
                 data-status="{{ $status }}">

                @forelse($colTasks as $task)
                @php
                    $pColors = ['high' => 'text-red-400 bg-red-500/10', 'medium' => 'text-amber-400 bg-amber-500/10', 'low' => 'text-green-400 bg-green-500/10'];
                    $pIcons  = ['high' => '↑↑', 'medium' => '→', 'low' => '↓'];
                    $isAssigned = $task->isAssigned(auth()->user());
                    $user = auth()->user();
                    $canChangeStatus = $user->isAdmin() || ($user->isLeader() && $user->canManageCategory($task->getStatusCategory())) || $isAssigned;
                    $dragClass = $canChangeStatus ? 'task-card' : 'opacity-70 cannot-drag';
                @endphp

                <!-- Task Card -->
                <div class="{{ $dragClass }} bg-gray-800 border border-gray-700/50 rounded-lg p-3 {{ $canChangeStatus ? 'cursor-pointer' : 'cursor-not-allowed' }} group"
                     data-task-id="{{ $task->id }}"
                     @if($canChangeStatus) @click="openTask({{ $task->id }})" @endif>

                    <!-- Priority badge + files indicator -->
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-xs font-semibold px-1.5 py-0.5 rounded {{ $pColors[$task->priority] ?? '' }}">
                                {{ $pIcons[$task->priority] ?? '' }} {{ $task->priority_label }}
                            </span>
                            @if($task->profession)
                            @php
                                $profColors = [
                                    'writers' => 'text-purple-300 bg-purple-500/10',
                                    'graphics' => 'text-pink-300 bg-pink-500/10',
                                    'programmers' => 'text-blue-300 bg-blue-500/10',
                                ];
                            @endphp
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wider {{ $profColors[$task->profession] ?? 'text-gray-400 bg-gray-800' }}">
                                {{ \App\Models\Task::STATUSES[$task->profession] ?? $task->profession }}
                            </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-1.5">
                            @if($task->files_count > 0)
                            <span class="text-xs text-gray-500 flex items-center gap-0.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                {{ $task->files_count }}
                            </span>
                            @endif
                            @if($task->comments_count > 0)
                            <span class="text-xs text-gray-500 flex items-center gap-0.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                {{ $task->comments_count }}
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- Title -->
                    <h3 class="text-sm font-semibold text-white mb-1 line-clamp-1">{{ $task->title }}</h3>

                    <!-- Description preview -->
                    <p class="text-xs text-gray-400 line-clamp-2 mb-3">{{ $task->description_short ?? $task->description }}</p>

                    <!-- Assignees + date -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center -space-x-1">
                            @foreach($task->assignees->take(3) as $assignee)
                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-brand-400 to-purple-500 flex items-center justify-center text-xs font-bold text-white ring-2 ring-gray-800"
                                 title="{{ $assignee->name }}">
                                {{ $assignee->initials }}
                            </div>
                            @endforeach
                            @if($task->assignees->count() > 3)
                            <div class="w-6 h-6 rounded-full bg-gray-700 flex items-center justify-center text-xs text-gray-400 ring-2 ring-gray-800">
                                +{{ $task->assignees->count() - 3 }}
                            </div>
                            @endif
                            @if($task->assignees->isEmpty())
                            <span class="text-xs text-gray-600">Nieprzypisane</span>
                            @endif
                        </div>

                        @if($task->due_date)
                        <span class="text-xs {{ $task->due_date->isPast() && $task->status !== 'done' ? 'text-red-400' : 'text-gray-500' }} flex items-center gap-0.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $task->due_date->format('d.m') }}
                        </span>
                        @endif
                    </div>

                    <!-- "Moje zadanie" indicator -->
                    @if($isAssigned)
                    <div class="mt-2 pt-2 border-t border-gray-700/50 flex items-center gap-1">
                        <div class="w-1.5 h-1.5 rounded-full {{ $clr['dot'] }}"></div>
                        <span class="text-xs text-gray-500">Twoje zadanie</span>
                    </div>
                    @endif
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-10 text-gray-600">
                    <svg class="w-8 h-8 mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span class="text-xs">Brak zadań</span>
                </div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
function kanbanBoard() {
    return {
        openTask(id) {
            window.location.href = `/tasks/${id}`;
        },
        init() {
            // Initialize drag & drop for each column for all users (server validates permissions)
            document.querySelectorAll('.kanban-column').forEach(col => {
                Sortable.create(col, {
                    group: 'tasks',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    handle: '.task-card', // only elements with .task-card are draggable
                    filter: '.cannot-drag',
                    onEnd: (evt) => {
                        if (evt.from === evt.to) return;
                        const taskId = evt.item.dataset.taskId;
                        const newStatus = evt.to.dataset.status;
                        this.changeTaskStatus(taskId, newStatus);
                    }
                });
            });
        },
        changeTaskStatus(taskId, newStatus) {
            fetch(`/tasks/${taskId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        || '{{ csrf_token() }}'
                },
                body: JSON.stringify({ status: newStatus })
            }).then(r => {
                if (!r.ok) { window.location.reload(); }
            }).catch(() => window.location.reload());
        }
    }
}
</script>
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
@endsection
