@extends('layouts.auth')

@section('title', 'Logowanie')

@section('content')
<h2 class="text-2xl font-bold text-white mb-2">Witaj z powrotem</h2>
<p class="text-gray-400 text-sm mb-8">Zaloguj się do systemu HIKIXMORI Kanban</p>

@if($errors->any())
<div class="mb-6 p-4 rounded-lg bg-red-900/40 border border-red-700/50 text-sm text-red-300">
    <div class="flex items-center gap-2 mb-1 font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Błąd logowania
    </div>
    @foreach($errors->all() as $error)
        <p>{{ $error }}</p>
    @endforeach
</div>
@endif

<form method="POST" action="{{ route('login.post') }}" x-data="loginForm()" @submit="validate">
    @csrf

    <!-- Email -->
    <div class="mb-5">
        <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">
            Adres e-mail
        </label>
        <input
            id="email"
            type="email"
            name="email"
            value="{{ old('email') }}"
            placeholder="twoj@email.pl"
            required
            autocomplete="email"
            class="w-full px-4 py-2.5 rounded-lg bg-gray-800 border border-gray-700 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all text-sm"
            :class="errors.email ? 'border-red-500 focus:ring-red-500' : ''"
            @blur="validateEmail"
        >
        <p x-show="errors.email" x-text="errors.email" class="mt-1.5 text-xs text-red-400"></p>
    </div>

    <!-- Password -->
    <div class="mb-5">
        <label for="password" class="block text-sm font-medium text-gray-300 mb-1.5">
            Hasło
        </label>
        <div class="relative">
            <input
                id="password"
                :type="showPassword ? 'text' : 'password'"
                name="password"
                placeholder="••••••••"
                required
                autocomplete="current-password"
                class="w-full px-4 py-2.5 pr-12 rounded-lg bg-gray-800 border border-gray-700 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all text-sm"
                :class="errors.password ? 'border-red-500' : ''"
                @blur="validatePassword"
            >
            <button type="button" @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-200 transition-colors">
                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
            </button>
        </div>
        <p x-show="errors.password" x-text="errors.password" class="mt-1.5 text-xs text-red-400"></p>
    </div>

    <!-- Remember me -->
    <div class="flex items-center justify-between mb-6">
        <label class="flex items-center gap-2 text-sm text-gray-400 cursor-pointer">
            <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-gray-800 border-gray-700 text-brand-500">
            Zapamiętaj mnie
        </label>
    </div>

    <!-- Submit -->
    <button type="submit"
            :disabled="loading"
            class="w-full py-2.5 px-4 rounded-lg bg-gradient-to-r from-brand-500 to-purple-600 text-white font-semibold text-sm hover:from-brand-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 focus:ring-offset-gray-900 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
        <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <span x-text="loading ? 'Logowanie...' : 'Zaloguj się'"></span>
    </button>
</form>

<div class="mt-6 text-center">
    <p class="text-gray-400 text-sm">
        Nie masz konta?
        <a href="{{ route('register') }}" class="text-brand-400 hover:text-brand-300 font-medium transition-colors">
            Zarejestruj się
        </a>
    </p>
</div>

<!-- Demo credentials -->
<div class="mt-6 p-4 rounded-lg bg-gray-800/50 border border-gray-700/50">
    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Dane demo</p>
    <div class="space-y-1.5 text-xs text-gray-500">
        <p><span class="text-gray-300 font-medium">Admin:</span> admin@kanban.pl / admin123</p>
        <p><span class="text-gray-300 font-medium">Lider Writers:</span> lider.writers@kanban.pl / lider123</p>
        <p><span class="text-gray-300 font-medium">User PB:</span> pb@kanban.pl / user123</p>
    </div>
</div>

<script>
function loginForm() {
    return {
        showPassword: false,
        loading: false,
        errors: { email: '', password: '' },
        validateEmail() {
            const el = document.getElementById('email');
            if (!el.value) { this.errors.email = 'E-mail jest wymagany.'; return false; }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(el.value)) { this.errors.email = 'Podaj prawidłowy e-mail.'; return false; }
            this.errors.email = ''; return true;
        },
        validatePassword() {
            const el = document.getElementById('password');
            if (!el.value) { this.errors.password = 'Hasło jest wymagane.'; return false; }
            this.errors.password = ''; return true;
        },
        validate(e) {
            const emailOk = this.validateEmail();
            const passOk = this.validatePassword();
            if (!emailOk || !passOk) { e.preventDefault(); return; }
            this.loading = true;
        }
    }
}
</script>
@endsection
