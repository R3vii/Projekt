@extends('layouts.app')

@section('title', 'Edytuj: ' . $user->name)
@section('page-title', 'Edytuj użytkownika')
@section('breadcrumb', 'Użytkownicy → Edycja')

@section('content')
<div class="p-6 max-w-xl mx-auto">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-white mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Powrót do listy
    </a>

    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-brand-400 to-purple-500 flex items-center justify-center text-lg font-bold text-white">
                {{ $user->initials }}
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">{{ $user->name }}</h2>
                <p class="text-gray-400 text-sm">{{ $user->email }}</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-5 p-4 rounded-lg bg-red-900/40 border border-red-700/50 text-sm text-red-300">
            @foreach($errors->all() as $error)<p>• {{ $error }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Imię i nazwisko <span class="text-red-400">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required minlength="2"
                       class="w-full px-4 py-2.5 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Adres e-mail <span class="text-red-400">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full px-4 py-2.5 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Rola <span class="text-red-400">*</span></label>
                <select name="role" required class="w-full px-4 py-2.5 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                    <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>Użytkownik</option>
                    <option value="leader" {{ old('role', $user->role) === 'leader' ? 'selected' : '' }}>Lider</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Specjalizacja <span class="text-red-400">*</span></label>
                <select name="specialization" required class="w-full px-4 py-2.5 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                    <option value="writers" {{ old('specialization', $user->specialization) === 'writers' ? 'selected' : '' }}>✍️ Writerzy</option>
                    <option value="graphics" {{ old('specialization', $user->specialization) === 'graphics' ? 'selected' : '' }}>🎨 Graficy</option>
                    <option value="programmers" {{ old('specialization', $user->specialization) === 'programmers' ? 'selected' : '' }}>💻 Programiści</option>
                </select>
            </div>

            <div class="mb-6">
                <label class="flex items-center gap-3 cursor-pointer">
                    <div class="relative">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-10 h-5 rounded-full bg-gray-700 peer-checked:bg-brand-500 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:w-4 after:h-4 after:transition-all peer-checked:after:translate-x-5"></div>
                    </div>
                    <span class="text-sm text-gray-300">Konto aktywne</span>
                </label>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Dostęp do projektów <span class="text-brand-400">*</span></label>
                @php $userProjectIds = $user->projects->pluck('id')->toArray(); @endphp
                <div class="bg-gray-800 border @error('projects') border-red-500 @else border-gray-700 @enderror rounded-lg overflow-hidden">
                    <div class="max-h-48 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-600 scrollbar-track-gray-800 divide-y divide-gray-700/50">
                        @foreach($projects as $project)
                        <label class="flex items-center justify-between p-3 cursor-pointer hover:bg-gray-700/30 transition-colors group">
                            <span class="text-sm text-gray-300 font-medium group-hover:text-white transition-colors">{{ $project->name }}</span>
                            <div class="relative flex items-center">
                                <input type="checkbox" name="projects[]" value="{{ $project->id }}" 
                                       {{ (is_array(old('projects')) && in_array($project->id, old('projects'))) || (!old('projects') && in_array($project->id, $userProjectIds)) ? 'checked' : '' }}
                                       class="w-5 h-5 rounded border-gray-600 bg-gray-900 text-brand-500 focus:ring-brand-500 focus:ring-offset-gray-800 transition-all cursor-pointer">
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @error('projects') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                <p class="mt-2 text-xs text-gray-500">Zaznacz na liście wszystkie projekty, do których użytkownik ma mieć przypisany dostęp.</p>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-800">
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold transition-colors">
                    Zapisz zmiany
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-6 py-2.5 rounded-lg border border-gray-700 text-gray-300 hover:text-white text-sm transition-colors">Anuluj</a>
            </div>
        </form>
    </div>
</div>
@endsection
