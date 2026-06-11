@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard - Podsumowanie')

@section('content')
<div class="p-6 max-w-7xl mx-auto space-y-6">

    <!-- Top Cards (All users) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Overdue Tasks Card -->
        <div class="bg-gray-900 rounded-xl border {{ $overdueTasks > 0 ? 'border-red-500/50' : 'border-gray-800' }} p-5 relative overflow-hidden group">
            @if($overdueTasks > 0)
            <div class="absolute inset-0 bg-red-500/5 group-hover:bg-red-500/10 transition-colors"></div>
            @endif
            <div class="relative flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 mb-1">Zadania przeterminowane</p>
                    <h3 class="text-3xl font-bold {{ $overdueTasks > 0 ? 'text-red-400' : 'text-white' }}">{{ $overdueTasks }}</h3>
                </div>
                <div class="w-12 h-12 rounded-lg {{ $overdueTasks > 0 ? 'bg-red-500/20 text-red-400' : 'bg-gray-800 text-gray-400' }} flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Unread Notifications Card -->
        <div class="bg-gray-900 rounded-xl border {{ $unreadNotifications->count() > 0 ? 'border-brand-500/50 animate-pulse' : 'border-gray-800' }} p-5 relative overflow-hidden group">
            @if($unreadNotifications->count() > 0)
            <div class="absolute inset-0 bg-brand-500/5 group-hover:bg-brand-500/10 transition-colors"></div>
            @endif
            <div class="relative flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 mb-1">Nowe komentarze</p>
                    <h3 class="text-3xl font-bold {{ $unreadNotifications->count() > 0 ? 'text-brand-400' : 'text-white' }}">{{ $unreadNotifications->count() }}</h3>
                </div>
                <div class="w-12 h-12 rounded-lg {{ $unreadNotifications->count() > 0 ? 'bg-brand-500/20 text-brand-400' : 'bg-gray-800 text-gray-400' }} flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Tasks -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 mb-1">Wszystkie zadania</p>
                    <h3 class="text-3xl font-bold text-white">{{ $totalTasks }}</h3>
                </div>
                <div class="w-12 h-12 rounded-lg bg-gray-800 flex items-center justify-center text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    @if($unreadNotifications->count() > 0)
    <!-- Notifications List -->
    <div class="bg-gray-900 rounded-xl border border-brand-500/30 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-800 bg-brand-900/10">
            <h3 class="text-sm font-semibold text-brand-300 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                Nieprzeczytane powiadomienia
            </h3>
        </div>
        <div class="divide-y divide-gray-800 max-h-80 overflow-y-auto">
            @foreach($unreadNotifications as $notification)
            <div class="relative group block hover:bg-gray-800/50 transition-colors">
                <a href="{{ route('notifications.read', $notification->id) }}" class="flex items-start gap-4 p-4 pr-12">
                    <div class="w-8 h-8 rounded-full bg-brand-500/20 text-brand-400 flex items-center justify-center flex-shrink-0 mt-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-300">
                            <strong class="text-white">{{ $notification->data['author'] ?? 'System' }}</strong>: 
                            <strong class="text-brand-300">"{{ $notification->data['task_title'] }}"</strong>
                        </p>
                        <p class="text-xs text-gray-500 mt-1 italic">"{{ $notification->data['content'] ?? '' }}..."</p>
                        <p class="text-xs text-gray-600 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                </a>

                <form method="POST" action="{{ route('notifications.dismiss', $notification->id) }}" class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                    @csrf
                    <button type="submit" class="p-1 rounded text-gray-500 hover:text-red-400 hover:bg-red-500/10 transition-colors" title="Usuń powiadomienie">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Tasks Status Chart -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
            <h3 class="text-sm font-semibold text-white mb-6">Status zadań</h3>
            <div class="h-64 relative">
                <canvas id="tasksChart"></canvas>
            </div>
        </div>

        <!-- Priorities -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
            <h3 class="text-sm font-semibold text-white mb-6">Priorytety</h3>
            <div class="space-y-4">
                @php $totalP = array_sum($priorityCounts); @endphp
                @foreach(['high' => ['🔴 Wysoki', 'bg-red-500', 'text-red-400'], 'medium' => ['🟡 Średni', 'bg-amber-500', 'text-amber-400'], 'low' => ['🟢 Niski', 'bg-green-500', 'text-green-400']] as $k => $c)
                @php $pct = $totalP > 0 ? round(($priorityCounts[$k] / $totalP) * 100) : 0; @endphp
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="{{ $c[2] }} font-medium">{{ $c[0] }}</span>
                        <span class="text-gray-400">{{ $priorityCounts[$k] }} ({{ $pct }}%)</span>
                    </div>
                    <div class="w-full bg-gray-800 rounded-full h-2">
                        <div class="{{ $c[1] }} h-2 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    @if(auth()->user()->isAdmin() || auth()->user()->isLeader())
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Admin/Leader Tools: Pending files -->
        <div class="lg:col-span-2 bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-800 flex justify-between items-center">
                <h3 class="text-sm font-semibold text-white">Oczekujące pliki ({{ $pendingFilesCount }})</h3>
                <a href="{{ route('admin.tasks.index') }}" class="text-xs text-brand-400 hover:text-brand-300">Zarządzaj</a>
            </div>
            @if($pendingFiles->isEmpty())
            <div class="p-6 text-center text-sm text-gray-500">Brak plików oczekujących na zatwierdzenie.</div>
            @else
            <div class="divide-y divide-gray-800">
                @foreach($pendingFiles as $file)
                <div class="p-4 flex items-center justify-between hover:bg-gray-800/50 transition-colors">
                    <div class="min-w-0 pr-4">
                        <p class="text-sm font-medium text-white truncate">{{ $file->original_name }}</p>
                        <p class="text-xs text-gray-500 truncate">Zadanie: {{ $file->task->title }}</p>
                        <p class="text-xs text-gray-500">Dodane przez {{ $file->uploader->name }} ({{ $file->created_at->diffForHumans() }})</p>
                    </div>
                    <a href="{{ route('tasks.show', $file->task_id) }}" class="flex-shrink-0 px-3 py-1.5 rounded-lg bg-gray-800 text-gray-300 hover:text-white text-xs font-medium">Przejrzyj</a>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        @if(auth()->user()->isAdmin())
        <!-- Admin Tools: Users -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
            <h3 class="text-sm font-semibold text-white mb-6">Zespół</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 rounded-lg bg-gray-800/50">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider">Liderzy</p>
                        <p class="text-2xl font-bold text-white">{{ $totalLeaders }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-purple-500/20 text-purple-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                </div>
                <div class="flex items-center justify-between p-4 rounded-lg bg-gray-800/50">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider">Pracownicy</p>
                        <p class="text-2xl font-bold text-white">{{ $totalUsers }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('tasksChart').getContext('2d');
    const data = {!! json_encode(array_values($tasksByStatus)) !!};
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(d => d.label),
            datasets: [{
                data: data.map(d => d.count),
                backgroundColor: [
                    '#a855f7', // purple (writers)
                    '#ec4899', // pink (graphics)
                    '#3b82f6', // blue (programmers)
                    '#f59e0b', // amber (in_progress)
                    '#14b8a6', // teal (to_approve)
                    '#22c55e', // green (done)
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { color: '#9ca3af', font: { family: "'Inter', sans-serif", size: 12 } } }
            },
            cutout: '75%'
        }
    });
</script>
@endpush
