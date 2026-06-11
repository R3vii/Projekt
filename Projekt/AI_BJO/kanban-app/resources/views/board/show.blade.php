@extends('layouts.app')

@section('title', $task->title)
@section('page-title', $task->title)
@section('breadcrumb', 'Tablica Kanban → Szczegóły zadania')

@section('content')
<div class="p-6 max-w-5xl mx-auto" x-data="{ uploadOpen: false, rejectingFile: null, rejectFeedback: '' }">

    <!-- Back button -->
    <a href="{{ route('board.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-white mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Powrót do tablicy
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Main content (2/3) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Task header -->
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex-1">
                        @php
                        $pColors = ['high' => 'text-red-400 bg-red-500/10 border-red-500/20', 'medium' => 'text-amber-400 bg-amber-500/10 border-amber-500/20', 'low' => 'text-green-400 bg-green-500/10 border-green-500/20'];
                        $sColors = ['writers' => 'text-purple-300 bg-purple-500/10 border-purple-500/20', 'graphics' => 'text-pink-300 bg-pink-500/10 border-pink-500/20', 'programmers' => 'text-blue-300 bg-blue-500/10 border-blue-500/20', 'in_progress' => 'text-amber-300 bg-amber-500/10 border-amber-500/20', 'to_approve' => 'text-teal-300 bg-teal-500/10 border-teal-500/20', 'done' => 'text-green-300 bg-green-500/10 border-green-500/20'];
                        @endphp

                        <div class="flex flex-wrap gap-2 mb-3">
                            <span class="text-xs font-semibold px-2 py-1 rounded border {{ $pColors[$task->priority] }}">{{ $task->priority_label }}</span>
                            <span class="text-xs font-semibold px-2 py-1 rounded border {{ $sColors[$task->status] }}">{{ $task->status_label }}</span>
                            @if($task->due_date)
                            <span class="text-xs px-2 py-1 rounded border {{ $task->due_date->isPast() && $task->status !== 'done' ? 'text-red-400 bg-red-500/10 border-red-500/20' : 'text-gray-400 bg-gray-800 border-gray-700' }}">
                                📅 Termin: {{ $task->due_date->format('d.m.Y') }}
                            </span>
                            @endif
                        </div>

                        <h1 class="text-2xl font-bold text-white mb-2">{{ $task->title }}</h1>
                        <p class="text-xs text-gray-500">Utworzone przez <span class="text-gray-400">{{ $task->creator->name }}</span> · {{ $task->created_at->diffForHumans() }}</p>
                    </div>

                    <div class="flex flex-shrink-0 gap-2">
                        @if($canEdit)
                        <a href="{{ route('admin.tasks.edit', $task) }}"
                           class="px-3 py-1.5 rounded-lg border border-gray-700 text-gray-300 hover:text-white hover:border-gray-600 text-sm transition-colors flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edytuj
                        </a>
                        @endif
                        @can('delete', $task)
                        <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Czy na pewno chcesz usunąć to zadanie?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 rounded-lg border border-red-900/50 text-red-400 hover:text-white hover:bg-red-600 hover:border-red-600 text-sm transition-colors flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Usuń
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>

                <!-- Description -->
                @if($hasFullAccess)
                <div class="prose prose-invert prose-sm max-w-none">
                    <p class="text-gray-300 leading-relaxed whitespace-pre-wrap">{{ $task->description }}</p>
                </div>
                @else
                <div class="p-4 rounded-lg bg-amber-900/20 border border-amber-700/30 text-sm text-amber-300">
                    <p class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Pełny opis dostępny tylko dla przypisanych członków zespołu.
                    </p>
                </div>
                @endif
            </div>

            <!-- Change status (for assigned users) -->
            @if($canChangeStatus)
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
                <h3 class="text-sm font-semibold text-white mb-3">Zmień status zadania</h3>
                <form method="POST" action="{{ route('tasks.change-status', $task) }}" class="flex flex-wrap gap-2">
                    @csrf
                    @foreach(\App\Models\Task::STATUSES as $status => $label)
                    @php
                        $showButton = true;
                        if (!auth()->user()->isAdmin()) {
                            $allowed = array_filter([auth()->user()->specialization, 'in_progress', 'to_approve', 'done']);
                            if (!in_array($status, $allowed)) {
                                $showButton = false;
                            }
                        }
                    @endphp
                    @if($showButton)
                    @php
                        $sColors2 = ['writers' => 'border-purple-500/50 text-purple-300 hover:bg-purple-500/10', 'graphics' => 'border-pink-500/50 text-pink-300 hover:bg-pink-500/10', 'programmers' => 'border-blue-500/50 text-blue-300 hover:bg-blue-500/10', 'in_progress' => 'border-amber-500/50 text-amber-300 hover:bg-amber-500/10', 'to_approve' => 'border-teal-500/50 text-teal-300 hover:bg-teal-500/10', 'done' => 'border-green-500/50 text-green-300 hover:bg-green-500/10'];
                    @endphp
                    <button type="submit" name="status" value="{{ $status }}"
                            {{ $task->status === $status ? 'disabled' : '' }}
                            class="px-3 py-1.5 rounded-lg border text-xs font-medium transition-all {{ $task->status === $status ? 'bg-gray-700 border-gray-600 text-gray-400 cursor-not-allowed' : $sColors2[$status] }}">
                        {{ $label }}
                        @if($task->status === $status) ✓ @endif
                    </button>
                    @endif
                    @endforeach
                </form>
            </div>
            @endif

            <!-- Files section -->
            @if($hasFullAccess)
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        Pliki ({{ $task->files->count() }})
                    </h3>
                    @if($canUploadFile)
                    <button @click="uploadOpen = !uploadOpen"
                            class="text-xs px-3 py-1.5 rounded-lg bg-brand-500/10 border border-brand-500/30 text-brand-400 hover:bg-brand-500/20 transition-colors">
                        + Dodaj plik
                    </button>
                    @endif
                </div>

                <!-- Upload form -->
                @if($canUploadFile)
                <div x-show="uploadOpen" x-cloak class="mb-4 p-4 rounded-lg bg-gray-800 border border-gray-700">
                    <form method="POST" action="{{ route('files.store', $task) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="flex gap-3">
                            <input type="file" name="file" required
                                   class="flex-1 text-sm text-gray-400 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-500/10 file:text-brand-400 hover:file:bg-brand-500/20 cursor-pointer">
                            <button type="submit"
                                    class="px-4 py-1.5 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium transition-colors">
                                Prześlij
                            </button>
                            <button type="button" @click="uploadOpen = false"
                                    class="px-3 py-1.5 rounded-lg border border-gray-600 text-gray-400 hover:text-white text-sm transition-colors">
                                Anuluj
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Maks. rozmiar: 20MB</p>
                    </form>
                </div>
                @endif

                <!-- Files list -->
                @if($task->files->isEmpty())
                <p class="text-sm text-gray-600 text-center py-4">Brak dołączonych plików</p>
                @else
                <div class="space-y-2">
                    @foreach($task->files as $file)
                    @php
                        $fColors = ['pending' => 'text-amber-400 bg-amber-500/10', 'approved' => 'text-green-400 bg-green-500/10', 'rejected' => 'text-red-400 bg-red-500/10'];
                    @endphp
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-800/50 border border-gray-700/50">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-gray-700 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-white truncate">{{ $file->original_name }}</p>
                                <p class="text-xs text-gray-500">{{ $file->formatted_size }} · {{ $file->uploader->name }} · {{ $file->created_at->diffForHumans() }}</p>
                                @if($file->feedback)
                                <p class="text-xs text-red-400 mt-0.5">💬 {{ $file->feedback }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0 ml-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded {{ $fColors[$file->status] }}">{{ $file->status_label }}</span>

                            @if(auth()->user()->isLeaderOrAdmin())
                                @if($file->isPending())
                                <form method="POST" action="{{ route('files.approve', $file) }}">
                                    @csrf
                                    <button class="p-1.5 rounded text-green-400 hover:bg-green-500/10 transition-colors" title="Zatwierdź">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </form>
                                <button @click="rejectingFile = {{ $file->id }}" class="p-1.5 rounded text-red-400 hover:bg-red-500/10 transition-colors" title="Odrzuć">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                @endif
                            @endif

                            <a href="{{ route('files.download', $file) }}"
                               class="p-1.5 rounded text-gray-400 hover:text-white hover:bg-gray-700 transition-colors" title="Pobierz">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </a>

                            @if(auth()->user()->isAdmin() || (auth()->user()->isLeader() && auth()->user()->canManageCategory($task->getStatusCategory())) || $file->uploaded_by === auth()->id())
                            <form method="POST" action="{{ route('files.destroy', $file) }}" class="inline-block m-0" onsubmit="return confirm('Czy na pewno chcesz usunąć ten plik?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 rounded text-gray-400 hover:text-red-400 hover:bg-gray-700 transition-colors" title="Usuń plik">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                    <!-- Reject modal inline -->
                    <div x-show="rejectingFile === {{ $file->id }}" x-cloak
                         class="p-3 rounded-lg bg-red-900/20 border border-red-700/30">
                        <p class="text-xs font-medium text-red-300 mb-2">Powód odrzucenia (opcjonalnie):</p>
                        <form method="POST" action="{{ route('files.reject', $file) }}" class="flex gap-2">
                            @csrf
                            <input type="text" name="feedback" x-model="rejectFeedback"
                                   placeholder="Opisz problem..."
                                   class="flex-1 px-3 py-1.5 rounded bg-gray-800 border border-gray-700 text-white text-xs focus:outline-none focus:ring-1 focus:ring-red-500">
                            <button class="px-3 py-1.5 rounded bg-red-600 hover:bg-red-700 text-white text-xs font-medium">Odrzuć</button>
                            <button type="button" @click="rejectingFile = null" class="px-3 py-1.5 rounded border border-gray-600 text-gray-400 text-xs">Anuluj</button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            <!-- Comments section -->
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
                <h3 class="text-sm font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    Komentarze ({{ $task->comments->count() }})
                </h3>

                <!-- Add comment form -->
                <form method="POST" action="{{ route('comments.store', $task) }}" class="mb-5">
                    @csrf
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-400 to-purple-500 flex items-center justify-center text-xs font-bold text-white flex-shrink-0 mt-0.5">
                            {{ auth()->user()->initials }}
                        </div>
                        <div class="flex-1">
                            <textarea name="content" rows="2" required
                                      placeholder="Dodaj komentarz..."
                                      class="w-full px-3 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm resize-none transition-all"></textarea>
                            @error('content')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                            <button type="submit"
                                    class="mt-2 px-4 py-1.5 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium transition-colors">
                                Wyślij komentarz
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Comments list -->
                @if($task->comments->isEmpty())
                <p class="text-sm text-gray-600 text-center py-4">Brak komentarzy. Bądź pierwszym!</p>
                @else
                <div class="space-y-4">
                    @foreach($task->comments as $comment)
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-gray-600 to-gray-700 flex items-center justify-center text-xs font-bold text-white flex-shrink-0">
                            {{ $comment->user->initials }}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-sm font-medium text-white">{{ $comment->user->name }}</span>
                                <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-300">{{ $comment->content }}</p>
                            @if(auth()->id() === $comment->user_id || auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('comments.destroy', [$task, $comment]) }}" class="mt-1">
                                @csrf @method('DELETE')
                                <button class="text-xs text-gray-600 hover:text-red-400 transition-colors">
                                    Usuń
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar (1/3) -->
        <div class="space-y-4">
            <!-- Assignees -->
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
                <h3 class="text-sm font-semibold text-white mb-3">Przypisani</h3>
                @if($task->assignees->isEmpty())
                <p class="text-sm text-gray-600">Nieprzypisane</p>
                @else
                <div class="space-y-2">
                    @foreach($task->assignees as $assignee)
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-400 to-purple-500 flex items-center justify-center text-xs font-bold text-white">
                            {{ $assignee->initials }}
                        </div>
                        <div>
                            <p class="text-sm text-white">{{ $assignee->name }}</p>
                            <p class="text-xs text-gray-500">{{ $assignee->specialization_label }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Task info -->
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
                <h3 class="text-sm font-semibold text-white mb-3">Informacje</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Status</dt>
                        <dd class="text-white font-medium">{{ $task->status_label }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Priorytet</dt>
                        <dd class="text-white font-medium">{{ $task->priority_label }}</dd>
                    </div>
                    @if($task->due_date)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Termin</dt>
                        <dd class="{{ $task->due_date->isPast() && $task->status !== 'done' ? 'text-red-400' : 'text-white' }} font-medium">{{ $task->due_date->format('d.m.Y') }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Autor</dt>
                        <dd class="text-white">{{ $task->creator->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Pliki</dt>
                        <dd class="text-white">{{ $task->files->count() }}</dd>
                    </div>
                </dl>
            </div>

            <!-- GitHub Actions -->
            @if((auth()->user()->isAdmin() || (auth()->user()->isLeader() && auth()->user()->canManageCategory($task->getStatusCategory()))) && $task->status === 'done' && in_array($task->profession, ['writers', 'graphics', 'programmers']))
            <div class="bg-gray-900 rounded-xl border border-brand-500/30 p-5">
                <h3 class="text-sm font-semibold text-brand-400 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                    Eksport do GitHuba
                </h3>
                <form method="POST" action="{{ route('tasks.push-github', $task) }}" onsubmit="return confirm('Wszystkie zatwierdzone pliki z tego zadania zostaną wysłane do repozytorium GitHub na gałąź {{ $task->profession }}. Kontynuować?')">
                    @csrf
                    <button type="submit"
                            class="w-full px-3 py-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm transition-colors flex items-center justify-center gap-2">
                        Wypchnij pliki (Push)
                    </button>
                </form>
            </div>
            @endif

            <!-- Admin actions -->
            @if(auth()->user()->isAdmin())
            <div class="bg-gray-900 rounded-xl border border-red-900/30 p-5">
                <h3 class="text-sm font-semibold text-red-400 mb-3">Akcje administratora</h3>
                <form method="POST" action="{{ route('tasks.destroy', $task) }}"
                      onsubmit="return confirm('Czy na pewno chcesz usunąć to zadanie?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-full px-3 py-2 rounded-lg border border-red-700/50 text-red-400 hover:bg-red-900/20 text-sm transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Usuń zadanie
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
