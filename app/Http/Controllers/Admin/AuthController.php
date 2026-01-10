<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = config('admin.login');
        $password = config('admin.password');

        if ($credentials['email'] === $login && $credentials['password'] === $password) {
            $request->session()->put('admin_authenticated', true);
            $request->session()->regenerate();

            return redirect()->route('admin.halls.index');
        }

        return back()
            ->withErrors(['email' => 'Неверный логин или пароль.'])
            ->withInput();
    }

    public function logout(Request $request)
    {
        $request->session()->forget('admin_authenticated');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
