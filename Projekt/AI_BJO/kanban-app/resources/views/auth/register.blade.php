@extends('layouts.auth')

@section('title', 'Rejestracja')

@section('content')
<h2 class="text-2xl font-bold text-white mb-2">Utwórz konto</h2>
<p class="text-gray-400 text-sm mb-6">Dołącz do zespołu HIKIXMORI</p>

@if($errors->any())
<div class="mb-5 p-4 rounded-lg bg-red-900/40 border border-red-700/50 text-sm text-red-300">
    <p class="font-medium mb-1">Popraw poniższe błędy:</p>
    @foreach($errors->all() as $error)
        <p>• {{ $error }}</p>
    @endforeach
</div>
@endif

<form method="POST" action="{{ route('register.post') }}" x-data="registerForm()" @submit="validate">
    @csrf

    <!-- Name -->
    <div class="mb-4">
        <label for="name" class="block text-sm font-medium text-gray-300 mb-1.5">Nazwa użytkownika (Nick)</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}"
               placeholder="Jan Kowalski" required autocomplete="name"
               class="w-full px-4 py-2.5 rounded-lg bg-gray-800 border border-gray-700 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 transition-all text-sm"
               :class="errors.name ? 'border-red-500' : ''"
               @blur="if(!$event.target.value || $event.target.value.length < 2) errors.name='Min. 2 znaki.'; else errors.name=''">
        <p x-show="errors.name" x-text="errors.name" class="mt-1 text-xs text-red-400"></p>
    </div>

    <!-- Email -->
    <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">Adres e-mail</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}"
               placeholder="jan@email.pl" required autocomplete="email"
               class="w-full px-4 py-2.5 rounded-lg bg-gray-800 border border-gray-700 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 transition-all text-sm"
               :class="errors.email ? 'border-red-500' : ''"
               @blur="validateEmail">
        <p x-show="errors.email" x-text="errors.email" class="mt-1 text-xs text-red-400"></p>
    </div>

    <!-- Password -->
    <div class="mb-4">
        <label for="password" class="block text-sm font-medium text-gray-300 mb-1.5">Hasło</label>
        <div class="relative">
            <input id="password" :type="showPassword ? 'text' : 'password'" name="password"
                   placeholder="Min. 8 znaków" required autocomplete="new-password"
                   class="w-full px-4 py-2.5 pr-12 rounded-lg bg-gray-800 border border-gray-700 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 transition-all text-sm"
                   :class="errors.password ? 'border-red-500' : ''"
                   @input="updateStrength" @blur="validatePassword">
            <button type="button" @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-200">
                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
            </button>
        </div>
        <!-- Password strength bar -->
        <div class="mt-2 flex gap-1" x-show="password.length > 0">
            <div class="h-1 flex-1 rounded-full transition-colors duration-300"
                 :class="strength >= 1 ? (strength >= 3 ? 'bg-green-500' : strength >= 2 ? 'bg-amber-500' : 'bg-red-500') : 'bg-gray-700'"></div>
            <div class="h-1 flex-1 rounded-full transition-colors duration-300"
                 :class="strength >= 2 ? (strength >= 3 ? 'bg-green-500' : 'bg-amber-500') : 'bg-gray-700'"></div>
            <div class="h-1 flex-1 rounded-full transition-colors duration-300"
                 :class="strength >= 3 ? 'bg-green-500' : 'bg-gray-700'"></div>
        </div>
        <p x-show="errors.password" x-text="errors.password" class="mt-1 text-xs text-red-400"></p>
    </div>

    <!-- Confirm Password -->
    <div class="mb-5">
        <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-1.5">Potwierdź hasło</label>
        <input id="password_confirmation" type="password" name="password_confirmation"
               placeholder="Powtórz hasło" required autocomplete="new-password"
               class="w-full px-4 py-2.5 rounded-lg bg-gray-800 border border-gray-700 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 transition-all text-sm"
               :class="errors.confirm ? 'border-red-500' : ''"
               @blur="validateConfirm">
        <p x-show="errors.confirm" x-text="errors.confirm" class="mt-1 text-xs text-red-400"></p>
    </div>

    <!-- Specialization -->
    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-300 mb-2">Twoja specjalizacja <span class="text-red-400">*</span></label>
        <p class="text-xs text-gray-500 mb-3">Wybierz dział, do którego należysz w projekcie</p>

        <div class="grid grid-cols-3 gap-2">
            @php $specOptions = [
                ['value' => 'writers', 'label' => 'Writerzy', 'icon' => '✍️', 'desc' => 'Tworzenie treści i dialogów'],
                ['value' => 'graphics', 'label' => 'Graficy', 'icon' => '🎨', 'desc' => 'Projekt wizualny i UI'],
                ['value' => 'programmers', 'label' => 'Programiści', 'icon' => '💻', 'desc' => 'Implementacja i kod'],
            ]; @endphp

            @foreach($specOptions as $spec)
            <label class="relative cursor-pointer">
                <input type="radio" name="specialization" value="{{ $spec['value'] }}"
                       class="sr-only peer" {{ old('specialization') === $spec['value'] ? 'checked' : '' }}
                       @change="errors.specialization = ''; specialization = $event.target.value">
                <div class="p-3 rounded-xl border-2 border-gray-700 bg-gray-800/50 peer-checked:border-brand-500 peer-checked:bg-brand-500/10 transition-all text-center hover:border-gray-600">
                    <div class="text-2xl mb-1">{{ $spec['icon'] }}</div>
                    <p class="text-xs font-semibold text-white">{{ $spec['label'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 hidden sm:block">{{ $spec['desc'] }}</p>
                </div>
            </label>
            @endforeach
        </div>
        <p x-show="errors.specialization" x-text="errors.specialization" class="mt-1.5 text-xs text-red-400"></p>
    </div>

    <button type="submit" :disabled="loading"
            class="w-full py-2.5 px-4 rounded-lg bg-gradient-to-r from-brand-500 to-purple-600 text-white font-semibold text-sm hover:from-brand-600 hover:to-purple-700 transition-all disabled:opacity-50 flex items-center justify-center gap-2">
        <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <span x-text="loading ? 'Tworzenie konta...' : 'Utwórz konto'"></span>
    </button>
</form>

<div class="mt-5 text-center">
    <p class="text-gray-400 text-sm">
        Masz już konto?
        <a href="{{ route('login') }}" class="text-brand-400 hover:text-brand-300 font-medium transition-colors">Zaloguj się</a>
    </p>
</div>

<script>
function registerForm() {
    return {
        showPassword: false, loading: false, password: '', specialization: '', strength: 0,
        errors: { name: '', email: '', password: '', confirm: '', specialization: '' },
        updateStrength() {
            this.password = document.getElementById('password').value;
            let s = 0;
            if (this.password.length >= 8) s++;
            if (/[A-Z]/.test(this.password) && /[0-9]/.test(this.password)) s++;
            if (/[^A-Za-z0-9]/.test(this.password)) s++;
            this.strength = s;
        },
        validateEmail() {
            const v = document.getElementById('email').value;
            if (!v) { this.errors.email = 'E-mail jest wymagany.'; return false; }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) { this.errors.email = 'Podaj prawidłowy e-mail.'; return false; }
            this.errors.email = ''; return true;
        },
        validatePassword() {
            if (!this.password || this.password.length < 8) { this.errors.password = 'Hasło musi mieć min. 8 znaków.'; return false; }
            this.errors.password = ''; return true;
        },
        validateConfirm() {
            const v = document.getElementById('password_confirmation').value;
            if (v !== this.password) { this.errors.confirm = 'Hasła nie są identyczne.'; return false; }
            this.errors.confirm = ''; return true;
        },
        validate(e) {
            const nameEl = document.getElementById('name');
            let ok = true;
            if (!nameEl.value || nameEl.value.length < 2) { this.errors.name = 'Min. 2 znaki.'; ok = false; }
            if (!this.validateEmail()) ok = false;
            if (!this.validatePassword()) ok = false;
            if (!this.validateConfirm()) ok = false;
            if (!this.specialization) { this.errors.specialization = 'Wybierz specjalizację.'; ok = false; }
            if (!ok) { e.preventDefault(); return; }
            this.loading = true;
        }
    }
}
</script>
@endsection
