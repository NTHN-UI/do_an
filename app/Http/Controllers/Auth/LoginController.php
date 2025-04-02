<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        Log::info("Xin chao");
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $credentials = [
            $field => $request->login,
            'password' => $request->password,
            'is_active' => true
        ];

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            Log::info("Xin chao");

            // Kiểm tra role và chuyển hướng phù hợp
            if ($user->isSuperAdmin() || $user->isSchoolAdmin()) {
                return redirect()->intended(route('home.index'));
            } elseif ($user->isTeacher()) {
                return redirect()->intended(route('home.index'));
            } elseif ($user->isStudent()) {
                return redirect()->intended(route('home.index'));
            }
        }

        throw ValidationException::withMessages([
            'login' => [trans('auth.failed')],
        ]);

    }
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Bạn đã đăng xuất thành công!');
    }

}
