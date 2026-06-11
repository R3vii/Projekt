<!DOCTYPE html>
<html lang="pl" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="System zarządzania zadaniami Kanban dla projektu HIKIXMORI">
    <title>@yield('title', 'Dashboard') — HIKIXMORI Kanban</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            50: '#f0f4ff', 100: '#e0e9ff', 200: '#c7d5ff',
                            300: '#a3b8ff', 400: '#7a93ff', 500: '#5a6dff',
                            600: '#4b5ae8', 700: '#3d48cc', 800: '#333ca3',
                            900: '#2e3680', 950: '#1c1f50',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Sortable.js for drag & drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-item { transition: all 0.15s ease; }
        .sidebar-item:hover { background: rgba(255,255,255,0.08); }
        .sidebar-item.active { background: rgba(90,109,255,0.2); border-left: 3px solid #5a6dff; }
        .task-card { transition: transform 0.15s ease, box-shadow 0.15s ease; }
        .task-card:hover { transform: translateY(-1px); box-shadow: 0 4px 20px rgba(0,0,0,0.3); }
        .sortable-ghost { opacity: 0.4; }
        .kanban-column { min-height: 500px; }
        [x-cloak] { display: none !important; }
        .flash-success { animation: slideIn 0.3s ease; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        ::-webkit-scrollbar { width: 6px; } ::-webkit-scrollbar-track { background: #1a1a2e; }
        ::-webkit-scrollbar-thumb { background: #3d48cc; border-radius: 3px; }
    </style>
    @stack('styles')
</head>

<body class="bg-gray-950 text-gray-100 h-full" x-data="{ sidebarOpen: true, notifications: [] }">

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 flex-shrink-0 bg-gray-900 border-r border-gray-800 flex flex-col"
               :class="sidebarOpen ? 'w-64' : 'w-16'">

            <!-- Logo -->
            <div class="flex items-center gap-3 px-4 py-5 border-b border-gray-800">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-brand-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                </div>
                <div x-show="sidebarOpen" class="overflow-hidden">
                    <p class="text-white font-bold text-sm tracking-wide">HIKIXMORI</p>
                    <p class="text-gray-400 text-xs">System Kanban</p>
                </div>
            </div>

            <!-- User info -->
            <div class="px-3 py-4 border-b border-gray-800" x-show="sidebarOpen">
                <div class="flex items-center gap-3 px-2 py-2 rounded-lg bg-gray-800">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-400 to-purple-500 flex items-center justify-center text-xs font-bold text-white flex-shrink-0">
                        {{ auth()->user()->initials }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ auth()->user()->role_label }}</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a href="{{ route('board.index') }}"
                   class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 text-sm {{ request()->routeIs('board.*') ? 'active text-white' : 'hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                    <span x-show="sidebarOpen">Tablica Kanban</span>
                </a>

                <a href="{{ route('dashboard') }}"
                   class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 text-sm {{ request()->routeIs('dashboard') ? 'active text-white' : 'hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span x-show="sidebarOpen">Dashboard</span>
                </a>

                @if(session('active_project_id'))
                <a href="{{ route('projects.download', session('active_project_id')) }}"
                   class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 text-sm hover:text-white">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span x-show="sidebarOpen">Pobierz projekt (main)</span>
                </a>
                @endif

                @if(auth()->user()->isLeaderOrAdmin())
                <div class="pt-2">
                    <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2" x-show="sidebarOpen">Zarządzanie</p>

                    <a href="{{ route('admin.tasks.index') }}"
                       class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 text-sm {{ request()->routeIs('admin.tasks.*') ? 'active text-white' : 'hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span x-show="sidebarOpen">Zarządzaj zadaniami</span>
                    </a>
                @endif

                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.projects.index') }}"
                       class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 text-sm {{ request()->routeIs('admin.projects.*') ? 'active text-white' : 'hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span x-show="sidebarOpen">Projekty</span>
                    </a>

                    <a href="{{ route('admin.users.index') }}"
                       class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 text-sm {{ request()->routeIs('admin.users.*') ? 'active text-white' : 'hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span x-show="sidebarOpen">Użytkownicy</span>
                    </a>
                @endif
                @if(auth()->user()->isLeaderOrAdmin())
                </div>
                @endif

                <div class="pt-2">
                    <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2" x-show="sidebarOpen">Konto</p>
                    <a href="{{ route('profile.edit') }}"
                       class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-300 text-sm {{ request()->routeIs('profile.*') ? 'active text-white' : 'hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span x-show="sidebarOpen">Profil</span>
                    </a>
                </div>
            </nav>

            <!-- Logout -->
            <div class="px-3 py-4 border-t border-gray-800">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="sidebar-item w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 hover:text-red-400 text-sm transition-colors">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span x-show="sidebarOpen">Wyloguj się</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top bar -->
            <header class="flex-shrink-0 h-16 bg-gray-900 border-b border-gray-800 flex items-center justify-between px-6">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="text-gray-400 hover:text-white transition-colors p-1 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-sm font-semibold text-white">@yield('page-title', 'Dashboard')</h1>
                        @hasSection('breadcrumb')
                        <p class="text-xs text-gray-400">@yield('breadcrumb')</p>
                        @endif
                    </div>
                </div>
                
                @php
                    $currentUser = auth()->user();
                    $userProjects = collect();
                    $activeProject = null;
                    if ($currentUser) {
                        if ($currentUser->isAdmin()) {
                            $userProjects = \App\Models\Project::orderBy('name')->get();
                        } else {
                            $userProjects = $currentUser->projects()->orderBy('name')->get();
                        }
                        $activeProjectId = session('active_project_id') ?? ($userProjects->first()->id ?? null);
                        if (!session()->has('active_project_id') && $activeProjectId) {
                            session(['active_project_id' => $activeProjectId]);
                        }
                        $activeProject = $userProjects->firstWhere('id', $activeProjectId);
                    }
                @endphp

                <div class="flex items-center gap-4">
                    @if($userProjects->isNotEmpty())
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-800 border border-gray-700 text-sm text-gray-300 hover:text-white transition-colors">
                            <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            {{ $activeProject ? $activeProject->name : 'Wybierz projekt' }}
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-cloak class="absolute right-0 mt-2 w-56 rounded-lg bg-gray-800 border border-gray-700 shadow-xl z-50 overflow-hidden">
                            <div class="px-3 py-2 bg-gray-900/50 border-b border-gray-700 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                Twoje Projekty
                            </div>
                            <div class="max-h-60 overflow-y-auto">
                                @foreach($userProjects as $p)
                                <form method="POST" action="{{ route('projects.switch') }}">
                                    @csrf
                                    <input type="hidden" name="project_id" value="{{ $p->id }}">
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm {{ $activeProjectId == $p->id ? 'bg-brand-500/20 text-brand-300 font-medium' : 'text-gray-300 hover:bg-gray-700' }}">
                                        {{ $p->name }}
                                    </button>
                                </form>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="w-px h-6 bg-gray-800 hidden sm:block"></div>
                    @endif

                    <span class="text-xs text-gray-400 hidden sm:block">{{ auth()->user()->specialization_label }}</span>
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-400 to-purple-500 flex items-center justify-center text-xs font-bold text-white">
                        {{ auth()->user()->initials }}
                    </div>
                </div>
            </header>

            <!-- Flash messages -->
            @if(session('success'))
            <div class="flash-success mx-6 mt-4 flex items-center gap-3 px-4 py-3 rounded-lg bg-green-900/50 border border-green-700 text-green-300 text-sm"
                 x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="mx-6 mt-4 flex items-center gap-3 px-4 py-3 rounded-lg bg-red-900/50 border border-red-700 text-red-300 text-sm">
                <svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('error') }}
            </div>
            @endif

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
