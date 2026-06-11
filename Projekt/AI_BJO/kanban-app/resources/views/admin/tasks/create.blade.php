@extends('layouts.app')

@section('title', 'Nowe zadanie')
@section('page-title', 'Utwórz nowe zadanie')
@section('breadcrumb', 'Zarządzanie → Nowe zadanie')

@section('content')
<div class="p-6 max-w-2xl mx-auto">

    <a href="{{ route('admin.tasks.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-white mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Powrót do listy
    </a>

    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
        <h2 class="text-lg font-bold text-white mb-6">Szczegóły zadania</h2>

        <form method="POST" action="{{ route('admin.tasks.store') }}" x-data="taskForm()" @submit="validate">
            @csrf

            @if($errors->any())
            <div class="mb-5 p-4 rounded-lg bg-red-900/40 border border-red-700/50 text-sm text-red-300">
                <p class="font-medium mb-1">Popraw błędy:</p>
                @foreach($errors->all() as $error)<p>• {{ $error }}</p>@endforeach
            </div>
            @endif

            <!-- Title -->
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-300 mb-1.5">
                    Tytuł zadania <span class="text-red-400">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       minlength="3" maxlength="255"
                       placeholder="Krótki, opisowy tytuł zadania"
                       class="w-full px-4 py-2.5 rounded-lg bg-gray-800 border border-gray-700 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm transition-all"
                       :class="errors.title ? 'border-red-500' : ''"
                       @blur="validateField('title', $event.target.value, 3)">
                <p x-show="errors.title" x-text="errors.title" class="mt-1 text-xs text-red-400"></p>
            </div>

            <!-- Description -->
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-300 mb-1.5">
                    Opis zadania <span class="text-red-400">*</span>
                </label>
                <p class="text-xs text-gray-500 mb-2">Szczegółowy opis — czego oczekujesz od przypisanych użytkowników?</p>
                <textarea name="description" rows="5" required
                          placeholder="Opisz szczegółowo zadanie, oczekiwania, wymagania..."
                          class="w-full px-4 py-2.5 rounded-lg bg-gray-800 border border-gray-700 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm resize-none transition-all"
                          :class="errors.description ? 'border-red-500' : ''"
                          @blur="validateField('description', $event.target.value, 10)">{{ old('description') }}</textarea>
                <div class="flex items-center justify-between mt-1">
                    <p x-show="errors.description" x-text="errors.description" class="text-xs text-red-400"></p>
                    <p class="text-xs text-gray-600 ml-auto">Min. 10 znaków</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-5">
                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">
                        Profesja zadania (Dział) <span class="text-red-400">*</span>
                    </label>
                    <select name="status" required x-model="selectedProfession"
                            @change="document.querySelectorAll('.assignee-checkbox').forEach(cb => { if(cb.dataset.spec !== selectedProfession) cb.checked = false; })"
                            class="w-full px-4 py-2.5 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                        <option value="" disabled>Wybierz dział...</option>
                        @foreach($statuses as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Priority -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">
                        Priorytet <span class="text-red-400">*</span>
                    </label>
                    <select name="priority" required
                            class="w-full px-4 py-2.5 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>🟢 Niski</option>
                        <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>🟡 Średni</option>
                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>🔴 Wysoki</option>
                    </select>
                </div>
            </div>

            <!-- Due date -->
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Termin wykonania (opcjonalnie)</label>
                <input type="date" name="due_date" value="{{ old('due_date') }}"
                       min="{{ date('Y-m-d') }}"
                       class="w-full px-4 py-2.5 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
                @error('due_date')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <!-- Assignees -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-sm font-medium text-gray-300">
                        Przypisani użytkownicy
                    </label>
                    <input type="text" x-model="searchUser" placeholder="Szukaj użytkownika..."
                           class="px-3 py-1 w-48 text-xs rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-1 focus:ring-brand-500 placeholder-gray-500">
                </div>
                <p class="text-xs text-gray-500 mb-3">Wybierz członków zespołu którzy będą pracować nad tym zadaniem</p>

                @if($users->isEmpty())
                <p class="text-sm text-gray-600 italic">Brak dostępnych użytkowników w tej kategorii.</p>
                @else
                <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                    @foreach($users as $u)
                    <label x-show="(selectedProfession === '' || selectedProfession === '{{ $u->specialization }}') && {{ json_encode(strtolower($u->name)) }}.includes(searchUser.toLowerCase())"
                           class="flex items-center gap-3 p-2.5 rounded-lg border border-gray-700 hover:bg-gray-800 cursor-pointer transition-colors peer-checked:border-brand-500">
                        <input type="checkbox" name="assignees[]" value="{{ $u->id }}" data-spec="{{ $u->specialization }}"
                               {{ in_array($u->id, old('assignees', [])) ? 'checked' : '' }}
                               class="assignee-checkbox w-4 h-4 rounded border-gray-600 bg-gray-800 text-brand-500">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-brand-400 to-purple-500 flex items-center justify-center text-xs font-bold text-white">
                            {{ $u->initials }}
                        </div>
                        <div>
                            <p class="text-sm text-white">{{ $u->name }}</p>
                            <p class="text-xs text-gray-500">{{ $u->specialization_label }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Submit buttons -->
            <div class="flex gap-3 pt-4 border-t border-gray-800">
                <button type="submit"
                        class="px-6 py-2.5 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Utwórz zadanie
                </button>
                <a href="{{ route('admin.tasks.index') }}"
                   class="px-6 py-2.5 rounded-lg border border-gray-700 text-gray-300 hover:text-white hover:border-gray-600 text-sm font-medium transition-colors">
                    Anuluj
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function taskForm() {
    return {
        selectedProfession: '{{ old('status', auth()->user()->isLeader() ? auth()->user()->specialization : '') }}',
        searchUser: ''
    }
}
</script>
@endsection
