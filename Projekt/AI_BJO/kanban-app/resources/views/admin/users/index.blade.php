@extends('layouts.app')

@section('title', 'Użytkownicy')
@section('page-title', 'Zarządzanie użytkownikami')
@section('breadcrumb', 'Panel administracyjny')

@section('content')
<div class="p-6">

    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('admin.users.create') }}"
           class="px-4 py-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Dodaj użytkownika / Lidera
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Szukaj po nazwie lub e-mailu..."
                   class="flex-1 min-w-48 px-3 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
            <select name="role" class="px-3 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                <option value="">Wszystkie role</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                <option value="leader" {{ request('role') === 'leader' ? 'selected' : '' }}>Lider</option>
                <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>Użytkownik</option>
            </select>
            <select name="specialization" class="px-3 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                <option value="">Wszystkie specjalizacje</option>
                <option value="writers" {{ request('specialization') === 'writers' ? 'selected' : '' }}>Writerzy</option>
                <option value="graphics" {{ request('specialization') === 'graphics' ? 'selected' : '' }}>Graficy</option>
                <option value="programmers" {{ request('specialization') === 'programmers' ? 'selected' : '' }}>Programiści</option>
            </select>
            <select name="status" class="px-3 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                <option value="">Wszystkie statusy</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktywne</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nieaktywne</option>
            </select>
            <button type="submit" class="px-4 py-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium transition-colors">Filtruj</button>
            @if(request()->hasAny(['search','role','specialization','status']))
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-gray-300 text-sm font-medium transition-colors">Wyczyść</a>
            @endif
        </form>
    </div>

    <!-- Users table -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-800">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Użytkownik</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Rola</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Specjalizacja</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Data rejestracji</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Akcje</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($users as $user)
                <tr class="hover:bg-gray-800/50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-400 to-purple-500 flex items-center justify-center text-xs font-bold text-white flex-shrink-0">
                                {{ $user->initials }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-white">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        @php $roleColors = ['admin' => 'text-red-300 bg-red-500/10', 'leader' => 'text-amber-300 bg-amber-500/10', 'user' => 'text-blue-300 bg-blue-500/10']; @endphp
                        <span class="text-xs font-medium px-2 py-1 rounded {{ $roleColors[$user->role] ?? '' }}">{{ $user->role_label }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ $user->specialization_label }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-medium px-2 py-1 rounded {{ $user->is_active ? 'text-green-300 bg-green-500/10' : 'text-gray-500 bg-gray-800' }}">
                            {{ $user->is_active ? '● Aktywny' : '○ Nieaktywny' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ $user->created_at->format('d.m.Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-700 transition-colors" title="Edytuj">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            @if($user->id !== auth()->id() && !$user->isAdmin())
                            <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                                @csrf
                                <button type="submit"
                                        class="p-1.5 rounded-lg transition-colors {{ $user->is_active ? 'text-gray-400 hover:text-amber-400 hover:bg-amber-900/20' : 'text-gray-400 hover:text-green-400 hover:bg-green-900/20' }}"
                                        title="{{ $user->is_active ? 'Dezaktywuj' : 'Aktywuj' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $user->is_active ? 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636' : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' }}"/>
                                    </svg>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Czy na pewno chcesz usunąć tego użytkownika? Ta operacja usunie również jego dane.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-900/20 transition-colors"
                                        title="Usuń">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-gray-600 text-sm">
                        Brak użytkowników spełniających kryteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($users->hasPages())
        <div class="px-4 py-3 border-t border-gray-800">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
