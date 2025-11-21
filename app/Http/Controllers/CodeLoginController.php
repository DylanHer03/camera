<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;


class CodeLoginController extends Controller
{
    public function showLoginForm()
    {
        return Inertia::render('Auth/LoginWithCode');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:8'],
        ]);

        $user = User::where('access_code', strtoupper($validated['code']))->first();

        if (!$user) {
            return back()->withErrors([
                'code' => 'Codice non valido.',
            ])->onlyInput('code');
        }

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
