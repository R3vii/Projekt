@extends('layouts.app')

@section('title', 'Mój profil')
@section('page-title', 'Mój profil')
@section('breadcrumb', 'Ustawienia konta')

@section('content')
<div class="p-6 max-w-2xl mx-auto">

    <!-- Profile info card -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 mb-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-brand-400 to-purple-600 flex items-center justify-center text-2xl font-bold text-white">
                {{ $user->initials }}
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">{{ $user->name }}</h2>
                <p class="text-gray-400 text-sm">{{ $user->role_label }}@if($user->specialization) · {{ $user->specialization_label }}@endif</p>
                <p class="text-gray-500 text-xs mt-0.5">Konto od {{ $user->created_at->format('d.m.Y') }}</p>
            </div>
        </div>

        <h3 class="text-sm font-semibold text-white mb-4">Dane podstawowe</h3>

        @if(session('success'))
        <div class="mb-4 p-3 rounded-lg bg-green-900/40 border border-green-700/50 text-sm text-green-300">
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Nazwa użytkownika (Nick)</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full px-4 py-2.5 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm transition-all">
                @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Adres e-mail</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full px-4 py-2.5 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm transition-all">
                @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Specjalizacja</label>
                <p class="px-4 py-2.5 rounded-lg bg-gray-800/50 border border-gray-700 text-gray-400 text-sm">{{ $user->specialization_label }}</p>
                <p class="text-xs text-gray-600 mt-1">Specjalizację może zmienić tylko Administrator.</p>
            </div>

            <button type="submit"
                    class="px-5 py-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium transition-colors">
                Zapisz zmiany
            </button>
        </form>
    </div>

    <!-- Change password card -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
        <h3 class="text-sm font-semibold text-white mb-4">Zmień hasło</h3>

        <form method="POST" action="{{ route('profile.password') }}">
            @csrf @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Aktualne hasło</label>
                <input type="password" name="current_password" required
                       class="w-full px-4 py-2.5 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm transition-all">
                @error('current_password')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Nowe hasło</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-2.5 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm transition-all">
                @error('password')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Potwierdź nowe hasło</label>
                <input type="password" name="password_confirmation" required
                       class="w-full px-4 py-2.5 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm transition-all">
            </div>

            <button type="submit"
                    class="px-5 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium transition-colors">
                Zmień hasło
            </button>
        </form>
    </div>
</div>
@endsection
