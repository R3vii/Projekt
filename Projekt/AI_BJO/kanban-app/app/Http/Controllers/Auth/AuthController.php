<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) return redirect()->route('board.index');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Adres e-mail jest wymagany.',
            'email.email' => 'Podaj prawidłowy adres e-mail.',
            'password.required' => 'Hasło jest wymagane.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if (!Auth::user()->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Twoje konto zostało dezaktywowane.'])->onlyInput('email');
            }
            $request->session()->regenerate();
            return redirect()->intended(route('board.index'));
        }

        return back()->withErrors(['email' => 'Podane dane logowania są nieprawidłowe.'])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) return redirect()->route('board.index');
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'specialization' => ['required', 'in:writers,graphics,programmers'],
        ], [
            'name.required' => 'Imię i nazwisko jest wymagane.',
            'name.min' => 'Imię musi mieć co najmniej 2 znaki.',
            'email.required' => 'Adres e-mail jest wymagany.',
            'email.email' => 'Podaj prawidłowy adres e-mail.',
            'email.unique' => 'Ten adres e-mail jest już zarejestrowany.',
            'password.required' => 'Hasło jest wymagane.',
            'password.confirmed' => 'Hasła nie są identyczne.',
            'password.min' => 'Hasło musi mieć co najmniej 8 znaków.',
            'specialization.required' => 'Wybierz swoją specjalizację.',
            'specialization.in' => 'Wybierz prawidłową specjalizację.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'specialization' => $request->specialization,
            'is_active' => true,
        ]);

        Auth::login($user);
        return redirect()->route('board.index')->with('success', 'Konto zostało utworzone! Witaj w systemie.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
