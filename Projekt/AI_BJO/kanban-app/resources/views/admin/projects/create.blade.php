@extends('layouts.app')

@section('title', 'Dodaj projekt')
@section('page-title', 'Dodaj nowy projekt')
@section('breadcrumb', 'Zarządzanie projektami → Nowy')

@section('content')
<div class="p-6 max-w-2xl mx-auto">
    <a href="{{ route('admin.projects.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-white mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Powrót
    </a>

    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
        <form method="POST" action="{{ route('admin.projects.store') }}" class="space-y-6">
            @csrf
            
            <div>
                <label for="name" class="block text-sm font-medium text-gray-300 mb-1.5">Nazwa Projektu <span class="text-brand-400">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       class="w-full px-4 py-2.5 bg-gray-800 border @error('name') border-red-500 @else border-gray-700 @enderror rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-300 mb-1.5">Opis (opcjonalnie)</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-4 py-2.5 bg-gray-800 border @error('description') border-red-500 @else border-gray-700 @enderror rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-brand-500">{{ old('description') }}</textarea>
                @error('description') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="p-4 rounded-lg bg-gray-800/50 border border-gray-700 space-y-4" x-data="{ autoCreate: false }">
                <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                    Integracja z GitHubem
                </h3>
                
                <div>
                    <label for="github_token" class="block text-sm font-medium text-gray-300 mb-1.5">Token dostępu (Personal Access Token z uprawnieniem 'repo')</label>
                    <input type="password" id="github_token" name="github_token" value="{{ old('github_token') }}"
                           class="w-full px-4 py-2.5 bg-gray-800 border @error('github_token') border-red-500 @else border-gray-700 @enderror rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @error('github_token') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    <p class="text-xs text-gray-500 mt-1">Wymagane do automatycznego tworzenia repozytorium lub wypychania zadań.</p>
                </div>

                <div class="flex items-center gap-2 mt-4 mb-4">
                    <input type="checkbox" id="auto_create_repo" name="auto_create_repo" value="1" x-model="autoCreate" class="w-4 h-4 text-brand-500 bg-gray-800 border-gray-700 rounded focus:ring-brand-500">
                    <label for="auto_create_repo" class="text-sm text-gray-300">Utwórz repozytorium automatycznie (tylko prywatne)</label>
                </div>

                <div>
                    <label for="github_repo" class="block text-sm font-medium text-gray-300 mb-1.5" x-text="autoCreate ? 'Nazwa nowego repozytorium (bez nazwy użytkownika)' : 'Pełna nazwa repozytorium (np. uzytkownik/repo)'">Pełna nazwa repozytorium (np. uzytkownik/repo)</label>
                    <input type="text" id="github_repo" name="github_repo" value="{{ old('github_repo') }}"
                           class="w-full px-4 py-2.5 bg-gray-800 border @error('github_repo') border-red-500 @else border-gray-700 @enderror rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @error('github_repo') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-800">
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-brand-500 hover:bg-brand-600 text-white font-medium transition-colors">
                    Zapisz projekt
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
